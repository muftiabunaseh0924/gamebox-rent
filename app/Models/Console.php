<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    protected $table = 'consoles';

    // booking_aktif_id sengaja tidak di fillable (diatur sistem saat booking).
    protected $fillable = [
        'kode_unit', 'jenis', 'nama_tampil', 'tag', 'foto',
        'deskripsi', 'game', 'status', 'lokasi_saat_ini',
    ];

    /** PS5 dulu, lalu PS4; di dalam jenis urut kode unit. */
    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderByDesc('jenis')->orderBy('kode_unit');
    }

    public function fotoUrl(): ?string
    {
        if (blank($this->foto)) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        return asset('storage/'.ltrim($this->foto, '/'));
    }

    /** Kolom game berisi teks; dipisah koma, baris baru, atau tanda |. */
    public function daftarGame(): array
    {
        return collect(preg_split('/[,\n|]+/', (string) $this->game))
            ->map(fn ($game) => trim($game))
            ->filter()
            ->values()
            ->all();
    }
}