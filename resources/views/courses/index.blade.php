@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Cursos</title>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Todos los Cursos</h5>
                            <span class="text-muted">Gestión y seguimiento de cursos activos en el sistema</span>
                        </div>

                        <a href="{{ route('courses.create') }}" class="btn btn-inverse btn-sm">
                        <i class="fas fa-plus"></i> Agregar Curso</a>
                    </div>
                    <div class="card-block">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Edad Mínima</th>
                                    <th>Edad Máxima</th>
                                    <th>Precio</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Activo</th>
                                    <th>Sede</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courses as $course)
                                    <tr>
                                        <td>{{ $course->id }}</td>
                                        <td>{{ $course->name }}</td>
                                        <td>{{ $course->min_age ?? 'N/A' }}</td>
                                        <td>{{ $course->max_age ?? 'N/A' }}</td>
                                        <td>{{ $course->price ? '$' . number_format($course->price, 2) : 'N/A' }}</td>
                                        <td>{{ $course->start_date->format('Y-m-d') }}</td>
                                        <td>{{ $course->end_date->format('Y-m-d') }}</td>
                                        <td>{{ $course->active ? 'Sí' : 'No' }}</td>
                                        <td>{{ $course->branch->name ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('courses.edit', $course->id) }}"
                                                class="btn btn-sm btn-success">Editar</a>
                                            <form action="{{ route('courses.destroy', $course->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11">No se encontraron cursos.</td>
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
