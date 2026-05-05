<?php

use App\Console\Commands\SendInstallmentDueRemindersCommand;
use App\Console\Commands\DispatchParentEventNotificationsCommand;
use App\Console\Commands\SendClassRemindersCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendInstallmentDueRemindersCommand::class)->dailyAt('08:00');
Schedule::command(DispatchParentEventNotificationsCommand::class)->everyThirtyMinutes();
Schedule::command(SendClassRemindersCommand::class)->dailyAt('18:00'); // Recordatorio de clases próximas
