<?php

namespace App\Console\Commands;

use App\Models\LBClass;
use App\Notifications\ClassReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendClassRemindersCommand extends Command
{
    protected $signature = 'classes:send-reminders';
    protected $description = 'Envía recordatorios de clases próximas a padres y coaches (D-1)';

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow();
        $sent = 0;

        $classes = LBClass::with(['course', 'branch', 'coach', 'enrollments.parent'])
            ->whereDate('date', $tomorrow)
            ->get();

        foreach ($classes as $class) {
            // Notificar a cada padre de los inscritos
            foreach ($class->enrollments as $enrollment) {
                if ($enrollment->parent) {
                    $enrollment->parent->notify(new ClassReminderNotification($class));
                    $sent++;
                }
            }
            // Notificar al coach
            if ($class->coach) {
                $class->coach->notify(new ClassReminderNotification($class));
                $sent++;
            }
        }

        $this->info('Recordatorios de clase enviados: ' . $sent);
        return self::SUCCESS;
    }
}
