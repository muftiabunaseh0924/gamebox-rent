<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    // Sengaja tanpa $fillable: diisi PaymentService lewat forceFill().

    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
            'payload' => 'array',
            'waktu_bayar' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}