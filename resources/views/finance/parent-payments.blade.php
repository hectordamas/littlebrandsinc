@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Pagos de Padres</title>
@endsection

@section('styles')
    <style>
        .receipt-thumb {
            max-width: 80px;
            max-height: 60px;
            border-radius: 0.35rem;
            border: 1px solid #dee2e6;
            object-fit: cover;
            cursor: pointer;
        }

        .reject-form-inline {
            display: flex;
            gap: 0.35rem;
            align-items: center;
        }

        .reject-form-inline input {
            width: 140px;
        }

        .action-cell {
            white-space: nowrap;
        }

        .processed-info {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.3;
        }
    </style>
@endsection

@section('content')
    <div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="mb-0">Comprobante de pago</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="receiptPreviewContainer" class="text-center"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1">Pagos de Padres</h5>
                <span class="text-muted">Pagos registrados por los padres con comprobante para revisión.</span>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="parentPaymentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Padre</th>
                                <th>Cuenta por Cobrar</th>
                                <th>Monto</th>
                                <th>Comprobante</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td>{{ $payment->user->name ?? 'N/A' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($payment->receivable->title ?? 'Sin concepto', 40) }}</td>
                                    <td>${{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>
                                        @if ($payment->receipt_path)
                                            <button type="button" class="btn btn-sm btn-outline-primary js-view-receipt"
                                                data-receipt-url="{{ asset('storage/' . $payment->receipt_path) }}">
                                                <i class="fas fa-paperclip"></i> Ver
                                            </button>
                                        @else
                                            <span class="text-muted">Sin comprobante</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->created_at->format('d/m/Y h:i A') }}</td>
                                    <td>
                                        @if ($payment->status === 'approved')
                                            <span class="badge bg-success">Aprobado</span>
                                        @elseif ($payment->status === 'rejected')
                                            <span class="badge bg-danger">Rechazado</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="action-cell">
                                        @if ($payment->status === 'pending')
                                            <div class="d-flex gap-2 align-items-center">
                                                <form method="POST"
                                                    action="{{ route('finance.parent-payments.approve', $payment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Aprobar
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                    action="{{ route('finance.parent-payments.reject', $payment) }}"
                                                    class="reject-form-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text" name="rejected_reason"
                                                        class="form-control form-control-sm"
                                                        placeholder="Motivo de rechazo" required>
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i> Rechazar
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="processed-info">
                                                @if ($payment->status === 'approved')
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle"></i>
                                                        {{ $payment->approved_at ? $payment->approved_at->format('d/m/Y h:i A') : '' }}
                                                    </span>
                                                @else
                                                    <span class="text-danger">
                                                        <i class="fas fa-times-circle"></i>
                                                        {{ $payment->rejected_reason ? \Illuminate\Support\Str::limit($payment->rejected_reason, 50) : 'Sin motivo' }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay pagos de padres registrados.</td>
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
            $('#parentPaymentsTable').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"f>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3"lip>',
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                language: {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 registros',
                    emptyTable: 'No hay pagos de padres registrados.',
                    paginate: {
                        previous: 'Anterior',
                        next: 'Siguiente'
                    }
                },
                columnDefs: [{
                    targets: [7],
                    orderable: false,
                    searchable: false
                }]
            });

            $(document).on('click', '.js-view-receipt', function() {
                const url = $(this).data('receipt-url');
                if (!url) return;

                const isPdf = String(url).toLowerCase().includes('.pdf');
                const html = isPdf
                    ? `<iframe src="${url}" style="width:100%;height:70vh;border:0;"></iframe>`
                    : `<img src="${url}" alt="Comprobante" class="img-fluid rounded border" style="max-height:70vh;">`;

                $('#receiptPreviewContainer').html(html);
                const modal = new bootstrap.Modal(document.getElementById('receiptPreviewModal'));
                modal.show();
            });
        });
    </script>
@endsection
