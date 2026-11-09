<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function bayar(Request $request, Booking $booking, PaymentService $layanan): JsonResponse
    {
        $this->pastikanMilik($request, $booking);

        try {
            $payment = $layanan->buatTransaksi($booking);
        } catch (BookingException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Tidak dapat membuat transaksi pembayaran. Pastikan koneksi internet aktif, lalu coba lagi.',
            ], 502);
        }

        return response()->json(['token' => $payment->snap_token, 'order_id' => $payment->order_id]);
    }

    public function cekStatus(Request $request, Booking $booking, PaymentService $layanan): JsonResponse
    {
        $this->pastikanMilik($request, $booking);

        $payment = $booking->payments()->latest('id')->first();
        if (! $payment) {
            return response()->json([
                'lunas' => false,
                'pesan' => 'Belum ada transaksi pembayaran. Klik "Bayar Sekarang" untuk memulai.',
            ]);
        }

        try {
            $payment = $layanan->sinkronkan($payment);
        } catch (BookingException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal memeriksa status pembayaran. Coba lagi sebentar.'], 502);
        }

        $pesan = match ($payment->status_pembayaran) {
            'Berhasil' => 'Pembayaran berhasil. Kode konfirmasi sudah terbit.',
            'Kedaluwarsa' => 'Pembayaran kedaluwarsa. Silakan buat booking baru.',
            'Dibatalkan' => 'Pembayaran dibatalkan. Klik "Bayar Sekarang" untuk mencoba lagi.',
            'Gagal' => 'Pembayaran gagal atau ditolak. Silakan coba lagi.',
            default => filled($payment->transaction_status)
                ? 'Menunggu pembayaran. Selesaikan pembayaran, lalu cek status lagi.'
                : 'Belum ada pembayaran yang dilakukan. Klik "Bayar Sekarang" untuk memilih metode.',
        };

        return response()->json([
            'lunas' => $payment->status_pembayaran === 'Berhasil',
            'status' => $payment->status_pembayaran,
            'pesan' => $pesan,
        ]);
    }

    /** Notifikasi dari Midtrans (butuh alamat publik; di komputer lokal pakai tombol Cek status). */
    public function webhook(Request $request, PaymentService $layanan): JsonResponse
    {
        $data = $request->json()->all();

        if (! $layanan->tandaTanganValid($data)) {
            return response()->json(['message' => 'Tanda tangan tidak valid.'], 403);
        }

        $payment = Payment::where('order_id', $data['order_id'] ?? '')->first();
        if (! $payment) {
            return response()->json(['message' => 'Order tidak dikenal.'], 404);
        }

        try {
            $layanan->sinkronkan($payment);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal memproses notifikasi.'], 500);
        }

        return response()->json(['message' => 'OK']);
    }

    private function pastikanMilik(Request $request, Booking $booking): void
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
    }
}