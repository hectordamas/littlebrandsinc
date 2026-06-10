@component('mail::message')
# Pago Aprobado

Hola {{ $userName }},

Tu pago por **${{ $amount }}** correspondiente a **{{ $receivableTitle }}** ha sido **aprobado**.

Fecha de aprobación: {{ $approvedAt }}

Gracias por mantener tu cuenta al día.

@component('mail::button', ['url' => route('parent.portal')])
Ir al Portal de Familia
@endcomponent

Saludos,<br>
{{ config('app.name') }}
@endcomponent
