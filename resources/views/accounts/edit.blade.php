@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Editar Cuenta</title>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Editar Cuenta #{{ $account->id }}</h5>
                            <span class="text-muted">Solo se permite modificar el nombre de la cuenta.</span>
                        </div>
                        <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $account->name) }}"
                                    class="form-control"
                                    maxlength="255"
                                    required>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                <a href="{{ route('accounts.index') }}" class="btn btn-light">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
