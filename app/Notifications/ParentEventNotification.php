<?php

namespace App\Notifications;

use App\Models\ParentEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParentEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ParentEvent $event)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $eventDate = $this->event->event_date ? $this->event->event_date->format('d/m/Y') : null;

        $mail = (new MailMessage)
            ->subject($this->event->title)
            ->greeting('Hola '.$notifiable->name.',')
            ->line($this->event->message);

        if ($eventDate) {
            $mail->line('Fecha del evento: '.$eventDate);
        }

        return $mail->line('Gracias por formar parte de nuestra comunidad deportiva infantil.')
            ->salutation('Equipo de '.config('app.name'));
    }
}
