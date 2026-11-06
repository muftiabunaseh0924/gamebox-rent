<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// ---------- Pelanggan ----------
Route::middleware('guest')->group(function () {
    Route::get('/daftar', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [CustomerAuthController::class, 'register'])->middleware('throttle:10,1');

    Route::get('/masuk', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [CustomerAuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::post('/keluar', [CustomerAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
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