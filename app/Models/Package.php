<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'packages';

    protected $fillable = ['jenis', 'durasi_jam', 'harga', 'aktif'];

    protected function casts(): array
    {
        return [
            'durasi_jam' => 'integer',
            'harga' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function hargaRupiah(): string
    {
        return 'Rp '.number_format($this->harga, 0, ',', '.');
    }
}