@extends('layouts.landing')


@section('title')
    <title>Términos y Condiciones - {{ config('app.name') }}</title>
@endsection


@section('content')
    <main>
        <section class="section-muted" style="padding-top:120px;min-height:60vh">
            <div class="container py-5">
                <h1 class="mb-4">Términos y Condiciones</h1>
                <p>Bienvenido a {{ config('app.name') }}. Al utilizar nuestro sitio y servicios, aceptas los siguientes
                    términos y condiciones. Por favor, léelos cuidadosamente.</p>
                <ul>
                    <li>El uso de la plataforma implica la aceptación de estos términos.</li>
                    <li>La información proporcionada debe ser verídica y actualizada.</li>
                    <li>Nos reservamos el derecho de modificar estos términos en cualquier momento.</li>
                    <li>Para dudas, contáctanos a través de los medios oficiales.</li>
                </ul>
                <p>Última actualización: 5 de mayo de 2026</p>
            </div>
        </section>
    </main>
@endsection
