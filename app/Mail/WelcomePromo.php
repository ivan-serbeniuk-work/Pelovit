<?php

namespace App\Mail;

use App\Models\PromoCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomePromo extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PromoCode $promo,
        public ?string $name = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваш промокод на першу покупку в PELOVIT',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.welcome-promo',
        );
    }
}
