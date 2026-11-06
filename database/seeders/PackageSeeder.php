<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    /**
     * Harga simulasi dari PRD bagian 11 (dapat diubah admin).
     * Durasi 6 sampai 24 jam, kelipatan 2.
     */
    public function run(): void
    {
        $harga = [
            'PS4' => [50000, 65000, 80000, 95000, 110000, 125000, 138000, 150000, 162000, 175000],
            'PS5' => [80000, 105000, 130000, 150000, 175000, 195000, 215000, 235000, 255000, 275000],
        ];

        $now = now();
        $baris = [];
        foreach ($harga as $jenis => $daftar) {
            foreach ($daftar as $i => $nominal) {
                $baris[] = [
                    'jenis' => $jenis,
                    'durasi_jam' => 6 + $i * 2,
                    'harga' => $nominal,
                    'aktif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('packages')->insertOrIgnore($baris);
    }
}
