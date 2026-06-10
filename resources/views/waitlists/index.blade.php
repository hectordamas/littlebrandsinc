@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Lista de Espera</title>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div>
                    <h5>Lista de Espera</h5>
                    <span class="text-muted">Estudiantes en espera de cupo por Clase.</span>
                </div>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Estudiante</th>
                                <th>Representante</th>
                                <th>Clase</th>
                                <th>Sede</th>
                                <th>Fecha de registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($waitlists as $waitlist)
                                <tr>
                                    <td>{{ $waitlist->id }}</td>
                                    <td>{{ $waitlist->student->name }}</td>
                                    <td>{{ $waitlist->parent->name }}</td>
                                    <td>{{ $waitlist->course->title }}</td>
                                    <td>{{ $waitlist->course->branch->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($waitlist->created_at)->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($waitlist->status === 'approved')
                                            <span class="badge bg-success">Aprobado</span>
                                        @elseif ($waitlist->status === 'rejected')
                                            <span class="badge bg-danger">Rechazado</span>
                                        @else
                                            <span class="badge bg-warning text-dark">En espera</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($waitlist->status === 'waiting')
                                            <form action="{{ route('waitlists.approve', $waitlist) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Aprobar</button>
                                            </form>
                                            <form action="{{ route('waitlists.reject', $waitlist) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-danger">Rechazar</button>
                                            </form>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay estudiantes en lista de espera.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
