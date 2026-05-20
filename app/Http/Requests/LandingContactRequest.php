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
            'representative_name' => ['required', 'string', 'max:160'],
            'child_name' => ['required', 'string', 'max:160'],
            'child_age' => ['required', 'integer', 'min:1', 'max:18'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'min:7', 'max:25'],
            'comment' => ['required', 'string', 'min:12', 'max:1200'],
        ];
    }

    public function messages(): array
    {
        return [
            'representative_name.required' => 'Ingresa el nombre del representante.',
            'child_name.required' => 'Ingresa el nombre del nino o nina.',
            'child_age.required' => 'Indica la edad del nino o nina.',
            'program_id.required' => 'Selecciona el programa de interes.',
            'branch_id.required' => 'Selecciona la sede de preferencia.',
            'email.required' => 'Ingresa tu correo electronico.',
            'email.email' => 'Ingresa un correo electronico valido.',
            'phone.required' => 'Ingresa tu telefono.',
            'comment.required' => 'Cuentanos como podemos ayudarte.',
            'comment.min' => 'Tu mensaje debe tener al menos 12 caracteres.',
        ];
    }
}
