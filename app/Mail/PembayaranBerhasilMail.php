<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\DB;

class PembayaranBerhasilMail extends Mailable
{
    public function __construct(
        public Booking $booking,
        public string $subjekTeks,
        public string $isiTeks,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjekTeks);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pembayaran-berhasil',
            with: [
                'isi' => $this->isiTeks,
                'booking' => $this->booking,
                'namaSitus' => DB::table('settings')->where('key', 'nama_situs')->value('value') ?: config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        $invoice = app(InvoiceService::class);

        return [
            Attachment::fromData(fn () => $invoice->pdf($this->booking)->output(), $invoice->namaBerkas($this->booking))
                ->withMime('application/pdf'),
        ];
    }
}