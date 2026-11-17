<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActivityLogger
{
    public static function catat(User $admin, string $aksi, string $detail): void
    {
        DB::table('activity_logs')->insert([
            'admin_id' => $admin->id,
            'aksi' => $aksi,
            'detail' => $detail,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}