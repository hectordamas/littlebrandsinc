@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Cuentas por Pagar</title>
@endsection

@section('styles')
    <style>
        .summary-card {
            border: 1px solid #e9ecef;
            border-radius: 0.9rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .summary-label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .summary-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #111827;
        }

        .dataTables-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .dataTables-actions .dt-button {
            border: 0;
            box-shadow: none;
        }
    </style>
@endsection

@section('content')
    <div class="modal fade" id="createPayableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('finance.payables.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h6 class="mb-0">Nueva cuenta por pagar</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Sede</label>
                                <select name="branch_id" class="form-control" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((int) old('branch_id') === $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Proveedor</label>
                                <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name') }}" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Concepto</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Monto total</label>
                                <input type="number" step="any" name="amount_total" class="form-control" value="{{ old('amount_total') }}" data-money-format required>
                                <strong class="money-preview" data-money-preview></strong>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Vencimiento</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notas</label>
                                <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cuenta por pagar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="summary-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="summary-label">Saldo pendiente total en CxP</div>
                    <div class="summary-value">${{ number_format($pendingPayableAmount, 2) }}</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPayableModal">
                        <i class="fas fa-plus"></i> Nueva CxP
                    </button>
                    <a href="{{ route('finance.index') }}#finance-transactions" class="btn btn-inverse btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver a movimientos
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Cuentas por pagar</h5>
                <span class="text-muted">Obligaciones pendientes de la empresa y su seguimiento de pagos</span>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="payablesTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Proveedor</th>
                                <th>Concepto</th>
                                <th>Sede</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payables as $payable)
                                <tr>
                                    <td>{{ $payable->id }}</td>
                                    <td>{{ $payable->vendor_name }}</td>
                                    <td>{{ $payable->title }}</td>
                                    <td>{{ optional($payable->branch)->name ?? 'N/A' }}</td>
                                    <td>${{ number_format((float) $payable->amount_total, 2) }}</td>
                                    <td>${{ number_format((float) $payable->balance_due, 2) }}</td>
                                    <td>
                                        @if ($payable->status === 'paid')
                                            <span class="badge bg-primary">Pagada</span>
                                        @elseif ($payable->status === 'partial')
                                            <span class="badge bg-warning text-dark">Abonada</span>
                                        @elseif ($payable->status === 'cancelled')
                                            <span class="badge bg-secondary">Cancelada</span>
                                        @else
                                            <span class="badge bg-danger">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('finance.payables.show', $payable) }}" class="btn btn-sm btn-inverse">
                                            <i class="far fa-eye"></i> Detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay cuentas por pagar registradas.</td>
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
            const table = $('#payablesTable').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"fB>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3"lip>',
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

            @if ($errors->any())
                const createModal = new bootstrap.Modal(document.getElementById('createPayableModal'));
                createModal.show();
            @endif
        });
    </script>
@endsection
