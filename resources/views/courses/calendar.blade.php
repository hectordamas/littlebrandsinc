@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Calendario de Clases</title>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <style>
        .calendar-card {
            border: 1px solid #e9ecef;
            border-radius: 0.9rem;
            background: #fff;
        }

        .calendar-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .calendar-toolbar .form-group {
            min-width: 260px;
            margin-bottom: 0;
        }

        #classesCalendar {
            min-height: 700px;
            border: 1px solid #dbe3f1;
            border-radius: 0.95rem;
            padding: 0.55rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .fc .fc-toolbar-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
        }

        .fc .fc-button {
            text-transform: capitalize;
            border-radius: 0.65rem !important;
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #0f172a !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
            transition: all 0.2s ease;
        }

        .fc .fc-button:hover,
        .fc .fc-button:focus {
            transform: translateY(-1px);
            border-color: #93c5fd !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
        }

        .fc .fc-col-header-cell {
            background: #f8fafc;
        }

        .fc .fc-col-header-cell-cushion {
            color: #334155;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            padding: 0.55rem 0.35rem;
        }

        .fc .fc-daygrid-day {
            transition: background-color 0.16s ease;
        }

        .fc .fc-daygrid-day:hover {
            background: #f8fbff;
        }

        .fc .fc-daygrid-day.fc-day-today {
            background: #eef6ff !important;
        }

        .fc .fc-daygrid-day-number {
            color: #1e293b;
            font-weight: 600;
        }

        .fc .fc-daygrid-event {
            border-radius: 0.55rem;
            padding: 0.14rem 0.24rem;
            box-shadow: 0 1px 5px rgba(15, 23, 42, 0.08);
            transition: transform 0.14s ease, box-shadow 0.14s ease;
        }

        .fc .fc-daygrid-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
        }

        .fc .fc-timegrid-event {
            border-radius: 0.55rem;
        }

        #classDetailModal .modal-content {
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.24);
        }

        #classDetailModal .modal-header {
            border-bottom: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #0ea5e9 100%);
            color: #fff;
            padding: 1rem 1.25rem;
        }

        #classDetailModal .modal-header .btn-close {
            filter: invert(1);
            opacity: 0.85;
        }

        #classDetailModal .modal-title {
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        #classDetailModal .modal-subtitle {
            margin-top: 0.15rem;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.88);
        }

        #classDetailModal .modal-body {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 42%),
                linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            padding: 1rem;
        }

        .class-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.8rem;
        }

        .class-detail-item {
            border: 1px solid #dbe3f1;
            border-radius: 0.75rem;
            padding: 0.85rem;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
        }

        .class-detail-item .detail-label {
            display: block;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #334155;
            margin-bottom: 0.28rem;
            font-weight: 700;
        }

        .class-detail-item .detail-value {
            font-weight: 600;
            color: #0b1324;
            word-break: break-word;
        }

        .class-detail-item.class-detail-item-highlight {
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
            border-color: #bfdbfe;
        }

        .occupancy-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.28rem 0.55rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .occupancy-pill.occupancy-high {
            color: #166534;
            background: #dcfce7;
            border-color: #86efac;
        }

        .occupancy-pill.occupancy-medium {
            color: #854d0e;
            background: #fef9c3;
            border-color: #fde68a;
        }

        .occupancy-pill.occupancy-full {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .occupancy-pill.occupancy-na {
            color: #334155;
            background: #e2e8f0;
            border-color: #cbd5e1;
        }

        #classDetailModal .modal-footer {
            border-top: 1px solid #dbe3f1;
            background: #fff;
        }

        #detailDescription {
            color: #1e293b;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card calendar-card">
            <div class="card-header">
                <h5 class="mb-1">Calendario General de Clases</h5>
                <span class="text-muted">Visualiza todas las clases de todos los cursos en una sola agenda.</span>
            </div>
            <div class="card-body">
                <div class="calendar-toolbar">
                    <div class="form-group">
                        <label for="calendarBranchFilter" class="form-label">Filtrar por sede</label>
                        <select id="calendarBranchFilter" class="form-control">
                            <option value="">Todas las sedes</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-muted small" id="calendarStatus">Cargando calendario...</div>
                </div>

                <div id="classesCalendar"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="classDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="classDetailTitle">Detalle de clase</h5>
                        <div class="modal-subtitle" id="classDetailSubtitle">Informacion completa del curso y sesion</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="class-detail-grid">
                        <div class="class-detail-item">
                            <span class="detail-label">Sede</span>
                            <span class="detail-value" id="detailBranch">N/A</span>
                        </div>
                        <div class="class-detail-item">
                            <span class="detail-label">Coach</span>
                            <span class="detail-value" id="detailCoach">N/A</span>
                        </div>
                        <div class="class-detail-item">
                            <span class="detail-label">Horario</span>
                            <span class="detail-value" id="detailTime">N/A</span>
                        </div>
                        <div class="class-detail-item class-detail-item-highlight">
                            <span class="detail-label">Inscritos</span>
                            <span class="detail-value" id="detailEnrolledChildren">0</span>
                        </div>
                        <div class="class-detail-item class-detail-item-highlight">
                            <span class="detail-label">Capacidad</span>
                            <span class="detail-value" id="detailCapacity">N/A</span>
                        </div>
                        <div class="class-detail-item class-detail-item-highlight">
                            <span class="detail-label">Cupos disponibles</span>
                            <span class="detail-value" id="detailAvailableSpots">N/A</span>
                        </div>
                        <div class="class-detail-item class-detail-item-highlight">
                            <span class="detail-label">Nivel de ocupacion</span>
                            <span class="detail-value" id="detailOccupancy">N/A</span>
                        </div>
                        <div class="class-detail-item">
                            <span class="detail-label">Precio de inscripcion</span>
                            <span class="detail-value" id="detailPrice">N/A</span>
                        </div>
                        <div class="class-detail-item">
                            <span class="detail-label">Mensualidad</span>
                            <span class="detail-value" id="detailMonthlyFee">N/A</span>
                        </div>
                        <div class="class-detail-item">
                            <span class="detail-label">Inicio del curso</span>
                            <span class="detail-value" id="detailStartDate">N/A</span>
                        </div>
                        <div class="class-detail-item">
                            <span class="detail-label">Fin del curso</span>
                            <span class="detail-value" id="detailEndDate">N/A</span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <span class="detail-label">Descripcion del curso</span>
                        <div class="class-detail-item" id="detailDescription">Sin descripcion registrada.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('classesCalendar');
            const branchFilterEl = document.getElementById('calendarBranchFilter');
            const statusEl = document.getElementById('calendarStatus');
            const classDetailModalEl = document.getElementById('classDetailModal');
            const classDetailModal = new bootstrap.Modal(classDetailModalEl);

            function formatCurrency(value) {
                const number = Number(value);
                if (Number.isNaN(number)) {
                    return 'N/A';
                }

                return new Intl.NumberFormat('es-VE', {
                    style: 'currency',
                    currency: 'USD',
                }).format(number);
            }

            function formatDate(value) {
                if (!value) {
                    return 'N/A';
                }

                const date = new Date(value + 'T00:00:00');
                if (Number.isNaN(date.getTime())) {
                    return value;
                }

                return new Intl.DateTimeFormat('es-VE', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                }).format(date);
            }

            function getOccupancyMeta(enrolledChildren, capacity) {
                if (capacity === null || Number.isNaN(capacity) || capacity <= 0) {
                    return {
                        label: 'Capacidad no definida',
                        pillClass: 'occupancy-na',
                        eventBackground: '#e2e8f0',
                        eventBorder: '#cbd5e1',
                        eventText: '#0f172a'
                    };
                }

                const ratio = enrolledChildren / capacity;

                if (ratio >= 1) {
                    return {
                        label: 'Curso lleno',
                        pillClass: 'occupancy-full',
                        eventBackground: '#fee2e2',
                        eventBorder: '#fca5a5',
                        eventText: '#7f1d1d'
                    };
                }

                if (ratio >= 0.8) {
                    return {
                        label: 'Pocos cupos',
                        pillClass: 'occupancy-medium',
                        eventBackground: '#fef9c3',
                        eventBorder: '#fde68a',
                        eventText: '#713f12'
                    };
                }

                return {
                    label: 'Buen cupo disponible',
                    pillClass: 'occupancy-high',
                    eventBackground: '#dcfce7',
                    eventBorder: '#86efac',
                    eventText: '#14532d'
                };
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 'auto',
                firstDay: 1,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Dia'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    statusEl.textContent = 'Cargando clases...';

                    const params = new URLSearchParams({
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                    });

                    if (branchFilterEl.value) {
                        params.set('branch_id', branchFilterEl.value);
                    }

                    fetch(`{{ route('calendar.events') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('No se pudo cargar el calendario.');
                        }

                        return response.json();
                    })
                    .then(function(events) {
                        statusEl.textContent = `Mostrando ${events.length} clase(s).`;
                        successCallback(events);
                    })
                    .catch(function(error) {
                        statusEl.textContent = 'Error al cargar clases.';
                        failureCallback(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Calendario no disponible',
                            text: 'No fue posible cargar las clases del calendario.'
                        });
                    });
                },
                eventClick: function(info) {
                    const props = info.event.extendedProps || {};
                    const enrolledChildren = Number(props.enrolled_children || 0);
                    const capacity = props.course_capacity !== null && props.course_capacity !== undefined
                        ? Number(props.course_capacity)
                        : null;
                    const availableSpots = capacity !== null && !Number.isNaN(capacity)
                        ? Math.max(0, capacity - enrolledChildren)
                        : null;
                    const occupancy = getOccupancyMeta(enrolledChildren, capacity);
                    const eventDate = info.event.start
                        ? new Intl.DateTimeFormat('es-VE', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        }).format(info.event.start)
                        : 'Fecha no disponible';

                    document.getElementById('classDetailTitle').textContent = info.event.title || 'Detalle de clase';
                    document.getElementById('classDetailSubtitle').textContent = eventDate + ' | ' + (props.time || 'Horario no disponible');
                    document.getElementById('detailBranch').textContent = props.branch || 'N/A';
                    document.getElementById('detailCoach').textContent = props.coach || 'N/A';
                    document.getElementById('detailTime').textContent = props.time || 'N/A';
                    document.getElementById('detailEnrolledChildren').textContent = String(enrolledChildren);
                    document.getElementById('detailCapacity').textContent = capacity === null || Number.isNaN(capacity) ? 'N/A' : String(capacity);
                    document.getElementById('detailAvailableSpots').textContent = availableSpots === null ? 'N/A' : String(availableSpots);
                    document.getElementById('detailOccupancy').innerHTML = '<span class="occupancy-pill ' + occupancy.pillClass + '">' + occupancy.label + '</span>';
                    document.getElementById('detailPrice').textContent = formatCurrency(props.course_price);
                    document.getElementById('detailMonthlyFee').textContent = formatCurrency(props.course_monthly_fee);
                    document.getElementById('detailStartDate').textContent = formatDate(props.course_start_date);
                    document.getElementById('detailEndDate').textContent = formatDate(props.course_end_date);
                    document.getElementById('detailDescription').textContent = props.course_description || 'Sin descripcion registrada.';

                    classDetailModal.show();
                },
                eventDidMount: function(info) {
                    const props = info.event.extendedProps || {};
                    const enrolledChildren = Number(props.enrolled_children || 0);
                    const capacity = props.course_capacity !== null && props.course_capacity !== undefined
                        ? Number(props.course_capacity)
                        : null;
                    const occupancy = getOccupancyMeta(enrolledChildren, capacity);

                    info.el.style.backgroundColor = occupancy.eventBackground;
                    info.el.style.borderColor = occupancy.eventBorder;
                    info.el.style.color = occupancy.eventText;
                    info.el.style.borderLeft = '4px solid ' + occupancy.eventBorder;
                }
            });

            calendar.render();

            branchFilterEl.addEventListener('change', function() {
                calendar.refetchEvents();
            });
        });
    </script>
@endsection
