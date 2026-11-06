<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    /**
     * Teks halaman. Format khusus:
     *  - cara_menyewa : "Judul::Deskripsi|Judul::Deskripsi|..."
     *  - hero_poin    : "Judul::Deskripsi|Judul::Deskripsi"
     *  - operasional  : "Judul::Deskripsi"
     *  - Placeholder  : {{buffer_jam}}, {{denda}}
     */
    public function run(): void
    {
        $konten = [
            'beranda_judul' => 'Sewa PS4 & PS5, Main Sepuasnya',
            'beranda_deskripsi' => 'Pilih konsol, tentukan jadwal, bayar online, lalu ambil di tempat sewa.',
            'badge_hero' => 'Rental PlayStation Terpercaya',
            'hero_poin' => 'Bebas Jadwal Bentrok::Dicek otomatis oleh sistem|Verifikasi Serah Terima::Kode konfirmasi unik',
            'cara_menyewa' => 'Pilih Konsol & Jadwal::Pilih unit PS4 atau PS5, tanggal, jam mulai, dan durasi sewa.|'
                . 'Bayar Online::Pembayaran non-tunai melalui QRIS, Virtual Account, atau e-wallet.|'
                . 'Dapatkan Kode Booking::Sistem menerbitkan kode konfirmasi dan invoice PDF otomatis.|'
                . 'Ambil Unit di Outlet::Tunjukkan kode ke admin untuk serah terima.',
            'kelengkapan_standar' => '2 Controller, HDMI, Kabel Power',
            'operasional' => 'Buka 24 Jam::Layanan sewa dan pengambilan unit siap setiap hari',
            'catatan_buffer' => 'Setiap jeda antar penyewa diberi buffer {{buffer_jam}} jam untuk pembersihan unit dan pengecekan stik.',
            'catatan_pengiriman' => 'Admin mengantar unit ke alamat yang kamu isi menjelang jam mulai sewa. '
                . 'Tunjukkan kode konfirmasi saat unit diterima. Biaya pengiriman berlaku per sewa.',
            'catatan_identitas' => 'Bawa identitas asli (KTP/SIM) atas nama pemesan untuk verifikasi. '
                . 'Keterlambatan dikenakan denda {{denda}}.',
            'syarat_ketentuan' => 'Perangkat diambil sendiri di tempat sewa atau diantar sesuai pilihan saat memesan. '
                . 'Keterlambatan dikenakan denda sesuai ketentuan.',
        ];

        $now = now();
        $baris = [];
        foreach ($konten as $key => $value) {
            $baris[] = ['key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('contents')->insertOrIgnore($baris);
    }
}
