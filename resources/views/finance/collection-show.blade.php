@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Detalle CxC</title>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Cuenta por cobrar #{{ $receivable->id }}</h5>
                    <span class="text-muted">{{ $receivable->title }}</span>
                </div>
                <a href="{{ route('finance.collections') }}" class="btn btn-inverse btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver a CxC
                </a>
            </div>
            <div class="card-block">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Sede:</strong> {{ optional($receivable->branch)->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Estudiante:</strong> {{ optional(optional($receivable->enrollment)->student)->name ?? 'N/A' }}</div>
                    <div class="col-md-3"><strong>Inscripción:</strong> {{ $receivable->enrollment_id ? '#'.$receivable->enrollment_id : 'Manual' }}</div>
                    <div class="col-md-3"><strong>Vencimiento:</strong> {{ $receivable->due_date ? $receivable->due_date->format('d/m/Y') : 'N/A' }}</div>
                    <div class="col-md-3"><strong>Total:</strong> ${{ number_format((float) $receivable->amount_total, 2) }}</div>
                    <div class="col-md-3"><strong>Saldo:</strong> ${{ number_format((float) $receivable->balance_due, 2) }}</div>
                    <div class="col-md-3"><strong>Estado:</strong> {{ ucfirst($receivable->status) }}</div>
                    <div class="col-md-12"><strong>Notas:</strong> {{ $receivable->notes ?: 'Sin notas' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-1">Registrar abono</h6>
            </div>
            <div class="card-block">
                <form method="POST" action="{{ route('finance.collections.payments.store', $receivable) }}">
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
                        <div class="col-md-12">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
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
                                <th>Movimiento</th>
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
                                    <td>#{{ $payment->id }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay abonos registrados.</td>
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
