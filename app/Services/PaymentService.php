<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PaymentService
{
    private const ALFABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa I, O, 0, 1

    public function __construct(private readonly BookingService $bookingService)
    {
    }

    private function siapkan(): void
    {
        if (blank(config('midtrans.server_key'))) {
            throw new BookingException('pembayaran', 'Kunci Midtrans belum diatur di berkas .env.');
        }

        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        Config::$is3ds = (bool) config('midtrans.is_3ds');
    }

    /**
     * Membuat transaksi Snap untuk satu booking. Jumlah bayar SELALU diambil dari
     * database (total_harga), bukan dari browser. Bila sudah ada percobaan bayar
     * yang masih Pending dan punya token, token itu dipakai ulang.
     */
    public function buatTransaksi(Booking $booking): Payment
    {
        $this->siapkan();

        return DB::transaction(function () use ($booking) {
            $booking = Booking::with(['console', 'user'])->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status_booking !== 'Menunggu Pembayaran') {
                throw new BookingException('booking', 'Pesanan ini tidak sedang menunggu pembayaran.');
            }
            if ($booking->kedaluwarsa()) {
                throw new BookingException('booking', 'Batas waktu pembayaran sudah lewat. Silakan buat booking baru.');
            }

            $terakhir = $booking->payments()->latest('id')->first();
            if ($terakhir && $terakhir->status_pembayaran === 'Pending' && filled($terakhir->snap_token)) {
                return $terakhir;
            }

            $orderId = $booking->kode_booking.'-'.now()->format('ymdHis').Str::upper(Str::random(2));
            $menit = max(1, (int) ceil(abs(now()->diffInSeconds($booking->batas_bayar)) / 60));

            $item = [[
                'id' => 'PAKET',
                'price' => (int) $booking->harga_paket,
                'quantity' => 1,
                'name' => Str::limit("Sewa {$booking->console->nama_tampil} {$booking->durasi_jam} jam", 50, ''),
            ]];
            if ($booking->jumlah_stik > 0) {
                $item[] = [
                    'id' => 'STIK',
                    'price' => (int) $booking->harga_stik,
                    'quantity' => (int) $booking->jumlah_stik,
                    'name' => 'Stik tambahan',
                ];
            }
            if ($booking->biaya_pengiriman > 0) {
                $item[] = [
                    'id' => 'KIRIM',
                    'price' => (int) $booking->biaya_pengiriman,
                    'quantity' => 1,
                    'name' => 'Biaya pengiriman',
                ];
            }

            $pelanggan = array_filter([
                'first_name' => Str::limit($booking->user->name, 50, ''),
                'email' => $booking->user->email,
                'phone' => $booking->user->no_hp,
            ]);

            $token = Snap::getSnapToken([
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $booking->total_harga,
                ],
                'item_details' => $item,
                'customer_details' => $pelanggan,
                // Transaksi di Midtrans ikut kedaluwarsa bersama batas bayar booking.
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'minutes',
                    'duration' => $menit,
                ],
            ]);

            $payment = new Payment();
            $payment->forceFill([
                'booking_id' => $booking->id,
                'order_id' => $orderId,
                'nominal' => (int) $booking->total_harga,
                'status_pembayaran' => 'Pending',
                'snap_token' => $token,
            ])->save();

            return $payment;
        });
    }

    /**
     * Menanyakan status transaksi langsung ke Midtrans lalu menerapkannya.
     * Dipakai tombol "Cek status" dan webhook (sumber kebenaran = API Midtrans).
     */
    public function sinkronkan(Payment $payment): Payment
    {
        $this->siapkan();

        try {
            $hasil = Transaction::status($payment->order_id);
        } catch (\Throwable $e) {
            // 404: transaksi belum dibuat di Midtrans (pelanggan belum memilih metode).
            if ((int) $e->getCode() === 404 || str_contains($e->getMessage(), '404')) {
                return $payment;
            }
            Log::error('Gagal cek status Midtrans: '.$e->getMessage(), ['order_id' => $payment->order_id]);
            throw new BookingException('pembayaran', 'Gagal menghubungi Midtrans. Coba lagi sebentar.');
        }

        return $this->terapkan($payment, json_decode(json_encode($hasil), true) ?: []);
    }

    /** Menerapkan status Midtrans. Aman dipanggil berulang (idempoten). */
    public function terapkan(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            // Urutan kunci: booking dulu, lalu payment (konsisten di seluruh kode).
            $booking = Booking::lockForUpdate()->findOrFail($payment->booking_id);
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            $baru = $this->petakan($data);
            if ($baru === null || $payment->status_pembayaran === 'Berhasil') {
                return $payment;
            }

            if (isset($data['gross_amount']) && (int) round((float) $data['gross_amount']) !== (int) $payment->nominal) {
                Log::warning('Nominal Midtrans tidak cocok, diabaikan.', ['order_id' => $payment->order_id]);

                return $payment;
            }

            $payment->forceFill([
                'status_pembayaran' => $baru,
                'transaction_id' => $data['transaction_id'] ?? $payment->transaction_id,
                'payment_type' => $data['payment_type'] ?? $payment->payment_type,
                'transaction_status' => $data['transaction_status'] ?? $payment->transaction_status,
                'fraud_status' => $data['fraud_status'] ?? $payment->fraud_status,
                'metode' => $this->namaMetode($data) ?? $payment->metode,
                'payload' => $data,
                'waktu_bayar' => $baru === 'Berhasil' ? now() : $payment->waktu_bayar,
            ])->save();

            if ($baru === 'Berhasil') {
                $this->tandaiDibayar($booking);
            }

            return $payment;
        });
    }

    public function tandaTanganValid(array $data): bool
    {
        $asli = hash('sha512',
            ($data['order_id'] ?? '').($data['status_code'] ?? '').($data['gross_amount'] ?? '').config('midtrans.server_key'));

        return hash_equals($asli, (string) ($data['signature_key'] ?? ''));
    }

    private function tandaiDibayar(Booking $booking): void
    {
        if ($booking->status_booking !== 'Menunggu Pembayaran') {
            return;
        }

        // Pembayaran masuk setelah jadwal sempat dilepas dan sudah diambil orang lain.
        if ($booking->kedaluwarsa() && $this->bookingService->adaBentrok(
            $booking->console_id, $booking->waktu_mulai, $booking->waktu_selesai, (int) $booking->buffer_jam
        )) {
            Log::warning('Pembayaran berhasil tetapi jadwal sudah terisi. Perlu penanganan admin.', ['booking' => $booking->kode_booking]);

            return;
        }

        $booking->forceFill([
            'status_booking' => 'Dibayar',
            'kode_konfirmasi' => $this->kodeKonfirmasi(),
        ])->save();
    }

    private function kodeKonfirmasi(): string
    {
        $panjang = max(4, (int) (DB::table('settings')->where('key', 'panjang_kode')->value('value') ?? 6));
        $maks = strlen(self::ALFABET) - 1;

        do {
            $kode = '';
            for ($i = 0; $i < $panjang; $i++) {
                $kode .= self::ALFABET[random_int(0, $maks)];
            }
        } while (Booking::where('kode_konfirmasi', $kode)->exists());

        return $kode;
    }

    /** Status Midtrans menjadi status_pembayaran kita. null = abaikan (mis. refund). */
    private function petakan(array $d): ?string
    {
        $ts = $d['transaction_status'] ?? '';
        $fraud = $d['fraud_status'] ?? null;

        return match (true) {
            $ts === 'settlement' => 'Berhasil',
            $ts === 'capture' && $fraud === 'accept' => 'Berhasil',
            $ts === 'capture', $ts === 'pending' => 'Pending',
            $ts === 'expire' => 'Kedaluwarsa',
            $ts === 'cancel' => 'Dibatalkan',
            in_array($ts, ['deny', 'failure'], true) => 'Gagal',
            default => null,
        };
    }

    private function namaMetode(array $d): ?string
    {
        $tipe = $d['payment_type'] ?? null;
        if (! $tipe) {
            return null;
        }

        if ($tipe === 'bank_transfer') {
            $bank = $d['va_numbers'][0]['bank'] ?? (isset($d['permata_va_number']) ? 'permata' : null);

            return $bank ? Str::upper($bank).' Virtual Account' : 'Transfer Bank';
        }

        return match ($tipe) {
            'echannel' => 'Mandiri Virtual Account',
            'qris' => 'QRIS',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'credit_card' => 'Kartu Kredit',
            default => Str::headline($tipe),
        };
    }
}