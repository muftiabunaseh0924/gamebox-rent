<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * kode_gateway = padanan metode pada gateway (Midtrans).
     * Nilainya dipastikan ulang di langkah pembayaran (L6).
     */
    public function run(): void
    {
        $metode = [
            ['nama' => 'QRIS', 'tipe' => 'qris', 'kode_gateway' => 'qris', 'nomor_va_simulasi' => null],
            ['nama' => 'BCA Virtual Account', 'tipe' => 'va', 'kode_gateway' => 'bca_va', 'nomor_va_simulasi' => null],
            ['nama' => 'Mandiri Virtual Account', 'tipe' => 'va', 'kode_gateway' => 'echannel', 'nomor_va_simulasi' => null],
            ['nama' => 'E-Wallet', 'tipe' => 'ewallet', 'kode_gateway' => 'gopay', 'nomor_va_simulasi' => null],
        ];

        $now = now();
        $baris = array_map(fn ($m) => $m + ['aktif' => true, 'created_at' => $now, 'updated_at' => $now], $metode);

        DB::table('payment_methods')->insertOrIgnore($baris);
    }
}
