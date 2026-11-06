<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Membuat satu akun admin awal HANYA jika belum ada.
     * Password dibaca dari .env (SEED_ADMIN_PASSWORD); jika kosong dipakai nilai bawaan untuk pengembangan.
     * WAJIB diganti sebelum website dipakai sungguhan.
     */
    public function run(): void
    {
        $email = 'admin@gamebox.test';

        if (DB::table('users')->where('email', $email)->exists()) {
            return;
        }

        $now = now();
        DB::table('users')->insert([
            'name' => 'Admin Utama',
            'email' => $email,
            'email_verified_at' => $now,
            'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'Admin123!')),
            'role' => 'admin',
            'status' => 'Aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
