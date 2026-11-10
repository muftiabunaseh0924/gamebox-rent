<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __invoke(Request $request, Booking $booking, InvoiceService $invoice)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        abort_unless($invoice->bisaDibuat($booking), 404, 'Invoice tersedia setelah pembayaran berhasil.');

        return $invoice->pdf($booking)->download($invoice->namaBerkas($booking));
    }
}