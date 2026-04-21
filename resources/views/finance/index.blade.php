@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Finanzas y Facturacion</title>
@endsection

@section('styles')
    <style>
        .finance-card {
            border: 1px solid #e9ecef;
            border-radius: 0.9rem;
            padding: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            height: 100%;
        }

        .finance-card-label {
            color: #6b7280;
            font-size: 0.8rem;
            margin-bottom: 0.35rem;
        }

        .finance-card-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1f2937;
        }

        .account-pill {
            display: inline-block;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            background: #eef2f7;
            color: #334155;
            font-size: 0.75rem;
            margin: 0 0.35rem 0.35rem 0;
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

        .finance-form-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.9rem;
            background: #fff;
            padding: 1rem;
        }

        .finance-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: space-between;
            align-items: end;
            margin-bottom: 1rem;
        }

        .finance-toolbar .form-group {
            min-width: 240px;
            margin-bottom: 0;
        }

        .finance-filter-spinner {
            display: none;
            align-items: center;
            gap: 0.45rem;
            color: #475569;
            font-size: 0.85rem;
            margin-bottom: 0.2rem;
        }

        .finance-filter-spinner.is-visible {
            display: inline-flex;
        }

        .finance-table-wrapper {
            position: relative;
        }

        .finance-table-loading {
            position: absolute;
            inset: 0;
            display: none;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.68);
            z-index: 5;
        }

        .finance-table-loading.is-visible {
            display: flex;
        }
    </style>
@endsection

@section('content')
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('finance.transactions.store') }}">
                    @csrf
                    <input type="hidden" name="return_branch_id" id="transactionReturnBranchId" value="{{ $selectedBranchId }}">

                    <div class="modal-header">
                        <h6 class="mb-0">Registrar movimiento manual</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select name="type" class="form-control" required>
                                    <option value="income" @selected(old('type') === 'income')>Ingreso</option>
                                    <option value="expense" @selected(old('type') === 'expense')>Gasto</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cuenta</label>
                                <select name="account_id" class="form-control" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('account_id') === $account->id)>{{ $account->name }} ({{ strtoupper($account->currency) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sede</label>
                                <select name="branch_id" class="form-control" required>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((int) old('branch_id') === $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Monto</label>
                                <input type="number" step="any" name="amount" value="{{ old('amount') }}" class="form-control" data-money-format required>
                                <strong class="money-preview" data-money-preview></strong>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-control" required>
                                    <option value="completed" @selected(old('status', 'completed') === 'completed')>Completado</option>
                                    <option value="pending" @selected(old('status') === 'pending')>Pendiente</option>
                                    <option value="failed" @selected(old('status') === 'failed')>Fallido</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Referencia</label>
                                <input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="Factura, recibo, transferencia...">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Descripcion</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Detalle del movimiento">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Registrar movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="finance-toolbar">
            <div class="form-group">
                <label for="financeBranchFilter" class="form-label">Filtrar por sede</label>
                <select id="financeBranchFilter" class="form-control">
                    <option value="">Todas las sedes</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($selectedBranchId === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="financeFilterSpinner" class="finance-filter-spinner" aria-live="polite">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>Filtrando movimientos...</span>
            </div>

        </div>

        <div class="row g-3 mb-3" id="finance-dashboard">
            <div class="col-md-3">
                <div class="finance-card">
                    <div class="finance-card-label">Ingresos completados</div>
                    <div class="finance-card-value" data-summary-field="completedIncome">${{ number_format($completedIncome, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="finance-card">
                    <div class="finance-card-label">Egresos completados</div>
                    <div class="finance-card-value" data-summary-field="completedExpenses">${{ number_format($completedExpenses, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="finance-card">
                    <div class="finance-card-label">Cobranza pendiente</div>
                    <div class="finance-card-value" data-summary-field="pendingCollectionAmount">${{ number_format($pendingCollectionAmount, 2) }}</div>
                    <div class="mt-2">
                        <a href="{{ route('finance.collections') }}" class="btn btn-sm btn-inverse">
                            <i class="fas fa-money-check-dollar"></i> Ver cobranzas
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="finance-card">
                    <div class="finance-card-label">Balance neto</div>
                    <div class="finance-card-value" data-summary-field="netBalance">${{ number_format($netBalance, 2) }}</div>
                    <div class="small text-muted mt-2" data-summary-field="pendingCollectionsCount">{{ $pendingCollectionsCount }} inscripciones pendientes de cobro</div>
                </div>
            </div>
        </div>



        <div class="card mb-3" id="finance-transactions">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Movimientos financieros</h5>
                    <span class="text-muted">Ledger central de ingresos y egresos del sistema</span>
                </div>
                <div>
                    <button type="button" class="btn btn-inverse" data-bs-toggle="modal" data-bs-target="#transactionModal">
                        <i class="fas fa-plus"></i> Registrar movimiento
                    </button>
                </div>
            </div>
            <div class="card-block">
                <div class="finance-table-wrapper">
                    <div id="financeTableLoading" class="finance-table-loading" aria-hidden="true">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <span>Cargando datos...</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="transactionsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Cuenta</th>
                                    <th>Sede</th>
                                    <th>Referencia</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let transactionsTableInstance;
        let financeFilterRequest = null;

        function transactionsExportColumns() {
            return [0, 1, 2, 3, 4, 5, 6, 7];
        }

        function buildButtons(columnsCallback) {
            return [{
                    extend: 'copyHtml5',
                    text: '<i class="fas fa-copy"></i> Copiar',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: columnsCallback()
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: columnsCallback()
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: columnsCallback()
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: columnsCallback()
                    },
                    orientation: 'landscape',
                    pageSize: 'A4',
                    customize: function(doc) {
                        doc.pageMargins = [12, 12, 12, 12];
                        doc.defaultStyle.fontSize = 9;
                        doc.styles.tableHeader.fontSize = 10;
                        const tableBody = doc.content[1].table.body;
                        const columnCount = tableBody[0].length;
                        doc.content[1].table.widths = Array(columnCount).fill('*');
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-sm btn-inverse',
                    exportOptions: {
                        columns: columnsCallback()
                    }
                }
            ];
        }

        function formatCurrency(value) {
            return `$${Number(value || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        }

        function initializeTransactionsTable() {
            transactionsTableInstance = $('#transactionsTable').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"fB>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3"lip>',
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                buttons: buildButtons(transactionsExportColumns),
                language: {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    emptyTable: 'No hay transacciones registradas.',
                    zeroRecords: 'No hay transacciones para la sede seleccionada.',
                    paginate: {
                        previous: 'Anterior',
                        next: 'Siguiente'
                    }
                }
            });

            transactionsTableInstance.buttons().container().addClass('dataTables-actions');
        }

        function updateSummary(summary) {
            $('[data-summary-field="completedIncome"]').text(formatCurrency(summary.completedIncome));
            $('[data-summary-field="completedExpenses"]').text(formatCurrency(summary.completedExpenses));
            $('[data-summary-field="pendingCollectionAmount"]').text(formatCurrency(summary.pendingCollectionAmount));
            $('[data-summary-field="netBalance"]').text(formatCurrency(summary.netBalance));
            $('[data-summary-field="pendingCollectionsCount"]').text(`${summary.pendingCollectionsCount} inscripciones pendientes de cobro`);
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = String(value ?? '');
            return div.innerHTML;
        }

        function renderTypeBadge(type) {
            if (type === 'income') {
                return '<span class="badge bg-success">Ingreso</span>';
            }

            return '<span class="badge bg-danger">Egreso</span>';
        }

        function renderStatusBadge(status) {
            if (status === 'completed') {
                return '<span class="badge bg-primary">Completado</span>';
            }

            if (status === 'pending') {
                return '<span class="badge bg-warning text-dark">Pendiente</span>';
            }

            return '<span class="badge bg-secondary">Fallido</span>';
        }

        function renderReceiptButton(transaction) {
            if (!transaction.receipt_url) {
                return '<span class="text-muted">N/A</span>';
            }

            return `<a href="${escapeHtml(transaction.receipt_url)}" class="btn btn-sm btn-inverse" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-pdf"></i></a>`;
        }

        function renderTransactionsRows(transactions) {
            const rowsHtml = (transactions || []).map(function(transaction) {
                return `
                    <tr>
                        <td>${escapeHtml(transaction.id)}</td>
                        <td>${escapeHtml(transaction.created_at)}</td>
                        <td>${renderTypeBadge(transaction.type)}</td>
                        <td>${formatCurrency(transaction.amount)}</td>
                        <td>${renderStatusBadge(transaction.status)}</td>
                        <td>${escapeHtml(transaction.account)}</td>
                        <td>${escapeHtml(transaction.branch)}</td>
                        <td>${escapeHtml(transaction.reference)}</td>
                        <td>${renderReceiptButton(transaction)}</td>
                    </tr>
                `;
            }).join('');

            $('#transactionsTableBody').html(rowsHtml);
        }

        function loadFinanceData(branchId) {
            const filter = $('#financeBranchFilter');
            const spinner = $('#financeFilterSpinner');
            const tableLoading = $('#financeTableLoading');
            const requestData = branchId ? {
                branch_id: branchId
            } : {};

            if (financeFilterRequest) {
                financeFilterRequest.abort();
            }

            filter.prop('disabled', true);
            spinner.addClass('is-visible');
            tableLoading.addClass('is-visible').attr('aria-hidden', 'false');

            financeFilterRequest = $.ajax({
                url: '{{ route('finance.index') }}',
                method: 'GET',
                data: requestData,
                timeout: 15000,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });

            financeFilterRequest
                .done(function(response) {
                    try {
                        if ($.fn.DataTable.isDataTable('#transactionsTable')) {
                            $('#transactionsTable').DataTable().destroy();
                        }

                        updateSummary(response.summary);
                        renderTransactionsRows(response.transactions);
                        initializeTransactionsTable();
                    } catch (error) {
                        console.error('Error al refrescar la tabla de movimientos', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo actualizar la tabla',
                            text: 'La sede se filtró, pero ocurrió un error al renderizar los movimientos.'
                        });
                    }
                })
                .fail(function(xhr, status) {
                    if (status === 'abort') {
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo filtrar',
                        text: 'No fue posible actualizar la información financiera para la sede seleccionada.'
                    });
                })
                .always(function() {
                    filter.prop('disabled', false);
                    spinner.removeClass('is-visible');
                    tableLoading.removeClass('is-visible').attr('aria-hidden', 'true');
                    financeFilterRequest = null;
                });
        }

        $(document).ready(function() {
            loadFinanceData($('#financeBranchFilter').val());

            $('#financeBranchFilter').on('change', function() {
                $('#transactionReturnBranchId').val($(this).val());
                loadFinanceData($(this).val());
            });

            @if ($errors->any())
                const transactionModal = new bootstrap.Modal(document.getElementById('transactionModal'));
                transactionModal.show();
            @endif
        });
    </script>
@endsection
