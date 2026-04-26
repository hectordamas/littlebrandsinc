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
                'description' => 'Programa integral para niños y niñas que combina técnica, trabajo en equipo y formación en valores.',
                'logo' => asset('landing_page/logos/Logo%20LB_S1.png'),
                'brochure' => asset('landing_page/brochures/Brochure%20Marzo%202026LS.pdf'),
                'accent' => '#f97316',
            ],
            [
                'name' => 'Little Paddlers',
                'sport' => 'Pádel infantil',
                'description' => 'Entrenamiento progresivo en pádel con enfoque lúdico, motriz y competitivo para cada etapa de crecimiento.',
                'logo' => asset('landing_page/logos/Logo%20LB_P.png'),
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
