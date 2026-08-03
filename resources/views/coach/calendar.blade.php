@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Calendario del Entrenador</title>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <style>
        #coachCalendar {
            min-height: 680px;
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

        #attendanceModal .modal-content {
            max-height: calc(100vh - 3rem);
            border-radius: 0.9rem;
            overflow: hidden;
            border: 0;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.2);
        }

        #attendanceModal #attendanceForm {
            display: flex;
            flex-direction: column;
            min-height: 0;
            height: 100%;
        }

        #attendanceModal .modal-header {
            border-bottom: 0;
            background: linear-gradient(120deg, #0f172a, #1d4ed8);
            color: #fff;
        }

        #attendanceModal .modal-header .btn-close {
            filter: invert(1);
            opacity: 0.9;
        }

        #attendanceModal .modal-body {
            overflow-y: auto;
            min-height: 0;
            background: linear-gradient(180deg, #f8fafc, #eef2ff);
        }

        #attendanceModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #dbe3f1;
            z-index: 2;
        }

        #attendanceModal {
            z-index: 1050 !important;
        }

        #studentObservationModal {
            z-index: 1070 !important;
        }

        .class-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .class-detail-item {
            border: 1px solid #dbe3f1;
            border-radius: 0.75rem;
            padding: 0.75rem;
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }

        .class-detail-item .detail-label {
            display: block;
            font-size: 0.73rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .class-detail-item .detail-value {
            font-weight: 600;
            color: #0f172a;
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
            padding: 0.25rem 0.55rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .occupancy-pill.occupancy-high {
            color: #166534;
            background: #dcfce7;
            border-color: #bbf7d0;
        }

        .occupancy-pill.occupancy-medium {
            color: #854d0e;
            background: #fef9c3;
            border-color: #fef08a;
        }

        .occupancy-pill.occupancy-full {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .occupancy-pill.occupancy-none {
            color: #475569;
            background: #f1f5f9;
            border-color: #e2e8f0;
        }

        .attendance-table-container {
            border: 1px solid #dbe3f1;
            border-radius: 0.75rem;
            overflow: hidden;
            background: #ffffff;
            margin-bottom: 1rem;
        }

        .attendance-table th {
            background-color: #f8fafc;
            color: #334155;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 0.75rem 0.6rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .attendance-table td {
            vertical-align: middle;
            padding: 0.65rem 0.6rem;
            font-size: 0.88rem;
        }

        .attendance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.6rem;
            border: 1px solid #dbe3f1;
            border-radius: 0.75rem;
            padding: 0.7rem 0.8rem;
            background: #fff;
        }

        .attendance-student {
            font-weight: 600;
            color: #0f172a;
        }

        .attendance-check {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.9rem;
            color: #1e293b;
            user-select: none;
        }

        .attendance-note {
            margin-top: 0.5rem;
        }

        .attendance-check input[type="checkbox"] {
            width: 1.1rem;
            height: 1.1rem;
            accent-color: #2563eb;
        }

        .attendance-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 0.75rem;
            background: #fff;
            color: #475569;
            padding: 0.9rem;
        }

        .attendance-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
            padding: 0.6rem 0.75rem;
            border: 1px solid #dbe3f1;
            border-radius: 0.75rem;
            background: #ffffff;
        }

        .attendance-save-status {
            font-size: 0.82rem;
            font-weight: 600;
            color: #1e40af;
        }

        .attendance-save-status.is-saving {
            color: #92400e;
        }

        .attendance-save-status.is-error {
            color: #b91c1c;
        }

        #calendarStatus {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.82rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
        }

        #calendarStatus * {
            color: #ffffff !important;
        }

        @media (max-width: 768px) {
            .attendance-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Mi Programación de Sesiones de Clase</h5>
                        <span class="text-muted">Cada sesión de clase muestra inscritos, nombres de estudiantes y estado de check in.</span>
                    </div>
                    <span class="badge bg-primary text-white d-inline-flex align-items-center gap-1" id="calendarStatus">
                        <span class="spinner-border spinner-border-sm d-none" id="coachSpinner" style="width: 0.85rem; height: 0.85rem;" role="status"></span>
                        <span id="coachStatusText" class="text-white fw-bold">Cargando...</span>
                    </span>
                </div>
                <div class="card-block">
                    <div id="coachCalendar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form id="attendanceForm" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0" id="attendanceTitle">Asistencia</h5>
                            <div class="small opacity-75 mt-1" id="attendanceSubtitle">Información de la clase</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Información Detallada de la Clase (Estilo Admin) -->
                        <div class="mb-3">
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
                                    <span class="detail-label">Nivel de ocupación</span>
                                    <span class="detail-value" id="detailOccupancy">N/A</span>
                                </div>
                                <div class="class-detail-item">
                                    <span class="detail-label">Precio de Inscripción</span>
                                    <span class="detail-value" id="detailPrice">N/A</span>
                                </div>
                                <div class="class-detail-item">
                                    <span class="detail-label">Mensualidad</span>
                                    <span class="detail-value" id="detailMonthlyFee">N/A</span>
                                </div>
                                <div class="class-detail-item">
                                    <span class="detail-label">Inicio de la Clase</span>
                                    <span class="detail-value" id="detailStartDate">N/A</span>
                                </div>
                                <div class="class-detail-item">
                                    <span class="detail-label">Fin de la Clase</span>
                                    <span class="detail-value" id="detailEndDate">N/A</span>
                                </div>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-md-6">
                                    <div class="class-detail-item h-100">
                                        <span class="detail-label"><i class="fas fa-align-left me-1 text-primary"></i>Descripción de la Clase</span>
                                        <div class="detail-value small text-secondary mt-1" id="detailDescription" style="min-height: 45px; max-height: 90px; overflow-y: auto;">Sin descripción registrada.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="class-detail-item h-100">
                                        <span class="detail-label"><i class="fas fa-chart-pie me-1 text-success"></i>Resumen de Asistencias</span>
                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            <span class="badge bg-success px-2 py-1" id="badgePresentCount"><i class="fas fa-check-circle me-1"></i>Presentes: 0</span>
                                            <span class="badge bg-danger px-2 py-1" id="badgeAbsentCount"><i class="fas fa-times-circle me-1"></i>Ausentes: 0</span>
                                            <span class="badge bg-warning text-dark px-2 py-1" id="badgeLateCount"><i class="fas fa-clock me-1"></i>Tarde: 0</span>
                                            <span class="badge bg-secondary px-2 py-1" id="badgePendingCount"><i class="fas fa-hourglass-start me-1"></i>Pendientes: 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="attendance-tools" id="attendanceTools">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="toggleAllAttendance">
                                <label class="form-check-label" for="toggleAllAttendance">Marcar todos presentes</label>
                            </div>
                            <span id="attendanceSaveStatus" class="attendance-save-status">Guardado automático activo</span>
                        </div>
                        <div id="attendanceRows"></div>

                        <!-- Observaciones generales de la clase -->
                        <div class="mb-3 mt-3 border rounded p-3 bg-white shadow-sm" id="generalObservationsContainer">
                            <label for="generalObservations" class="form-label fw-bold text-dark mb-2">
                                <i class="fas fa-edit me-1 text-primary"></i> Observaciones Generales de la Sesión
                            </label>
                            <textarea id="generalObservations" class="form-control" rows="3" placeholder="Escribe observaciones generales del día, temas vistos, incidencias, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
        </div>
    </div>

    <!-- Modal secundario para editar Observaciones del Estudiante -->
    <div class="modal fade" id="studentObservationModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white py-2.5">
                    <h6 class="modal-title fw-bold" id="studentObservationModalTitle">
                        <i class="fas fa-user-edit me-2 text-primary"></i>Observaciones del Estudiante
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-3 bg-light">
                    <div class="fw-bold text-dark mb-2" id="studentObservationModalStudentName"></div>
                    <label for="studentObservationInput" class="form-label text-muted small fw-semibold">Observación / Nota de asistencia:</label>
                    <textarea id="studentObservationInput" class="form-control" rows="4" placeholder="Escribe observaciones específicas sobre la asistencia o desarrollo del estudiante en esta sesión..."></textarea>
                </div>
                <div class="modal-footer py-2 bg-white">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-primary" id="saveStudentObservationBtn">
                        <i class="fas fa-save me-1"></i>Guardar Observación
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('coachCalendar');
            const statusEl = document.getElementById('calendarStatus');
            const attendanceForm = document.getElementById('attendanceForm');
            const attendanceRowsEl = document.getElementById('attendanceRows');
            const attendanceTitleEl = document.getElementById('attendanceTitle');
            const attendanceMetaEl = document.getElementById('attendanceMeta');
            const attendanceToolsEl = document.getElementById('attendanceTools');
            const toggleAllAttendanceEl = document.getElementById('toggleAllAttendance');
            const attendanceSaveStatusEl = document.getElementById('attendanceSaveStatus');
            const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));
            const csrfToken = '{{ csrf_token() }}';

            let savingInProgress = false;
            let pendingSave = false;
            let currentEvent = null;

            function getAttendanceCheckboxes() {
                return Array.from(attendanceRowsEl.querySelectorAll('input[type="checkbox"].attendance-item-checkbox'));
            }

            function buildAttendancePayload() {
                const attendance = {};
                const notes = {};
                getAttendanceCheckboxes().forEach(function(checkbox) {
                    const studentId = checkbox.dataset.studentId;
                    if (!studentId) {
                        return;
                    }

                    attendance[studentId] = checkbox.checked ? 'present' : 'absent';

                    const noteInput = attendanceRowsEl.querySelector(`textarea[data-note-student-id="${studentId}"]`);
                    notes[studentId] = noteInput ? noteInput.value : '';
                });

                return {
                    attendance: attendance,
                    notes: notes,
                    observations: document.getElementById('generalObservations').value,
                };
            }

            function syncCurrentEventStudents(attendanceMap) {
                if (!currentEvent) {
                    return;
                }

                const currentStudents = Array.isArray(currentEvent.extendedProps.students)
                    ? currentEvent.extendedProps.students
                    : [];

                const updatedStudents = currentStudents.map(function(student) {
                    const studentId = String(student.student_id || '');
                    if (!Object.prototype.hasOwnProperty.call(attendanceMap, studentId)) {
                        return student;
                    }

                    return {
                        ...student,
                        check_in: attendanceMap[studentId],
                    };
                });

                currentEvent.setExtendedProp('students', updatedStudents);
            }

            function syncCurrentEventObservations(observations) {
                if (!currentEvent) {
                    return;
                }
                currentEvent.setExtendedProp('observations', observations);
            }

            function syncToggleAllState() {
                const checkboxes = getAttendanceCheckboxes();
                if (!checkboxes.length) {
                    toggleAllAttendanceEl.checked = false;
                    toggleAllAttendanceEl.disabled = true;
                    return;
                }

                toggleAllAttendanceEl.disabled = false;
                toggleAllAttendanceEl.checked = checkboxes.every(function(checkbox) {
                    return checkbox.checked;
                });
            }

            function setSaveStatus(text, tone) {
                attendanceSaveStatusEl.textContent = text;
                attendanceSaveStatusEl.classList.remove('is-saving', 'is-error');
                if (tone === 'saving') {
                    attendanceSaveStatusEl.classList.add('is-saving');
                }
                if (tone === 'error') {
                    attendanceSaveStatusEl.classList.add('is-error');
                }
            }

            async function saveAttendanceAjax() {
                if (!attendanceForm.action) {
                    return;
                }

                if (savingInProgress) {
                    pendingSave = true;
                    return;
                }

                savingInProgress = true;
                setSaveStatus('Guardando cambios...', 'saving');

                try {
                    const payload = buildAttendancePayload();

                    const response = await fetch(attendanceForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        throw new Error('No se pudo guardar la asistencia.');
                    }

                    syncCurrentEventStudents(payload.attendance || {});
                    syncCurrentEventObservations(payload.observations || '');

                    setSaveStatus('Cambios guardados automaticamente', null);
                } catch (error) {
                    setSaveStatus(error.message || 'Error al guardar asistencia', 'error');
                } finally {
                    savingInProgress = false;
                    
                    if (pendingSave) {
                        pendingSave = false;
                        saveAttendanceAjax();
                    }
                }
            }

            function formatCurrency(value) {
                const number = Number(value);
                if (Number.isNaN(number) || value === null || value === undefined) {
                    return 'N/A';
                }
                return new Intl.NumberFormat('es-VE', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                }).format(number);
            }

            function formatDate(value) {
                if (!value) return 'N/A';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return value;
                return new Intl.DateTimeFormat('es-VE', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                }).format(date);
            }

            function getOccupancyMeta(enrolled, capacity) {
                if (capacity === null || capacity === undefined || Number.isNaN(capacity) || capacity <= 0) {
                    return { label: 'Sin límite', pillClass: 'occupancy-none' };
                }
                const ratio = enrolled / capacity;
                if (ratio >= 1) return { label: 'Lleno (100%)', pillClass: 'occupancy-full' };
                if (ratio >= 0.75) return { label: `Alto (${Math.round(ratio * 100)}%)`, pillClass: 'occupancy-high' };
                if (ratio >= 0.3) return { label: `Medio (${Math.round(ratio * 100)}%)`, pillClass: 'occupancy-medium' };
                return { label: `Bajo (${Math.round(ratio * 100)}%)`, pillClass: 'occupancy-none' };
            }

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
                    const coachSpinnerEl = document.getElementById('coachSpinner');
                    const coachStatusTextEl = document.getElementById('coachStatusText');

                    if (coachSpinnerEl) coachSpinnerEl.classList.remove('d-none');
                    if (coachStatusTextEl) coachStatusTextEl.textContent = 'Cargando...';

                    const params = new URLSearchParams({
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                    });

                    fetch(`{{ route('coach.calendar.events') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        if (!response.ok) {
                             throw new Error('No fue posible cargar tus sesiones de clase.');
                        }

                        return response.json();
                    })
                    .then(function(events) {
                        if (coachSpinnerEl) coachSpinnerEl.classList.add('d-none');
                        if (coachStatusTextEl) coachStatusTextEl.textContent = `${events.length} sesión(es) de clase`;
                        successCallback(events);
                    })
                    .catch(function(error) {
                        if (coachSpinnerEl) coachSpinnerEl.classList.add('d-none');
                        if (coachStatusTextEl) coachStatusTextEl.textContent = 'Error';
                        failureCallback(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Calendario no disponible',
                            text: 'No fue posible cargar tu programación de sesiones de clase.'
                        });
                    });
                },
                eventDidMount: function(info) {
                    const props = info.event.extendedProps || {};
                    const enrolledChildren = Number(props.enrolled_children || props.enrolled_count || 0);
                    const capacity = props.course_capacity !== null && props.course_capacity !== undefined && !Number.isNaN(Number(props.course_capacity))
                        ? Number(props.course_capacity)
                        : null;

                    const occupancyText = capacity !== null && capacity > 0
                        ? `${enrolledChildren}/${capacity} inscritos`
                        : `${enrolledChildren} inscritos`;

                    let attendanceText = 'Sin inscritos';
                    const attSummary = props.attendance_summary;
                    if (attSummary && attSummary.total > 0) {
                        const attendedCount = (attSummary.present || 0) + (attSummary.late || 0);
                        attendanceText = `${attendedCount}/${attSummary.total} asistieron`;
                    } else if (enrolledChildren > 0) {
                        attendanceText = `0/${enrolledChildren} asistieron`;
                    }

                    const titleText = `${info.event.title}\nSede: ${props.branch || 'N/A'} (${props.time || 'N/A'})\nOcupación: ${occupancyText}\nAsistencia: ${attendanceText}`;

                    info.el.setAttribute('title', titleText);
                    info.el.querySelectorAll('*').forEach(function(child) {
                        child.setAttribute('title', titleText);
                    });

                    const htmlContent = `
                        <div class="text-start p-1" style="font-size: 0.78rem; line-height: 1.35;">
                            <div class="fw-bold mb-1 border-bottom pb-1" style="font-size: 0.82rem; color: #fff;">${info.event.title}</div>
                            <div><strong>Sede:</strong> ${props.branch || 'N/A'}</div>
                            <div><strong>Horario:</strong> ${props.time || 'N/A'}</div>
                            <div><strong>Ocupación:</strong> ${occupancyText}</div>
                            <div><strong>Asistencia:</strong> ${attendanceText}</div>
                        </div>
                    `;

                    if (window.bootstrap && bootstrap.Tooltip) {
                        try {
                            const oldInst = bootstrap.Tooltip.getInstance(info.el);
                            if (oldInst) oldInst.dispose();
                            new bootstrap.Tooltip(info.el, {
                                title: htmlContent,
                                html: true,
                                placement: 'top',
                                container: 'body',
                                trigger: 'hover'
                            });
                        } catch (e) {}
                    } else if (window.jQuery && $.fn && $.fn.tooltip) {
                        try {
                            $(info.el).tooltip('dispose');
                        } catch (e) {}
                        try {
                            $(info.el).tooltip({
                                title: htmlContent,
                                html: true,
                                placement: 'top',
                                container: 'body',
                                trigger: 'hover'
                            });
                        } catch (e) {}
                    }
                },
                eventClick: function(info) {
                    currentEvent = info.event;
                    const props = info.event.extendedProps || {};
                    const students = Array.isArray(props.students) ? props.students : [];

                    const enrolledChildren = Number(props.enrolled_children || props.enrolled_count || 0);
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

                    attendanceTitleEl.textContent = info.event.title || 'Detalle de clase';
                    const subtitleEl = document.getElementById('attendanceSubtitle');
                    if (subtitleEl) {
                        subtitleEl.textContent = `${eventDate} | ${props.time || 'Horario no disponible'}`;
                    }

                    document.getElementById('detailBranch').textContent = props.branch || 'N/A';
                    document.getElementById('detailCoach').textContent = props.coach || 'N/A';
                    document.getElementById('detailTime').textContent = props.time || 'N/A';
                    document.getElementById('detailEnrolledChildren').textContent = String(enrolledChildren);
                    document.getElementById('detailCapacity').textContent = capacity === null || Number.isNaN(capacity) ? 'N/A' : String(capacity);
                    document.getElementById('detailAvailableSpots').textContent = availableSpots === null ? 'N/A' : String(availableSpots);
                    document.getElementById('detailOccupancy').innerHTML = `<span class="occupancy-pill ${occupancy.pillClass}">${occupancy.label}</span>`;
                    document.getElementById('detailPrice').textContent = formatCurrency(props.course_price);
                    document.getElementById('detailMonthlyFee').textContent = formatCurrency(props.course_monthly_fee);
                    document.getElementById('detailStartDate').textContent = formatDate(props.course_start_date);
                    document.getElementById('detailEndDate').textContent = formatDate(props.course_end_date);
                    document.getElementById('detailDescription').textContent = props.course_description || 'Sin descripción registrada.';

                    const attSummary = props.attendance_summary || { present: 0, absent: 0, late: 0, pending: 0 };
                    document.getElementById('badgePresentCount').innerHTML = `<i class="fas fa-check-circle me-1"></i>Presentes: ${attSummary.present || 0}`;
                    document.getElementById('badgeAbsentCount').innerHTML = `<i class="fas fa-times-circle me-1"></i>Ausentes: ${attSummary.absent || 0}`;
                    document.getElementById('badgeLateCount').innerHTML = `<i class="fas fa-clock me-1"></i>Tarde: ${attSummary.late || 0}`;
                    document.getElementById('badgePendingCount').innerHTML = `<i class="fas fa-hourglass-start me-1"></i>Pendientes: ${attSummary.pending || 0}`;

                    attendanceForm.action = `{{ url('coach/clases') }}/${info.event.id}/attendance`;

                    document.getElementById('generalObservations').value = props.observations || '';

                    attendanceRowsEl.innerHTML = '';
                    toggleAllAttendanceEl.checked = false;
                    setSaveStatus('Guardado automático activo', null);

                    if (!students.length) {
                        attendanceRowsEl.innerHTML = '<div class="attendance-empty">No hay estudiantes inscritos en esta sesión de clase.</div>';
                        attendanceToolsEl.classList.add('d-none');
                    } else {
                        attendanceToolsEl.classList.remove('d-none');

                        const tableContainer = document.createElement('div');
                        tableContainer.className = 'table-responsive attendance-table-container';

                        let tableHtml = `
                            <table class="table table-hover attendance-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th class="text-center">Uso de Imagen</th>
                                        <th class="text-center">Estado de Pago</th>
                                        <th class="text-center">Asistencia</th>
                                        <th class="text-center">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        students.forEach(function(student) {
                            let trialBadge = '';
                            if (student.is_free_trial) {
                                trialBadge = '<span class="badge bg-info text-white ms-1" style="font-size:0.7rem;">Prueba gratis</span>';
                            }

                            let consentBadge = student.image_consent
                                ? '<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fas fa-check-circle me-1"></i>Autorizado</span>'
                                : '<span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1" title="No otorgó uso de imagen"><i class="fas fa-times-circle me-1"></i>No Autorizado</span>';

                            let paymentBadge = '';
                            if (student.is_free_trial) {
                                paymentBadge = '<span class="badge bg-info text-white px-2 py-1">Prueba gratis</span>';
                            } else if (student.payment_status === 'paid') {
                                paymentBadge = '<span class="badge bg-success text-white px-2 py-1">Pagado</span>';
                            } else {
                                paymentBadge = '<span class="badge bg-warning text-dark px-2 py-1">Pendiente</span>';
                            }

                            const isChecked = (student.check_in === 'present' || student.check_in === 'late');
                            const hasNote = student.notes && student.notes.trim().length > 0;

                            tableHtml += `
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">${student.student_name} ${trialBadge}</div>
                                    </td>
                                    <td class="text-center">${consentBadge}</td>
                                    <td class="text-center">${paymentBadge}</td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-flex align-items-center m-0">
                                            <input class="form-check-input attendance-item-checkbox me-2" type="checkbox" 
                                                data-student-id="${student.student_id}" ${isChecked ? 'checked' : ''} style="cursor: pointer;">
                                            <label class="form-check-label text-dark fw-semibold small mb-0" style="cursor: pointer;">Presente</label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <textarea data-note-student-id="${student.student_id}" style="display:none;">${student.notes || ''}</textarea>
                                        <button type="button" class="btn btn-sm ${hasNote ? 'btn-primary' : 'btn-outline-secondary'} open-student-note-btn" 
                                            data-student-id="${student.student_id}" data-student-name="${student.student_name}">
                                            <i class="fas ${hasNote ? 'fa-comment-dots' : 'fa-comment'} me-1"></i>
                                            <span class="note-btn-label">${hasNote ? 'Ver Nota' : 'Observación'}</span>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        tableHtml += `
                                </tbody>
                            </table>
                        `;
                        tableContainer.innerHTML = tableHtml;
                        attendanceRowsEl.appendChild(tableContainer);
                    }

                    syncToggleAllState();

                    attendanceModal.show();
                }
            });
            calendar.render();

            const studentObservationModalEl = document.getElementById('studentObservationModal');
            const studentObservationModal = new bootstrap.Modal(studentObservationModalEl, {
                backdrop: 'static'
            });
            let activeStudentIdForNote = null;

            // Prevent Bootstrap focus trapping from stealing focus away from stacked studentObservationModal
            document.addEventListener('focusin', function(e) {
                if (e.target && e.target.closest('#studentObservationModal')) {
                    e.stopImmediatePropagation();
                }
            }, true);

            if (window.jQuery && $.fn && $.fn.modal && $.fn.modal.Constructor) {
                $.fn.modal.Constructor.prototype._enforceFocus = function() {};
            }

            studentObservationModalEl.addEventListener('show.bs.modal', function () {
                setTimeout(function() {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops[backdrops.length - 1].style.zIndex = '1065';
                    }
                    studentObservationModalEl.style.zIndex = '1070';
                }, 10);
            });

            studentObservationModalEl.addEventListener('shown.bs.modal', function () {
                const noteInput = document.getElementById('studentObservationInput');
                if (noteInput) {
                    noteInput.focus();
                }
            });

            studentObservationModalEl.addEventListener('hidden.bs.modal', function () {
                if (document.getElementById('attendanceModal').classList.contains('show')) {
                    document.body.classList.add('modal-open');
                }
            });

            attendanceRowsEl.addEventListener('click', function(event) {
                const btn = event.target.closest('.open-student-note-btn');
                if (!btn) return;

                activeStudentIdForNote = btn.dataset.studentId;
                const studentName = btn.dataset.studentName;
                document.getElementById('studentObservationModalStudentName').textContent = `Estudiante: ${studentName}`;

                const hiddenTextarea = attendanceRowsEl.querySelector(`textarea[data-note-student-id="${activeStudentIdForNote}"]`);
                document.getElementById('studentObservationInput').value = hiddenTextarea ? hiddenTextarea.value : '';

                studentObservationModal.show();
            });

            document.getElementById('saveStudentObservationBtn').addEventListener('click', function() {
                if (!activeStudentIdForNote) return;

                const noteText = document.getElementById('studentObservationInput').value.trim();
                const hiddenTextarea = attendanceRowsEl.querySelector(`textarea[data-note-student-id="${activeStudentIdForNote}"]`);
                if (hiddenTextarea) {
                    hiddenTextarea.value = noteText;
                }

                if (currentEvent && Array.isArray(currentEvent.extendedProps.students)) {
                    const studentObj = currentEvent.extendedProps.students.find(s => String(s.student_id) === String(activeStudentIdForNote));
                    if (studentObj) {
                        studentObj.notes = noteText;
                    }
                }

                const btn = attendanceRowsEl.querySelector(`.open-student-note-btn[data-student-id="${activeStudentIdForNote}"]`);
                if (btn) {
                    const icon = btn.querySelector('i');
                    const label = btn.querySelector('.note-btn-label');
                    const hasNote = noteText.length > 0;

                    if (hasNote) {
                        btn.className = 'btn btn-sm btn-primary open-student-note-btn';
                        if (icon) icon.className = 'fas fa-comment-dots me-1';
                        if (label) label.textContent = 'Ver Nota';
                    } else {
                        btn.className = 'btn btn-sm btn-outline-secondary open-student-note-btn';
                        if (icon) icon.className = 'fas fa-comment me-1';
                        if (label) label.textContent = 'Observación';
                    }
                }

                studentObservationModal.hide();
                saveAttendanceAjax();
            });

            attendanceForm.addEventListener('submit', function(event) {
                event.preventDefault();
            });

            attendanceRowsEl.addEventListener('change', function(event) {
                const target = event.target;
                if (!target.matches('input[type="checkbox"].attendance-item-checkbox') && !target.matches('textarea[data-note-student-id]')) {
                    return;
                }

                syncToggleAllState();
                saveAttendanceAjax();
            });

            toggleAllAttendanceEl.addEventListener('change', function() {
                const shouldCheck = toggleAllAttendanceEl.checked;
                getAttendanceCheckboxes().forEach(function(checkbox) {
                    checkbox.checked = shouldCheck;
                });

                saveAttendanceAjax();
            });

            document.getElementById('generalObservations').addEventListener('change', function() {
                saveAttendanceAjax();
            });

            document.getElementById('attendanceModal').addEventListener('hidden.bs.modal', function() {
                savingInProgress = false;
                pendingSave = false;
                currentEvent = null;
            });
        });
    </script>
@endsection
