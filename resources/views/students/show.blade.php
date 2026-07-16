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
                    <span class="text-muted">Resumen de Clases inscritas y próximas Clases</span>
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
                    <span class="detail-chip">Clases</span>
                    <div class="detail-section-title">Clases a las que ha sido inscrito</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Programa</th>
                                    <th>Clases</th>
                                    <th>Sede</th>
                                    <th>Pago / Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($student->enrollments as $enrollment)
                                    @foreach ($enrollment->courses as $course)
                                        <tr>
                                            <td>{{ $course->id }}</td>
                                            <td>{{ optional($enrollment->program)->name ?? 'N/A' }}</td>
                                            <td>{{ $course->title }}</td>
                                            <td>{{ optional($course->branch)->name ?? 'N/A' }}</td>
                                            <td>
                                                @if ($enrollment->status === 'cancelled')
                                                    <span class="badge bg-danger">Cancelado</span>
                                                @elseif ($enrollment->payment_status === 'paid')
                                                    <span class="badge bg-success">Pagado</span>
                                                @else
                                                    <div>
                                                        <span class="badge bg-warning text-dark mb-1">Pendiente</span>
                                                        <button class="btn btn-xs btn-outline-primary d-block mx-auto" type="button" data-bs-toggle="collapse" data-bs-target="#attach-payment-{{ $enrollment->id }}-{{ $course->id }}" aria-expanded="false" aria-controls="attach-payment-{{ $enrollment->id }}-{{ $course->id }}">
                                                            <i class="fas fa-file-invoice-dollar"></i> Registrar Pago
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($enrollment->status !== 'cancelled')
                                                    <form action="{{ route('enrollment.status', $enrollment->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('¿Seguro que deseas retirar a este estudiante del curso? Se cancelará su inscripción.')" title="Retirar estudiante">
                                                            <i class="fas fa-user-minus"></i> Retirar
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($enrollment->payment_status !== 'paid' && $enrollment->status !== 'cancelled')
                                            <tr class="collapse" id="attach-payment-{{ $enrollment->id }}-{{ $course->id }}">
                                                <td colspan="6" class="bg-light p-0">
                                                    <div class="p-3 border rounded m-2 bg-white shadow-sm text-start">
                                                        <form action="{{ route('enrollment.attach-payment', $enrollment->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            
                                                            <!-- Detalle de la Factura (Breakdown) -->
                                                            <div class="row g-3 mb-3">
                                                                <div class="col-md-12 text-start">
                                                                    <div class="p-3 border rounded bg-light" style="border-left: 4px solid #0d6efd !important;">
                                                                        <h6 class="fw-bold mb-2 small text-dark"><i class="fas fa-file-invoice-dollar text-primary me-1"></i> Detalle de la Factura (Monto Sugerido)</h6>
                                                                        @php
                                                                            $enrollmentFee = $enrollment->getEnrollmentFee();
                                                                            $monthlyFees = 0.0;
                                                                            foreach ($enrollment->courses as $c) {
                                                                                $monthlyFees += (float) ($c->monthly_fee ?? 0);
                                                                            }
                                                                            $suggestedTotal = $enrollmentFee + $monthlyFees;
                                                                        @endphp
                                                                        <div class="d-flex justify-content-between small mb-1">
                                                                            <span class="text-muted">Inscripción:</span>
                                                                            <span class="fw-semibold">${{ number_format($enrollmentFee, 2) }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-between small mb-1">
                                                                            <span class="text-muted">Mensualidad (1er Mes):</span>
                                                                            <span class="fw-semibold">${{ number_format($monthlyFees, 2) }}</span>
                                                                        </div>
                                                                        <hr class="my-2">
                                                                        <div class="d-flex justify-content-between small fw-bold">
                                                                            <span>Total Sugerido:</span>
                                                                            <span class="text-primary">${{ number_format($suggestedTotal, 2) }}</span>
                                                                        </div>
                                                                        @if($enrollment->receivable)
                                                                            <div class="d-flex justify-content-between small fw-bold text-danger mt-1">
                                                                                <span>Saldo Total Pendiente:</span>
                                                                                <span>${{ number_format($enrollment->receivable->balance_due, 2) }}</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Monto a Registrar / Pagar (Total) -->
                                                            <div class="row g-3 mb-3">
                                                                <div class="col-md-12 text-start">
                                                                    <label class="form-label small fw-bold d-block">Monto a Registrar / Pagar (Total)</label>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input payment-option-radio" type="radio" name="amount_option" id="amount_opt_suggested_{{ $enrollment->id }}_{{ $course->id }}" value="suggested" checked data-enrollment-id="{{ $enrollment->id }}-{{ $course->id }}">
                                                                        <label class="form-check-label" for="amount_opt_suggested_{{ $enrollment->id }}_{{ $course->id }}">
                                                                            Monto sugerido (Total: ${{ number_format($enrollment->getInitialChargeAmount(), 2) }})
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input payment-option-radio" type="radio" name="amount_option" id="amount_opt_custom_{{ $enrollment->id }}_{{ $course->id }}" value="custom" data-enrollment-id="{{ $enrollment->id }}-{{ $course->id }}">
                                                                        <label class="form-check-label" for="amount_opt_custom_{{ $enrollment->id }}_{{ $course->id }}">
                                                                            Monto total personalizado
                                                                        </label>
                                                                    </div>
                                                                    
                                                                    <div class="mt-2 d-none" id="custom-amount-container-{{ $enrollment->id }}-{{ $course->id }}">
                                                                        <label class="form-label small fw-bold mb-1">Monto total pagado ($)</label>
                                                                        <input type="number" name="custom_amount" id="custom_amount_{{ $enrollment->id }}-{{ $course->id }}" class="form-control form-control-sm w-50" step="0.01" min="0.01" max="{{ $enrollment->receivable ? $enrollment->receivable->balance_due : '' }}" placeholder="Ingrese el monto total pagado">
                                                                        <small class="text-muted d-block mt-1">💡 Si el monto total pagado es menor al sugerido, la diferencia se descontará automáticamente de la inscripción para saldar la cuenta.</small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row g-3">
                                                                <div class="col-md-4 text-start">
                                                                    <label class="form-label small fw-bold">Cuenta de Pago</label>
                                                                    <select name="account_id" class="form-control form-control-sm" required>
                                                                        @foreach ($accounts ?? [] as $account)
                                                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4 text-start">
                                                                    <label class="form-label small fw-bold">Referencia / Observación</label>
                                                                    <input type="text" name="reference" class="form-control form-control-sm" placeholder="Ej. Transacción 1234">
                                                                </div>
                                                                <div class="col-md-4 text-start">
                                                                    <label class="form-label small fw-bold">Comprobante de Pago</label>
                                                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                                                </div>
                                                                <div class="col-md-12 text-end mt-2">
                                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                                        <i class="fas fa-check"></i> Registrar Pago y Confirmar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted text-center">Este estudiante no tiene Clases inscritas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="detail-section">
                    <span class="detail-chip">Próximas Clases</span>
                    <div class="detail-section-title">Sesiones de Clase pendientes por asistir</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Horario</th>
                                    <th>Clase</th>
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
                                        <td>{{ $class->coach_name ?? 'Sin asignar' }}</td>
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

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.payment-option-radio').on('change', function() {
                let enrollmentId = $(this).data('enrollment-id');
                let customContainer = $('#custom-amount-container-' + enrollmentId);
                let customInput = $('#custom_amount_' + enrollmentId);

                if ($(this).val() === 'custom') {
                    customContainer.removeClass('d-none');
                    customInput.prop('required', true);
                } else {
                    customContainer.addClass('d-none');
                    customInput.prop('required', false).val('');
                }
            });
        });
    </script>
@endsection
