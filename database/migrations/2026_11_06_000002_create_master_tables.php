<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consoles', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit', 20)->unique();
            $table->string('jenis', 10)->index();
            $table->string('nama_tampil', 100);
            $table->string('tag', 100)->nullable();
            $table->string('foto')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('game')->nullable();
            $table->string('status', 20)->default('Tersedia')->index();
            $table->string('lokasi_saat_ini', 50)->default('Di tempat sewa');
            $table->unsignedBigInteger('booking_aktif_id')->nullable(); // FK ditambahkan setelah tabel bookings dibuat
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 10);
            $table->unsignedSmallInteger('durasi_jam');
            $table->unsignedInteger('harga');
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['jenis', 'durasi_jam']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_email', 50)->unique();
            $table->string('subjek', 200);
            $table->text('isi');
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('tipe', 20);
            $table->string('kode_gateway', 50)->nullable();
            $table->string('nomor_va_simulasi', 50)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('console_id')->nullable()->constrained('consoles')->cascadeOnDelete(); // kosong = semua unit
            $table->date('tanggal')->index();
            $table->string('alasan', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('consoles');
    }
};
