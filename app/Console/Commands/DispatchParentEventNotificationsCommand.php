<?php

namespace App\Console\Commands;

use App\Models\ParentEvent;
use App\Models\User;
use App\Notifications\ParentEventNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchParentEventNotificationsCommand extends Command
{
    protected $signature = 'parents:notify-events
        {--type= : season_start|tournament|event}
        {--title= : Titulo del aviso}
        {--message= : Contenido del aviso}
        {--event-date= : Fecha del evento YYYY-MM-DD}
        {--send-at= : Fecha/hora de envio YYYY-MM-DD HH:MM:SS}';

    protected $description = 'Envia notificaciones por correo a padres para inicio de temporada, torneos y eventos.';

    public function handle(): int
    {
        $this->createEventFromOptions();

        $events = ParentEvent::query()
            ->whereNull('sent_at')
            ->where(function ($query) {
                $query->whereNull('send_at')->orWhere('send_at', '<=', now());
            })
            ->orderBy('id')
            ->get();

        if ($events->isEmpty()) {
            $this->info('No hay notificaciones pendientes para enviar.');
            return self::SUCCESS;
        }

        $parents = User::query()
            ->where('role', 'Padre')
            ->whereNotNull('email')
            ->get();

        if ($parents->isEmpty()) {
            $this->warn('No hay padres con email registrado.');
            return self::SUCCESS;
        }

        foreach ($events as $event) {
            foreach ($parents as $parent) {
                $parent->notify(new ParentEventNotification($event));
            }

            $event->update([
                'sent_at' => now(),
            ]);
        }

        $this->info('Notificaciones enviadas: '.$events->count());

        return self::SUCCESS;
    }

    protected function createEventFromOptions(): void
    {
        $title = trim((string) $this->option('title'));
        $message = trim((string) $this->option('message'));

        if ($title === '' && $message === '') {
            return;
        }

        if ($title === '' || $message === '') {
            $this->warn('Para crear una notificacion debes enviar --title y --message.');
            return;
        }

        $type = trim((string) $this->option('type')) ?: 'event';
        if (! in_array($type, ['season_start', 'tournament', 'event'], true)) {
            $type = 'event';
        }

        $eventDate = $this->option('event-date')
            ? Carbon::parse((string) $this->option('event-date'))->toDateString()
            : null;

        $sendAt = $this->option('send-at')
            ? Carbon::parse((string) $this->option('send-at'))->toDateTimeString()
            : now()->toDateTimeString();

        DB::transaction(function () use ($type, $title, $message, $eventDate, $sendAt) {
            ParentEvent::query()->create([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'event_date' => $eventDate,
                'send_at' => $sendAt,
            ]);
        });

        $this->info('Notificacion creada para envio.');
    }
}
