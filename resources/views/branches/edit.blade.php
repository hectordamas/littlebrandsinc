@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Editar Sucursal</title>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Editar Sucursal</h5>
                    </div>
                    <div class="card-block">
                        <form action="{{ route('branches.update', $branch->id) }}" method="POST"
                            enctype="multipart/form-data" class="row">
                            @csrf
                            @method('PUT')
                            <div class="form-group col-md-12">
                                @if ($branch->logo)
                                    <img src="{{ asset($branch->logo) }}" alt="Logo de la Sucursal"
                                        class="img-thumbnail mb-3" style="max-width: 150px;">
                                @else
                                    <p class="text-muted">No hay logo disponible</p>
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                <label for="name">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ $branch->name }}" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="logo">Logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="address">Dirección</label>
                                <input type="text" class="form-control" id="address" name="address"
                                    value="{{ $branch->address }}" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="phone">Teléfono</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ $branch->phone }}" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ $branch->email }}" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="active">Activa</label>
                                <select class="form-control" id="active" name="active">
                                    <option value="1" {{ $branch->active ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ !$branch->active ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
