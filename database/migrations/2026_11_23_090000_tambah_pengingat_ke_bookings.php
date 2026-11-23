<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Penanda "pengingat sudah dikirim", agar pengingat tidak terkirim dobel.
            $table->timestamp('pengingat_mulai_at')->nullable()->after('email_pembayaran_at');
            $table->timestamp('pengingat_selesai_at')->nullable()->after('pengingat_mulai_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['pengingat_mulai_at', 'pengingat_selesai_at']);
        });
    }
};