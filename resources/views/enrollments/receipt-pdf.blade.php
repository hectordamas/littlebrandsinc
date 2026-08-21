<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Inscripción y Estado de Cuenta #{{ $enrollment->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
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
            max-height: 52px;
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

        .two-col-table {
            width: 100%;
            border-collapse: collapse;
        }

        .two-col-table td {
            border: none;
            padding: 2px 4px;
            font-size: 10.5px;
            vertical-align: top;
        }

        .label {
            font-weight: 700;
            color: #334155;
        }

        /* Financial summary card */
        .summary-box {
            margin-top: 12px;
            border: 1.5px solid #0284c7;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 8px 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .summary-table td {
            border: 1px solid #e2e8f0;
            padding: 7px 6px;
            background: #ffffff;
            vertical-align: middle;
        }

        .summary-label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 3px;
        }

        .summary-val {
            font-size: 14px;
            font-weight: 800;
        }

        .val-total {
            color: #0f172a;
        }

        .val-paid {
            color: #059669;
        }

        .val-due {
            color: #dc2626;
        }

        .val-zero {
            color: #059669;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            font-size: 9.5px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Standard data tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 10px;
            text-align: left;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9.5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 18px;
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

        $firstCourse = $enrollment->courses->first();
        $branch = optional($firstCourse)->branch;
        $branchLogo = null;
        if ($branch && !empty($branch->logo) && !str_starts_with($branch->logo, 'http://') && !str_starts_with($branch->logo, 'https://')) {
            $bLogoPath = public_path(ltrim((string) $branch->logo, '/'));
            if (file_exists($bLogoPath)) {
                $branchLogo = $bLogoPath;
            }
        }

        // Calculate Totals and Allocations
        $isFreeTrial = (bool) $enrollment->is_free_trial;
        $totalAccountAmount = 0.00;
        $coursesBreakdown = [];

        if (!$isFreeTrial) {
            foreach ($enrollment->courses as $idx => $c) {
                $cAmount = $enrollment->getCourseAmount($c, $idx);
                $totalAccountAmount += $cAmount;
                $coursesBreakdown[] = [
                    'course' => $c,
                    'amount' => $cAmount,
                    'paid' => 0.00,
                    'balance' => 0.00,
                ];
            }
        }

        $totalPaidIncome = (float) $enrollment->transactions
            ->where('status', 'completed')
            ->where('type', 'income')
            ->sum('amount');

        $allocatedTemp = $totalPaidIncome;
        foreach ($coursesBreakdown as $idx => &$item) {
            $p = min($item['amount'], max(0.00, $allocatedTemp));
            $allocatedTemp = max(0.00, $allocatedTemp - $p);
            $item['paid'] = $p;
            if ($enrollment->status === 'cancelled') {
                $item['balance'] = 0.00;
            } else {
                $item['balance'] = max(0.00, $item['amount'] - $p);
            }
        }
        unset($item);

        if ($enrollment->status === 'cancelled') {
            $totalBalanceDue = 0.00;
        } else {
            $totalBalanceDue = max(0.00, $totalAccountAmount - $totalPaidIncome);
        }
    @endphp

    <!-- Encabezado con Logo y Datos de la Empresa -->
    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                @if ($companyLogoPath)
                    <img src="{{ $companyLogoPath }}" class="company-logo" alt="Little Brands Inc">
                @else
                    <div class="company-name">LITTLE BRANDS INC</div>
                @endif
                <div class="doc-title">Comprobante de Inscripción</div>
                <div class="doc-meta">
                    <strong>Folio:</strong> #INS-{{ str_pad($enrollment->id, 5, '0', STR_PAD_LEFT) }} &nbsp;|&nbsp;
                    <strong>Fecha de Emisión:</strong> {{ $generatedAt->format('d/m/Y h:i A') }}
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

    <!-- Tarjeta Resumen Financiero y Cuenta por Cobrar -->
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td style="width: 25%;">
                    <div class="summary-label">Estado Inscripción</div>
                    @if ($enrollment->status === 'cancelled')
                        <span class="badge badge-cancelled">Cancelado</span>
                    @elseif ($isFreeTrial)
                        <span class="badge badge-paid">Prueba Gratis</span>
                    @elseif ($enrollment->payment_status === 'paid')
                        <span class="badge badge-paid">Pagado</span>
                    @else
                        <span class="badge badge-pending">Pendiente</span>
                    @endif
                </td>
                <td style="width: 25%;">
                    <div class="summary-label">Monto Total Cuenta</div>
                    <div class="summary-val val-total">${{ number_format($totalAccountAmount, 2) }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-label">Total Abonado</div>
                    <div class="summary-val val-paid">${{ number_format($totalPaidIncome, 2) }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-label">Cuenta por Cobrar (Saldo)</div>
                    @if ($totalBalanceDue > 0)
                        <div class="summary-val val-due">${{ number_format($totalBalanceDue, 2) }}</div>
                    @else
                        <div class="summary-val val-zero">$0.00</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Datos del Estudiante y Representante -->
    <div class="section">
        <div class="section-title">Información del Estudiante y Representante</div>
        <table class="two-col-table">
            <tr>
                <td style="width: 50%;">
                    <div><span class="label">Estudiante:</span> {{ optional($enrollment->student)->name ?? 'N/A' }}</div>
                    @if (optional($enrollment->student)->birthdate)
                        <div><span class="label">Nacimiento / Edad:</span> {{ \Carbon\Carbon::parse($enrollment->student->birthdate)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($enrollment->student->birthdate)->age }} años)</div>
                    @endif
                    <div><span class="label">Programa:</span> {{ optional($enrollment->program)->name ?? 'N/A' }}</div>
                </td>
                <td style="width: 50%;">
                    <div><span class="label">Representante:</span> {{ optional(optional($enrollment->student)->user)->name ?? optional($enrollment->parent)->name ?? 'N/A' }}</div>
                    <div><span class="label">Email:</span> {{ optional(optional($enrollment->student)->user)->email ?? optional($enrollment->parent)->email ?? 'N/A' }}</div>
                    <div><span class="label">WhatsApp / Tel:</span> {{ trim((optional(optional($enrollment->student)->user)->dial_code ?? '') . ' ' . (optional(optional($enrollment->student)->user)->whatsapp ?? '')) ?: 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Detalle de Clases y Cuentas por Cobrar -->
    <div class="section">
        <div class="section-title">Desglose de Clases y Cuentas por Cobrar</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Clase / Disciplina</th>
                    <th style="width: 20%;">Sede</th>
                    <th class="text-right" style="width: 15%;">Monto Cuenta</th>
                    <th class="text-right" style="width: 15%;">Abonado</th>
                    <th class="text-right" style="width: 15%;">Cuenta por Cobrar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coursesBreakdown as $item)
                    @php $c = $item['course']; @endphp
                    <tr>
                        <td>
                            <strong>{{ $c->title }}</strong>
                            @if ($c->start_date && $c->end_date)
                                <div style="font-size: 8.5px; color: #64748b;">
                                    Período: {{ \Carbon\Carbon::parse($c->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($c->end_date)->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <td>{{ optional($c->branch)->name ?? 'N/A' }}</td>
                        <td class="text-right">${{ number_format($item['amount'], 2) }}</td>
                        <td class="text-right" style="color: #059669; font-weight: 700;">${{ number_format($item['paid'], 2) }}</td>
                        <td class="text-right" style="font-weight: 700; {{ $item['balance'] > 0 ? 'color: #dc2626;' : 'color: #059669;' }}">
                            @if ($item['balance'] > 0)
                                ${{ number_format($item['balance'], 2) }}
                            @else
                                $0.00 (Al día)
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay clases registradas en esta inscripción.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Historial de Abonos y Pagos Realizados -->
    <div class="section">
        <div class="section-title">Historial de Abonos y Pagos Recibidos</div>
        @php
            $completedTransactions = $enrollment->transactions->where('status', 'completed')->where('type', 'income');
        @endphp
        @if ($completedTransactions->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 12%;"># Trans.</th>
                        <th style="width: 18%;">Fecha</th>
                        <th style="width: 20%;">Cuenta de Pago</th>
                        <th style="width: 18%;">Método</th>
                        <th style="width: 17%;">Referencia</th>
                        <th class="text-right" style="width: 15%;">Monto Abonado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedTransactions as $tx)
                        <tr>
                            <td>#{{ $tx->id }}</td>
                            <td>{{ $tx->created_at ? $tx->created_at->format('d/m/Y h:i A') : 'N/A' }}</td>
                            <td>{{ optional($tx->account)->name ?? 'Caja General' }}</td>
                            <td>{{ ucfirst($tx->payment_method ?? 'Transferencia') }}</td>
                            <td>{{ $tx->reference ?? 'S/R' }}</td>
                            <td class="text-right" style="font-weight: 800; color: #059669;">
                                ${{ number_format((float) $tx->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="font-size: 10px; color: #64748b; padding: 4px 0;">
                No se registran abonos o pagos recibidos para esta inscripción hasta la fecha.
            </div>
        @endif
    </div>

    <div class="footer">
        Comprobante oficial emitido por <strong>Little Brands Inc</strong>. Documento de control administrativo y financiero.
        @if ($branch)
            <br>Sede: {{ $branch->name }} &bull; {{ $branch->phone }} &bull; {{ $branch->email }}
        @endif
    </div>
</body>

</html>
