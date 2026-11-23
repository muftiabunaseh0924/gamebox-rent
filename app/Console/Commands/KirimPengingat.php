<?php

namespace App\Console\Commands;

use App\Services\PengingatService;
use Illuminate\Console\Command;

class KirimPengingat extends Command
{
    protected $signature = 'booking:kirim-pengingat {--simulasi : Hanya tampilkan yang akan diingatkan, tanpa mengirim email}';

    protected $description = 'Kirim email pengingat mulai sewa (1 jam sebelum) dan selesai sewa (30 menit sebelum)';

    public function handle(PengingatService $layanan): int
    {
        $simulasi = (bool) $this->option('simulasi');
        $hasil = $layanan->jalankan($simulasi);

        $awalan = $simulasi ? '[SIMULASI] Akan dikirim' : 'Terkirim';

        foreach (['mulai' => 'Pengingat mulai', 'selesai' => 'Pengingat selesai'] as $kunci => $nama) {
            $h = $hasil[$kunci];
            $this->info("{$nama} - {$awalan}: {$h['terkirim']}".($h['kode'] ? ' ('.implode(', ', $h['kode']).')' : '').'.');
            if ($h['gagal'] > 0) {
                $this->warn("{$nama} - gagal: {$h['gagal']} (dicoba lagi pada putaran berikutnya; lihat tabel email_logs).");
            }
        }

        return self::SUCCESS;
    }
}