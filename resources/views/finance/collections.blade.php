@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Cuentas por Cobrar</title>
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
    <div class="modal fade" id="createReceivableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('finance.collections.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h6 class="mb-0">Nueva cuenta por cobrar</h6>
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
                                <label class="form-label">Monto total</label>
                                <input type="number" step="any" name="amount_total" class="form-control" value="{{ old('amount_total') }}" data-money-format required>
                                <strong class="money-preview" data-money-preview></strong>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Concepto</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
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
                        <button type="submit" class="btn btn-primary">Guardar cuenta por cobrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="summary-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="summary-label">Saldo pendiente total en CxC</div>
                    <div class="summary-value">${{ number_format($pendingCollectionAmount, 2) }}</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createReceivableModal">
                        <i class="fas fa-plus"></i> Nueva CxC
                    </button>
                    <a href="{{ route('finance.index') }}#finance-transactions" class="btn btn-inverse btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver a movimientos
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Cuentas por cobrar</h5>
                <span class="text-muted">Incluye las generadas por inscripción y las creadas manualmente</span>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="receivablesTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Concepto</th>
                                <th>Estudiante</th>
                                <th>Origen CxC</th>
                                <th>Sede</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receivables as $receivable)
                                <tr>
                                    <td>{{ $receivable->id }}</td>
                                    <td>{{ $receivable->title }}</td>
                                    <td>{{ optional(optional($receivable->enrollment)->student)->name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($receivable->enrollment_id)
                                            <span class="badge bg-info text-dark">Automática</span>
                                            <div class="small text-muted mt-1">Inscripción #{{ $receivable->enrollment_id }}</div>
                                        @else
                                            <span class="badge bg-secondary">Manual</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($receivable->branch)->name ?? 'N/A' }}</td>
                                    <td>${{ number_format((float) $receivable->amount_total, 2) }}</td>
                                    <td>${{ number_format((float) $receivable->balance_due, 2) }}</td>
                                    <td>
                                        @if ($receivable->status === 'paid')
                                            <span class="badge bg-primary">Pagada</span>
                                        @elseif ($receivable->status === 'partial')
                                            <span class="badge bg-warning text-dark">Abonada</span>
                                        @elseif ($receivable->status === 'cancelled')
                                            <span class="badge bg-secondary">Cancelada</span>
                                        @else
                                            <span class="badge bg-danger">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('finance.collections.show', $receivable) }}" class="btn btn-sm btn-inverse">
                                            <i class="far fa-eye"></i> Detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No hay cuentas por cobrar registradas.</td>
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
        function receivablesExportColumns() {
            return [0, 1, 2, 3, 4, 5, 6, 7];
        }

        function buildReceivableButtons() {
            return [{
                    extend: 'copyHtml5',
                    text: '<i class="fas fa-copy"></i> Copiar',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: receivablesExportColumns()
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: receivablesExportColumns()
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: receivablesExportColumns()
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: receivablesExportColumns()
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: receivablesExportColumns()
                    }
                }
            ];
        }

        $(document).ready(function() {
            const table = $('#receivablesTable').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"fB>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3"lip>',
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                buttons: buildReceivableButtons(),
                columnDefs: [{
                    targets: [8],
                    orderable: false,
                    searchable: false
                }],
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

            table.buttons().container().addClass('dataTables-actions');

            @if ($errors->any())
                const createModal = new bootstrap.Modal(document.getElementById('createReceivableModal'));
                createModal.show();
            @endif
        });
    </script>
@endsection
