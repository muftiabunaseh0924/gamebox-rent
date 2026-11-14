<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $table = 'deliveries';

    // Sengaja tanpa $fillable: diisi DeliveryService lewat forceFill().

    public const URUTAN = ['Menunggu Dikirim', 'Kurir Ditugaskan', 'Dalam Perjalanan', 'Telah Diterima'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function kelasStatus(): string
    {
        return match ($this->status_kirim) {
            'Menunggu Dikirim' => 'bg-surface-container-high text-on-surface-variant',
            'Kurir Ditugaskan' => 'bg-primary-container/20 text-primary',
            'Dalam Perjalanan' => 'bg-amber-500/15 text-amber-300',
            'Telah Diterima' => 'bg-emerald-500/15 text-emerald-300',
            'Gagal Dikirim' => 'bg-error-container/40 text-error',
            default => 'bg-surface-container-high text-on-surface-variant',
        };
    }
}