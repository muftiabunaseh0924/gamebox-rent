<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Aman dijalankan ulang: nilai yang sudah ada (mis. sudah diubah admin) tidak ditimpa.
     */
    public function run(): void
    {
        $pengaturan = [
            // Aturan bisnis
            'harga_stik' => 10000,
            'denda_keterlambatan' => 20000,
            'buffer_jam' => 1,
            'batas_bayar_menit' => 15,
            'toleransi_ambil_menit' => 30,
            'deposit' => 0,
            'stok_stik' => 6,
            'jam_mulai_slot' => '0,2,4,6,8,10,12,14,16,18,20,22',
            'maks_hari_ke_depan' => 7,
            'maks_booking_menunggu' => 2,

            // Pengiriman
            'biaya_pengiriman' => 20000,
            'pengiriman_aktif' => 1,

            // Identitas situs
            'nama_situs' => 'GameBox Rent',
            'tagline' => 'Sewa PS4 & PS5 tanpa ribet',
            'kontak' => '08xx-xxxx-xxxx',
            'alamat' => 'Isi alamat tempat sewa',
            'logo_url' => '',

            // Tema (dari DESIGN.md Stitch)
            'warna_latar' => '#0e1323',
            'warna_kartu' => '#1a1f30',
            'warna_utama' => '#2f6bff',
            'warna_aksen' => '#5de6ff',
            'warna_aksen2' => '#8657f1',
            'font_judul' => 'Space Grotesk',
            'font_isi' => 'Inter',

            // Format kode dan nomor
            'prefix_invoice' => 'INV',
            'panjang_kode' => 6,
        ];

        $now = now();
        $baris = [];
        foreach ($pengaturan as $key => $value) {
            $baris[] = ['key' => $key, 'value' => (string) $value, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('settings')->insertOrIgnore($baris);
    }
}
