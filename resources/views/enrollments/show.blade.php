@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Detalle de Inscripción</title>
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
                    <h5 class="mb-1">Detalle de Inscripción #{{ $enrollment->id }}</h5>
                    <span class="text-muted">Panel de detalle y seguimiento de la inscripción</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('enrollment.receipt', $enrollment) }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf"></i> Descargar comprobante PDF
                    </a>
                    <a href="{{ url('enrollment') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>
            <div class="card-block">

                <div class="detail-section">
                    <div class="d-flex gap-2 flex-wrap">
                        @if ($enrollment->is_free_trial)
                            <span class="badge bg-info fs-6">Clase de prueba gratuita</span>
                        @endif
                        @if ($enrollment->image_consent_accepted)
                            <span class="badge bg-success fs-6">Consentimiento de imagen: Aceptado</span>
                        @else
                            <span class="badge bg-secondary fs-6">Consentimiento de imagen: No aceptado</span>
                        @endif
                    </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Programa</span>
                    <div class="detail-section-title">Información del Programa</div>
                    <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted">Programa</label>
                        <input type="text" class="form-control" value="{{ optional($enrollment->program)->name ?? 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Cuota de inscripción</label>
                        <input type="text" class="form-control"
                            value="{{ optional($enrollment->program)->enrollment_fee !== null ? '$' . number_format((float) $enrollment->program->enrollment_fee, 2) : 'N/A' }}" readonly>
                    </div>
                    @if (optional($enrollment->program)->description)
                    <div class="col-md-12">
                        <label class="form-label text-muted">Descripción</label>
                        <textarea class="form-control" rows="2" readonly>{{ $enrollment->program->description }}</textarea>
                    </div>
                    @endif
                </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Clases</span>
                    <div class="detail-section-title">Clases Inscritas</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Clase</th>
                                    <th>Sede</th>
                                    <th>Horario</th>
                                    <th style="width: 1%; white-space: nowrap;">Cuota mensual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($enrollment->courses as $course)
                                    @php
                                        $weekDays = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                        $classes = $course->classes ?? collect([]);
                                        $grouped = $classes->groupBy(function ($class) {
                                            return optional(\Carbon\Carbon::parse($class->date))->dayOfWeekIso;
                                        })->map(function ($classes, $day) use ($weekDays) {
                                            $dayName = $weekDays[$day] ?? '';
                                            $times = $classes->map(function ($class) {
                                                $start = \Carbon\Carbon::parse($class->start_time);
                                                $end   = \Carbon\Carbon::parse($class->end_time);
                                                $fmt   = function ($t) {
                                                    return $t->format('g:i') . ' ' . ($t->format('A') === 'AM' ? 'a.m.' : 'p.m.');
                                                };
                                                return $fmt($start) . ' - ' . $fmt($end);
                                            })->unique()->values()->join(', ');
                                            return $dayName ? $dayName . ' ' . $times : $times;
                                        })->values();
                                    @endphp
                                    <tr>
                                        <td>{{ $course->title }}</td>
                                        <td>{{ optional($course->branch)->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($grouped->isNotEmpty())
                                                @foreach ($grouped as $line)
                                                    <div>{{ $line }}</div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Sin horario</span>
                                            @endif
                                        </td>
                                        <td>{{ $course->monthly_fee !== null ? '$' . number_format((float) $course->monthly_fee, 2) : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">No hay Clases asociadas a esta inscripción.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                            value="{{ optional($enrollment->student)->birthdate ? \Carbon\Carbon::parse($enrollment->student->birthdate)->age . ' años' : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-muted">Notas médicas</label>
                        <textarea class="form-control" rows="2" readonly>{{ optional($enrollment->student)->medical_notes ?: 'Sin notas médicas.' }}</textarea>
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
                            value="{{ trim((optional(optional($enrollment->student)->user)->dial_code ?? '') . ' ' . (optional(optional($enrollment->student)->user)->whatsapp ?? '')) ?: 'N/A' }}" readonly>
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
                            <label class="form-label text-muted">Comprobante adjunto</label>
                            @if ($enrollment->payment_receipt_path)
                                <div>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ asset($enrollment->payment_receipt_path) }}" target="_blank" rel="noopener">
                                        Ver comprobante
                                    </a>
                                </div>
                            @else
                                <input type="text" class="form-control" value="No adjunto" readonly>
                            @endif
                        </div>

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

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="alert alert-light border mb-0 w-100">
                                El programa y las Clases son solo lectura en este panel para proteger la trazabilidad de la inscripción.
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
