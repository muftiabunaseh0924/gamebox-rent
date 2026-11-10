<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    public function pembayaran(Booking $b): ?Payment
    {
        return $b->payments()->where('status_pembayaran', 'Berhasil')->latest('id')->first();
    }

    /** Invoice hanya ada untuk booking yang pembayarannya berhasil. */
    public function bisaDibuat(Booking $b): bool
    {
        return in_array($b->status_booking, ['Dibayar', 'Sedang Disewa', 'Selesai'], true)
            && $this->pembayaran($b) !== null;
    }

    /** Contoh: INV-20261109-AB3K7M (awalan dari settings, tanpa kolom baru). */
    public function nomor(Booking $b): string
    {
        $awalan = DB::table('settings')->where('key', 'prefix_invoice')->value('value') ?: 'INV';
        $tanggal = ($this->pembayaran($b)?->waktu_bayar ?? $b->created_at)->format('Ymd');

        return $awalan.'-'.$tanggal.'-'.Str::afterLast($b->kode_booking, '-');
    }

    public function namaBerkas(Booking $b): string
    {
        return 'Invoice-'.$this->nomor($b).'.pdf';
    }

    public function pdf(Booking $b): \Barryvdh\DomPDF\PDF
    {
        $b->loadMissing(['console', 'user']);
        $situs = DB::table('settings')
            ->whereIn('key', ['nama_situs', 'tagline', 'kontak', 'alamat'])
            ->pluck('value', 'key');
        $pembayaran = $this->pembayaran($b);

        return Pdf::loadView('invoice.pdf', [
            'booking' => $b,
            'situs' => $situs,
            'nomor' => $this->nomor($b),
            'tanggal' => ($pembayaran?->waktu_bayar ?? $b->created_at)->format('d-m-Y H:i'),
            'pembayaran' => $pembayaran,
        ])->setPaper('a4');
    }
}