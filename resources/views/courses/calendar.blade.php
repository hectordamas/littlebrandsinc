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
        }

        .fc .fc-toolbar-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .fc .fc-button {
            text-transform: capitalize;
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('classesCalendar');
            const branchFilterEl = document.getElementById('calendarBranchFilter');
            const statusEl = document.getElementById('calendarStatus');

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
                    Swal.fire({
                        title: info.event.title,
                        html: `
                            <div style="text-align:left;line-height:1.6;">
                                <strong>Sede:</strong> ${props.branch || 'N/A'}<br>
                                <strong>Horario:</strong> ${props.time || 'N/A'}<br>
                                <strong>Coach:</strong> ${props.coach || 'N/A'}
                            </div>
                        `,
                        confirmButtonText: 'Cerrar'
                    });
                }
            });

            calendar.render();

            branchFilterEl.addEventListener('change', function() {
                calendar.refetchEvents();
            });
        });
    </script>
@endsection
