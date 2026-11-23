<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// L12: batalkan pesanan yang melewati batas bayar, diperiksa tiap 5 menit.
Schedule::command('booking:batal-kedaluwarsa')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// L12: kirim pengingat mulai (1 jam sebelum) dan selesai (30 menit sebelum), diperiksa tiap 5 menit.
Schedule::command('booking:kirim-pengingat')
    ->everyFiveMinutes()
    ->withoutOverlapping();