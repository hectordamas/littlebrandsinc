@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Calendario de Clases y Asistencias</title>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <style>
        #parentCalendar {
            min-height: 650px;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }

        .fc .fc-button {
            text-transform: capitalize;
            border-radius: 0.65rem !important;
            border: 1px solid #cbd5e1 !important;
            background: #ffffff !important;
            color: #0f172a !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .fc .fc-button:hover {
            transform: translateY(-1px);
            border-color: #93c5fd !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
        }

        .fc .fc-col-header-cell {
            background: #f8fafc;
            padding: 0.6rem 0;
        }

        .fc .fc-col-header-cell-cushion {
            color: #475569;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .fc .fc-daygrid-day {
            transition: background-color 0.15s ease;
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
            padding: 0.4rem;
        }

        .fc .fc-daygrid-event {
            border-radius: 0.55rem;
            padding: 0.15rem 0.35rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
            cursor: pointer;
        }

        .fc .fc-daygrid-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(15, 23, 42, 0.12);
        }

        .legend-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Detail Modal */
        #detailModal .modal-content {
            border-radius: 16px;
            overflow: hidden;
            border: 0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        #detailModal .modal-header {
            background: linear-gradient(135deg, #1e293b 0%, #1e40af 100%);
            color: #ffffff;
            border-bottom: 0;
            padding: 1.25rem 1.5rem;
        }

        #detailModal .modal-header .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }

        #detailModal .modal-body {
            background-color: #f8fafc;
            padding: 1.5rem;
        }

        .detail-row {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.01);
        }
    </style>
@endsection

@section('content')
    <div class="row g-3">
        <!-- Tarjeta Filtro Superior -->
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">Clases y Asistencias</h4>
                        <p class="text-muted mb-0">Revisa la programación de clases de tus hijos y conoce en tiempo real su estatus de asistencia.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="studentFilter" class="form-label mb-0 fw-semibold text-muted text-nowrap">Filtrar por Hijo:</label>
                        <select id="studentFilter" class="form-select border-slate-200" style="border-radius: 10px; min-width: 200px; padding: 0.45rem 1rem;">
                            <option value="">Todos los hijos</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendario Principal -->
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <!-- Leyenda de colores -->
                    <div class="d-flex flex-wrap gap-4 mb-4 justify-content-start align-items-center bg-light p-3 rounded-3" style="font-size: 0.85rem; border: 1px solid #e2e8f0; border-radius: 12px !important;">
                        <span class="fw-bold text-muted uppercase me-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">ESTADO DE ASISTENCIA:</span>
                        <div class="d-flex align-items-center">
                            <span class="legend-indicator" style="background-color: #2563eb;"></span>
                            <span class="text-dark fw-medium">Programada / Pendiente</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="legend-indicator" style="background-color: #10b981;"></span>
                            <span class="text-dark fw-medium">Presente</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="legend-indicator" style="background-color: #f59e0b;"></span>
                            <span class="text-dark fw-medium">Tarde</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="legend-indicator" style="background-color: #ef4444;"></span>
                            <span class="text-dark fw-medium">Ausente</span>
                        </div>
                    </div>

                    <div id="parentCalendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalle de Clase -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <small class="text-white-50 d-block text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;" id="modalCourseSubtitle">CLASE</small>
                        <h5 class="modal-title fw-bold" id="modalCourseTitle">Detalle de Sesión</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column gap-3">
                        <!-- Alumno y Asistencia -->
                        <div class="detail-row d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Alumno</small>
                                <span class="fw-bold text-dark fs-6" id="modalStudentName">-</span>
                            </div>
                            <span class="badge px-3 py-2 fs-7" id="modalAttendanceBadge" style="border-radius: 20px;">-</span>
                        </div>

                        <!-- Detalles de Sede, Coach y Horario -->
                        <div class="detail-row d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 0.85rem;"><i class="fas fa-building me-2"></i>Sede</span>
                                <span class="fw-bold text-dark text-end" id="modalBranchName" style="font-size: 0.85rem;">-</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 0.85rem;"><i class="fas fa-user-tie me-2"></i>Entrenador (Coach)</span>
                                <span class="fw-bold text-dark text-end" id="modalCoachName" style="font-size: 0.85rem;">-</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 0.85rem;"><i class="fas fa-clock me-2"></i>Fecha y Horario</span>
                                <span class="fw-bold text-dark text-end" id="modalClassTime" style="font-size: 0.85rem;">-</span>
                            </div>
                        </div>

                        <!-- Observaciones del Coach -->
                        <div class="detail-row d-none" id="modalNotesSection">
                            <small class="text-muted d-block fw-semibold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;"><i class="fas fa-comment-alt me-1 text-primary"></i>Observaciones del Coach</small>
                            <p class="mb-0 text-dark" id="modalNotesContent" style="font-size: 0.85rem; font-style: italic; color: #475569;"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 pt-0 justify-content-end px-4 pb-3">
                    <button type="button" class="btn btn-secondary px-4" style="border-radius: 8px;" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('parentCalendar');
            const studentFilterEl = document.getElementById('studentFilter');
            const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            
            const modalStudentName = document.getElementById('modalStudentName');
            const modalAttendanceBadge = document.getElementById('modalAttendanceBadge');
            const modalCourseTitle = document.getElementById('modalCourseTitle');
            const modalCourseSubtitle = document.getElementById('modalCourseSubtitle');
            const modalBranchName = document.getElementById('modalBranchName');
            const modalCoachName = document.getElementById('modalCoachName');
            const modalClassTime = document.getElementById('modalClassTime');
            const modalNotesSection = document.getElementById('modalNotesSection');
            const modalNotesContent = document.getElementById('modalNotesContent');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                firstDay: 1,
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                        student_id: studentFilterEl.value
                    });

                    fetch(`{{ route('parent.calendar.events') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('No fue posible cargar el calendario.');
                        }
                        return response.json();
                    })
                    .then(events => {
                        successCallback(events);
                    })
                    .catch(error => {
                        failureCallback(error);
                        console.error('Error fetching calendar events:', error);
                    });
                },
                eventClick: function(info) {
                    const props = info.event.extendedProps || {};
                    
                    modalCourseTitle.textContent = props.course_title || 'Clase';
                    modalCourseSubtitle.textContent = 'Detalle de Sesión';
                    modalStudentName.textContent = props.student_name || 'Estudiante';
                    modalBranchName.textContent = props.branch || 'Sin sede';
                    modalCoachName.textContent = props.coach || 'Sin asignar';
                    
                    // Format class date nicely
                    const classDate = info.event.start;
                    const dateFormatted = classDate.toLocaleDateString('es-ES', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    
                    // Capitalize weekday
                    const dateCapitalized = dateFormatted.charAt(0).toUpperCase() + dateFormatted.slice(1);
                    modalClassTime.textContent = `${dateCapitalized} de ${props.time}`;
                    
                    // Reset and configure badge class
                    modalAttendanceBadge.className = 'badge px-3 py-2 fs-7';
                    if (props.attendance === 'present') {
                        modalAttendanceBadge.textContent = 'Presente';
                        modalAttendanceBadge.classList.add('bg-success');
                    } else if (props.attendance === 'late') {
                        modalAttendanceBadge.textContent = 'Tarde';
                        modalAttendanceBadge.classList.add('bg-warning', 'text-dark');
                    } else if (props.attendance === 'absent') {
                        modalAttendanceBadge.textContent = 'Ausente';
                        modalAttendanceBadge.classList.add('bg-danger');
                    } else {
                        modalAttendanceBadge.textContent = 'Programada / Pendiente';
                        modalAttendanceBadge.classList.add('bg-primary');
                    }

                    // Coach comments
                    if (props.notes && props.notes.trim() !== '') {
                        modalNotesSection.classList.remove('d-none');
                        modalNotesContent.textContent = `"${props.notes}"`;
                    } else {
                        modalNotesSection.classList.add('d-none');
                        modalNotesContent.textContent = '';
                    }

                    detailModal.show();
                }
            });

            calendar.render();

            studentFilterEl.addEventListener('change', function() {
                calendar.refetchEvents();
            });
        });
    </script>
@endsection
