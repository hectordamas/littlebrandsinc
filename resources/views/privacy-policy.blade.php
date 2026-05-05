@extends('layouts.landing')


@section('title')
	<title>Aviso de Privacidad - {{ config('app.name') }}</title>
@endsection


@section('content')
<section class="section-muted" style="padding-top:120px; min-height:60vh">
	<div class="container py-5">
		<h1 class="mb-4">Aviso de Privacidad</h1>
		<p>En {{ config('app.name') }} nos comprometemos a proteger tu información personal. Este aviso describe cómo recopilamos, usamos y protegemos tus datos.</p>
		<ul>
			<li>Solo solicitamos la información necesaria para la prestación de nuestros servicios.</li>
			<li>No compartimos tus datos con terceros sin tu consentimiento, salvo obligación legal.</li>
			<li>Puedes ejercer tus derechos de acceso, rectificación y cancelación contactándonos.</li>
			<li>Implementamos medidas de seguridad para proteger tu información.</li>
		</ul>
		<p>Última actualización: 5 de mayo de 2026</p>
	</div>
</section>
@endsection
