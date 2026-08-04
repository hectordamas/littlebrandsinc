@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Detalle CxC</title>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Cuenta por cobrar #{{ $receivable->id }}</h5>
                    <span class="text-muted">{{ $receivable->title }}</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editReceivableModal">
                        <i class="fas fa-edit"></i> Editar Cuenta
                    </button>
                    <a href="{{ route('finance.collections') }}" class="btn btn-inverse btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver a CxC
                    </a>
                </div>
            </div>
            <div class="card-block">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Sede:</strong> {{ $receivable->branch_id ? (optional($receivable->branch)->name ?? 'N/A') : 'Ingresos Generales' }}</div>
                    <div class="col-md-3"><strong>Estudiante:</strong> {{ optional(optional($receivable->enrollment)->student)->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Inscripción:</strong> {{ $receivable->enrollment_id ? '#'.$receivable->enrollment_id : 'Manual' }}</div>
                    <div class="col-md-3"><strong>Vencimiento:</strong> {{ $receivable->due_date ? $receivable->due_date->format('d/m/Y') : 'N/A' }}</div>
                    <div class="col-md-3"><strong>Total:</strong> ${{ number_format((float) $receivable->amount_total, 2) }}</div>
                    <div class="col-md-3"><strong>Saldo:</strong> ${{ number_format((float) $receivable->balance_due, 2) }}</div>
                    <div class="col-md-3"><strong>Estado:</strong> {{ ucfirst($receivable->status) }}</div>
                    <div class="col-md-3"><strong>Programa:</strong> {{ optional(optional($receivable->enrollment)->program)->name ?? 'N/A' }}</div>
                    <div class="col-md-12"><strong>Clases:</strong> {{ $receivable->enrollment ? $receivable->enrollment->courses->pluck('title')->join(', ') ?: 'Sin Clases' : 'N/A' }}</div>
                    <div class="col-md-12"><strong>Notas:</strong> {{ $receivable->notes ?: 'Sin notas' }}</div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Cuenta por Cobrar -->
        <div class="modal fade" id="editReceivableModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('finance.collections.update', $receivable) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-1 text-primary"></i> Editar monto de la cuenta #{{ $receivable->id }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Monto total ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount_total" class="form-control form-control-sm" value="{{ old('amount_total', $receivable->amount_total) }}" required min="0.01">
                                <small class="text-muted d-block mt-2">💡 Al modificar el monto total, el saldo pendiente se recalculará automáticamente.</small>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header">
                <h6 class="mb-1">Registrar abono</h6>
            </div>
            <div class="card-block">
                <form method="POST" action="{{ route('finance.collections.payments.store', $receivable) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Cuenta</label>
                            <select name="account_id" class="form-control" required>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((int) old('account_id') === $account->id)>{{ $account->name }} ({{ strtoupper($account->currency) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Monto</label>
                            <input type="number" step="any" name="amount" value="{{ old('amount') }}" class="form-control" data-money-format required>
                            <strong class="money-preview" data-money-preview></strong>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Referencia</label>
                            <input type="text" name="reference" value="{{ old('reference') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Comprobante de pago</label>
                            <input type="file" name="payment_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Formatos permitidos: JPG, JPEG, PNG, PDF.</small>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar abono</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-1">Abonos registrados</h6>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="receivablePaymentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Cuenta</th>
                                <th>Referencia</th>
                                <th>Comprobante</th>
                                <th>Movimiento</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receivable->transactions as $payment)
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td>{{ $payment->created_at ? $payment->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>${{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ optional($payment->account)->name ?? 'N/A' }}</td>
                                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $receiptPath = $payment->payment_receipt_path ?: optional($receivable->enrollment)->payment_receipt_path;
                                        @endphp
                                        @if ($receiptPath)
                                            <a href="{{ asset($receiptPath) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Ver</a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>#{{ $payment->id }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-xs btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editPaymentModal{{ $payment->id }}" title="Editar Abono">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('finance.transactions.destroy', $payment->id) }}" method="POST" class="d-inline mb-0" onsubmit="return confirm('¿Seguro que deseas eliminar este abono? El saldo de la cuenta será recalculado automáticamente.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Eliminar Abono">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Editar Abono -->
                                <div class="modal fade" id="editPaymentModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('finance.transactions.update', $payment->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h6 class="mb-0 fw-bold"><i class="fas fa-edit text-warning me-1"></i> Editar abono #{{ $payment->id }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Monto del abono ($) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" name="amount" class="form-control form-control-sm" value="{{ old('amount', $payment->amount) }}" required min="0.01">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Cuenta de pago <span class="text-danger">*</span></label>
                                                        <select name="account_id" class="form-control form-control-sm" required>
                                                            @foreach ($accounts as $acc)
                                                                <option value="{{ $acc->id }}" @selected($payment->account_id == $acc->id)>{{ $acc->name }} ({{ strtoupper($acc->currency) }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Fecha del pago <span class="text-danger">*</span></label>
                                                        <input type="date" name="payment_date" value="{{ old('payment_date', $payment->created_at ? $payment->created_at->format('Y-m-d') : now()->toDateString()) }}" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Referencia</label>
                                                        <input type="text" name="reference" value="{{ old('reference', $payment->reference) }}" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Notas</label>
                                                        <textarea name="description" class="form-control form-control-sm" rows="2">{{ old('description', $payment->description) }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Cambiar comprobante</label>
                                                        <input type="file" name="payment_receipt" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                                                        @if ($payment->payment_receipt_path)
                                                            <small class="text-muted d-block mt-1">Actual: <a href="{{ asset($payment->payment_receipt_path) }}" target="_blank">Ver comprobante</a></small>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-save me-1"></i> Actualizar abono</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay abonos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#receivablePaymentsTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                language: {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    paginate: {
                        previous: 'Anterior',
                        next: 'Siguiente'
                    }
                }
            });
        });
    </script>
@endsection
