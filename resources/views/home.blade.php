@extends('layouts.admin')

@section('title')
    <title>Escritorio - {{ env('APP_NAME') }}</title>
@endsection

@section('styles')
    <style>
        .home-hero {
            background:
                radial-gradient(circle at 10% 20%, rgba(255, 196, 86, 0.28), transparent 46%),
                radial-gradient(circle at 90% 10%, rgba(54, 162, 235, 0.25), transparent 42%),
                linear-gradient(145deg, #0f172a, #1e293b 55%, #111827);
            color: #e5e7eb;
            border-radius: 18px;
            padding: 1.4rem;
            border: 1px solid rgba(148, 163, 184, 0.25);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.25);
            margin-bottom: 1rem;
        }

        .home-hero h4 {
            color: #f8fafc;
            margin-bottom: 0.35rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .home-hero p {
            margin-bottom: 0;
            color: #cbd5e1;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .quick-link {
            display: block;
            border-radius: 14px;
            padding: 0.9rem 1rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0f172a;
            text-decoration: none;
            transition: all 0.2s ease;
            min-height: 104px;
        }

        .quick-link:hover {
            transform: translateY(-3px);
            border-color: #93c5fd;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.1);
            color: #0f172a;
        }

        .quick-link .icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.45rem;
            background: #e0ecff;
            color: #1d4ed8;
        }

        .quick-link h6 {
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .quick-link small {
            color: #64748b;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .kpi-card {
            border-radius: 14px;
            padding: 0.95rem 1rem;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .kpi-label {
            color: #64748b;
            font-size: 0.82rem;
            margin-bottom: 0.2rem;
        }

        .kpi-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .kpi-hint {
            color: #64748b;
            font-size: 0.76rem;
            margin-top: 0.3rem;
        }

        .dashboard-card {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #fff;
            height: 100%;
        }

        .dashboard-card .card-header {
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }

        .chart-wrap {
            position: relative;
            min-height: 320px;
        }

        .home-footer {
            margin-top: 1rem;
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .home-hero {
                padding: 1rem;
            }

            .chart-wrap {
                min-height: 280px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="home-hero">
        <h4>Panel Ejecutivo</h4>
        <p>Vista general de operación, cobranzas y agenda para tomar decisiones rápidas.</p>
    </div>

    <div class="quick-grid">
        <a href="{{ url('enrollment') }}" class="quick-link">
            <span class="icon"><i class="fas fa-address-book"></i></span>
            <h6>Inscripciones</h6>
            <small>Altas, estados y seguimiento</small>
        </a>
        <a href="{{ route('finance.index') }}" class="quick-link">
            <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
            <h6>Finanzas</h6>
            <small>Dashboard y movimientos</small>
        </a>
        <a href="{{ route('finance.collections') }}" class="quick-link">
            <span class="icon"><i class="fas fa-money-check-dollar"></i></span>
            <h6>Cobranzas</h6>
            <small>Cuentas por cobrar</small>
        </a>
        <a href="{{ route('finance.payables') }}" class="quick-link">
            <span class="icon"><i class="fas fa-file-invoice"></i></span>
            <h6>Pagos</h6>
            <small>Cuentas por pagar</small>
        </a>
        <a href="{{ route('calendar.index') }}" class="quick-link">
            <span class="icon"><i class="fas fa-calendar-days"></i></span>
            <h6>Calendario</h6>
            <small>Clases por fecha y sede</small>
        </a>
        <a href="{{ route('courses.index') }}" class="quick-link">
            <span class="icon"><i class="fas fa-diagram-project"></i></span>
            <h6>Cursos</h6>
            <small>Oferta y configuración</small>
        </a>
        <a href="{{ route('students.index') }}" class="quick-link">
            <span class="icon"><i class="fas fa-user-graduate"></i></span>
            <h6>Estudiantes</h6>
            <small>Base activa del sistema</small>
        </a>
        <a href="{{ route('trainers.index') }}" class="quick-link">
            <span class="icon"><i class="fas fa-dumbbell"></i></span>
            <h6>Entrenadores</h6>
            <small>Equipo de coaches</small>
        </a>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Balance neto</div>
            <div class="kpi-value">${{ number_format($netBalance, 2) }}</div>
            <div class="kpi-hint">Ingresos completados - egresos completados</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Ingresos completados</div>
            <div class="kpi-value">${{ number_format($completedIncome, 2) }}</div>
            <div class="kpi-hint">Transacciones de tipo ingreso</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Egresos completados</div>
            <div class="kpi-value">${{ number_format($completedExpense, 2) }}</div>
            <div class="kpi-hint">Transacciones de tipo gasto</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Cuentas por cobrar pendientes</div>
            <div class="kpi-value">${{ number_format($pendingReceivables, 2) }}</div>
            <div class="kpi-hint">Saldos de cobranza pendientes/parciales</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Cuentas por pagar pendientes</div>
            <div class="kpi-value">${{ number_format($pendingPayables, 2) }}</div>
            <div class="kpi-hint">Compromisos pendientes/parciales</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Inscripciones / Estudiantes</div>
            <div class="kpi-value">{{ $enrollmentsCount }} / {{ $studentsCount }}</div>
            <div class="kpi-hint">Volumen comercial y base activa</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Cursos activos / Coaches</div>
            <div class="kpi-value">{{ $activeCoursesCount }} / {{ $coachesCount }}</div>
            <div class="kpi-hint">Capacidad operativa actual</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Clases hoy / próximos 7 días</div>
            <div class="kpi-value">{{ $todayClasses }} / {{ $next7DaysClasses }}</div>
            <div class="kpi-hint">Agenda inmediata en {{ $branchesCount }} sede(s)</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7 col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-1">Tendencia Financiera (6 meses)</h5>
                    <span class="text-muted">Comparativo mensual de ingresos y egresos completados</span>
                </div>
                <div class="card-body">
                    <div class="chart-wrap">
                        <canvas id="monthlyFinanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-1">Estado de Pagos de Inscripciones</h5>
                    <span class="text-muted">Distribución pagado vs pendiente</span>
                </div>
                <div class="card-body">
                    <div class="chart-wrap">
                        <canvas id="enrollmentPaymentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-1">Carga de Clases por Sede</h5>
                    <span class="text-muted">Top de sedes con mayor número de clases registradas</span>
                </div>
                <div class="card-body">
                    <div class="chart-wrap" style="min-height:340px;">
                        <canvas id="classesByBranchChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyLabels = @json($monthlyLabels);
            const monthlyIncome = @json($monthlyIncome);
            const monthlyExpense = @json($monthlyExpense);
            const enrollmentPaid = Number(@json($enrollmentPaid));
            const enrollmentPending = Number(@json($enrollmentPending));
            const classesByBranchLabels = @json($classesByBranchLabels);
            const classesByBranchValues = @json($classesByBranchValues);

            new Chart(document.getElementById('monthlyFinanceChart'), {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                            label: 'Ingresos',
                            data: monthlyIncome,
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderColor: '#16a34a',
                            borderWidth: 1,
                            borderRadius: 8,
                        },
                        {
                            label: 'Egresos',
                            data: monthlyExpense,
                            backgroundColor: 'rgba(239, 68, 68, 0.78)',
                            borderColor: '#dc2626',
                            borderWidth: 1,
                            borderRadius: 8,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + Number(value).toLocaleString('en-US');
                                }
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('enrollmentPaymentChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Pagado', 'Pendiente'],
                    datasets: [{
                        data: [enrollmentPaid, enrollmentPending],
                        backgroundColor: ['#22c55e', '#f59e0b'],
                        borderColor: ['#16a34a', '#d97706'],
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            new Chart(document.getElementById('classesByBranchChart'), {
                type: 'bar',
                data: {
                    labels: classesByBranchLabels,
                    datasets: [{
                        label: 'Clases registradas',
                        data: classesByBranchValues,
                        backgroundColor: 'rgba(59, 130, 246, 0.78)',
                        borderColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 8,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
