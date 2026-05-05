<?php

namespace App\Notifications;

use App\Models\LBClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LBClass $class)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->class->date ? $this->class->date->format('d/m/Y') : null;
        $start = $this->class->start_time;
        $end = $this->class->end_time;
        $course = optional($this->class->course)->title;
        $branch = optional($this->class->branch)->name;
        $coach = optional($this->class->coach)->name;

        $mail = (new MailMessage)
            ->subject('Recordatorio de clase: ' . $course)
            ->greeting('Hola,')
            ->line('Te recordamos que tienes una clase próxima:')
            ->line('Curso: ' . $course)
            ->line('Fecha: ' . $date)
            ->line('Horario: ' . $start . ' - ' . $end)
            ->line('Sede: ' . $branch);

        if ($coach) {
            $mail->line('Coach: ' . $coach);
        }

        return $mail->line('¡Nos vemos en clase!')
            ->salutation('Equipo de ' . config('app.name'));
    }
}
