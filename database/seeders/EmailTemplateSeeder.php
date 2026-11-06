<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    /** Placeholder memakai format {{nama}}, diganti saat email dikirim (L7 dan L12). */
    public function run(): void
    {
        $templat = [
            ['jenis_email' => 'booking_dibuat', 'subjek' => 'Booking {{kode_booking}} menunggu pembayaran',
                'isi' => 'Halo {{nama}}, selesaikan pembayaran sebelum {{batas_bayar}}. Total: {{total}}.'],
            ['jenis_email' => 'pembayaran_berhasil', 'subjek' => 'Pembayaran berhasil - Kode {{kode}}',
                'isi' => 'Halo {{nama}}, kode konfirmasi kamu: {{kode}}. Jadwal: {{jadwal}}. Invoice terlampir.'],
            ['jenis_email' => 'pengingat_mulai', 'subjek' => 'Pengingat: sewa dimulai 2 jam lagi',
                'isi' => 'Halo {{nama}}, sewa {{unit}} mulai {{waktu_mulai}}. Kode: {{kode}}.'],
            ['jenis_email' => 'pengingat_selesai', 'subjek' => 'Pengingat: sewa berakhir 1 jam lagi',
                'isi' => 'Halo {{nama}}, kembalikan {{unit}} sebelum {{waktu_selesai}}. Denda {{denda}} jika terlambat.'],
            ['jenis_email' => 'terlambat', 'subjek' => 'Sewa melewati batas waktu',
                'isi' => 'Halo {{nama}}, {{unit}} belum dikembalikan. Denda {{denda}} berlaku.'],
            ['jenis_email' => 'dibatalkan', 'subjek' => 'Booking {{kode_booking}} dibatalkan',
                'isi' => 'Halo {{nama}}, booking kamu dibatalkan ({{alasan}}).'],
            ['jenis_email' => 'terima_kasih', 'subjek' => 'Terima kasih telah menyewa',
                'isi' => 'Halo {{nama}}, sewa selesai. Denda: {{denda}}.'],
        ];

        $now = now();
        $baris = array_map(fn ($t) => $t + ['created_at' => $now, 'updated_at' => $now], $templat);

        DB::table('email_templates')->insertOrIgnore($baris);
    }
}
