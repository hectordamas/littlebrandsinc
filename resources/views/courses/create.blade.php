@extends('layouts.admin')
@section('title')
    <title>{{ config('app.name') }} - Crear Curso</title>
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Agregar Nuevo Curso</h5>
                <span class="text-muted">Llena los siguientes campos para registrar un nuevo curso</span>
            </div>
            <div class="card-block">
                <form action="{{ route('courses.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="program_id" class="form-label">Programa</label>
                            <select name="program_id" id="program_id" class="form-control" required>
                                <option value="">Selecciona un programa</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}" {{ (int) old('program_id') === (int) $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="title" class="form-label">Título del curso</label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="{{ old('title') }}" required>
                        </div>


                        <div class="mb-3 col-md-12">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea name="description" id="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>


                        <div class="mb-3 col-md-3">
                            <label for="min_age" class="form-label">Edad Mínima</label>
                            <input type="number" name="min_age" id="min_age" class="form-control"
                                value="{{ old('min_age') }}">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="max_age" class="form-label">Edad Máxima</label>
                            <input type="number" name="max_age" id="max_age" class="form-control"
                                value="{{ old('max_age') }}">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="capacity" class="form-label">Capacidad</label>
                            <input type="number" name="capacity" id="capacity" class="form-control"
                                value="{{ old('capacity') }}">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="price" class="form-label">Precio de inscripción</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01"
                                value="{{ old('price') }}">
                            <span id="price-preview" class="fw-bold text-success">$0.00</span>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="monthly_fee" class="form-label">Mensualidad</label>
                            <input type="number" name="monthly_fee" id="monthly_fee" class="form-control" step="0.01"
                                value="{{ old('monthly_fee') }}">
                            <span id="monthly-fee-preview" class="fw-bold text-primary">$0.00</span>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="branch_id" class="form-label">Sede</label>
                            <select name="branch_id" id="branch_id" class="form-control" required>
                                <option value="">Selecciona una sede</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="start_date" class="form-label">Fecha de Inicio</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ old('start_date') }}" required>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="end_date" class="form-label">Fecha de Fin</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ old('end_date') }}" required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="active" class="form-label">Activo</label>
                            <select name="active" id="active" class="form-control" required>
                                <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="coach_ids" class="form-label">Entrenadores del curso</label>
                            <select name="coach_ids[]" id="coach_ids" class="form-control select2" multiple required>
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id }}" {{ in_array((string) $coach->id, (array) old('coach_ids', []), true) ? 'selected' : '' }}>
                                        {{ $coach->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Puedes seleccionar varios entrenadores.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Modo de programación</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="schedule_mode" id="mode_manual" value="manual" checked>
                                    <label class="form-check-label" for="mode_manual">Manual (actual)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="schedule_mode" id="mode_recurrence" value="recurrence">
                                    <label class="form-check-label" for="mode_recurrence">Por recurrencia (automático)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="recurrence-config" class="border rounded p-3 mb-3 d-none">
                        <div class="alert alert-light border small mb-3">
                            Se usarán automáticamente las fechas del curso (inicio/fin) para generar las clases.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Incluir</th>
                                        <th>Hora inicio</th>
                                        <th>Hora fin</th>
                                        <th>Entrenador</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Lunes</td>
                                        <td><input class="form-check-input recurrence-day-toggle" type="checkbox" data-day="1"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-start" data-day="1"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-end" data-day="1"></td>
                                        <td>
                                            <select class="form-control form-control-sm recurrence-day-coach" data-day="1">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Martes</td>
                                        <td><input class="form-check-input recurrence-day-toggle" type="checkbox" data-day="2"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-start" data-day="2"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-end" data-day="2"></td>
                                        <td>
                                            <select class="form-control form-control-sm recurrence-day-coach" data-day="2">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Miércoles</td>
                                        <td><input class="form-check-input recurrence-day-toggle" type="checkbox" data-day="3"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-start" data-day="3"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-end" data-day="3"></td>
                                        <td>
                                            <select class="form-control form-control-sm recurrence-day-coach" data-day="3">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jueves</td>
                                        <td><input class="form-check-input recurrence-day-toggle" type="checkbox" data-day="4"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-start" data-day="4"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-end" data-day="4"></td>
                                        <td>
                                            <select class="form-control form-control-sm recurrence-day-coach" data-day="4">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Viernes</td>
                                        <td><input class="form-check-input recurrence-day-toggle" type="checkbox" data-day="5"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-start" data-day="5"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-end" data-day="5"></td>
                                        <td>
                                            <select class="form-control form-control-sm recurrence-day-coach" data-day="5">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Sábado</td>
                                        <td><input class="form-check-input recurrence-day-toggle" type="checkbox" data-day="6"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-start" data-day="6"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-end" data-day="6"></td>
                                        <td>
                                            <select class="form-control form-control-sm recurrence-day-coach" data-day="6">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Domingo</td>
                                        <td><input class="form-check-input recurrence-day-toggle" type="checkbox" data-day="0"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-start" data-day="0"></td>
                                        <td><input type="time" class="form-control form-control-sm recurrence-day-end" data-day="0"></td>
                                        <td>
                                            <select class="form-control form-control-sm recurrence-day-coach" data-day="0">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button type="button" id="preview-recurrence" class="btn btn-outline-primary btn-sm">Previsualizar clases</button>
                        </div>
                        <div id="recurrence-preview" class="table-responsive mt-3 d-none">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Incluir</th>
                                        <th>Fecha</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Entrenador</th>
                                        <th>Día</th>
                                    </tr>
                                </thead>
                                <tbody id="recurrence-preview-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <h5 class="my-3">Clases (manual)</h5>
                    </div>

                    <div id="manual-sessions-wrapper">

                    <div id="sessions-wrapper">

                        <template id="session-template">
                            <div class="session-item border rounded p-3 mb-3 position-relative">

                                <button type="button"
                                    class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-session">
                                    ✕
                                </button>

                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label>Fecha</label>
                                        <input type="date" name="sessions[__INDEX__][date]" class="form-control"
                                            required>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Inicio</label>
                                        <input type="time" name="sessions[__INDEX__][start_time]" class="form-control"
                                            required>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Fin</label>
                                        <input type="time" name="sessions[__INDEX__][end_time]" class="form-control"
                                            required>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Entrenador</label>
                                        <select name="sessions[__INDEX__][coach_id]" class="form-control">
                                            __COACH_OPTIONS__
                                        </select>
                                    </div>



                                </div>

                            </div>
                        </template>

                    </div>

                    <button type="button" id="add-session" class="btn btn-inverse btn-sm mb-4">
                        + Agregar clase
                    </button>

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Guardar curso</button>

                            <a href="{{ route('courses.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let index = 0;
            const wrapper = document.getElementById('sessions-wrapper');
            const coachOptions = `
                <option value="">Sin asignar</option>
                @foreach ($coaches as $coach)
                    <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                @endforeach
            `;
            const template = document.getElementById('session-template').innerHTML;
            const modeManual = document.getElementById('mode_manual');
            const modeRecurrence = document.getElementById('mode_recurrence');
            const recurrenceConfig = document.getElementById('recurrence-config');
            const manualWrapper = document.getElementById('manual-sessions-wrapper');

            function addSession() {
                let html = template.replace(/__INDEX__/g, index).replace(/__COACH_OPTIONS__/g, coachOptions);
                wrapper.insertAdjacentHTML('beforeend', html);
                index++;
            }

            // primera clase automática
            addSession();

            // agregar
            document.getElementById('add-session').addEventListener('click', addSession);

            // eliminar (delegado)
            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-session')) {
                    e.target.closest('.session-item').remove();
                }
            });

            function toggleMode() {
                const isRecurrence = modeRecurrence.checked;
                recurrenceConfig.classList.toggle('d-none', !isRecurrence);
                manualWrapper.classList.toggle('d-none', isRecurrence);

                wrapper.querySelectorAll('input, select').forEach((el) => {
                    if (isRecurrence) {
                        el.setAttribute('disabled', 'disabled');
                    } else {
                        el.removeAttribute('disabled');
                    }
                });
            }

            modeManual.addEventListener('change', toggleMode);
            modeRecurrence.addEventListener('change', toggleMode);
            toggleMode();

            const dayMap = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];

            document.getElementById('preview-recurrence').addEventListener('click', function() {
                const start = document.getElementById('start_date').value;
                const end = document.getElementById('end_date').value;

                if (!start || !end) {
                    Swal.fire({ icon: 'warning', text: 'Primero define fecha de inicio y fin del curso.' });
                    return;
                }

                const dayConfig = {};
                document.querySelectorAll('.recurrence-day-toggle').forEach((toggle) => {
                    const day = Number(toggle.dataset.day);
                    if (!toggle.checked) {
                        return;
                    }

                    const startTimeInput = document.querySelector(`.recurrence-day-start[data-day="${day}"]`);
                    const endTimeInput = document.querySelector(`.recurrence-day-end[data-day="${day}"]`);
                    const coachInput = document.querySelector(`.recurrence-day-coach[data-day="${day}"]`);
                    if (!startTimeInput?.value || !endTimeInput?.value) {
                        return;
                    }

                    dayConfig[day] = {
                        start_time: startTimeInput.value,
                        end_time: endTimeInput.value,
                        coach_id: coachInput?.value || ''
                    };
                });

                if (!Object.keys(dayConfig).length) {
                    Swal.fire({ icon: 'warning', text: 'Marca al menos un día con su hora de inicio y fin.' });
                    return;
                }

                const startDate = new Date(start + 'T00:00:00');
                const endDate = new Date(end + 'T00:00:00');
                const body = document.getElementById('recurrence-preview-body');
                body.innerHTML = '';

                let rowIndex = 0;
                for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                    const day = d.getDay();
                    if (!dayConfig[day]) {
                        continue;
                    }

                    const cfg = dayConfig[day];
                    const iso = d.toISOString().slice(0, 10);
                    body.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input recurrence-row-enabled" checked>
                            </td>
                            <td>
                                <input type="date" class="form-control form-control-sm recurrence-date" name="recurrence_sessions[${rowIndex}][date]" value="${iso}">
                            </td>
                            <td>
                                <input type="time" class="form-control form-control-sm recurrence-start" name="recurrence_sessions[${rowIndex}][start_time]" value="${cfg.start_time}">
                            </td>
                            <td>
                                <input type="time" class="form-control form-control-sm recurrence-end" name="recurrence_sessions[${rowIndex}][end_time]" value="${cfg.end_time}">
                            </td>
                            <td>
                                <select class="form-control form-control-sm recurrence-coach" name="recurrence_sessions[${rowIndex}][coach_id]">
                                    ${coachOptions}
                                </select>
                            </td>
                            <td>${dayMap[day]}</td>
                        </tr>
                    `);

                    const lastRowCoach = body.querySelector(`tr:last-child .recurrence-coach`);
                    if (lastRowCoach && cfg.coach_id) {
                        lastRowCoach.value = cfg.coach_id;
                    }

                    rowIndex++;
                }

                document.getElementById('recurrence-preview').classList.toggle('d-none', rowIndex === 0);
                if (rowIndex === 0) {
                    Swal.fire({ icon: 'info', text: 'No hay clases en el rango para los días seleccionados.' });
                }
            });

            document.getElementById('recurrence-preview-body').addEventListener('change', function(e) {
                if (!e.target.classList.contains('recurrence-row-enabled')) {
                    return;
                }

                const row = e.target.closest('tr');
                const enabled = e.target.checked;
                row.querySelectorAll('input.recurrence-date,input.recurrence-start,input.recurrence-end,select.recurrence-coach').forEach((el) => {
                    if (enabled) {
                        el.removeAttribute('disabled');
                    } else {
                        el.setAttribute('disabled', 'disabled');
                    }
                });
            });

        });


        $(document).ready(function() {
            if ($.fn.select2) {
                $('#coach_ids').select2({
                    placeholder: 'Selecciona uno o más entrenadores',
                    width: '100%'
                });
            }

            $('#price').on('input', function() {
                let value = parseFloat($(this).val());
                if (isNaN(value)) value = 0;
                $('#price-preview').text('$' + value.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }).trigger('input');

            $('#monthly_fee').on('input', function() {
                let value = parseFloat($(this).val());
                if (isNaN(value)) value = 0;
                $('#monthly-fee-preview').text('$' + value.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }).trigger('input');
        });
    </script>
@endsection
