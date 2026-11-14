<?php

use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\FleetController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Webhook Midtrans: dipanggil server Midtrans (tanpa sesi/CSRF), dijaga tanda tangan.
Route::post('/midtrans/notifikasi', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([PreventRequestForgery::class])
    ->name('midtrans.notifikasi');

// ---------- Pelanggan ----------
Route::middleware('guest')->group(function () {
    Route::get('/daftar', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [CustomerAuthController::class, 'register'])->middleware('throttle:10,1');

    Route::get('/masuk', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [CustomerAuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::post('/keluar', [CustomerAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
    Route::get('/booking/slot', [BookingController::class, 'slot'])->name('booking.slot');
    Route::post('/booking', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');

    Route::get('/pesanan', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/pesanan/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::get('/pesanan/{booking}/invoice', InvoiceController::class)->name('booking.invoice');
    Route::post('/pesanan/{booking}/bayar', [PaymentController::class, 'bayar'])->middleware('throttle:10,1')->name('booking.bayar');
    Route::post('/pesanan/{booking}/cek-status', [PaymentController::class, 'cekStatus'])->middleware('throttle:20,1')->name('booking.cek-status');
});

// ---------- Admin ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/masuk', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/masuk', [AdminAuthController::class, 'login'])->middleware('throttle:6,1');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::post('/keluar', [AdminAuthController::class, 'logout'])->name('logout');

        // L8: serah terima dan armada
        Route::get('/konfirmasi', [HandoverController::class, 'index'])->name('konfirmasi');
        Route::post('/konfirmasi', [HandoverController::class, 'cek'])->middleware('throttle:30,1')->name('konfirmasi.cek');
        Route::post('/konfirmasi/serahkan', [HandoverController::class, 'serahkan'])->middleware('throttle:30,1')->name('konfirmasi.serahkan');

        Route::get('/armada', [FleetController::class, 'index'])->name('armada');
        Route::post('/armada/{booking}/selesai', [FleetController::class, 'selesai'])->name('armada.selesai');

        // L9: pelacakan pengantaran
        Route::get('/pengiriman', [DeliveryController::class, 'index'])->name('pengiriman');
        Route::post('/pengiriman/{booking}/ubah', [DeliveryController::class, 'ubah'])->middleware('throttle:60,1')->name('pengiriman.ubah');
    });
});