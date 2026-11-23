<?php

namespace App\Console\Commands;

use App\Services\AutoCancelService;
use Illuminate\Console\Command;

class BatalkanKedaluwarsa extends Command
{
    protected $signature = 'booking:batal-kedaluwarsa {--simulasi : Hanya tampilkan yang akan dibatalkan, tanpa mengubah data}';

    protected $description = 'Batalkan otomatis pesanan Menunggu Pembayaran yang melewati batas bayar';

    public function handle(AutoCancelService $layanan): int
    {
        $simulasi = (bool) $this->option('simulasi');
        $hasil = $layanan->jalankan($simulasi);

        $awalan = $simulasi ? '[SIMULASI] Akan dibatalkan' : 'Dibatalkan';
        $this->info("{$awalan}: {$hasil['dibatalkan']} pesanan".($hasil['kode'] ? ' ('.implode(', ', $hasil['kode']).')' : '').'.');

        if ($hasil['dilewati'] > 0) {
            $this->warn("Dilewati: {$hasil['dilewati']} pesanan (pembayaran mungkin sudah masuk atau status belum bisa dipastikan).");
        }

        return self::SUCCESS;
    }
}