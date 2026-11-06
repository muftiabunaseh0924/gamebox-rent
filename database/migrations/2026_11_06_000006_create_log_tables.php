<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 50);
            $table->text('detail')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('console_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('console_id')->constrained('consoles')->restrictOnDelete();
            $table->string('lokasi', 50);
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('jenis_email', 50);
            $table->string('tujuan', 150);
            $table->string('status_kirim', 20); // Terkirim | Gagal
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('console_movements');
        Schema::dropIfExists('activity_logs');
    }
};
