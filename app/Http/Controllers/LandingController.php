<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingContactRequest;
use App\Mail\LandingContactConfirmationMailable;
use App\Mail\LandingContactMailable;
use App\Models\Branch;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $programs = Program::query()->where('active', true)->orderBy('name')->get();
        $branches = Branch::query()->orderBy('name')->get();

        $brands = $programs->map(function (Program $program) {
            $isPaddlers = str_contains(mb_strtolower($program->name), 'paddlers');

            return [
                'name' => $program->name,
                'sport' => $isPaddlers ? 'Padel infantil' : 'Futbol infantil',
                'description' => ($isPaddlers
                    ? 'Programa de pádel para niños y niñas de 2 a 5 años, diseñado para introducirlos al deporte de forma progresiva a través del juego. Desarrolla habilidades motoras, coordinación, equilibrio y confianza en un entorno lúdico, seguro y adaptado a cada etapa de crecimiento.'
                    : 'Programa deportivo educativo para niños y niñas de 18 meses a 4 años, diseñado para introducirlos al fútbol a través del juego. Desarrolla habilidades motoras, coordinación, socialización y confianza en un ambiente seguro, divertido y guiado por profesionales.'),
                'logo' => $isPaddlers
                    ? asset('landing_page/logos/little_paddlers_logo.jpeg')
                    : asset('landing_page/logos/little_strikers_logo.png'),
                'brochure' => $isPaddlers
                    ? asset('landing_page/brochures/Brochure%20LP%202026.pdf')
                    : asset('landing_page/brochures/Brochure%20Marzo%202026LS.pdf'),
                'accent' => $isPaddlers ? '#0ea5e9' : '#f97316',
            ];
        })->values()->all();

        return view('welcome', [
            'brands' => $brands,
            'programs' => $programs,
            'branches' => $branches,
            'holdingLogo' => asset('landing_page/logos/logo-littlebrandsinc.png'),
        ]);
    }

    public function classes(): View
    {
        $strikersProgram = Program::query()->where('slug', 'little-strikers')->first();
        $paddlersProgram = Program::query()->where('slug', 'little-paddlers')->first();

        $courses = Course::query()
            ->with(['program', 'branch', 'classes'])
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereHas('program', fn ($query) => $query->whereIn('slug', ['little-strikers', 'little-paddlers']))
            ->get();

        $groupedPrograms = [];

        foreach ($courses as $course) {
            $programSlug = optional($course->program)->slug;
            $branchName = optional($course->branch)->name;

            if (! $programSlug || ! $branchName) {
                continue;
            }

            $groupedPrograms[$programSlug][$branchName][] = [
                'title' => $course->title,
                'schedule' => $this->formatCourseSchedule($course),
                'url' => route('enrollment.wizard', [
                    'course_id' => $course->id,
                    'is_free_trial' => 1,
                ]),
            ];
        }

        $strikersSchedules = $this->normalizeProgramSchedules($groupedPrograms['little-strikers'] ?? [], 'little-strikers');
        $paddlersSchedules = $this->normalizeProgramSchedules($groupedPrograms['little-paddlers'] ?? [], 'little-paddlers');
        $freeTrialUrl = route('enrollment.wizard', ['is_free_trial' => 1]);
        $strikersFreeTrialParams = ['is_free_trial' => 1];
        if ($strikersProgram?->id) {
            $strikersFreeTrialParams['program_id'] = $strikersProgram->id;
        }
        $strikersFreeTrialUrl = route('enrollment.wizard', $strikersFreeTrialParams);

        $paddlersFreeTrialParams = ['is_free_trial' => 1];
        if ($paddlersProgram?->id) {
            $paddlersFreeTrialParams['program_id'] = $paddlersProgram->id;
        }
        $paddlersFreeTrialUrl = route('enrollment.wizard', $paddlersFreeTrialParams);

        return view('classes.index', [
            'freeTrialUrl' => $freeTrialUrl,
            'strikersFreeTrialUrl' => $strikersFreeTrialUrl,
            'paddlersFreeTrialUrl' => $paddlersFreeTrialUrl,
            'strikersSchedules' => $strikersSchedules,
            'paddlersSchedules' => $paddlersSchedules,
        ]);
    }

    protected function normalizeProgramSchedules(array $branchGroups, string $programSlug = ''): array
    {
        $branchOrder = [
            'SEDE SAN LUIS' => 1,
            'SEDE LOS CAMPITOS' => 2,
            'SEDE LOS CHORROS' => 3,
        ];

        $branches = collect($branchGroups)
            ->map(function ($items, $branch) use ($programSlug) {
                if ($programSlug === 'little-strikers' && mb_strtoupper($branch) === 'SEDE LOS CHORROS') {
                    $items = collect($items)
                        ->filter(function ($item) {
                            $title = mb_strtolower($item['title'] ?? '');
                            $schedule = mb_strtolower($item['schedule'] ?? '');
                            return str_contains($title, 'sábado') || str_contains($title, 'sabado') ||
                                   str_contains($schedule, 'sábado') || str_contains($schedule, 'sabado');
                        })
                        ->all();
                }

                $items = collect($items)
                    ->sortBy(fn ($item) => $this->courseTitleOrder((string) ($item['title'] ?? '')))
                    ->map(fn ($item) => [
                        'title' => $item['title'],
                        'schedule' => $item['schedule'],
                        'url' => $item['url'],
                    ])
                    ->values()
                    ->all();

                return [
                    'branch' => $branch,
                    'items' => $items,
                ];
            })
            ->sortBy(fn ($branchData) => $branchOrder[$branchData['branch']] ?? 99)
            ->values()
            ->all();

        return $branches;
    }

    protected function formatCourseSchedule(Course $course): string
    {
        $weekDays = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábados',
            7 => 'Domingos',
        ];

        $slots = $course->classes
            ->map(function ($class) {
                $date = $class->date instanceof Carbon
                    ? $class->date
                    : Carbon::parse($class->date);

                return [
                    'day' => (int) $date->dayOfWeekIso,
                    'time' => substr((string) $class->start_time, 0, 5),
                ];
            })
            ->unique(fn ($slot) => $slot['day'] . '|' . $slot['time'])
            ->sortBy([
                ['day', 'asc'],
                ['time', 'asc'],
            ])
            ->values();

        if ($slots->isEmpty()) {
            return 'Horario por confirmar';
        }

        return $slots
            ->map(function ($slot) use ($weekDays) {
                $carbonTime = Carbon::createFromFormat('H:i', $slot['time']);

                return ($weekDays[$slot['day']] ?? 'Dia') . ' ' . mb_strtolower($carbonTime->format('g:i A'));
            })
            ->map(fn ($value) => str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $value))
            ->implode(' • ');
    }

    protected function courseTitleOrder(string $title): int
    {
        $normalized = mb_strtolower($title);

        if (str_contains($normalized, 'baby')) {
            return 1;
        }

        if (str_contains($normalized, 'mini')) {
            return 2;
        }

        if (str_contains($normalized, 'super') || str_contains($normalized, 'súper')) {
            return 3;
        }

        return 99;
    }

    public function contact(LandingContactRequest $request): RedirectResponse
    {
        $contactRedirectUrl = route('landing.index') . '#contacto';
        $payload = $request->validated();
        $program = Program::query()->find($payload['program_id']);
        $branch = Branch::query()->find($payload['branch_id']);
        $payload['program_name'] = optional($program)->name;
        $payload['branch_name'] = optional($branch)->name;

        // 1. Persist message in database FIRST to guarantee application inbox delivery
        $contactMessage = ContactMessage::create([
            'representative_name' => $payload['representative_name'],
            'child_name' => $payload['child_name'],
            'child_age' => (int) $payload['child_age'],
            'program_id' => (int) $payload['program_id'],
            'branch_id' => (int) $payload['branch_id'],
            'phone' => $payload['phone'],
            'email' => $payload['email'],
            'comment' => $payload['comment'],
        ]);

        // 2. Queue emails to Admin and User in background with exception handling
        try {
            $recipientAddress = (string) config('mail.admin_recipient.address');
            $recipientName = (string) config('mail.admin_recipient.name', 'Little Brands Inc');

            // Send notification to Admin if MAIL_TO_ADDRESS is configured
            if (!empty($recipientAddress)) {
                Mail::to($recipientAddress, $recipientName)->queue(new LandingContactMailable($payload));
            } else {
                Log::warning('Landing contact email for admin skipped: MAIL_TO_ADDRESS is empty.');
            }

            // Send confirmation receipt to User
            if (!empty($payload['email'])) {
                Mail::to($payload['email'], $payload['representative_name'])->queue(new LandingContactConfirmationMailable($payload));
            }
        } catch (\Throwable $exception) {
            Log::error('Landing contact background email dispatch failed', [
                'contact_message_id' => $contactMessage->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()->to($contactRedirectUrl)
            ->with('success', 'Gracias por escribirnos. Te responderemos muy pronto.');
    }
}
