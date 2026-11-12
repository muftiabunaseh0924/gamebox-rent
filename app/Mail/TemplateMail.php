<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\DB;

class TemplateMail extends Mailable
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
            view: 'emails.sederhana',
            with: [
                'isi' => $this->isiTeks,
                'namaSitus' => DB::table('settings')->where('key', 'nama_situs')->value('value') ?: config('app.name'),
            ],
        );
    }
}