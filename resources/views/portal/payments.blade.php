@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Gestión de Pagos</title>
@endsection

@section('styles')
    <style>
        .finance-header-banner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        .finance-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .finance-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
            border-color: #cbd5e1;
        }

        .status-pill {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 30px;
            display: inline-block;
        }

        .status-pill.status-paid {
            background-color: #ecfdf5;
            color: #10b981;
        }

        .status-pill.status-overdue {
            background-color: #fef2f2;
            color: #ef4444;
        }

        .status-pill.status-pending {
            background-color: #fffbeb;
            color: #f59e0b;
        }

        .status-pill.status-partial {
            background-color: #eff6ff;
            color: #3b82f6;
        }

        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background-color: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-upload-wrapper:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .file-upload-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .custom-tabs .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .custom-tabs .nav-link.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }

        .custom-tabs .nav-link:hover:not(.active) {
            background-color: #f1f5f9;
            color: #0f172a;
        }
    </style>
@endsection

@section('content')
    <!-- Alertas de Éxito / Error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #ecfdf5; color: #065f46;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fs-5"></i>
                <div>
                    {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Cabecera de Finanzas -->
    <div class="finance-header-banner text-white p-4 rounded-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="fw-bold mb-1">Gestión de Pagos y Facturación</h4>
                <p class="mb-0 opacity-75" style="font-size: 0.95rem;">
                    Revisa tus cuotas mensuales, sube tus comprobantes de transferencia o depósito y mantén tu historial al día.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-3 justify-content-md-end">
                <div class="p-3 rounded-3 text-center" style="min-width: 130px; background-color: rgba(255, 255, 255, 0.15); backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.15);">
                    <small class="text-white-50 d-block uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">SALDO PENDIENTE</small>
                    <span class="fs-5 fw-bold text-white">${{ number_format($pendingBalance, 2) }}</span>
                </div>
                <div class="p-3 rounded-3 text-center" style="min-width: 130px; background-color: rgba(255, 255, 255, 0.15); backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.15);">
                    <small class="text-white-50 d-block uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">CUOTAS PENDIENTES</small>
                    <span class="fs-5 fw-bold text-white">{{ $pendingInstallments }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación por pestañas -->
    <div class="row g-3">
        <div class="col-12">
            <ul class="nav nav-pills custom-tabs mb-4 gap-2" id="paymentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="installments-tab" data-bs-toggle="pill" data-bs-target="#installments-pane" type="button" role="tab" aria-selected="true">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Cuotas de Inscripción
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upload-tab" data-bs-toggle="pill" data-bs-target="#upload-pane" type="button" role="tab" aria-selected="false">
                        <i class="fas fa-file-upload me-2"></i>Registrar Pago
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-pane" type="button" role="tab" aria-selected="false">
                        <i class="fas fa-history me-2"></i>Historial de Comprobantes
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="paymentTabsContent">
                <!-- Pestaña 1: Cuotas de Inscripción -->
                <div class="tab-pane fade show active" id="installments-pane" role="tabpanel" aria-labelledby="installments-tab">
                    <div class="card finance-card border-0">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                            <h5 class="fw-bold mb-0 text-dark">Cuotas Próximas y Atrasadas</h5>
                            <small class="text-muted">Desglose de mensualidades y pagos programados asociados a tus hijos.</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="border-collapse: separate;">
                                    <thead>
                                        <tr class="bg-light text-muted" style="font-size: 0.8rem;">
                                            <th class="py-3 px-4">Estudiante</th>
                                            <th class="py-3">Detalle de Cuota</th>
                                            <th class="py-3">Fecha de Vencimiento</th>
                                            <th class="py-3">Monto</th>
                                            <th class="py-3 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($installments as $installment)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="py-3.5 px-4">
                                                    <span class="fw-bold text-dark">{{ optional(optional($installment->enrollment)->student)->name ?? 'N/A' }}</span>
                                                </td>
                                                <td class="py-3.5">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-dark fw-semibold" style="font-size: 0.85rem;">
                                                            Cuota {{ $installment->period_month }}/{{ $installment->period_year }}
                                                        </span>
                                                        <small class="text-muted" style="font-size: 0.75rem;">
                                                            {{ optional(optional($installment->enrollment)->program)->name ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="py-3.5">
                                                    <span class="fw-medium text-dark" style="font-size: 0.85rem;">
                                                        {{ optional($installment->due_date)->format('d/m/Y') ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5 fw-bold text-dark" style="font-size: 0.85rem;">
                                                    ${{ number_format((float) $installment->amount, 2) }} <small class="text-muted uppercase fs-8">{{ $installment->currency }}</small>
                                                </td>
                                                <td class="py-3.5 text-center">
                                                    @php
                                                        $status = $installment->status;
                                                        $dueDate = optional($installment->due_date);
                                                    @endphp
                                                    @if ($status === 'paid' || $status === 'completed')
                                                        <span class="status-pill status-paid">Pagada</span>
                                                    @elseif ($status === 'overdue' || $status === 'failed' || ($status === 'pending' && $dueDate->isPast()))
                                                        <span class="status-pill status-overdue">Vencida / Atrasada</span>
                                                    @else
                                                        <span class="status-pill status-pending">Próxima</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-file-invoice fa-2x mb-2 text-slate-300"></i>
                                                    <p class="mb-0">No se encontraron cuotas registradas para tus inscripciones.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestaña 2: Registrar Pago -->
                <div class="tab-pane fade" id="upload-pane" role="tabpanel" aria-labelledby="upload-tab">
                    <div class="row g-4 justify-content-center">
                        <div class="col-lg-8 col-12">
                            <div class="card finance-card border-0">
                                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                                    <h5 class="fw-bold mb-0 text-dark">Registrar Comprobante de Pago</h5>
                                    <small class="text-muted">Si realizaste una transferencia bancaria o depósito, sube los detalles aquí para alimentar tu saldo.</small>
                                </div>
                                <div class="card-body p-4">
                                    <form action="{{ route('parent.payments.store') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-4">
                                            <div class="col-md-12">
                                                <label for="account_receivable_id" class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Cuenta por Cobrar Asociada</label>
                                                <select name="account_receivable_id" id="account_receivable_id" class="form-select border-slate-200" style="border-radius: 10px; padding: 0.6rem 1rem;" required>
                                                    <option value="">Selecciona una cuenta de cobro pendiente</option>
                                                    @foreach ($receivables->whereIn('status', ['pending', 'partial']) as $item)
                                                        <option value="{{ $item->id }}" data-balance="{{ (float) $item->balance_due }}">
                                                            {{ $item->title }} — Saldo Pendiente: ${{ number_format((float) $item->balance_due, 2) }} 
                                                            ({{ optional(optional($item->enrollment)->student)->name ?? 'Alumno N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="amount" class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Monto Pagado ($)</label>
                                                <input type="number" name="amount" id="amount" class="form-control border-slate-200" style="border-radius: 10px; padding: 0.6rem 1rem;" step="0.01" min="0.01" placeholder="0.00" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="reference" class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Número de Referencia (Opcional)</label>
                                                <input type="text" name="reference" id="reference" class="form-control border-slate-200" style="border-radius: 10px; padding: 0.6rem 1rem;" maxlength="255" placeholder="Eje: #TR-94829">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-bold text-dark" style="font-size: 0.85rem;">Comprobante Digital (Imagen o PDF)</label>
                                                <div class="file-upload-wrapper">
                                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                                    <h6 class="fw-bold mb-1 text-dark">Arrastra tu comprobante aquí o haz clic para buscar</h6>
                                                    <p class="text-muted small mb-0">Formatos permitidos: JPG, PNG, PDF (Tamaño máx. 6 MB)</p>
                                                    <input type="file" name="payment_receipt" id="payment_receipt" class="file-upload-input" accept=".jpg,.jpeg,.png,.pdf" required>
                                                </div>
                                                <div id="file-selected-name" class="mt-2 text-primary fw-semibold small d-none">
                                                    <i class="fas fa-file-alt me-1"></i> Archivo seleccionado: <span id="file-name-text"></span>
                                                </div>
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary px-5 py-2.5" style="border-radius: 10px; font-weight: 600; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);">
                                                    Enviar Comprobante
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestaña 3: Historial de Comprobantes -->
                <div class="tab-pane fade" id="history-pane" role="tabpanel" aria-labelledby="history-tab">
                    <div class="card finance-card border-0">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                            <h5 class="fw-bold mb-0 text-dark">Registro de Envíos Realizados</h5>
                            <small class="text-muted">Estados de revisión de comprobantes subidos por ti para aprobación administrativa.</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="bg-light text-muted" style="font-size: 0.8rem;">
                                            <th class="py-3 px-4">Fecha de Envío</th>
                                            <th class="py-3">Cuenta por Cobrar</th>
                                            <th class="py-3">Referencia</th>
                                            <th class="py-3">Monto Subido</th>
                                            <th class="py-3 text-center">Comprobante</th>
                                            <th class="py-3 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($parentPayments as $payment)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="py-3.5 px-4">
                                                    <span class="text-dark fw-semibold" style="font-size: 0.85rem;">
                                                        {{ optional($payment->created_at)->format('d/m/Y') ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5">
                                                    <span class="text-dark font-medium" style="font-size: 0.85rem;">
                                                        {{ optional($payment->receivable)->title ?? 'Pago' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5">
                                                    <span class="text-muted" style="font-size: 0.85rem; font-family: monospace;">
                                                        {{ $payment->reference ?? '—' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5 fw-bold text-dark" style="font-size: 0.85rem;">
                                                    ${{ number_format((float) $payment->amount, 2) }}
                                                </td>
                                                <td class="py-3.5 text-center">
                                                    @if ($payment->receipt_path)
                                                        <a href="{{ Storage::url($payment->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-primary px-3" style="border-radius: 8px;">
                                                            <i class="fas fa-eye me-1"></i> Ver Recibo
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 text-center">
                                                    @if ($payment->status === 'approved')
                                                        <span class="status-pill status-paid">Aprobado</span>
                                                    @elseif ($payment->status === 'rejected')
                                                        <span class="status-pill status-overdue d-block" style="cursor: help;" data-bs-toggle="tooltip" data-bs-placement="top" title="Motivo: {{ $payment->rejected_reason ?? 'No especificado' }}">
                                                            Rechazado <i class="fas fa-info-circle ms-1"></i>
                                                        </span>
                                                    @else
                                                        <span class="status-pill status-pending">Pendiente de Aprobación</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="fas fa-history fa-2x mb-2 text-slate-300"></i>
                                                    <p class="mb-0">Aún no has registrado comprobantes de pago.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto fill amount field when select changes
            const selectReceivable = document.getElementById('account_receivable_id');
            const amountInput = document.getElementById('amount');
            
            if (selectReceivable) {
                selectReceivable.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const balance = selectedOption.getAttribute('data-balance');
                    if (balance) {
                        amountInput.value = parseFloat(balance).toFixed(2);
                    } else {
                        amountInput.value = '';
                    }
                });
            }

            // File upload label update
            const receiptInput = document.getElementById('payment_receipt');
            const selectedNameDiv = document.getElementById('file-selected-name');
            const nameText = document.getElementById('file-name-text');

            if (receiptInput) {
                receiptInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        nameText.textContent = this.files[0].name;
                        selectedNameDiv.classList.remove('d-none');
                    } else {
                        selectedNameDiv.classList.add('d-none');
                    }
                });
            }

            // Bootstrap Tooltips initialization
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
