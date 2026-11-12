<?php

namespace App\Services;

use App\Mail\PembayaranBerhasilMail;
use App\Mail\TemplateMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifikasiService
{
    /**
     * Mengirim email "pembayaran berhasil" SEKALI per booking. Penandanya diklaim
     * secara atomik di database, jadi pemanggilan ganda (cek status berulang,
     * webhook + cek status bersamaan) tidak mengirim email dua kali.
     * Kegagalan email tidak pernah membatalkan pembayaran.
     */
    public function pembayaranBerhasil(int $bookingId, bool $paksa = false): bool
    {
        $diklaim = Booking::whereKey($bookingId)
            ->when(! $paksa, fn ($q) => $q->whereNull('email_pembayaran_at'))
            ->update(['email_pembayaran_at' => now()]);

        if ($diklaim === 0) {
            return false;
        }

        try {
            $booking = Booking::with(['console', 'user', 'payments'])->findOrFail($bookingId);
            $templat = app(EmailTemplateService::class);
            [$subjek, $isi] = $templat->render('pembayaran_berhasil', $templat->dataBooking($booking));

            Mail::to($booking->user->email)->send(new PembayaranBerhasilMail($booking, $subjek, $isi));

            return true;
        } catch (\Throwable $e) {
            // Lepaskan klaim agar bisa dicoba lagi.
            Booking::whereKey($bookingId)->update(['email_pembayaran_at' => null]);
            Log::error('Gagal mengirim email pembayaran: '.$e->getMessage(), ['booking_id' => $bookingId]);

            return false;
        }
    }

    /** Email "terima kasih" saat penyewaan selesai. Kegagalan email tidak mengganggu proses selesai. */
    public function sewaSelesai(int $bookingId): bool
    {
        try {
            $booking = Booking::with(['console', 'user'])->findOrFail($bookingId);
            $templat = app(EmailTemplateService::class);
            $data = array_merge($templat->dataBooking($booking), [
                'denda' => Booking::rupiah($booking->denda),
            ]);
            [$subjek, $isi] = $templat->render('terima_kasih', $data);

            Mail::to($booking->user->email)->send(new TemplateMail($booking, $subjek, $isi));

            return true;
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email terima kasih: '.$e->getMessage(), ['booking_id' => $bookingId]);

            return false;
        }
    }
}