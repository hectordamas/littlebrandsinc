@foreach ($transactions as $transaction)
    <tr>
        <td>{{ $transaction->id }}</td>
        <td>{{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
        <td>
            @if ($transaction->type === 'income')
                <span class="badge bg-success">Ingreso</span>
            @else
                <span class="badge bg-danger">Egreso</span>
            @endif
        </td>
        <td>${{ number_format((float) $transaction->amount, 2) }}</td>
        <td>
            @if ($transaction->status === 'completed')
                <span class="badge bg-primary">Completado</span>
            @elseif ($transaction->status === 'pending')
                <span class="badge bg-warning text-dark">Pendiente</span>
            @else
                <span class="badge bg-secondary">Fallido</span>
            @endif
        </td>
        <td>{{ optional($transaction->account)->name ?? 'N/A' }}</td>
        <td>{{ optional($transaction->student)->name ?? 'N/A' }}</td>
        <td>{{ optional($transaction->course)->title ?? 'N/A' }}</td>
        <td>{{ optional($transaction->branch)->name ?? 'N/A' }}</td>
        <td>{{ $transaction->payment_method ? ucfirst($transaction->payment_method) : 'N/A' }}</td>
        <td>{{ $transaction->reference ?? 'N/A' }}</td>
    </tr>
@endforeach