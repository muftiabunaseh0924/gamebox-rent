<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Delivery;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Transaction;

class AutoCancelService
{
    /** Tenggang setelah batas bayar, untuk pelanggan yang membayar di detik terakhir. */
    private const TENGGANG_MENIT = 2;

    /** Status Midtrans yang berarti uang sudah masuk: pesanan TIDAK boleh dibatalkan otomatis. */
    private const STATUS_SUDAH_BAYAR = ['settlement', 'capture', 'authorize'];

    /**
     * Membatalkan pesanan "Menunggu Pembayaran" yang melewati batas bayar.
     * Jadwal otomatis lepas (pesanan Dibatalkan tidak memegang jadwal).
     *
     * @return array{dibatalkan: int, dilewati: int, kode: list<string>}
     */
    public function jalankan(bool $simulasi = false): array
    {
        $hasil = ['dibatalkan' => 0, 'dilewati' => 0, 'kode' => []];

        $kandidat = Booking::where('status_booking', 'Menunggu Pembayaran')
            ->whereNotNull('batas_bayar')
            ->where('batas_bayar', '<', now()->subMinutes(self::TENGGANG_MENIT))
            ->orderBy('id')
            ->get(['id', 'kode_booking']);

        foreach ($kandidat as $b) {
            $orders = Payment::where('booking_id', $b->id)
                ->where('status_pembayaran', 'Pending')
                ->pluck('order_id')
                ->all();

            // Pengecekan ke Midtrans dilakukan di luar transaksi database (panggilan jaringan).
            if ($this->mungkinSudahDibayar($orders)) {
                $hasil['dilewati']++;
                Log::info('Batal otomatis dilewati: pembayaran mungkin sudah masuk.', ['kode' => $b->kode_booking]);

                continue;
            }

            if ($simulasi) {
                $hasil['dibatalkan']++;
                $hasil['kode'][] = $b->kode_booking;

                continue;
            }

            if ($this->batalkan($b->id)) {
                $hasil['dibatalkan']++;
                $hasil['kode'][] = $b->kode_booking;
                $this->batalkanDiMidtrans($orders);
            } else {
                $hasil['dilewati']++;
            }
        }

        return $hasil;
    }

    /** Pembatalan di database; mengunci baris dan memeriksa ulang kondisinya. */
    private function batalkan(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $b = Booking::lockForUpdate()->find($id);

            if (! $b
                || $b->status_booking !== 'Menunggu Pembayaran'
                || $b->batas_bayar === null
                || $b->batas_bayar->gt(now()->subMinutes(self::TENGGANG_MENIT))) {
                return false;
            }

            // Pengaman: ada pembayaran berhasil tetapi status pesanan belum diperbarui (mis. webhook belum aktif).
            if (Payment::where('booking_id', $id)->where('status_pembayaran', 'Berhasil')->lockForUpdate()->exists()) {
                return false;
            }

            $b->forceFill([
                'status_booking' => 'Dibatalkan',
                'alasan_batal' => 'Batas waktu pembayaran habis (dibatalkan otomatis).',
                'dibatalkan_at' => now(),
            ])->save();

            Payment::where('booking_id', $id)
                ->where('status_pembayaran', 'Pending')
                ->update(['status_pembayaran' => 'Dibatalkan']);

            Delivery::where('booking_id', $id)
                ->where('status_kirim', '<>', 'Telah Diterima')
                ->update([
                    'status_kirim' => 'Dibatalkan',
                    'catatan' => 'Pesanan dibatalkan otomatis (batas bayar habis).',
                ]);

            DB::table('activity_logs')->insert([
                'admin_id' => null,
                'aksi' => 'Batal Otomatis',
                'detail' => "Pesanan {$b->kode_booking} dibatalkan otomatis karena batas waktu pembayaran habis.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    private function midtransSiap(): bool
    {
        if (blank(config('midtrans.server_key'))) {
            return false;
        }

        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');

        return true;
    }

    /**
     * True bila ada transaksi yang sudah dibayar, atau statusnya tidak bisa dipastikan
     * (kegagalan jaringan): pesanan dilewati dan dicoba lagi pada putaran berikutnya.
     */
    private function mungkinSudahDibayar(array $orders): bool
    {
        if ($orders === [] || ! $this->midtransSiap()) {
            return false;
        }

        foreach ($orders as $order) {
            try {
                $status = Transaction::status($order);
                if (in_array($status->transaction_status ?? '', self::STATUS_SUDAH_BAYAR, true)) {
                    return true;
                }
            } catch (\Throwable $e) {
                // 404 = pelanggan belum pernah memilih metode di popup, aman dibatalkan.
                if ((int) $e->getCode() === 404) {
                    continue;
                }
                Log::warning('Cek status Midtrans gagal saat batal otomatis: '.$e->getMessage(), ['order_id' => $order]);

                return true;
            }
        }

        return false;
    }

    /** Transaksi yang belum dibayar dibatalkan di Midtrans agar tidak bisa dibayar lagi. Kegagalan hanya dicatat. */
    private function batalkanDiMidtrans(array $orders): void
    {
        if ($orders === [] || ! $this->midtransSiap()) {
            return;
        }

        foreach ($orders as $order) {
            try {
                Transaction::cancel($order);
            } catch (\Throwable $e) {
                Log::info('Pembatalan transaksi Midtrans dilewati: '.$e->getMessage(), ['order_id' => $order]);
            }
        }
    }
}