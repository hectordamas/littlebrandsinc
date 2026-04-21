@extends('layouts.admin')

@section('title')
	<title>{{ env('APP_NAME') }} - Mi Perfil</title>
@endsection

@section('content')
	<div class="card">
		<div class="card-header">
			<h5 class="card-title mb-1">Mi Perfil</h5>
			<span class="text-muted">Puedes actualizar tu WhatsApp (con código de país) y tu contraseña.</span>
		</div>
		<div class="card-block">
			<form action="{{ route('users.profile.update') }}" method="POST" class="row">
				@csrf
				@method('PUT')

				<div class="form-group col-md-6 mb-3">
					<label for="name">Nombre</label>
					<input type="text" id="name" class="form-control" value="{{ $user->name }}" readonly>
				</div>

				<div class="form-group col-md-6 mb-3">
					<label for="email">Email</label>
					<input type="email" id="email" class="form-control" value="{{ $user->email }}" readonly>
				</div>

				<div class="form-group col-md-6 mb-3">
					<label for="whatsapp">WhatsApp</label>
					<div class="input-group phone-group">
						<select name="dial_code" id="dial_code" class="form-select">
							@include('partials.dialcode_edit')
						</select>
						<input
							type="text"
							name="whatsapp"
							id="whatsapp"
							class="form-control"
							placeholder="4121234567"
							value="{{ old('whatsapp', $user->whatsapp) }}">
					</div>
				</div>

				<div class="form-group col-md-6 mb-3">
					<label for="password">Nueva contraseña</label>
					<input type="password" name="password" id="password" class="form-control" minlength="8">
				</div>

				<div class="form-group col-md-6 mb-3">
					<label for="password_confirmation">Confirmar nueva contraseña</label>
					<input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8">
				</div>

				<div class="form-group col-md-12 mb-0">
					<button type="submit" class="btn btn-primary">Guardar cambios</button>
				</div>
			</form>
		</div>
	</div>
@endsection
