<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public string $paymentCardNumber;

    public string $paymentCardHolder;

    public function __construct(public Order $order)
    {
        $this->paymentCardNumber = (string) Setting::get('payment_card_number', '');
        $this->paymentCardHolder = (string) Setting::get('payment_card_holder', '');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваше замовлення #'.$this->order->id.' прийнято — PELOVIT',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation',
        );
    }
}
