<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Movimiento #{{ $transaction->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
            margin: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .company-logo {
            max-height: 50px;
            max-width: 180px;
            margin-bottom: 4px;
        }

        .branch-logo {
            max-height: 48px;
            max-width: 130px;
            margin-bottom: 4px;
        }

        .company-name {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .doc-title {
            font-size: 15px;
            font-weight: 800;
            color: #0369a1;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .doc-meta {
            font-size: 10px;
            color: #64748b;
        }

        .section {
            margin-top: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 9px 12px;
            background-color: #ffffff;
        }

        .section-title {
            margin: 0 0 7px 0;
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        .row {
            margin-bottom: 4px;
            font-size: 10.5px;
        }

        .label {
            font-weight: 700;
            color: #334155;
        }

        .amount-box {
            background-color: #f0fdf4;
            border: 1.5px solid #10b981;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .amount-title {
            font-size: 10px;
            font-weight: 700;
            color: #065f46;
            text-transform: uppercase;
        }

        .amount-value {
            font-size: 18px;
            font-weight: 800;
            color: #047857;
            margin-top: 2px;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $companyLogoPath = null;
        $possibleLogos = [
            public_path('assets/img/logo-littlebrandsinc.png'),
            public_path('landing_page/logos/logo-littlebrandsinc.png'),
            public_path('assets/img/lbinc-admin.png'),
        ];
        foreach ($possibleLogos as $p) {
            if (file_exists($p)) {
                $companyLogoPath = $p;
                break;
            }
        }

        $branch = $transaction->branch;
        $branchLogo = null;
        if ($branch && !empty($branch->logo) && !str_starts_with($branch->logo, 'http://') && !str_starts_with($branch->logo, 'https://')) {
            $bLogoPath = public_path(ltrim((string) $branch->logo, '/'));
            if (file_exists($bLogoPath)) {
                $branchLogo = $bLogoPath;
            }
        }
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                @if ($companyLogoPath)
                    <img src="{{ $companyLogoPath }}" class="company-logo" alt="Little Brands Inc">
                @else
                    <div class="company-name">LITTLE BRANDS INC</div>
                @endif
                <div class="doc-title">Comprobante de Movimiento</div>
                <div class="doc-meta">
                    <strong>Movimiento:</strong> #MOV-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }} &nbsp;|&nbsp;
                    <strong>Fecha Emisión:</strong> {{ $generatedAt->format('d/m/Y h:i A') }}
                </div>
            </td>
            <td style="width: 42%; text-align: right;">
                @if ($branchLogo)
                    <img src="{{ $branchLogo }}" class="branch-logo" alt="{{ optional($branch)->name }}"><br>
                @endif
                <div style="font-size: 11px; font-weight: 700; color: #0f172a;">
                    {{ optional($branch)->name ?? 'Little Brands Inc' }}
                </div>
                @if (optional($branch)->address)
                    <div style="font-size: 9.5px; color: #64748b;">{{ $branch->address }}</div>
                @endif
                @if (optional($branch)->phone)
                    <div style="font-size: 9.5px; color: #64748b;"><strong>Tel:</strong> {{ $branch->phone }}</div>
                @endif
                @if (optional($branch)->email)
                    <div style="font-size: 9.5px; color: #64748b;"><strong>Email:</strong> {{ $branch->email }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="amount-title">Monto de la Operación ({{ $transaction->type === 'income' ? 'Ingreso' : 'Egreso' }})</div>
        <div class="amount-value">${{ number_format((float) ($transaction->amount ?? 0), 2) }} {{ strtoupper($transaction->currency ?? 'USD') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Movimiento</div>
        <div class="row"><span class="label">Tipo:</span> {{ $transaction->type === 'income' ? 'Ingreso / Abono' : 'Egreso / Pago' }}</div>
        <div class="row"><span class="label">Estado:</span> {{ ucfirst($transaction->status ?? 'Completado') }}</div>
        <div class="row"><span class="label">Fecha y Hora:</span> {{ $transaction->created_at ? $transaction->created_at->format('d/m/Y h:i A') : 'N/A' }}</div>
        <div class="row"><span class="label">Cuenta Destino / Origen:</span> {{ optional($transaction->account)->name ?? 'Caja General' }}</div>
        <div class="row"><span class="label">Método de Pago:</span> {{ ucfirst($transaction->payment_method ?? 'Transferencia') }}</div>
        <div class="row"><span class="label">Referencia Bancaria / Operación:</span> {{ $transaction->reference ?? 'S/R' }}</div>
        @if ($transaction->description)
            <div class="row"><span class="label">Descripción / Observación:</span> {{ $transaction->description }}</div>
        @endif
    </div>

    @if ($transaction->enrollment)
        <div class="section">
            <div class="section-title">Información de la Inscripción Asociada</div>
            <div class="row"><span class="label">Inscripción:</span> #{{ $transaction->enrollment_id }} - {{ optional($transaction->enrollment->program)->name }}</div>
            <div class="row"><span class="label">Estudiante:</span> {{ optional($transaction->enrollment->student)->name ?? 'N/A' }}</div>
            <div class="row"><span class="label">Representante:</span> {{ optional(optional($transaction->enrollment->student)->user)->name ?? optional($transaction->enrollment->parent)->name ?? 'N/A' }}</div>
        </div>
    @endif

    <div class="footer">
        Comprobante oficial emitido por <strong>Little Brands Inc</strong>. Documento de control administrativo y financiero.
        @if ($branch)
            <br>Sede: {{ $branch->name }} &bull; {{ $branch->phone }} &bull; {{ $branch->email }}
        @endif
    </div>
</body>

</html>
