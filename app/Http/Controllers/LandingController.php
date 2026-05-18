<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandingContactRequest;
use App\Mail\LandingContactMailable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $brands = [
            [
                'name' => 'Little Strikers',
                'sport' => 'Fútbol infantil',
                'description' => 'Programa deportivo educativo para niños y niñas de 18 meses a 4 años, diseñado para introducirlos al fútbol a través del juego. Desarrolla habilidades motoras, coordinación, socialización y confianza en un ambiente seguro, divertido y guiado por profesionales.',
                'logo' => asset('landing_page/logos/little_strikers_logo.png'),
                'brochure' => asset('landing_page/brochures/Brochure%20Marzo%202026LS.pdf'),
                'accent' => '#f97316',
            ],
            [
                'name' => 'Little Paddlers',
                'sport' => 'Pádel infantil',
                'description' => 'Programa de pádel para niños y niñas de 2 a 5 años, diseñado para introducirlos al deporte de forma progresiva a través del juego. Desarrolla habilidades motoras, coordinación, equilibrio y confianza en un entorno lúdico, seguro y adaptado a cada etapa de crecimiento.',
                'logo' => asset('landing_page/logos/little_paddlers_logo.jpeg'),
                'brochure' => asset('landing_page/brochures/Brochure%20LP%202026.pdf'),
                'accent' => '#0ea5e9',
            ],
        ];

        return view('welcome', [
            'brands' => $brands,
            'holdingLogo' => asset('landing_page/logos/logo-littlebrandsinc.png'),
        ]);
    }

    public function contact(LandingContactRequest $request): RedirectResponse
    {
        $payload = $request->validated();
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
