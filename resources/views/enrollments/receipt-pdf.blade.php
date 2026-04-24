<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Inscripcion #{{ $enrollment->id }}</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background: #f3f4f6;
            color: #111827;
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
        $branch = optional($enrollment->course)->branch;
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
                    <h1 class="title">Comprobante de Inscripción</h1>
                    <div class="subtitle">Inscripción #{{ $enrollment->id }}</div>
                    <div class="meta">Emitido: {{ $generatedAt->format('d/m/Y H:i') }}</div>
                </td>
                <td style="border: 0; padding: 0; text-align: right; vertical-align: top; width: 32%;">
                    @if ($branchLogo)
                        <img src="{{ $branchLogo }}" alt="Logo sede" style="max-width: 120px; max-height: 60px; margin-bottom: 6px;">
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
        <h3>Datos de la Inscripción</h3>
        <div class="row"><span class="label">Estado:</span> {{ ucfirst($enrollment->status ?? 'N/A') }}</div>
        <div class="row"><span class="label">Estado de pago:</span> {{ $enrollment->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}</div>
        <div class="row"><span class="label">Método de pago:</span> {{ ucfirst($enrollment->payment_method ?? 'N/A') }}</div>
    </div>

    <div class="section">
        <h3>Curso</h3>
        <div class="row"><span class="label">Nombre:</span> {{ optional($enrollment->course)->title ?? 'N/A' }}</div>
        <div class="row"><span class="label">Sede:</span> {{ optional(optional($enrollment->course)->branch)->name ?? 'N/A' }}</div>
        <div class="row"><span class="label">Dirección sede:</span> {{ optional(optional($enrollment->course)->branch)->address ?? 'N/A' }}</div>
        <div class="row"><span class="label">Período:</span>
            {{ optional($enrollment->course)->start_date ? \Carbon\Carbon::parse($enrollment->course->start_date)->format('d/m/Y') : 'N/A' }} -
            {{ optional($enrollment->course)->end_date ? \Carbon\Carbon::parse($enrollment->course->end_date)->format('d/m/Y') : 'N/A' }}
        </div>
        <div class="row"><span class="label">Precio de inscripción:</span>
            {{ optional($enrollment->course)->price !== null ? '$' . number_format((float) $enrollment->course->price, 2) : 'N/A' }}
        </div>
        <div class="row"><span class="label">Mensualidad:</span>
            {{ optional($enrollment->course)->monthly_fee !== null ? '$' . number_format((float) $enrollment->course->monthly_fee, 2) : 'N/A' }}
        </div>
    </div>

    <div class="section">
        <h3>Estudiante y Representante</h3>
        <div class="row"><span class="label">Estudiante:</span> {{ optional($enrollment->student)->name ?? 'N/A' }}</div>
        <div class="row"><span class="label">Fecha de nacimiento:</span>
            {{ optional($enrollment->student)->birthdate ? \Carbon\Carbon::parse($enrollment->student->birthdate)->format('d/m/Y') : 'N/A' }}
        </div>
        <div class="row"><span class="label">Representante:</span> {{ optional(optional($enrollment->student)->user)->name ?? 'N/A' }}</div>
        <div class="row"><span class="label">Email:</span> {{ optional(optional($enrollment->student)->user)->email ?? 'N/A' }}</div>
        <div class="row"><span class="label">WhatsApp:</span>
            {{ trim((optional(optional($enrollment->student)->user)->dial_code ?? '') . ' ' . (optional(optional($enrollment->student)->user)->whatsapp ?? '')) ?: 'N/A' }}
        </div>
    </div>

    <div class="section">
        <h3>Clases y Horarios</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Coach</th>
                </tr>
            </thead>
            <tbody>
                @forelse (optional($enrollment->course)->classes ?? [] as $class)
                    <tr>
                        <td>{{ $class->date ? \Carbon\Carbon::parse($class->date)->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('H:i') : 'N/A' }} - {{ $class->end_time ? \Carbon\Carbon::parse($class->end_time)->format('H:i') : 'N/A' }}</td>
                        <td>{{ optional($class->coach)->name ?? 'Sin asignar' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No hay clases registradas para este curso.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Documento generado desde el sistema administrativo {{ env('APP_NAME') }}.
        @if ($branch)
            Sede emisora: {{ $branch->name }}{{ $branch->phone ? ' | ' . $branch->phone : '' }}{{ $branch->email ? ' | ' . $branch->email : '' }}.
        @endif
    </div>
</body>

</html>
