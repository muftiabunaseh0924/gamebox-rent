<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $table = 'bookings';

    // Sengaja tanpa $fillable: semua kolom diisi BookingService lewat forceFill(),
    // sehingga input pengguna tidak bisa menimpa harga atau status.

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'batas_bayar' => 'datetime',
            'waktu_serah_terima' => 'datetime',
            'waktu_kembali' => 'datetime',
            'kode_dipakai' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'kode_booking';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function console(): BelongsTo
    {
        return $this->belongsTo(Console::class);
    }

    public function kedaluwarsa(): bool
    {
        return $this->status_booking === 'Menunggu Pembayaran'
            && $this->batas_bayar !== null
            && $this->batas_bayar->isPast();
    }

    public static function rupiah(int|float $angka): string
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }
}