@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Programas</title>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Todos los Programas</h5>
                            <span class="text-muted">Gestión de programas y configuración de sus cuotas de inscripción</span>
                        </div>
                    </div>
                    <div class="card-block">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Cuota de Inscripción</th>
                                    <th>Activo</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($programs as $program)
                                    <tr>
                                        <td>{{ $program->id }}</td>
                                        <td>{{ $program->name }}</td>
                                        <td>{{ $program->description }}</td>
                                        <td>${{ number_format($program->enrollment_fee, 2) }}</td>
                                        <td>{{ $program->active ? 'Sí' : 'No' }}</td>
                                        <td>
                                            <a href="{{ route('programs.edit', $program->id) }}"
                                                class="btn btn-sm btn-success">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">No se encontraron programas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
