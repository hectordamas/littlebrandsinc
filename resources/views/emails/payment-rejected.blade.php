@component('mail::message')
# Pago Requiere Revisión

Hola {{ $userName }},

El comprobante de pago por **${{ $amount }}** correspondiente a **{{ $receivableTitle }}** no pudo ser verificado.

**Motivo:** {{ $reason }}

Por favor, revisa tu comprobante y vuelve a enviarlo desde el Portal de Familia, o contacta a administración para asistencia.

@component('mail::button', ['url' => route('parent.portal')])
Ir al Portal de Familia
@endcomponent

Saludos,<br>
{{ config('app.name') }}
@endcomponent
