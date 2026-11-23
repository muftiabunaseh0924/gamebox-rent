<?php

namespace App\Services;

use App\Mail\TemplateMail;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PengingatService
{
    private const MULAI_MENIT = 60;
    private const SELESAI_MENIT = 30;

    /**
     * Mengirim pengingat mulai dan selesai sewa, SEKALI per pesanan.
     *
     * @return array{mulai: array{terkirim: int, gagal: int, kode: list<string>}, selesai: array{terkirim: int, gagal: int, kode: list<string>}}
     */
    public function jalankan(bool $simulasi = false): array
    {
        $hasil = [
            'mulai' => ['terkirim' => 0, 'gagal' => 0, 'kode' => []],
            'selesai' => ['terkirim' => 0, 'gagal' => 0, 'kode' => []],
        ];

        $sekarang = now();

        $mulai = Booking::where('status_booking', 'Dibayar')
            ->whereNull('pengingat_mulai_at')
            ->where('waktu_mulai', '>', $sekarang)
            ->where('waktu_mulai', '<=', $sekarang->copy()->addMinutes(self::MULAI_MENIT))
            ->orderBy('waktu_mulai')
            ->get(['id', 'kode_booking']);

        foreach ($mulai as $b) {
            $this->proses($b, 'pengingat_mulai', 'pengingat_mulai_at', $simulasi, $hasil['mulai']);
        }

        $selesai = Booking::where('status_booking', 'Sedang Disewa')
            ->whereNull('pengingat_selesai_at')
            ->where('waktu_selesai', '>', $sekarang)
            ->where('waktu_selesai', '<=', $sekarang->copy()->addMinutes(self::SELESAI_MENIT))
            ->orderBy('waktu_selesai')
            ->get(['id', 'kode_booking']);

        foreach ($selesai as $b) {
            $this->proses($b, 'pengingat_selesai', 'pengingat_selesai_at', $simulasi, $hasil['selesai']);
        }

        return $hasil;
    }

    private function proses(Booking $b, string $jenis, string $kolom, bool $simulasi, array &$bagian): void
    {
        if ($simulasi) {
            $bagian['terkirim']++;
            $bagian['kode'][] = $b->kode_booking;

            return;
        }

        // Klaim atomik: hanya satu proses yang berhasil menandai, jadi tidak ada email ganda.
        $diklaim = Booking::whereKey($b->id)->whereNull($kolom)->update([$kolom => now()]);
        if ($diklaim === 0) {
            return;
        }

        if ($this->kirim($b->id, $jenis, $kolom)) {
            $bagian['terkirim']++;
            $bagian['kode'][] = $b->kode_booking;
        } else {
            $bagian['gagal']++;
        }
    }

    private function kirim(int $id, string $jenis, string $kolom): bool
    {
        $tujuan = '-';

        try {
            $booking = Booking::with(['console', 'user'])->findOrFail($id);
            $tujuan = $booking->user->email;

            $templat = app(EmailTemplateService::class);
            [$subjek, $isi] = $templat->render($jenis, $templat->dataBooking($booking));

            Mail::to($tujuan)->send(new TemplateMail($booking, $subjek, $isi));
            $this->catat($id, $jenis, $tujuan, 'Terkirim', null);

            return true;
        } catch (\Throwable $e) {
            // Lepaskan klaim agar dicoba lagi pada putaran berikutnya (selama masih dalam jendela waktu).
            Booking::whereKey($id)->update([$kolom => null]);
            $this->catat($id, $jenis, $tujuan, 'Gagal', mb_substr($e->getMessage(), 0, 500));
            Log::error("Gagal mengirim {$jenis}: ".$e->getMessage(), ['booking_id' => $id]);

            return false;
        }
    }

    private function catat(int $bookingId, string $jenis, string $tujuan, string $status, ?string $error): void
    {
        DB::table('email_logs')->insert([
            'booking_id' => $bookingId,
            'jenis_email' => $jenis,
            'tujuan' => mb_substr($tujuan, 0, 150),
            'status_kirim' => $status,
            'error' => $error,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}