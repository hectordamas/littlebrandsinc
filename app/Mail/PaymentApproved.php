<?php

namespace App\Mail;

use App\Models\ParentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ParentPayment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu pago ha sido aprobado - Little Brands Inc',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-approved',
            with: [
                'payment' => $this->payment,
                'userName' => $this->payment->user->name ?? 'Familia',
                'amount' => number_format((float) $this->payment->amount, 2),
                'receivableTitle' => $this->payment->receivable->title ?? 'Cuenta por cobrar',
                'approvedAt' => $this->payment->approved_at?->format('d/m/Y h:i A') ?? now()->format('d/m/Y h:i A'),
            ],
        );
    }
}
