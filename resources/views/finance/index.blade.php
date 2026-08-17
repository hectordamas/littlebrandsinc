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
    <div class="modal fade" id="paymentProofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="mb-0">Comprobante de pago</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="paymentProofContainer" class="text-center"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('finance.transactions.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="return_branch_id" id="transactionReturnBranchId" value="{{ $selectedBranchId }}">

                    <div class="modal-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-1 text-primary"></i> Registrar movimiento manual</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" required>
                                    <option value="income" @selected(old('type') === 'income')>Ingreso</option>
                                    <option value="expense" @selected(old('type') === 'expense')>Gasto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monto ($) <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="amount" value="{{ old('amount') }}" class="form-control" data-money-format required placeholder="0.00">
                                <strong class="money-preview" data-money-preview></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cuenta / Método de Pago <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-control" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('account_id') === $account->id)>{{ $account->name }} ({{ strtoupper($account->currency) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sede</label>
                                <select name="branch_id" id="transactionBranchSelect" class="form-control">
                                    <option value="" id="modalGeneralBranchOption">Ingresos Generales</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((int) old('branch_id') === $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="completed" @selected(old('status', 'completed') === 'completed')>Completado</option>
                                    <option value="pending" @selected(old('status') === 'pending')>Pendiente</option>
                                    <option value="failed" @selected(old('status') === 'failed')>Fallido</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha del Movimiento</label>
                                <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Referencia</label>
                                <input type="text" name="reference" value="{{ old('reference') }}" class="form-control" placeholder="Factura, recibo, transferencia...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Comprobante de Pago</label>
                                <input type="file" name="payment_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted d-block mt-1">Formatos: JPG, PNG, PDF. Máx 2 MB.</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Descripción / Detalle</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Detalle adicional del movimiento...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Registrar movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="editTransactionForm" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-1 text-primary"></i> Editar movimiento financiero</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <select name="type" id="editTransactionType" class="form-control" required>
                                    <option value="income">Ingreso</option>
                                    <option value="expense">Gasto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monto ($) <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="amount" id="editTransactionAmount" class="form-control" data-money-format required placeholder="0.00">
                                <strong class="money-preview" data-money-preview></strong>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cuenta / Método de Pago <span class="text-danger">*</span></label>
                                <select name="account_id" id="editTransactionAccountId" class="form-control" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ strtoupper($account->currency) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sede</label>
                                <select name="branch_id" id="editTransactionBranchId" class="form-control">
                                    <option value="" id="editModalGeneralBranchOption">Ingresos Generales</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select name="status" id="editTransactionStatus" class="form-control" required>
                                    <option value="completed">Completado</option>
                                    <option value="pending">Pendiente</option>
                                    <option value="failed">Fallido</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha del Movimiento</label>
                                <input type="date" name="payment_date" id="editTransactionPaymentDate" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Referencia</label>
                                <input type="text" name="reference" id="editTransactionReference" class="form-control" placeholder="Factura, recibo, transferencia...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Comprobante de Pago</label>
                                <input type="file" name="payment_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted d-block mt-1">Dejar vacío para conservar el comprobante actual.</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Descripción / Detalle</label>
                                <textarea name="description" id="editTransactionDescription" rows="3" class="form-control" placeholder="Detalle adicional del movimiento..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="deleteTransactionForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="mb-0">Detalle del movimiento</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-striped table-bordered mb-0" style="font-size: 0.9rem;">
                        <tbody>
                            <tr>
                                <th class="bg-light text-dark" style="width: 35%;">ID</th>
                                <td id="detail-id"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Fecha</th>
                                <td id="detail-date"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Tipo</th>
                                <td id="detail-type"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Monto</th>
                                <td id="detail-amount"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Estado</th>
                                <td id="detail-status"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Cuenta / Método</th>
                                <td id="detail-account"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Sede</th>
                                <td id="detail-branch"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Referencia</th>
                                <td id="detail-reference"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Estudiante</th>
                                <td id="detail-student"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Curso / Concepto</th>
                                <td id="detail-course"></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Descripción / Nota</th>
                                <td id="detail-description" style="white-space: pre-wrap; word-break: break-all;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="finance-toolbar">
            <div class="form-group">
                <label for="financeBranchFilter" class="form-label">Filtrar por sede</label>
                <select id="financeBranchFilter" class="form-control">
                    <option value="">Todas las sedes</option>
                    <option value="general" @selected($selectedBranchId === 'general')>Gastos e Ingresos Generales</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranchId === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="financeFilterSpinner" class="finance-filter-spinner" aria-live="polite">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>Filtrando movimientos...</span>
            </div>

        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mb-3" id="finance-dashboard">
            <div class="col">
                <div class="finance-card shadow-sm">
                    <div class="finance-card-label">Ingresos completados</div>
                    <div class="finance-card-value" data-summary-field="completedIncome">${{ number_format($completedIncome, 2) }}</div>
                </div>
            </div>
            <div class="col">
                <div class="finance-card shadow-sm">
                    <div class="finance-card-label">Egresos completados</div>
                    <div class="finance-card-value" data-summary-field="completedExpenses">${{ number_format($completedExpenses, 2) }}</div>
                </div>
            </div>
            <div class="col">
                <div class="finance-card shadow-sm">
                    <div class="finance-card-label">Cobranza pendiente</div>
                    <div class="finance-card-value" data-summary-field="pendingCollectionAmount">${{ number_format($pendingCollectionAmount, 2) }}</div>
                    <div class="mt-2">
                        <a href="{{ route('finance.collections', array_filter(['branch_id' => $selectedBranchId])) }}" id="btnVerCobranzas" class="btn btn-sm btn-inverse">
                            <i class="fas fa-money-check-dollar"></i> Ver cobranzas
                        </a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="finance-card shadow-sm">
                    <div class="finance-card-label">Por pagar pendiente</div>
                    <div class="finance-card-value" data-summary-field="pendingPayableAmount">${{ number_format($pendingPayableAmount, 2) }}</div>
                    <div class="mt-2">
                        <a href="{{ route('finance.payables', array_filter(['branch_id' => $selectedBranchId])) }}" id="btnVerPayables" class="btn btn-sm btn-inverse">
                            <i class="fas fa-file-invoice"></i> Ver cuentas por pagar
                        </a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="finance-card shadow-sm">
                    <div class="finance-card-label">Balance neto</div>
                    <div class="finance-card-value" data-summary-field="netBalance">${{ number_format($netBalance, 2) }}</div>
                    <div class="small text-muted mt-2" data-summary-field="pendingCollectionsCount">{{ $pendingCollectionsCount }} Inscripciones pendientes de cobro</div>
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
        let currentTransactionsList = [];

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
            $('[data-summary-field="pendingPayableAmount"]').text(formatCurrency(summary.pendingPayableAmount));
            $('[data-summary-field="netBalance"]').text(formatCurrency(summary.netBalance));
            $('[data-summary-field="pendingCollectionsCount"]').text(`${summary.pendingCollectionsCount} Inscripciones pendientes de cobro`);
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

        function renderPaymentProofButton(transaction) {
            if (!transaction.payment_receipt_url) {
                return '<span class="text-muted">Sin comp.</span>';
            }

            const url = escapeHtml(transaction.payment_receipt_url);
            const name = escapeHtml(transaction.payment_receipt_name || 'Comprobante');
            return `<button type="button" class="btn btn-sm btn-outline-primary js-open-proof" data-proof-url="${url}" data-proof-name="${name}"><i class="fas fa-paperclip"></i></button>`;
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
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-info js-open-details" data-transaction-id="${transaction.id}" title="Ver Detalle"><i class="fas fa-eye"></i></button>
                                ${renderPaymentProofButton(transaction)}
                                ${renderReceiptButton(transaction)}
                                <button type="button" class="btn btn-sm btn-outline-warning js-open-edit" data-transaction-id="${transaction.id}" title="Editar Movimiento"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger js-delete-transaction" data-destroy-url="${escapeHtml(transaction.destroy_url)}" data-transaction-id="${transaction.id}" title="Eliminar Movimiento"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
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
                branch_id: branchId,
                format: 'json'
            } : {
                format: 'json'
            };

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

                        currentTransactionsList = response.transactions || [];
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
            function updateFinanceNavigationLinks(branchId) {
                const collectionsBaseUrl = "{{ route('finance.collections') }}";
                const payablesBaseUrl = "{{ route('finance.payables') }}";

                if (branchId) {
                    $('#btnVerCobranzas').attr('href', collectionsBaseUrl + '?branch_id=' + encodeURIComponent(branchId));
                    $('#btnVerPayables').attr('href', payablesBaseUrl + '?branch_id=' + encodeURIComponent(branchId));
                } else {
                    $('#btnVerCobranzas').attr('href', collectionsBaseUrl);
                    $('#btnVerPayables').attr('href', payablesBaseUrl);
                }
            }

            function updateModalGeneralBranchOption() {
                const type = $('select[name="type"]').val();
                if (type === 'expense') {
                    $('#modalGeneralBranchOption').text('Gastos Generales');
                } else {
                    $('#modalGeneralBranchOption').text('Ingresos Generales');
                }
            }
            $('select[name="type"]').on('change', updateModalGeneralBranchOption);
            updateModalGeneralBranchOption();

            updateFinanceNavigationLinks($('#financeBranchFilter').val());
            loadFinanceData($('#financeBranchFilter').val());

            $('#financeBranchFilter').on('change', function() {
                const selectedVal = $(this).val();
                $('#transactionReturnBranchId').val(selectedVal);
                updateFinanceNavigationLinks(selectedVal);
                loadFinanceData(selectedVal);
            });

            $(document).on('click', '.js-open-details', function() {
                const transactionId = $(this).data('transaction-id');
                const transaction = currentTransactionsList.find(t => t.id == transactionId);
                if (!transaction) return;

                $('#detail-id').text(transaction.id);
                $('#detail-date').text(transaction.created_at);
                $('#detail-type').html(renderTypeBadge(transaction.type));
                $('#detail-amount').text(formatCurrency(transaction.amount));
                $('#detail-status').html(renderStatusBadge(transaction.status));
                $('#detail-account').text(transaction.account);
                $('#detail-branch').text(transaction.branch);
                $('#detail-reference').text(transaction.reference);
                $('#detail-student').text(transaction.student_name);
                $('#detail-course').text(transaction.course_title);
                $('#detail-description').text(transaction.description);

                const modal = new bootstrap.Modal(document.getElementById('transactionDetailModal'));
                modal.show();
            });

            $(document).on('click', '.js-open-proof', function() {
                const proofUrl = $(this).data('proof-url');
                const proofName = $(this).data('proof-name') || 'Comprobante';
                if (!proofUrl) {
                    return;
                }

                const isPdf = String(proofUrl).toLowerCase().includes('.pdf');
                const html = isPdf
                    ? `<iframe src="${proofUrl}" style="width:100%;height:70vh;border:0;"></iframe>`
                    : `<img src="${proofUrl}" alt="${proofName}" class="img-fluid rounded border" style="max-height:70vh;">`;

                $('#paymentProofContainer').html(html);
                const modal = new bootstrap.Modal(document.getElementById('paymentProofModal'));
                modal.show();
            });

            $(document).on('click', '.js-open-edit', function() {
                const transactionId = $(this).data('transaction-id');
                const transaction = currentTransactionsList.find(t => t.id == transactionId);
                if (!transaction) return;

                $('#editTransactionForm').attr('action', transaction.update_url);
                $('#editTransactionType').val(transaction.type);
                $('#editTransactionAccountId').val(transaction.account_id);
                $('#editTransactionBranchId').val(transaction.branch_id ?? '');
                $('#editTransactionAmount').val(transaction.amount);
                $('#editTransactionStatus').val(transaction.status);
                $('#editTransactionPaymentDate').val(transaction.created_at_raw || '');
                $('#editTransactionReference').val(transaction.reference || '');
                $('#editTransactionDescription').val(transaction.description || '');

                function updateEditModalGeneralBranchOption() {
                    const type = $('#editTransactionType').val();
                    if (type === 'expense') {
                        $('#editModalGeneralBranchOption').text('Gastos Generales');
                    } else {
                        $('#editModalGeneralBranchOption').text('Ingresos Generales');
                    }
                }
                $('#editTransactionType').off('change.editModal').on('change.editModal', updateEditModalGeneralBranchOption);
                updateEditModalGeneralBranchOption();

                const modal = new bootstrap.Modal(document.getElementById('editTransactionModal'));
                modal.show();
            });

            $(document).on('click', '.js-delete-transaction', function() {
                const destroyUrl = $(this).data('destroy-url');
                const transactionId = $(this).data('transaction-id');
                if (!destroyUrl) return;

                Swal.fire({
                    title: '¿Eliminar movimiento?',
                    text: `¿Estás seguro de eliminar el movimiento #${transactionId}? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#deleteTransactionForm').attr('action', destroyUrl).submit();
                    }
                });
            });

            @if ($errors->any())
                const transactionModal = new bootstrap.Modal(document.getElementById('transactionModal'));
                transactionModal.show();
            @endif
        });
    </script>
@endsection
