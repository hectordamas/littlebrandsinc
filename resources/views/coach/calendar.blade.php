@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Calendario del Entrenador</title>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <style>
        #coachCalendar {
            min-height: 680px;
        }

        .attendance-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 160px;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            align-items: center;
        }

        @media (max-width: 768px) {
            .attendance-row {
                grid-template-columns: 1fr;
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
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3" id="attendanceMeta"></p>
                        <div id="attendanceRows"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar asistencia</button>
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
                    const props = info.event.extendedProps || {};
                    const students = Array.isArray(props.students) ? props.students : [];

                    attendanceTitleEl.textContent = info.event.title;
                    attendanceMetaEl.textContent = `Sede: ${props.branch || 'N/A'} | Horario: ${props.time || 'N/A'} | Inscritos: ${props.enrolled_count || 0}`;
                    attendanceForm.action = `{{ url('coach/clases') }}/${info.event.id}/attendance`;

                    attendanceRowsEl.innerHTML = '';

                    if (!students.length) {
                        attendanceRowsEl.innerHTML = '<div class="alert alert-light border">No hay estudiantes inscritos en esta clase.</div>';
                    }

                    students.forEach(function(student) {
                        const row = document.createElement('div');
                        row.className = 'attendance-row';

                        const name = document.createElement('div');
                        name.textContent = student.student_name;

                        const select = document.createElement('select');
                        select.className = 'form-control';
                        select.name = `attendance[${student.student_id}]`;

                        const options = [{
                                value: 'pending',
                                text: 'Pendiente'
                            },
                            {
                                value: 'present',
                                text: 'Presente'
                            },
                            {
                                value: 'late',
                                text: 'Tarde'
                            },
                            {
                                value: 'absent',
                                text: 'Ausente'
                            },
                        ];

                        options.forEach(function(optionData) {
                            const option = document.createElement('option');
                            option.value = optionData.value;
                            option.textContent = optionData.text;
                            if ((student.check_in || 'pending') === optionData.value) {
                                option.selected = true;
                            }
                            select.appendChild(option);
                        });

                        row.appendChild(name);
                        row.appendChild(select);
                        attendanceRowsEl.appendChild(row);
                    });

                    $('#attendanceModal').modal('show');
                }
            });

            calendar.render();

            attendanceForm.addEventListener('submit', function() {
                const submitButton = attendanceForm.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Guardando...';
                }
            });
        });
    </script>
@endsection
