<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking', 30)->unique(); // mis. BK-20261101-001

            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('console_id')->constrained('consoles')->restrictOnDelete();

            // Jadwal (zona Asia/Jakarta)
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->unsignedSmallInteger('durasi_jam');

            // Harga dan aturan disalin saat booking dibuat (perubahan pengaturan tidak memengaruhi pesanan lama)
            $table->unsignedTinyInteger('jumlah_stik')->default(0);
            $table->unsignedInteger('harga_paket');
            $table->unsignedInteger('harga_stik')->default(0);            // harga per stik saat dipesan
            $table->unsignedInteger('biaya_pengiriman')->default(0);
            $table->unsignedInteger('total_harga');
            $table->unsignedTinyInteger('buffer_jam')->default(0);
            $table->unsignedInteger('denda_per_keterlambatan')->default(0);

            // Cara penerimaan unit
            $table->string('metode_serah', 20)->default('Ambil Sendiri'); // Ambil Sendiri | Diantar
            $table->text('alamat_pengiriman')->nullable();

            // Status dan pembayaran
            $table->string('status_booking', 30)->default('Menunggu Pembayaran')->index();
            $table->dateTime('batas_bayar')->nullable();

            // Kode konfirmasi serah terima (dibuat hanya setelah pembayaran berhasil)
            $table->string('kode_konfirmasi', 20)->nullable()->unique();
            $table->boolean('kode_dipakai')->default(false);

            // Serah terima dan pengembalian
            $table->dateTime('waktu_serah_terima')->nullable();
            $table->dateTime('waktu_kembali')->nullable();
            $table->unsignedInteger('denda')->default(0);
            $table->text('catatan_kondisi')->nullable();
            $table->foreignId('admin_konfirmasi_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Mempercepat pengecekan bentrok jadwal per unit
            $table->index(['console_id', 'waktu_mulai', 'waktu_selesai'], 'bookings_jadwal_index');
            $table->index(['user_id', 'status_booking']);
        });

        Schema::table('consoles', function (Blueprint $table) {
            $table->foreign('booking_aktif_id')->references('id')->on('bookings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consoles', function (Blueprint $table) {
            $table->dropForeign(['booking_aktif_id']);
        });

        Schema::dropIfExists('bookings');
    }
};
