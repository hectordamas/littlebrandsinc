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
                        <h5 class="mb-1">Mi Programación de Clases</h5>
                        <span class="text-muted">Cada clase muestra inscritos, nombres de estudiantes y estado de check in.</span>
                    </div>
                    <span class="badge bg-primary" id="calendarStatus">Cargando...</span>
                </div>
                <div class="card-block">
                    <div id="coachCalendar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="attendanceForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceTitle">Asistencia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3" id="attendanceMeta"></p>
                        <div class="attendance-tools" id="attendanceTools">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="toggleAllAttendance">
                                <label class="form-check-label" for="toggleAllAttendance">Marcar todos presentes</label>
                            </div>
                            <span id="attendanceSaveStatus" class="attendance-save-status">Guardado automático activo</span>
                        </div>
                        <div id="attendanceRows"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
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
                getAttendanceCheckboxes().forEach(function(checkbox) {
                    const studentId = checkbox.dataset.studentId;
                    if (!studentId) {
                        return;
                    }

                    attendance[studentId] = checkbox.checked ? 'present' : 'absent';
                });

                return {
                    attendance: attendance,
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
                    });

                    fetch(`{{ route('coach.calendar.events') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('No fue posible cargar tus clases.');
                        }

                        return response.json();
                    })
                    .then(function(events) {
                        statusEl.textContent = `${events.length} clase(s)`;
                        successCallback(events);
                    })
                    .catch(function(error) {
                        statusEl.textContent = 'Error';
                        failureCallback(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Calendario no disponible',
                            text: 'No fue posible cargar tu programación de clases.'
                        });
                    });
                },
                eventClick: function(info) {
                    currentEvent = info.event;
                    const props = info.event.extendedProps || {};
                    const students = Array.isArray(props.students) ? props.students : [];

                    attendanceTitleEl.textContent = info.event.title;
                    attendanceMetaEl.textContent = `Sede: ${props.branch || 'N/A'} | Horario: ${props.time || 'N/A'} | Inscritos: ${props.enrolled_count || 0}`;
                    attendanceForm.action = `{{ url('coach/clases') }}/${info.event.id}/attendance`;

                    attendanceRowsEl.innerHTML = '';
                    toggleAllAttendanceEl.checked = false;
                    setSaveStatus('Guardado automático activo', null);

                    if (!students.length) {
                        attendanceRowsEl.innerHTML = '<div class="attendance-empty">No hay estudiantes inscritos en esta clase.</div>';
                        attendanceToolsEl.classList.add('d-none');
                    } else {
                        attendanceToolsEl.classList.remove('d-none');
                    }

                    students.forEach(function(student) {
                        const row = document.createElement('div');
                        row.className = 'attendance-row';

                        const name = document.createElement('div');
                        name.className = 'attendance-student';
                        name.textContent = student.student_name;

                        const checkLabel = document.createElement('label');
                        checkLabel.className = 'attendance-check';

                        const check = document.createElement('input');
                        check.type = 'checkbox';
                        check.className = 'attendance-item-checkbox';
                        check.dataset.studentId = String(student.student_id);
                        check.checked = (student.check_in === 'present' || student.check_in === 'late');

                        const checkText = document.createElement('span');
                        checkText.textContent = 'Presente';

                        checkLabel.appendChild(check);
                        checkLabel.appendChild(checkText);

                        row.appendChild(name);
                        row.appendChild(checkLabel);
                        attendanceRowsEl.appendChild(row);
                    });

                    syncToggleAllState();

                    attendanceModal.show();
                }
            });
            calendar.render();

            attendanceForm.addEventListener('submit', function(event) {
                event.preventDefault();
            });

            attendanceRowsEl.addEventListener('change', function(event) {
                const target = event.target;
                if (!target.matches('input[type="checkbox"].attendance-item-checkbox')) {
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

            document.getElementById('attendanceModal').addEventListener('hidden.bs.modal', function() {
                savingInProgress = false;
                pendingSave = false;
                currentEvent = null;
            });
        });
    </script>
@endsection
