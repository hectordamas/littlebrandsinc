@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Portal de Familia</title>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-1">Portal de Familia</h5>
                    <span class="text-muted">Estado de cuenta, clases programadas, asistencias y reportes de tus hijos.</span>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="alert alert-warning mb-0">
                                <strong>Saldo pendiente:</strong> ${{ number_format($pendingBalance, 2) }}
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="alert alert-info mb-0">
                                <strong>Cuotas pendientes:</strong> {{ $pendingInstallments }}
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="alert alert-success mb-0">
                                <strong>Hijos registrados:</strong> {{ $students->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-1">Estado de Cuenta</h5>
                    <span class="text-muted">Resumen de cuentas por cobrar asociadas a tus inscripciones.</span>
                </div>
                <div class="card-block table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Estudiante</th>
                                <th>Programa</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receivables as $receivable)
                                <tr>
                                    <td>{{ $receivable->id }}</td>
                                    <td>{{ optional(optional($receivable->enrollment)->student)->name ?? 'N/A' }}</td>
                                    <td>{{ optional(optional($receivable->enrollment)->course)->title ?? 'N/A' }}</td>
                                    <td>${{ number_format((float) $receivable->amount_total, 2) }}</td>
                                    <td>${{ number_format((float) $receivable->balance_due, 2) }}</td>
                                    <td>
                                        @if ($receivable->status === 'paid')
                                            <span class="badge bg-success">Pagado</span>
                                        @elseif ($receivable->status === 'partial')
                                            <span class="badge bg-warning text-dark">Parcial</span>
                                        @else
                                            <span class="badge bg-secondary">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted">No hay cuentas por cobrar registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-12">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-1">Clases Programadas</h5>
                    <span class="text-muted">Próximas sesiones por estudiante.</span>
                </div>
                <div class="card-block table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Curso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingClasses as $row)
                                @php($class = $row['class'])
                                <tr>
                                    <td>{{ $row['student_name'] }}</td>
                                    <td>{{ optional($class->date)->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ substr((string) $class->start_time, 0, 5) }} - {{ substr((string) $class->end_time, 0, 5) }}</td>
                                    <td>{{ optional($class->course)->title ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">No hay clases próximas para tus hijos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-12">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-1">Asistencias y Reportes</h5>
                    <span class="text-muted">Últimos registros de check in por clase.</span>
                </div>
                <div class="card-block table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Fecha</th>
                                <th>Clase</th>
                                <th>Check in</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendanceRows as $row)
                                @php($attendance = $row['attendance'])
                                <tr>
                                    <td>{{ $row['student_name'] }}</td>
                                    <td>{{ optional($attendance->date)->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ optional(optional($attendance->class)->course)->title ?? 'N/A' }}</td>
                                    <td>
                                        @if ($attendance->status === 'present')
                                            <span class="badge bg-success">Presente</span>
                                        @elseif ($attendance->status === 'late')
                                            <span class="badge bg-warning text-dark">Tarde</span>
                                        @elseif ($attendance->status === 'absent')
                                            <span class="badge bg-danger">Ausente</span>
                                        @else
                                            <span class="badge bg-secondary">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">Aun no hay asistencias registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-1">Notificaciones por Correo</h5>
                    <span class="text-muted">Recibirás avisos de inicio de temporada, torneos y eventos especiales.</span>
                </div>
                <div class="card-block">
                    <ul class="mb-0">
                        <li>Inicio de temporada: cronograma de apertura y recomendaciones.</li>
                        <li>Torneos: fecha, sede, hora y pautas para la familia.</li>
                        <li>Eventos: actividades especiales y convocatorias del programa.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
