@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Detalle de Inscripcion</title>
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
                    <h5 class="mb-1">Detalle de Inscripcion #{{ $enrollment->id }}</h5>
                    <span class="text-muted">Panel de detalle y seguimiento de la inscripcion</span>
                </div>
                <a href="{{ url('enrollment') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="card-block">
                <div class="detail-section">
                    <span class="detail-chip">Curso</span>
                    <div class="detail-section-title">Informacion del Curso Inscrito</div>
                    <div class="row g-3">
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Curso</label>
                        <input type="text" class="form-control" value="{{ optional($enrollment->course)->title ?? 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Sede</label>
                        <input type="text" class="form-control"
                            value="{{ optional(optional($enrollment->course)->branch)->name ?? 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Fecha de inicio</label>
                        <input type="text" class="form-control"
                            value="{{ optional($enrollment->course)->start_date ? \Carbon\Carbon::parse($enrollment->course->start_date)->format('d/m/Y') : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Fecha de fin</label>
                        <input type="text" class="form-control"
                            value="{{ optional($enrollment->course)->end_date ? \Carbon\Carbon::parse($enrollment->course->end_date)->format('d/m/Y') : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Precio</label>
                        <input type="text" class="form-control"
                            value="{{ optional($enrollment->course)->price !== null ? '$' . number_format((float) $enrollment->course->price, 2) : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-muted">Descripcion</label>
                        <textarea class="form-control" rows="2" readonly>{{ optional($enrollment->course)->description ?: 'Sin descripcion.' }}</textarea>
                    </div>
                </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Agenda</span>
                    <div class="detail-section-title">Clases y Horarios</div>
                    <div class="row g-3">
                        <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Horario</th>
                                        <th>Coach</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (optional($enrollment->course)->classes ?? [] as $class)
                                        <tr>
                                            <td>{{ $class->date ? \Carbon\Carbon::parse($class->date)->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('H:i') : 'N/A' }} - {{ $class->end_time ? \Carbon\Carbon::parse($class->end_time)->format('H:i') : 'N/A' }}</td>
                                            <td>{{ optional($class->coach)->name ?? 'Sin asignar' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted">No hay clases registradas para este curso.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Estudiante</span>
                    <div class="detail-section-title">Información del Estudiante</div>
                    <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted">Nombre</label>
                        <input type="text" class="form-control" value="{{ optional($enrollment->student)->name ?? 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Fecha de nacimiento</label>
                        <input type="text" class="form-control"
                            value="{{ optional($enrollment->student)->birthdate ? \Carbon\Carbon::parse($enrollment->student->birthdate)->format('d/m/Y') : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Edad</label>
                        <input type="text" class="form-control"
                            value="{{ optional($enrollment->student)->birthdate ? \Carbon\Carbon::parse($enrollment->student->birthdate)->age . ' anios' : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-muted">Notas medicas</label>
                        <textarea class="form-control" rows="2" readonly>{{ optional($enrollment->student)->medical_notes ?: 'Sin notas medicas.' }}</textarea>
                    </div>
                </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Representante</span>
                    <div class="detail-section-title">Información del Representante</div>
                    <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted">Nombre</label>
                        <input type="text" class="form-control"
                            value="{{ optional(optional($enrollment->student)->user)->name ?? 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Email</label>
                        <input type="text" class="form-control"
                            value="{{ optional(optional($enrollment->student)->user)->email ?? 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">WhatsApp</label>
                        <input type="text" class="form-control"
                            value="{{ optional(optional($enrollment->student)->user)->whatsapp ?? 'N/A' }}" readonly>
                    </div>
                </div>
                </div>

                <form method="POST" action="{{ route('enrollment.update', $enrollment) }}" class="detail-section">
                    @csrf
                    @method('PATCH')
                    <span class="detail-chip">Gestión</span>
                    <div class="detail-section-title">Actualizar Estado de Pago</div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Estado de pago</label>
                            <select name="payment_status" class="form-control">
                                <option value="pending" @selected($enrollment->payment_status === 'pending')>Pendiente</option>
                                <option value="paid" @selected($enrollment->payment_status === 'paid')>Pagado</option>
                            </select>
                            @error('payment_status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8 d-flex align-items-end">
                            <div class="alert alert-light border mb-0 w-100">
                                El curso es solo lectura en este panel para proteger la trazabilidad de la inscripcion.
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
