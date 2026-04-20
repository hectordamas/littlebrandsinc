@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Editar Usuario</title>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Editar Usuario</h5>
        </div>
        <div class="card-block">
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="row">
                @csrf
                @method('PUT')

                <div class="form-group col-md-3 mb-3">
                    <label for="name">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}"
                        required>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="role">Rol</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="Padre" {{ $user->role == 'Padre' ? 'selected' : '' }}>Padre</option>
                        <option value="Coach" {{ $user->role == 'Coach' ? 'selected' : '' }}>Coach</option>
                        <option value="Administrador" {{ $user->role == 'Administrador' ? 'selected' : '' }}>Administrador
                        </option>
                    </select>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}"
                        required>
                </div>



                <div class="form-group col-md-3 mb-3">
                    <label for="whatsapp">WhatsApp</label>

                    <div class="input-group phone-group">
                        <select name="dial_code" id="dial_code" class="form-select">
                            @include('partials.dialcode_edit')
                        </select>

                        <input type="text" name="whatsapp" id="whatsapp" class="form-control" placeholder="4121234567"
                            value="{{ $user->whatsapp }}">
                    </div>
                </div>

                <div class="form-group col-md-3 mb-3">
                    <label for="password">Nueva Contraseña (opcional)</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <div class="form-group col-md-12 mb-3">
                    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    <a href="{{ url('users') }}" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
@endsection
