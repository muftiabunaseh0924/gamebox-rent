<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'bookings';

    // Sengaja tanpa $fillable: semua kolom diisi layanan lewat forceFill(),
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function kedaluwarsa(): bool
    {
        return $this->status_booking === 'Menunggu Pembayaran'
            && $this->batas_bayar !== null
            && $this->batas_bayar->isPast();
    }

    public function statusTampil(): string
    {
        return $this->kedaluwarsa() ? 'Kedaluwarsa' : $this->status_booking;
    }

    public function kelasStatus(): string
    {
        return match ($this->statusTampil()) {
            'Menunggu Pembayaran' => 'bg-amber-500/15 text-amber-300',
            'Dibayar', 'Sedang Disewa' => 'bg-emerald-500/15 text-emerald-300',
            'Selesai' => 'bg-primary-container/20 text-primary',
            default => 'bg-error-container/40 text-error',
        };
    }

    public static function rupiah(int|float $angka): string
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }
}