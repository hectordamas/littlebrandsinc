<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BirthdayInquiryAdminMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva solicitud de cumpleaños - ' . ($this->payload['representative_name'] ?? 'Cliente'),
            replyTo: !empty($this->payload['email']) ? [
                new Address($this->payload['email'], $this->payload['representative_name'] ?? 'Contacto'),
            ] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.birthday-admin',
            with: [
                'payload' => $this->payload,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
