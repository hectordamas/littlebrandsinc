<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Movimiento #{{ $transaction->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.45;
            margin: 28px;
        }

        .header {
            border-bottom: 2px solid #d1d5db;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            color: #111827;
        }

        .subtitle {
            margin-top: 4px;
            color: #4b5563;
        }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            color: #6b7280;
        }

        .section {
            margin-top: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
        }

        .section h3 {
            margin: 0 0 8px;
            font-size: 13px;
            color: #111827;
        }

        .row {
            margin-bottom: 4px;
        }

        .label {
            font-weight: 700;
            color: #374151;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    @php
        $branch = $transaction->branch;
        $branchLogo = null;

        if ($branch && !empty($branch->logo) && !str_starts_with($branch->logo, 'http://') && !str_starts_with($branch->logo, 'https://')) {
            $branchLogoPath = public_path(ltrim((string) $branch->logo, '/'));
            if (file_exists($branchLogoPath)) {
                $branchLogo = $branchLogoPath;
            }
        }
    @endphp

    <div class="header">
        <table style="border: 0; margin: 0; width: 100%;">
            <tr>
                <td style="border: 0; padding: 0; vertical-align: top; width: 68%;">
                    <h1 class="title">Comprobante de Movimiento</h1>
                    <div class="subtitle">Movimiento #{{ $transaction->id }}</div>
                    <div class="meta">Emitido: {{ $generatedAt->format('d/m/Y H:i') }}</div>
                </td>
                <td style="border: 0; padding: 0; text-align: right; vertical-align: top; width: 32%;">
                    @if ($branchLogo)
                        <img src="{{ $branchLogo }}" alt="Logo sede" style="max-width: 150px; max-height: 60px; margin-bottom: 6px;">
                    @endif
                    <div style="font-size: 11px; color: #374151;">
                        <strong>{{ optional($branch)->name ?? env('APP_NAME') }}</strong>
                    </div>
                    @if (optional($branch)->address)
                        <div style="font-size: 10px; color: #6b7280;">{{ $branch->address }}</div>
                    @endif
                    @if (optional($branch)->email)
                        <div style="font-size: 10px; color: #6b7280;">{{ $branch->email }}</div>
                    @endif
                    @if (optional($branch)->phone)
                        <div style="font-size: 10px; color: #6b7280;">{{ $branch->phone }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Datos del Movimiento</h3>
        <div class="row"><span class="label">Tipo:</span> {{ $transaction->type === 'income' ? 'Ingreso' : 'Egreso' }}</div>
        <div class="row"><span class="label">Estado:</span> {{ ucfirst($transaction->status ?? 'N/A') }}</div>
        <div class="row"><span class="label">Fecha:</span> {{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
        <div class="row"><span class="label">Monto:</span> {{ '$' . number_format((float) ($transaction->amount ?? 0), 2) }} {{ strtoupper($transaction->currency ?? 'USD') }}</div>
        <div class="row"><span class="label">Cuenta:</span> {{ optional($transaction->account)->name ?? 'N/A' }}</div>
        <div class="row"><span class="label">Metodo de pago:</span> {{ $transaction->payment_method ?? 'N/A' }}</div>
        <div class="row"><span class="label">Referencia:</span> {{ $transaction->reference ?? 'N/A' }}</div>
        <div class="row"><span class="label">Descripcion:</span> {{ $transaction->description ?? 'N/A' }}</div>
    </div>

    <div class="section">
        <h3>Datos de la Sede</h3>
        <div class="row"><span class="label">Nombre:</span> {{ optional($branch)->name ?? 'N/A' }}</div>
        <div class="row"><span class="label">Direccion:</span> {{ optional($branch)->address ?? 'N/A' }}</div>
        <div class="row"><span class="label">Telefono:</span> {{ optional($branch)->phone ?? 'N/A' }}</div>
        <div class="row"><span class="label">Email:</span> {{ optional($branch)->email ?? 'N/A' }}</div>
    </div>

    <div class="footer">
        Documento generado desde el sistema administrativo {{ env('APP_NAME') }}.
    </div>
</body>

</html>
