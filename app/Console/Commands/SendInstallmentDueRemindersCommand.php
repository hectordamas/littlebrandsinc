<?php

namespace App\Console\Commands;

use App\Models\EnrollmentInstallment;
use App\Notifications\InstallmentDueReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInstallmentDueRemindersCommand extends Command
{
    protected $signature = 'billing:send-installment-reminders';

    protected $description = 'Envia recordatorios de vencimiento de mensualidades (D-3, D-1, D+0, D+3).';

    public function handle(): int
    {
        $today = Carbon::today();
        $targets = [3, 1, 0, -3];
        $sent = 0;

        $installments = EnrollmentInstallment::query()
            ->with(['enrollment.parent', 'enrollment.student', 'enrollment.course'])
            ->whereIn('status', ['pending', 'overdue', 'failed'])
            ->get();

        foreach ($installments as $installment) {
            if (! $installment->due_date || ! $installment->enrollment || ! $installment->enrollment->parent) {
                continue;
            }

            $delta = $today->diffInDays(Carbon::parse($installment->due_date), false);
            if (! in_array($delta, $targets, true)) {
                continue;
            }

            $notificationColumn = match ($delta) {
                3 => 'notified_d3_at',
                1 => 'notified_d1_at',
                0 => 'notified_d0_at',
                -3 => 'notified_d3_plus_at',
                default => null,
            };

            if (! $notificationColumn) {
                continue;
            }

            if ($installment->{$notificationColumn}) {
                continue;
            }

            $installment->enrollment->parent->notify(new InstallmentDueReminderNotification($installment, $delta));
            $installment->update([$notificationColumn => now()]);
            $sent++;
        }

        $this->info('Recordatorios enviados: '.$sent);

        return self::SUCCESS;
    }
}
