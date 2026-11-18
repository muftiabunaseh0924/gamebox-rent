<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\FleetController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UnitController;
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
        // L10a: dasbor KPI
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::post('/keluar', [AdminAuthController::class, 'logout'])->name('logout');

        // L10a: manajemen pesanan
        Route::get('/pesanan', [OrderController::class, 'index'])->name('pesanan');
        Route::get('/pesanan/{booking}', [OrderController::class, 'show'])->name('pesanan.show');
        Route::post('/pesanan/{booking}/batal', [OrderController::class, 'batal'])->middleware('throttle:30,1')->name('pesanan.batal');
        Route::get('/pesanan/{booking}/invoice', [OrderController::class, 'invoice'])->name('pesanan.invoice');

        // L8: serah terima dan armada
        Route::get('/konfirmasi', [HandoverController::class, 'index'])->name('konfirmasi');
        Route::post('/konfirmasi', [HandoverController::class, 'cek'])->middleware('throttle:30,1')->name('konfirmasi.cek');
        Route::post('/konfirmasi/serahkan', [HandoverController::class, 'serahkan'])->middleware('throttle:30,1')->name('konfirmasi.serahkan');

        Route::get('/armada', [FleetController::class, 'index'])->name('armada');
        Route::post('/armada/{booking}/selesai', [FleetController::class, 'selesai'])->name('armada.selesai');

        // L9: pelacakan pengantaran
        Route::get('/pengiriman', [DeliveryController::class, 'index'])->name('pengiriman');
        Route::post('/pengiriman/{booking}/ubah', [DeliveryController::class, 'ubah'])->middleware('throttle:60,1')->name('pengiriman.ubah');

        // L10b: perangkat
        Route::get('/perangkat', [UnitController::class, 'index'])->name('perangkat');
        Route::get('/perangkat/tambah', [UnitController::class, 'create'])->name('perangkat.tambah');
        Route::post('/perangkat', [UnitController::class, 'store'])->name('perangkat.simpan');
        Route::get('/perangkat/{console}/ubah', [UnitController::class, 'edit'])->name('perangkat.ubah');
        Route::post('/perangkat/{console}', [UnitController::class, 'update'])->name('perangkat.perbarui');
        Route::post('/perangkat/{console}/hapus', [UnitController::class, 'destroy'])->name('perangkat.hapus');

        // L10b: harga paket
        Route::get('/harga', [PackageController::class, 'index'])->name('harga');
        Route::post('/harga', [PackageController::class, 'store'])->name('harga.tambah');
        Route::post('/harga/{package}', [PackageController::class, 'update'])->name('harga.perbarui');
        Route::post('/harga/{package}/hapus', [PackageController::class, 'destroy'])->name('harga.hapus');

        // L10b: tanggal libur
        Route::get('/libur', [HolidayController::class, 'index'])->name('libur');
        Route::post('/libur', [HolidayController::class, 'store'])->name('libur.tambah');
        Route::post('/libur/{id}/hapus', [HolidayController::class, 'destroy'])->whereNumber('id')->name('libur.hapus');

        // L10c: pengaturan, konten beranda, templat email
        Route::get('/pengaturan', [SettingsController::class, 'index'])->name('pengaturan');
        Route::post('/pengaturan', [SettingsController::class, 'update'])->name('pengaturan.simpan');

        Route::get('/konten', [ContentController::class, 'index'])->name('konten');
        Route::post('/konten', [ContentController::class, 'update'])->name('konten.simpan');

        Route::get('/email', [EmailTemplateController::class, 'index'])->name('email');
        Route::post('/email/{jenis}', [EmailTemplateController::class, 'update'])->name('email.simpan');
        Route::post('/email/{jenis}/pratinjau', [EmailTemplateController::class, 'pratinjau'])->middleware('throttle:60,1')->name('email.pratinjau');
        Route::post('/email/{jenis}/uji', [EmailTemplateController::class, 'uji'])->middleware('throttle:10,1')->name('email.uji');

        // L10d: pelanggan
        Route::get('/pelanggan', [CustomerController::class, 'index'])->name('pelanggan');
        Route::get('/pelanggan/{user}', [CustomerController::class, 'show'])->whereNumber('user')->name('pelanggan.show');
        Route::post('/pelanggan/{user}/status', [CustomerController::class, 'ubahStatus'])->whereNumber('user')->middleware('throttle:30,1')->name('pelanggan.status');

        // L10d: log aktivitas (hanya baca)
        Route::get('/log', [ActivityLogController::class, 'index'])->name('log');
    });
});