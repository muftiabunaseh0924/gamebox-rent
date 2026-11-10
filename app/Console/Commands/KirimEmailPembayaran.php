<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\InvoiceService;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;

class KirimEmailPembayaran extends Command
{
    protected $signature = 'email:kirim-pembayaran {kode : Kode booking} {--paksa : Kirim ulang walau sudah pernah terkirim}';

    protected $description = 'Kirim email pembayaran berhasil (dengan invoice PDF) untuk satu booking';

    public function handle(NotifikasiService $notifikasi, InvoiceService $invoice): int
    {
        $booking = Booking::where('kode_booking', $this->argument('kode'))->first();

        if (! $booking) {
            $this->error('Booking tidak ditemukan.');

            return self::FAILURE;
        }
        if (! $invoice->bisaDibuat($booking)) {
            $this->error('Booking ini belum dibayar, jadi belum ada invoice.');

            return self::FAILURE;
        }

        if ($notifikasi->pembayaranBerhasil($booking->id, (bool) $this->option('paksa'))) {
            $this->info('Email dikirim ke '.$booking->user->email.'.');

            return self::SUCCESS;
        }

        $this->warn('Email tidak dikirim (sudah pernah terkirim atau gagal). Pakai --paksa untuk kirim ulang.');

        return self::FAILURE;
    }
}