<?php

namespace App\Mail;

use App\Models\ParentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ParentPayment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu pago requiere revisión - Little Brands Inc',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-rejected',
            with: [
                'payment' => $this->payment,
                'userName' => $this->payment->user->name ?? 'Familia',
                'amount' => number_format((float) $this->payment->amount, 2),
                'receivableTitle' => $this->payment->receivable->title ?? 'Cuenta por cobrar',
                'reason' => $this->payment->rejected_reason ?? 'El comprobante no pudo ser verificado. Por favor, contacta a administración.',
            ],
        );
    }
}
