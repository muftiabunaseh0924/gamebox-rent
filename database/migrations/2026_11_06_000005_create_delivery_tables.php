<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kondisi pengiriman terkini (satu baris per booking yang diantar)
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->restrictOnDelete();
            $table->string('status_kirim', 30)->default('Menunggu Dikirim')->index();
            $table->string('nama_kurir', 60)->nullable();
            $table->string('hp_kurir', 20)->nullable();
            $table->string('catatan', 200)->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Riwayat perubahan status (timeline untuk pelanggan)
        Schema::create('delivery_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $table->string('status_kirim', 30);
            $table->string('catatan', 255)->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_events');
        Schema::dropIfExists('deliveries');
    }
};
