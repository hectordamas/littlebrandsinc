<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingContactRequest;
use App\Mail\LandingContactMailable;
use App\Models\Branch;
use App\Models\ContactMessage;
use App\Models\Program;
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
        $strikersSchedules = [
            [
                'branch' => 'SEDE SAN LUIS',
                'items' => [
                    'Baby Strikers (18 a 24 meses): Lunes 4:00 p.m. • Miércoles 4:00 p.m. • Sábados 9:00 a.m.',
                    'Mini Strikers (24 a 36 meses): Lunes 4:00 p.m. • Miércoles 4:00 p.m. • Sábados 10:00 a.m.',
                    'Súper Strikers (36 a 48 meses): Lunes 5:00 p.m. • Miércoles 5:00 p.m. • Sábados 11:00 a.m.',
                ],
            ],
            [
                'branch' => 'SEDE LOS CAMPITOS',
                'items' => [
                    'Baby Strikers (18 a 24 meses): Martes 4:00 p.m. • Jueves 4:00 p.m.',
                    'Mini Strikers (24 a 36 meses): Martes 4:00 p.m. • Jueves 4:00 p.m.',
                    'Súper Strikers (36 a 48 meses): Martes 5:00 p.m. • Jueves 5:00 p.m.',
                ],
            ],
            [
                'branch' => 'SEDE LOS CHORROS',
                'items' => [
                    'Baby Strikers (18 a 24 meses): Sábados 9:00 a.m.',
                    'Mini Strikers (24 a 36 meses): Sábados 9:00 a.m.',
                    'Súper Strikers (36 a 48 meses): Sábados 10:00 a.m.',
                ],
            ],
        ];

        $paddlersSchedules = [
            [
                'branch' => 'SEDE LOS CHORROS',
                'items' => [
                    'Baby Paddlers (2 a 3 años): Martes 4:00 p.m.',
                    'Mini Paddlers (3 a 4 años): Martes 4:00 p.m.',
                    'Súper Paddlers (4 a 5 años): Martes 5:00 p.m.',
                ],
            ],
            [
                'branch' => 'SEDE LOS CAMPITOS',
                'items' => [
                    'Baby Paddlers (2 a 3 años): Miércoles 4:00 p.m.',
                    'Mini Paddlers (3 a 4 años): Miércoles 4:00 p.m.',
                    'Súper Paddlers (4 a 5 años): Miércoles 5:00 p.m.',
                ],
            ],
        ];

        $freeTrialUrl = route('enrollment.wizard', ['is_free_trial' => 1]);

        return view('classes.index', [
            'freeTrialUrl' => $freeTrialUrl,
            'strikersSchedules' => $strikersSchedules,
            'paddlersSchedules' => $paddlersSchedules,
        ]);
    }

    public function contact(LandingContactRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $program = Program::query()->find($payload['program_id']);
        $branch = Branch::query()->find($payload['branch_id']);
        $payload['program_name'] = optional($program)->name;
        $payload['branch_name'] = optional($branch)->name;
        $recipientAddress = (string) config('mail.to.address');
        $recipientName = (string) config('mail.to.name', 'Little Brands Inc');

        if ($recipientAddress === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'No se ha configurado MAIL_TO_ADDRESS en el archivo .env.',
                ]);
        }

        try {
            ContactMessage::create([
                'representative_name' => $payload['representative_name'],
                'child_name' => $payload['child_name'],
                'child_age' => (int) $payload['child_age'],
                'program_id' => (int) $payload['program_id'],
                'branch_id' => (int) $payload['branch_id'],
                'phone' => $payload['phone'],
                'email' => $payload['email'],
                'comment' => $payload['comment'],
            ]);

            Mail::to($recipientAddress, $recipientName)->send(new LandingContactMailable($payload));
        } catch (\Throwable $exception) {
            Log::error('Landing contact email failed', [
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'No se pudo enviar tu mensaje en este momento. Intenta nuevamente en unos minutos.',
                ]);
        }

        return back()->with('success', 'Gracias por escribirnos. Te responderemos muy pronto.');
    }
}
