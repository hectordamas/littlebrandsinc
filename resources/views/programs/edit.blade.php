@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Editar Programa</title>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Editar Programa</h5>
                    </div>
                    <div class="card-block">
                        <form action="{{ route('programs.update', $program->id) }}" method="POST" class="row">
                            @csrf
                            @method('PUT')
                            
                            <div class="form-group col-md-6">
                                <label for="name">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $program->name) }}" required>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="enrollment_fee">Cuota de Inscripción</label>
                                <input type="number" class="form-control" id="enrollment_fee" name="enrollment_fee"
                                    step="0.01" value="{{ old('enrollment_fee', $program->enrollment_fee) }}" required>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="description">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $program->description) }}</textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="active">Activo</label>
                                <select class="form-control" id="active" name="active" required>
                                    <option value="1" {{ old('active', $program->active) ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ !old('active', $program->active) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                <a href="{{ route('programs.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
