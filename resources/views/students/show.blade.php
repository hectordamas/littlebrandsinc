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
                                    <th>Clase / Sede</th>
                                    <th>Monto Cuenta</th>
                                    <th>Abonado</th>
                                    <th>Por Cobrar</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($student->enrollments as $enrollment)
                                    @foreach ($enrollment->courses as $course)
                                        @php
                                            $isFreeTrial = (bool) $enrollment->is_free_trial;
                                            $amountTotal = 0.00;
                                            $amountPaid = 0.00;
                                            $balanceDue = 0.00;

                                            if (!$isFreeTrial) {
                                                if ($enrollment->receivable) {
                                                    $amountTotal = (float) $enrollment->receivable->amount_total;
                                                    $balanceDue = (float) $enrollment->receivable->balance_due;
                                                    $amountPaid = $amountTotal - $balanceDue;
                                                } else {
                                                    $amountTotal = $enrollment->getInitialChargeAmount();
                                                    if ($enrollment->payment_status === 'paid') {
                                                        $amountPaid = $amountTotal;
                                                        $balanceDue = 0.00;
                                                    } else {
                                                        $amountPaid = 0.00;
                                                        $balanceDue = $amountTotal;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    <a href="{{ route('courses.edit', $course->id) }}" target="_blank" class="text-primary text-decoration-none">
                                                        <i class="fas fa-external-link-alt small me-1"></i> {{ $course->title }}
                                                    </a>
                                                    <span class="text-muted small ms-1">(#{{ $course->id }})</span>
                                                </div>
                                                <div class="text-muted small d-flex flex-wrap align-items-center gap-2 mt-1">
                                                    <span><i class="fas fa-layer-group text-secondary me-1"></i>{{ optional($enrollment->program)->name ?? 'N/A' }}</span>
                                                    <span class="text-secondary">•</span>
                                                    <span><i class="fas fa-map-marker-alt text-secondary me-1"></i>{{ optional($course->branch)->name ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($isFreeTrial)
                                                    <span class="text-muted small">Prueba Gratis</span>
                                                @else
                                                    <span class="fw-semibold text-dark">${{ number_format($amountTotal, 2) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($isFreeTrial)
                                                    <span class="text-muted small">-</span>
                                                @else
                                                    <div class="d-flex align-items-center">
                                                        <span class="fw-semibold text-dark">${{ number_format($amountPaid, 2) }}</span>
                                                        @if ($enrollment->transactions->isNotEmpty())
                                                            <button class="btn btn-link btn-xs p-0 ms-2 text-info" type="button" data-bs-toggle="modal" data-bs-target="#payments-modal-{{ $enrollment->id }}" title="Ver historial de abonos">
                                                                <i class="fas fa-history"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($isFreeTrial)
                                                    <span class="text-muted small">-</span>
                                                @else
                                                    <span class="fw-bold text-danger">${{ number_format($balanceDue, 2) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($enrollment->status === 'cancelled')
                                                    <span class="badge bg-danger">Cancelado</span>
                                                @elseif ($enrollment->payment_status === 'paid')
                                                    <span class="badge bg-success">Pagado</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    @if ($enrollment->status !== 'cancelled' && $enrollment->payment_status !== 'paid')
                                                        <button class="btn btn-xs btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#register-payment-modal-{{ $enrollment->id }}" title="Registrar Pago">
                                                            <i class="fas fa-file-invoice-dollar me-1"></i> Pagar
                                                        </button>
                                                    @endif

                                                    @if ($enrollment->status !== 'cancelled' && $enrollment->payment_status === 'paid' && $enrollment->receivable && $enrollment->receivable->balance_due > 0)
                                                        <button class="btn btn-xs btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#register-abono-modal-{{ $enrollment->id }}" title="Registrar Abono">
                                                            <i class="fas fa-plus-circle me-1"></i> Abonar
                                                        </button>
                                                    @endif

                                                    @if ($enrollment->status !== 'cancelled' && $enrollment->is_free_trial)
                                                        <button type="button" class="btn btn-xs btn-outline-info convert-to-paid-btn" data-url="{{ route('enrollment.update', $enrollment->id) }}" title="Convertir a inscripción de pago">
                                                            <i class="fas fa-dollar-sign"></i> Convertir a Pago
                                                        </button>
                                                    @endif

                                                    @if ($enrollment->status !== 'cancelled')
                                                        <form action="{{ route('enrollment.status', $enrollment->id) }}" method="POST" class="d-inline mb-0">
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
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted text-center">Este estudiante no tiene Clases inscritas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Modales de Facturación -->
                    @foreach ($student->enrollments as $enrollment)
                        @php
                            $isFreeTrial = (bool) $enrollment->is_free_trial;
                            $amountTotal = 0.00;
                            $amountPaid = 0.00;
                            $balanceDue = 0.00;

                            if (!$isFreeTrial) {
                                if ($enrollment->receivable) {
                                    $amountTotal = (float) $enrollment->receivable->amount_total;
                                    $balanceDue = (float) $enrollment->receivable->balance_due;
                                    $amountPaid = $amountTotal - $balanceDue;
                                } else {
                                    $amountTotal = $enrollment->getInitialChargeAmount();
                                    if ($enrollment->payment_status === 'paid') {
                                        $amountPaid = $amountTotal;
                                        $balanceDue = 0.00;
                                    } else {
                                        $amountPaid = 0.00;
                                        $balanceDue = $amountTotal;
                                    }
                                }
                            }
                        @endphp

                        <!-- Modal Historial de Abonos -->
                        @if ($enrollment->transactions->isNotEmpty())
                            <div class="modal fade" id="payments-modal-{{ $enrollment->id }}" tabindex="-1" aria-labelledby="payments-modal-label-{{ $enrollment->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold text-dark" id="payments-modal-label-{{ $enrollment->id }}">
                                                <i class="fas fa-history text-primary me-2"></i>Historial de Abonos
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="p-2 bg-light rounded border mb-3">
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Programa:</span>
                                                    <span class="fw-semibold text-dark">{{ optional($enrollment->program)->name ?? 'N/A' }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between small">
                                                    <span class="text-muted">Monto Cuenta:</span>
                                                    <span class="fw-bold text-primary">${{ number_format($amountTotal, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0 small">
                                                    <thead>
                                                        <tr class="table-secondary text-muted">
                                                            <th>Fecha</th>
                                                            <th>Monto</th>
                                                            <th>Cuenta</th>
                                                            <th>Método</th>
                                                            <th>Referencia</th>
                                                            <th>Comprobante</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($enrollment->transactions as $transaction)
                                                            <tr>
                                                                <td>{{ $transaction->created_at ? \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                                                                <td class="fw-semibold text-success">${{ number_format($transaction->amount, 2) }}</td>
                                                                <td>{{ optional($transaction->account)->name ?? 'N/A' }}</td>
                                                                <td>{{ $transaction->payment_method }}</td>
                                                                <td>{{ $transaction->reference ?? 'N/A' }}</td>
                                                                <td>
                                                                    @if ($transaction->payment_receipt_path)
                                                                        <a href="{{ asset($transaction->payment_receipt_path) }}" target="_blank" class="btn btn-xs btn-outline-info">
                                                                            <i class="fas fa-file-download me-1"></i> Ver
                                                                        </a>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Modal Registrar Pago -->
                        @if ($enrollment->payment_status !== 'paid' && $enrollment->status !== 'cancelled')
                            <div class="modal fade" id="register-payment-modal-{{ $enrollment->id }}" tabindex="-1" aria-labelledby="register-payment-modal-label-{{ $enrollment->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('enrollment.attach-payment', $enrollment->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-dark" id="register-payment-modal-label-{{ $enrollment->id }}">
                                                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Registrar Pago
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <!-- Detalle de la Factura (Breakdown) -->
                                                <div class="p-3 border rounded bg-light mb-3" style="border-left: 4px solid #0d6efd !important;">
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

                                                <!-- Monto a Registrar / Pagar (Total) -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold d-block">Monto a Registrar / Pagar (Total)</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input payment-option-radio" type="radio" name="amount_option" id="amount_opt_suggested_{{ $enrollment->id }}" value="suggested" checked data-enrollment-id="{{ $enrollment->id }}">
                                                        <label class="form-check-label small" for="amount_opt_suggested_{{ $enrollment->id }}">
                                                            Monto sugerido (Total: ${{ number_format($enrollment->getInitialChargeAmount(), 2) }})
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input payment-option-radio" type="radio" name="amount_option" id="amount_opt_custom_{{ $enrollment->id }}" value="custom" data-enrollment-id="{{ $enrollment->id }}">
                                                        <label class="form-check-label small" for="amount_opt_custom_{{ $enrollment->id }}">
                                                            Monto total personalizado
                                                        </label>
                                                    </div>
                                                    
                                                    <div class="mt-2 d-none" id="custom-amount-container-{{ $enrollment->id }}">
                                                        <label class="form-label small fw-bold mb-1">Monto total pagado ($)</label>
                                                        <input type="number" name="custom_amount" id="custom_amount_{{ $enrollment->id }}" class="form-control form-control-sm w-100" step="0.01" min="0.01" max="{{ $enrollment->receivable ? $enrollment->receivable->balance_due : '' }}" placeholder="Ingrese el monto total pagado">
                                                        <small class="text-muted d-block mt-1">💡 Si el monto total pagado es menor al sugerido, la diferencia se descontará automáticamente de la inscripción para saldar la cuenta.</small>
                                                    </div>
                                                </div>

                                                <!-- Cuenta de Pago -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Cuenta de Pago</label>
                                                    <select name="account_id" class="form-control form-control-sm" required>
                                                        @foreach ($accounts ?? [] as $account)
                                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Referencia / Observación -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Referencia / Observación</label>
                                                    <input type="text" name="reference" class="form-control form-control-sm" placeholder="Ej. Transacción 1234">
                                                </div>

                                                <!-- Comprobante de Pago -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Comprobante de Pago</label>
                                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-check me-1"></i>Registrar Pago y Confirmar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Modal Registrar Abono (Mensualidad / Saldo) -->
                        @if ($enrollment->status !== 'cancelled' && $enrollment->receivable && $enrollment->receivable->balance_due > 0)
                            <div class="modal fade" id="register-abono-modal-{{ $enrollment->id }}" tabindex="-1" aria-labelledby="register-abono-modal-label-{{ $enrollment->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('finance.collections.payments.store', $enrollment->receivable->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-dark" id="register-abono-modal-label-{{ $enrollment->id }}">
                                                    <i class="fas fa-plus-circle text-success me-2"></i>Registrar Abono a Cuenta
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <!-- Detalle del Saldo -->
                                                <div class="p-3 border rounded bg-light mb-3" style="border-left: 4px solid #198754 !important;">
                                                    <h6 class="fw-bold mb-2 small text-dark"><i class="fas fa-file-invoice-dollar text-success me-1"></i> Estado del Saldo</h6>
                                                    <div class="d-flex justify-content-between small mb-1">
                                                        <span class="text-muted">Total Cuenta:</span>
                                                        <span class="fw-semibold">${{ number_format($enrollment->receivable->amount_total, 2) }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between small mb-1">
                                                        <span class="text-muted">Ya Abonado:</span>
                                                        <span class="fw-semibold text-success">${{ number_format($enrollment->receivable->amount_total - $enrollment->receivable->balance_due, 2) }}</span>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between small fw-bold text-danger">
                                                        <span>Saldo Total Pendiente:</span>
                                                        <span>${{ number_format($enrollment->receivable->balance_due, 2) }}</span>
                                                    </div>
                                                </div>

                                                <!-- Cuenta de Pago -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Cuenta de Pago <span class="text-danger">*</span></label>
                                                    <select name="account_id" class="form-control form-control-sm" required>
                                                        @foreach ($accounts ?? [] as $account)
                                                            <option value="{{ $account->id }}">{{ $account->name }} ({{ strtoupper($account->currency) }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Monto del Abono -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Monto del Abono ($) <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" name="amount" class="form-control form-control-sm" min="0.01" max="{{ $enrollment->receivable->balance_due }}" placeholder="Ej: 70.00" required>
                                                    <small class="text-muted d-block mt-1">El monto del abono no puede superar el saldo pendiente de ${{ number_format($enrollment->receivable->balance_due, 2) }}.</small>
                                                </div>

                                                <!-- Fecha del Pago -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Fecha del Pago <span class="text-danger">*</span></label>
                                                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="form-control form-control-sm" required>
                                                </div>

                                                <!-- Referencia / Observación -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Referencia / Observación</label>
                                                    <input type="text" name="reference" class="form-control form-control-sm" placeholder="Ej. Transacción 1234">
                                                </div>

                                                <!-- Notas adicionales -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Notas o Comentarios</label>
                                                    <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Ej. Pago correspondiente al mes de agosto"></textarea>
                                                </div>

                                                <!-- Comprobante de Pago -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Comprobante de Pago</label>
                                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i>Registrar Abono
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
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

            $('.convert-to-paid-btn').on('click', function(e) {
                e.preventDefault();
                const url = $(this).data('url');

                Swal.fire({
                    title: '¿Convertir a inscripción de pago?',
                    text: 'Se desactivará la prueba gratuita y se generarán los cobros correspondientes.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, convertir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch(url, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    is_free_trial: 0,
                                    payment_status: 'pending'
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'No se pudo realizar la conversión.');
                            }

                            await Swal.fire({
                                icon: 'success',
                                text: 'Inscripción convertida correctamente.',
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#198754'
                            });

                            location.reload();
                        } catch (error) {
                            Swal.fire({
                                icon: 'error',
                                text: error.message || 'Error de conexión.'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection
