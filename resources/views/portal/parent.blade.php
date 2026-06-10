@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Mi Panel de Familia</title>
@endsection

@section('styles')
    <style>
        .parent-welcome-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(30, 64, 175, 0.12);
            position: relative;
            overflow: hidden;
        }

        .parent-welcome-banner::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
            pointer-events: none;
        }

        .metric-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            position: relative;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border-color: #cbd5e1;
        }

        .metric-icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-right: 1.15rem;
            flex-shrink: 0;
        }

        .metric-card.balance .metric-icon-wrapper {
            background-color: #fef2f2;
            color: #ef4444;
        }

        .metric-card.installments .metric-icon-wrapper {
            background-color: #eff6ff;
            color: #3b82f6;
        }

        .metric-card.students .metric-icon-wrapper {
            background-color: #ecfdf5;
            color: #10b981;
        }

        .quick-link-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            padding: 1.5rem;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: flex-start;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            height: 100%;
        }

        .quick-link-card:hover {
            transform: translateY(-4px);
            border-color: #3b82f6;
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.08);
            text-decoration: none;
            color: inherit;
        }

        .quick-link-card .link-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-right: 1.15rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .quick-link-card:hover .link-icon-wrapper {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
        }

        .student-item {
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            background-color: #f8fafc;
            padding: 0.9rem 1.1rem;
            transition: background-color 0.2s ease;
        }

        .student-item:hover {
            background-color: #f1f5f9;
        }

        .avatar-initials {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            margin-right: 0.9rem;
            box-shadow: 0 2px 5px rgba(79, 70, 229, 0.2);
            flex-shrink: 0;
        }

        .badge-status {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 30px;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endsection

@section('content')
    <!-- Banner de Bienvenida -->
    <div class="parent-welcome-banner text-white p-4 rounded-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h3 class="fw-bold mb-1">¡Hola, {{ Auth::user()->name }}! 👋</h3>
                <p class="mb-0 opacity-75" style="font-size: 1.05rem;">
                    Bienvenido a tu panel de familia. Aquí puedes ver el progreso, calendario de clases y gestionar los pagos de tus hijos.
                </p>
            </div>
            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                <span class="badge bg-white text-primary px-3 py-2 fs-6" style="border-radius: 30px; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    Acceso de Familia
                </span>
            </div>
        </div>
    </div>

    <!-- Fila de Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card balance d-flex align-items-center">
                <div class="metric-icon-wrapper">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <span class="text-muted d-block font-weight-bold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Saldo Pendiente</span>
                    <span class="fs-4 fw-bold text-dark">${{ number_format($pendingBalance, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card installments d-flex align-items-center">
                <div class="metric-icon-wrapper">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <span class="text-muted d-block font-weight-bold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Cuotas Pendientes</span>
                    <span class="fs-4 fw-bold text-dark">{{ $pendingInstallments }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card students d-flex align-items-center">
                <div class="metric-icon-wrapper">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <span class="text-muted d-block font-weight-bold" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Hijos Registrados</span>
                    <span class="fs-4 fw-bold text-dark">{{ $students->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Información de Alumnos e Inscripciones -->
        <div class="col-lg-7 col-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Mis Hijos Registrados</h5>
                        <small class="text-muted">Lista de alumnos vinculados a tu cuenta y sus programas.</small>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex flex-column gap-3">
                        @forelse ($students as $student)
                            <div class="student-item d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-initials">
                                        @php
                                            $parts = explode(' ', trim($student->name));
                                            $initials = '';
                                            if (count($parts) > 0 && !empty($parts[0])) {
                                                $initials .= strtoupper(substr($parts[0], 0, 1));
                                            }
                                            if (count($parts) > 1 && !empty($parts[1])) {
                                                $initials .= strtoupper(substr($parts[1], 0, 1));
                                            }
                                            echo $initials ?: 'S';
                                        @endphp
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ $student->name }}</h6>
                                        <small class="text-muted">
                                            @if($student->birthdate)
                                                Nacimiento: {{ \Carbon\Carbon::parse($student->birthdate)->format('d/m/Y') }}
                                            @else
                                                Sin fecha de nacimiento
                                            @endif
                                            @if($student->level)
                                                • Nivel: {{ $student->level }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <div class="text-md-end">
                                    @php($activeEnrollments = $student->enrollments->where('status', '!=', 'cancelled'))
                                    @if($activeEnrollments->count() > 0)
                                        <span class="badge bg-success-light text-success badge-status" style="background-color: #ecfdf5;">
                                            {{ $activeEnrollments->count() }} Inscripción(es)
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted badge-status">
                                            Sin inscripciones
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2 text-slate-300"></i>
                                <p class="mb-0">No tienes hijos registrados todavía.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Próximas Clases Condensadas -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-calendar-check me-2 text-primary"></i>Próximas Clases</h6>
                            <a href="{{ route('parent.calendar') }}" class="btn btn-sm btn-link text-primary p-0 fw-bold text-decoration-none">
                                Ver Calendario Completo <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="text-muted" style="font-size: 0.8rem; border-bottom: 1px solid #f1f5f9;">
                                        <th class="pb-2">Estudiante</th>
                                        <th class="pb-2">Fecha y Hora</th>
                                        <th class="pb-2">Sesión / Clase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($upcomingClasses as $row)
                                        @php($class = $row['class'])
                                        <tr style="border-bottom: 1px solid #f8fafc;">
                                            <td class="py-2.5">
                                                <span class="fw-semibold text-dark">{{ $row['student_name'] }}</span>
                                            </td>
                                            <td class="py-2.5">
                                                <div class="d-flex flex-column">
                                                    <span class="text-dark fw-medium" style="font-size: 0.85rem;">
                                                        {{ optional($class->date)->format('d/m/Y') ?? 'N/A' }}
                                                    </span>
                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        {{ substr((string) $class->start_time, 0, 5) }} - {{ substr((string) $class->end_time, 0, 5) }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="py-2.5">
                                                <div class="d-flex flex-column">
                                                    <span class="text-dark" style="font-size: 0.85rem; font-weight: 500;">
                                                        {{ optional($class->course)->title ?? 'N/A' }}
                                                    </span>
                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        Sede: {{ optional($class->branch)->name ?? 'N/A' }}
                                                    </small>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted" style="font-size: 0.85rem;">
                                                No hay próximas sesiones programadas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Enlaces Rápidos y Últimos Pagos -->
        <div class="col-lg-5 col-12">
            <div class="d-flex flex-column gap-4">
                <!-- Tarjetas de Acceso Rápido -->
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <h5 class="fw-bold mb-0 text-dark">Acciones Rápidas</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="d-flex flex-column gap-3">
                            <a href="{{ route('parent.calendar') }}" class="quick-link-card">
                                <div class="link-icon-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">Clases y Asistencias</h6>
                                    <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                                        Mira el calendario de clases de tus hijos y revisa el registro detallado de su asistencia diaria.
                                    </p>
                                </div>
                            </a>

                            <a href="{{ route('parent.payments') }}" class="quick-link-card">
                                <div class="link-icon-wrapper">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">Pagos y Comprobantes</h6>
                                    <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                                        Consulta tus cuotas pendientes, sube tus recibos de pago y mantén tu cuenta al día.
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Últimos Comprobantes de Pago Subidos -->
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Historial Reciente de Pagos</h5>
                        </div>
                        <a href="{{ route('parent.payments') }}" class="btn btn-sm btn-link text-primary p-0 fw-bold text-decoration-none">
                            Ver Todos
                        </a>
                    </div>
                    <div class="card-body px-4 pb-4 pt-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="text-muted" style="font-size: 0.78rem; border-bottom: 1px solid #f1f5f9;">
                                        <th class="pb-2">Fecha</th>
                                        <th class="pb-2">Monto</th>
                                        <th class="pb-2 text-end">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($parentPayments as $payment)
                                        <tr style="border-bottom: 1px solid #f8fafc;">
                                            <td class="py-2.5">
                                                <div class="d-flex flex-column">
                                                    <span class="text-dark fw-semibold" style="font-size: 0.85rem;">
                                                        {{ optional($payment->created_at)->format('d/m/Y') ?? 'N/A' }}
                                                    </span>
                                                    <small class="text-muted" style="font-size: 0.75rem; text-overflow: ellipsis; white-space: nowrap; max-width: 140px; overflow: hidden;">
                                                        {{ optional($payment->receivable)->title ?? 'Pago' }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="py-2.5 fw-bold text-dark" style="font-size: 0.85rem;">
                                                ${{ number_format((float) $payment->amount, 2) }}
                                            </td>
                                            <td class="py-2.5 text-end">
                                                @if ($payment->status === 'approved')
                                                    <span class="badge bg-success-light text-success" style="background-color: #ecfdf5; font-size: 0.72rem; border-radius: 20px; padding: 0.25rem 0.5rem;">
                                                        Aprobado
                                                    </span>
                                                @elseif ($payment->status === 'rejected')
                                                    <span class="badge bg-danger-light text-danger" style="background-color: #fef2f2; font-size: 0.72rem; border-radius: 20px; padding: 0.25rem 0.5rem;">
                                                        Rechazado
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-light text-warning" style="background-color: #fffbeb; font-size: 0.72rem; border-radius: 20px; padding: 0.25rem 0.5rem;">
                                                        Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3 text-muted" style="font-size: 0.82rem;">
                                                Aún no has registrado ningún pago.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
