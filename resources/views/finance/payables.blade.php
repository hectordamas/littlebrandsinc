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
                        <h6 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-1 text-primary"></i> Nueva cuenta por pagar</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                                <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name') }}" placeholder="Nombre del proveedor o empresa" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sede</label>
                                <select name="branch_id" class="form-control">
                                    <option value="">Gastos Generales</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId && $selectedBranchId !== 'general' ? $selectedBranchId : '') === (string) $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Concepto / Título <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Ej. Servicio de luz, compra de insumos..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monto total ($) <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="amount_total" class="form-control" value="{{ old('amount_total') }}" data-money-format required placeholder="0.00">
                                <strong class="money-preview" data-money-preview></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha de Vencimiento</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Notas / Observaciones</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="Detalles adicionales sobre esta obligación...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Guardar cuenta por pagar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card mb-3 shadow-sm border-0" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-radius: 0.9rem;">
            <div class="card-body p-3">
                <form id="payablesFilterForm" method="GET" action="{{ route('finance.payables') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="payablesBranchFilter" class="form-label small fw-bold text-muted mb-1">
                                <i class="fas fa-building me-1 text-primary"></i> Filtrar por sede
                            </label>
                            <select id="payablesBranchFilter" name="branch_id" class="form-control form-control-sm" onchange="handlePayablesFilterChange()">
                                <option value="">Todas las sedes</option>
                                <option value="general" @selected($selectedBranchId === 'general')>Gastos Generales</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="payablesPeriodFilter" class="form-label small fw-bold text-muted mb-1">
                                <i class="fas fa-calendar-alt me-1 text-primary"></i> Período de tiempo
                            </label>
                            <select id="payablesPeriodFilter" name="period" class="form-control form-control-sm" onchange="handlePayablesFilterChange()">
                                <option value="" @selected(empty($selectedPeriod))>Todo el histórico</option>
                                <option value="today" @selected($selectedPeriod === 'today')>Hoy</option>
                                <option value="yesterday" @selected($selectedPeriod === 'yesterday')>Ayer</option>
                                <option value="this_week" @selected($selectedPeriod === 'this_week')>Esta semana</option>
                                <option value="last_week" @selected($selectedPeriod === 'last_week')>Semana anterior</option>
                                <option value="this_month" @selected($selectedPeriod === 'this_month')>Este mes</option>
                                <option value="last_month" @selected($selectedPeriod === 'last_month')>Mes anterior</option>
                                <option value="this_quarter" @selected($selectedPeriod === 'this_quarter')>Este trimestre</option>
                                <option value="this_year" @selected($selectedPeriod === 'this_year')>Este año</option>
                                <option value="last_year" @selected($selectedPeriod === 'last_year')>Año anterior</option>
                                <option value="custom" @selected($selectedPeriod === 'custom')>Rango personalizado...</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-lg-4" id="payablesCustomDateContainer" style="{{ $selectedPeriod === 'custom' ? '' : 'display: none;' }}">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="payablesStartDate" class="form-label small fw-bold text-muted mb-1">Desde</label>
                                    <input type="date" id="payablesStartDate" name="start_date" class="form-control form-control-sm" value="{{ $selectedStartDate }}">
                                </div>
                                <div class="col-6">
                                    <label for="payablesEndDate" class="form-label small fw-bold text-muted mb-1">Hasta</label>
                                    <input type="date" id="payablesEndDate" name="end_date" class="form-control form-control-sm" value="{{ $selectedEndDate }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-auto ms-auto d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <a href="{{ route('finance.payables') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                                <i class="fas fa-undo me-1"></i> Limpiar
                            </a>
                            <a href="{{ route('finance.collections', array_filter(['branch_id' => $selectedBranchId, 'period' => $selectedPeriod, 'start_date' => $selectedStartDate, 'end_date' => $selectedEndDate])) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-money-check-dollar me-1"></i> Ir a CxC
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="summary-card shadow-sm">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="summary-label">Saldo pendiente total en CxP</div>
                    <div class="summary-value">${{ number_format($pendingPayableAmount, 2) }}</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPayableModal">
                        <i class="fas fa-plus"></i> Nueva CxP
                    </button>
                    <a href="{{ route('finance.index', array_filter(['branch_id' => $selectedBranchId, 'period' => $selectedPeriod, 'start_date' => $selectedStartDate, 'end_date' => $selectedEndDate])) }}#finance-transactions" class="btn btn-inverse btn-sm">
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
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Concepto</th>
                                <th>Sede</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payables as $payable)
                                <tr>
                                    <td>{{ $payable->id }}</td>
                                    <td>{{ $payable->created_at ? $payable->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $payable->vendor_name }}</td>
                                    <td>{{ $payable->title }}</td>
                                    <td>{{ $payable->branch_id ? (optional($payable->branch)->name ?? 'N/A') : 'Gastos Generales' }}</td>
                                    <td>${{ number_format((float) $payable->amount_total, 2) }}</td>
                                    <td>${{ number_format((float) $payable->balance_due, 2) }}</td>
                                    <td>
                                        @if ($payable->due_date)
                                            <span class="text-nowrap">{{ $payable->due_date->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
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
                                    <td colspan="10" class="text-center text-muted">No hay cuentas por pagar registradas para el período y sede seleccionados.</td>
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
        function handlePayablesFilterChange() {
            const period = $('#payablesPeriodFilter').val();
            if (period === 'custom') {
                $('#payablesCustomDateContainer').stop(true, true).slideDown(200);
            } else {
                $('#payablesCustomDateContainer').stop(true, true).slideUp(200);
                $('#payablesStartDate').val('');
                $('#payablesEndDate').val('');
                $('#payablesFilterForm').submit();
            }
        }

        function payablesExportColumns() {
            return [0, 1, 2, 3, 4, 5, 6, 7, 8];
        }

        function buildPayableButtons() {
            return [{
                    extend: 'copyHtml5',
                    text: '<i class="fas fa-copy"></i> Copiar',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: payablesExportColumns()
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: payablesExportColumns()
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: payablesExportColumns()
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: payablesExportColumns()
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: payablesExportColumns()
                    }
                }
            ];
        }

        $(document).ready(function() {
            const table = $('#payablesTable').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"fB>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3"lip>',
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                buttons: buildPayableButtons(),
                columnDefs: [{
                    targets: [9],
                    orderable: false,
                    searchable: false
                }],
                language: {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    emptyTable: 'No hay cuentas por pagar registradas.',
                    zeroRecords: 'No se encontraron resultados con los filtros aplicados.',
                    paginate: {
                        previous: 'Anterior',
                        next: 'Siguiente'
                    }
                }
            });

            table.buttons().container().addClass('dataTables-actions');

            @if ($errors->any())
                const createModal = new bootstrap.Modal(document.getElementById('createPayableModal'));
                createModal.show();
            @endif
        });
    </script>
@endsection
