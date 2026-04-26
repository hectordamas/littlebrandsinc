<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandingContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'min:7', 'max:25'],
            'message' => ['required', 'string', 'min:12', 'max:1200'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingresa tu nombre.',
            'email.required' => 'Ingresa tu correo electronico.',
            'email.email' => 'Ingresa un correo electronico valido.',
            'phone.required' => 'Ingresa tu telefono.',
            'message.required' => 'Cuentanos como podemos ayudarte.',
            'message.min' => 'Tu mensaje debe tener al menos 12 caracteres.',
        ];
    }
}
