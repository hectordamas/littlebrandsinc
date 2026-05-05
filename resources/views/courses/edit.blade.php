@extends('layouts.admin')
@section('styles')
    <style>
        .class-card {
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #f8fafc, #e9f2ff);
            transition: all 0.25s ease;
        }

        .class-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }
    </style>
@endsection

@section('title')
    <title>{{ config('app.name') }} - Editar Curso</title>
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar curso</h5>
                <span class="text-muted">Actualiza los siguientes campos para modificar el curso</span>
            </div>
            <div class="card-block">
                <form action="{{ route('courses.update', $course) }}" method="POST" class="row">
                    @csrf
                    @method('PUT')
                    <div class="mb-3 col-md-9">
                        <label for="title" class="form-label">Título del curso</label>
                        <input type="text" name="title" id="title" class="form-control"
                            value="{{ old('title', $course->title) }}" required>
                    </div>
                    <div class="col-md-3"></div>

                        <!-- Barra de ocupación -->
                        <div class="mb-3 col-md-12">
                            <label class="form-label">Ocupación actual</label>
                            <div id="occupancy-bar" class="progress" style="height: 28px;">
                                <div id="occupancy-bar-inner" class="progress-bar bg-success" role="progressbar" style="width: 0%">Cargando...</div>
                            </div>
                            <div class="small text-muted mt-1" id="occupancy-info"></div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                fetch("{{ route('courses.occupancy', $course->id) }}")
                                    .then(r => r.json())
                                    .then(data => {
                                        let percent = data.percent;
                                        let enrolled = data.enrolled;
                                        let capacity = data.capacity;
                                        let bar = document.getElementById('occupancy-bar-inner');
                                        let info = document.getElementById('occupancy-info');
                                        bar.style.width = percent + '%';
                                        bar.textContent = percent + '% (' + enrolled + '/' + capacity + ' inscritos)';
                                        if (percent < 60) bar.classList.add('bg-success');
                                        else if (percent < 90) bar.classList.add('bg-warning');
                                        else bar.classList.add('bg-danger');
                                        info.textContent = 'Inscritos: ' + enrolled + ' / Capacidad: ' + capacity;
                                    });
                            });
                        </script>
                    <div class="mb-3 col-md-9">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $course->description) }}</textarea>
                    </div>
                    <div class="col-md-3"></div>
                    <div class="mb-3 col-md-3">
                        <label for="min_age" class="form-label">Edad Mínima</label>
                        <input type="number" name="min_age" id="min_age" class="form-control"
                            value="{{ old('min_age', $course->min_age) }}">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="max_age" class="form-label">Edad Máxima</label>
                        <input type="number" name="max_age" id="max_age" class="form-control"
                            value="{{ old('max_age', $course->max_age) }}">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="capacity" class="form-label">Capacidad</label>
                        <input type="number" name="capacity" id="capacity" class="form-control"
                            value="{{ old('capacity', $course->capacity) }}">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="price" class="form-label">Precio de inscripción</label>
                        <input type="number" name="price" id="price" class="form-control" step="0.01"
                            value="{{ old('price', $course->price) }}">
                        <span id="price-preview"
                            class="fw-bold text-success">${{ number_format(old('price', $course->price ?? 0), 2) }}</span>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="monthly_fee" class="form-label">Mensualidad</label>
                        <input type="number" name="monthly_fee" id="monthly_fee" class="form-control" step="0.01"
                            value="{{ old('monthly_fee', $course->monthly_fee) }}">
                        <span id="monthly-fee-preview"
                            class="fw-bold text-primary">${{ number_format(old('monthly_fee', $course->monthly_fee ?? 0), 2) }}</span>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="branch_id" class="form-label">Sede</label>
                        <select name="branch_id" id="branch_id" class="form-control" required>
                            <option value="">Selecciona una sede</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ old('branch_id', $course->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="start_date" class="form-label">Fecha de Inicio</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="{{ old('start_date', $course->start_date) }}" required>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="end_date" class="form-label">Fecha de Fin</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="{{ old('end_date', $course->end_date) }}" required>
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="active" class="form-label">Activo</label>
                        <select name="active" id="active" class="form-control" required>
                            <option value="1" {{ old('active', $course->active) == '1' ? 'selected' : '' }}>Sí
                            </option>
                            <option value="0" {{ old('active', $course->active) == '0' ? 'selected' : '' }}>No
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="coach_id" class="form-label">Coach</label>
                        <select name="coach_id" id="coach_id" class="form-control" required>
                            <option value="">Sin asignar</option>
                            @foreach ($coaches as $coach)
                                <option value="{{ $coach->id }}"
                                    {{ old('coach_id', $course->coach_id) == $coach->id ? 'selected' : '' }}>
                                    {{ $coach->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>

                <div class="row">
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Clases</h5>
                        <button class="btn btn-inverse" data-bs-toggle="modal" data-bs-target="#createClassModal">
                            <i class="fas fa-plus"></i> Agregar clase
                        </button>
                    </div>
                    @if ($course->classes->isEmpty())
                        <p class="text-muted">Este curso no tiene clases todavía.</p>
                    @endif
                    <div class="row">
                        @foreach ($course->classes as $class)
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm h-100 class-card">
                                    <div class="card-block">
                                        <h5 class="card-title mb-2">
                                            {{ \Carbon\Carbon::parse($class->date)->format('d/m/Y') }}
                                        </h5>
                                        <p class="text-muted mb-2">
                                            ⏰ {{ \Carbon\Carbon::parse($class->start_time)->format('H:i a') }} -
                                            {{ \Carbon\Carbon::parse($class->end_time)->format('H:i a') }}
                                        </p>
                                        <div class="border-0 d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#editClassModal{{ $class->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('courses.classes.destroy', [$class]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Seguro que deseas eliminar esta clase?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="editClassModal{{ $class->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('courses.classes.update', [$class]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar clase</h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label>Fecha</label>
                                                    <input type="date" name="date" class="form-control"
                                                        value="{{ $class->date }}">
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label>Inicio</label>
                                                        <input type="time" name="start_time" class="form-control"
                                                            value="{{ $class->start_time }}">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label>Fin</label>
                                                        <input type="time" name="end_time" class="form-control"
                                                            value="{{ $class->end_time }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                                <button class="btn btn-primary">Guardar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Modal Agregar clase -->
                    <div class="modal fade" id="createClassModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <form action="{{ route('courses.classes.store', $course) }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="branch_id" value="{{ $course->branch_id }}">
                                    <input type="hidden" name="coach_id" value="{{ $course->coach_id }}">
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                                    <!-- HEADER -->
                                    <div class="modal-header">
                                        <h5 class="modal-title">Agregar nueva clase</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <!-- BODY -->
                                    <div class="modal-body">

                                        <!-- Fecha -->
                                        <div class="mb-3">
                                            <label class="form-label">Fecha</label>
                                            <input type="date" name="date" class="form-control" required>
                                        </div>

                                        <!-- Horas -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Hora inicio</label>
                                                <input type="time" name="start_time" class="form-control" required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Hora fin</label>
                                                <input type="time" name="end_time" class="form-control" required>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- FOOTER -->
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            Crear clase
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
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
