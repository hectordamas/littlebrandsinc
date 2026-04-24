<?php

namespace App\Notifications;

use App\Models\EnrollmentInstallment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstallmentDueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EnrollmentInstallment $installment,
        public int $deltaDays
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $enrollment = $this->installment->enrollment;
        $studentName = optional($enrollment?->student)->name ?? 'Estudiante';
        $courseName = optional($enrollment?->course)->title ?? 'Programa';
        $amount = '$'.number_format((float) $this->installment->amount, 2);
        $dueDate = optional($this->installment->due_date)->format('d/m/Y') ?? 'N/A';

        $subject = $this->buildSubject();

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola '.$notifiable->name.',')
            ->line('Te recordamos una cuota de mensualidad pendiente.')
            ->line('Programa: '.$courseName)
            ->line('Estudiante: '.$studentName)
            ->line('Monto: '.$amount)
            ->line('Vencimiento: '.$dueDate)
            ->line('Si ya realizaste el pago, puedes ignorar este mensaje.')
            ->salutation('Equipo de '.config('app.name'));
    }

    protected function buildSubject(): string
    {
        return match ($this->deltaDays) {
            3 => 'Recordatorio: tu mensualidad vence en 3 dias',
            1 => 'Recordatorio: tu mensualidad vence manana',
            0 => 'Hoy vence tu mensualidad',
            -3 => 'Tu mensualidad tiene 3 dias de atraso',
            default => 'Recordatorio de mensualidad',
        };
    }
}
