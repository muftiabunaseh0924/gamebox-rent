<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('alasan_batal', 255)->nullable()->after('catatan_kondisi');
            $table->timestamp('dibatalkan_at')->nullable()->after('alasan_batal');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['alasan_batal', 'dibatalkan_at']);
        });
    }
};