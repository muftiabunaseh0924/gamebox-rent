<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu booking boleh punya beberapa percobaan pembayaran; tiap percobaan punya order_id unik (kebutuhan Midtrans).
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $table->string('order_id', 64)->unique();
            $table->string('metode', 100)->nullable();
            $table->unsignedInteger('nominal');
            $table->string('status_pembayaran', 20)->default('Pending')->index(); // Pending | Paid | Failed | Expired

            // Data gateway (Midtrans)
            $table->string('snap_token')->nullable();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->string('payment_type', 50)->nullable();
            $table->string('transaction_status', 30)->nullable();
            $table->string('fraud_status', 30)->nullable();
            $table->json('payload')->nullable();

            $table->dateTime('waktu_bayar')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->restrictOnDelete();
            $table->string('no_invoice', 30)->unique(); // mis. INV20261101-001
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
    }
};
