<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsoleSeeder extends Seeder
{
    public function run(): void
    {
        $unit = [
            [
                'kode_unit' => 'PS4-01', 'jenis' => 'PS4',
                'nama_tampil' => 'PlayStation 4 Pro 1TB', 'tag' => 'PS4 PRO // 1TB HDR',
                'deskripsi' => 'Unit PS4-01. 4K Boost Mode, 2x DualShock 4 Wireless Controller, kabel power dan HDMI.',
                'game' => 'PES 2021, GTA V, FIFA 23, NFS Heat',
            ],
            [
                'kode_unit' => 'PS4-02', 'jenis' => 'PS4',
                'nama_tampil' => 'PlayStation 4 Slim 500GB', 'tag' => 'PS4 SLIM // 500GB',
                'deskripsi' => 'Unit PS4-02. 2x DualShock 4 Wireless Controller, kabel power dan HDMI.',
                'game' => 'eFootball 2024, GTA V, God of War 3',
            ],
            [
                'kode_unit' => 'PS5-01', 'jenis' => 'PS5',
                'nama_tampil' => 'PlayStation 5 Disc Edition', 'tag' => 'PS5 DISC // 4K-120HZ',
                'deskripsi' => 'Unit PS5-01. 2x DualSense Wireless Controller dan kabel HDMI 2.1 Ultra High Speed.',
                'game' => 'FC 24, Spider-Man 2, God of War Ragnarok, GTA V',
            ],
            [
                'kode_unit' => 'PS5-02', 'jenis' => 'PS5',
                'nama_tampil' => 'PlayStation 5 Disc Edition', 'tag' => 'PS5 DISC // ESPORTS PACK',
                'deskripsi' => 'Unit PS5-02. 2x DualSense Wireless Controller, siap untuk sesi turnamen multiplayer lokal.',
                'game' => 'eFootball 2024, Mortal Kombat 1, Tekken 8, FC 24',
            ],
            [
                'kode_unit' => 'PS5-03', 'jenis' => 'PS5',
                'nama_tampil' => 'PlayStation 5 Digital Edition', 'tag' => 'PS5 DIGITAL // 4K',
                'deskripsi' => 'Unit PS5-03. 2x DualSense Wireless Controller dan kabel HDMI.',
                'game' => 'FC 24, Spider-Man 2, GTA V',
            ],
        ];

        $now = now();
        $baris = array_map(fn ($u) => $u + [
            'foto' => null,
            'status' => 'Tersedia',
            'lokasi_saat_ini' => 'Di tempat sewa',
            'created_at' => $now,
            'updated_at' => $now,
        ], $unit);

        DB::table('consoles')->insertOrIgnore($baris);
    }
}
