@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Detalle de Estudiante</title>
@endsection

@section('styles')
    <style>
        .detail-section {
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            padding: 1rem;
            background: #fff;
            margin-bottom: 1rem;
        }

        .detail-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #2f3e4d;
            margin-bottom: 0.75rem;
        }

        .detail-chip {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: #eef2f7;
            color: #3d4b59;
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Detalle del Estudiante #{{ $student->id }}</h5>
                    <span class="text-muted">Resumen de cursos inscritos y próximas clases</span>
                </div>
                <a href="{{ route('students.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="card-block">
                <div class="detail-section">
                    <span class="detail-chip">Estudiante</span>
                    <div class="detail-section-title">Información del Estudiante</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Nombre</label>
                            <input type="text" class="form-control" value="{{ $student->name }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Fecha de nacimiento</label>
                            <input type="text" class="form-control"
                                value="{{ $student->birthdate ? \Carbon\Carbon::parse($student->birthdate)->format('d/m/Y') : 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Edad</label>
                            <input type="text" class="form-control"
                                value="{{ $student->birthdate ? \Carbon\Carbon::parse($student->birthdate)->age . ' años' : 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted">Notas médicas</label>
                            <textarea class="form-control" rows="2" readonly>{{ $student->medical_notes ?: 'Sin notas médicas.' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Representante</span>
                    <div class="detail-section-title">Información del Representante</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Nombre</label>
                            <input type="text" class="form-control" value="{{ optional($student->user)->name ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Email</label>
                            <input type="text" class="form-control" value="{{ optional($student->user)->email ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">WhatsApp</label>
                            <input type="text" class="form-control"
                                value="{{ trim((optional($student->user)->dial_code ?? '') . ' ' . (optional($student->user)->whatsapp ?? '')) ?: 'N/A' }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Cursos</span>
                    <div class="detail-section-title">Cursos a los que ha sido inscrito</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Curso</th>
                                    <th>Sede</th>
                                    <th>Periodo</th>
                                    <th>Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($student->enrollments as $enrollment)
                                    <tr>
                                        <td>{{ $enrollment->id }}</td>
                                        <td>{{ optional($enrollment->course)->title ?? 'N/A' }}</td>
                                        <td>{{ optional(optional($enrollment->course)->branch)->name ?? 'N/A' }}</td>
                                        <td>
                                            {{ optional($enrollment->course)->start_date ? \Carbon\Carbon::parse($enrollment->course->start_date)->format('d/m/Y') : 'N/A' }}
                                            -
                                            {{ optional($enrollment->course)->end_date ? \Carbon\Carbon::parse($enrollment->course->end_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            @if ($enrollment->payment_status === 'paid')
                                                <span class="badge bg-success">Pagado</span>
                                            @else
                                                <span class="badge bg-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">Este estudiante no tiene cursos inscritos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Próximas clases</span>
                    <div class="detail-section-title">Clases pendientes por asistir</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Horario</th>
                                    <th>Curso</th>
                                    <th>Sede</th>
                                    <th>Coach</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($upcomingClasses as $class)
                                    <tr>
                                        <td>{{ $class->date ? \Carbon\Carbon::parse($class->date)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('H:i') : 'N/A' }} - {{ $class->end_time ? \Carbon\Carbon::parse($class->end_time)->format('H:i') : 'N/A' }}</td>
                                        <td>{{ optional($class->course)->title ?? 'N/A' }}</td>
                                        <td>{{ optional(optional($class->course)->branch)->name ?? 'N/A' }}</td>
                                        <td>{{ optional($class->coach)->name ?? 'Sin asignar' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">No hay próximas clases registradas para este estudiante.</td>
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
