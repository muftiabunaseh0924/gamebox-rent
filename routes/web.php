<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

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
    Route::get('/pesanan/{booking}', [BookingController::class, 'show'])->name('booking.show');
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
    });
});