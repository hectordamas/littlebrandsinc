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

        #occupancy-bar {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s;
        }

        #occupancy-bar:hover {
            transform: scale(1.005);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            opacity: 0.95;
        }
    </style>
@endsection

@section('title')
    <title>{{ config('app.name') }} - Editar Clase</title>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Clase</h5>
                <span class="text-muted">Actualiza los siguientes campos para modificar la Clase</span>
            </div>
            <div class="card-block">
                <form action="{{ route('courses.update', $course) }}" method="POST" class="row">
                    @csrf
                    @method('PUT')
                    <div class="mb-3 col-md-6">
                        <label for="program_id" class="form-label">Programa</label>
                        <select name="program_id" id="program_id" class="form-control" required>
                            <option value="">Selecciona un programa</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}" {{ (int) old('program_id', $course->program_id) === (int) $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="title" class="form-label">Título de la Clase</label>
                        <input type="text" name="title" id="title" class="form-control"
                            value="{{ old('title', $course->title) }}" required>
                    </div>

                        <!-- Barra de ocupación -->
                        <div class="mb-3 col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Ocupación actual</label>
                                <div class="d-flex gap-3">
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-success" data-bs-toggle="modal" data-bs-target="#inscribirEstudianteModal">
                                        <i class="fas fa-user-plus"></i> Inscribir Estudiante
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#enrolledStudentsModal">
                                        <i class="fas fa-users"></i> Ver estudiantes inscritos
                                    </button>
                                </div>
                            </div>
                            <div id="occupancy-bar" class="progress" style="height: 28px;" data-bs-toggle="modal" data-bs-target="#enrolledStudentsModal" title="Click para ver la lista de estudiantes inscritos">
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

                    <div class="mb-3 col-md-12">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $course->description) }}</textarea>
                    </div>

                    <div class="mb-3 col-md-3">
                        <label for="min_age" class="form-label">Edad Mínima</label>
                        <input type="number" name="min_age" id="min_age" class="form-control" step="0.1"
                            value="{{ old('min_age', $course->min_age) }}">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="max_age" class="form-label">Edad Máxima</label>
                        <input type="number" name="max_age" id="max_age" class="form-control" step="0.1"
                            value="{{ old('max_age', $course->max_age) }}">
                    </div>
                    <div class="mb-3 col-md-3">
                        <label for="capacity" class="form-label">Capacidad</label>
                        <input type="number" name="capacity" id="capacity" class="form-control"
                            value="{{ old('capacity', $course->capacity) }}">
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
                    <div class="mb-3 col-md-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="auto_extend_classes" id="auto_extend_classes" value="1" checked>
                            <label class="form-check-label fw-bold small text-secondary" for="auto_extend_classes">
                                Generar clases en rango extendido (basado en clases actuales)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="coach_ids" class="form-label">Entrenadores de la Clase</label>
                        <select name="coach_ids[]" id="coach_ids" class="form-control select2" multiple required>
                            @foreach ($coaches as $coach)
                                @php
                                    $selectedCoachIdsInput = old('coach_ids', $selectedCoachIds ?? []);
                                @endphp
                                <option value="{{ $coach->id }}" {{ in_array((int) $coach->id, array_map('intval', (array) $selectedCoachIdsInput), true) ? 'selected' : '' }}>
                                    {{ $coach->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Puedes seleccionar varios entrenadores.</small>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>

                <div class="row">
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Sesiones de Clase</h5>
                        <button class="btn btn-inverse" data-bs-toggle="modal" data-bs-target="#createClassModal">
                            <i class="fas fa-plus"></i> Agregar sesión de clase
                        </button>
                    </div>
                    @if ($course->classes->isEmpty())
                        <p class="text-muted">Esta Clase no tiene sesiones de clase todavía.</p>
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
                                                    onclick="return confirm('¿Seguro que deseas eliminar esta sesión de clase?')">
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
                                                <h5 class="modal-title">Editar sesión de clase</h5>
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
                                                    <div class="col-md-12 mb-3">
                                                        <label>Entrenador</label>
                                                        <select name="coach_id" class="form-control">
                                                            <option value="">Sin asignar</option>
                                                            @foreach ($coaches as $coach)
                                                                @if (in_array((int) $coach->id, array_map('intval', (array) ($selectedCoachIds ?? [])), true))
                                                                    <option value="{{ $coach->id }}" {{ (int) $class->coach_id === (int) $coach->id ? 'selected' : '' }}>{{ $coach->name }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
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
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                                    <!-- HEADER -->
                                    <div class="modal-header">
                                        <h5 class="modal-title">Agregar nueva sesión de clase</h5>
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

                                        <div class="mb-3">
                                            <label class="form-label">Entrenador</label>
                                            <select name="coach_id" class="form-control">
                                                <option value="">Sin asignar</option>
                                                @foreach ($coaches as $coach)
                                                    @if (in_array((int) $coach->id, array_map('intval', (array) ($selectedCoachIds ?? [])), true))
                                                        <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- FOOTER -->
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            Crear sesión de clase
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                    <!-- Modal Registrar Inscripción en este Curso -->
                    <div class="modal fade" id="inscribirEstudianteModal" tabindex="-1" aria-labelledby="inscribirEstudianteModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <form id="inscribirEstudianteForm" class="modal-content" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="program_id" value="{{ $course->program_id }}">
                                    <input type="hidden" name="course_ids[]" value="{{ $course->id }}">
                                    <input type="hidden" name="enrollment_fee_type" value="standard">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="inscribirEstudianteModalLabel">Inscribir Estudiante en {{ $course->title }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body text-start">
                                        <div id="enrollmentErrorAlert" class="alert alert-danger d-none mb-3"></div>
                                        
                                        <div class="row g-3">
                                            <!-- Representante -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Representante</label>
                                                <select name="user_id" id="modalUserSelect" class="form-control select2-modal" style="width: 100%">
                                                    <option value="">-- Seleccionar representante --</option>
                                                    @foreach ($parents as $user)
                                                        <option value="{{ $user->id }}">
                                                            {{ $user->name }} - {{ $user->email }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="toggleModalUserForm()">
                                                    + Crear nuevo representante
                                                </button>
                                            </div>
                                            <div class="col-md-6"></div>

                                            <!-- Formulario Nuevo Representante -->
                                            <div id="modalUserForm" class="col-12 d-none bg-light p-3 rounded border">
                                                <h6 class="fw-bold mb-2">Nuevo Representante</h6>
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-1">Nombre</label>
                                                        <input type="text" name="user[name]" id="modal_user_name" class="form-control form-control-sm" placeholder="Ej: Maria Perez">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-1">Correo</label>
                                                        <input type="email" name="user[email]" id="modal_user_email" class="form-control form-control-sm" placeholder="Ej: maria@email.com">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-1">WhatsApp</label>
                                                        <div class="input-group input-group-sm">
                                                            <select name="user[dial_code]" class="form-select" style="max-width: 100px;">
                                                                @include('partials.dialcode_create')
                                                            </select>
                                                            <input type="text" name="user[whatsapp]" id="modal_user_whatsapp" class="form-control" placeholder="Ej: 4121234567">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-1">Contraseña temporal</label>
                                                        <input type="password" name="user[password]" id="modal_user_password" class="form-control form-control-sm" placeholder="Ej: Temporal2026">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Estudiante -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Estudiante</label>
                                                <select name="student_id" id="modalStudentSelect" class="form-control select2-modal" style="width: 100%">
                                                    <option value="">-- Seleccionar estudiante --</option>
                                                    @foreach ($students as $student)
                                                        <option value="{{ $student->id }}" data-user="{{ $student->user_id }}">
                                                            {{ $student->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="toggleModalStudentForm()">
                                                    + Crear nuevo estudiante
                                                </button>
                                            </div>
                                            <div class="col-md-6"></div>

                                            <!-- Formulario Nuevo Estudiante -->
                                            <div id="modalStudentForm" class="col-12 d-none bg-light p-3 rounded border">
                                                <h6 class="fw-bold mb-2">Nuevo Estudiante</h6>
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-1">Nombre</label>
                                                        <input type="text" name="student[name]" id="modal_student_name" class="form-control form-control-sm" placeholder="Ej: Sofia Martinez">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small mb-1">Fecha de nacimiento</label>
                                                        <input type="date" name="student[birthdate]" id="modal_student_birthdate" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small mb-1">Notas médicas (opcional)</label>
                                                        <input type="text" name="student[medical_notes]" id="modal_student_medical_notes" class="form-control form-control-sm" placeholder="Ej: Alergia al maní">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Opciones adicionales -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Estado de pago</label>
                                                <select name="payment_status" id="modalPaymentStatusSelect" class="form-control">
                                                    <option value="pending">Pendiente</option>
                                                    <option value="paid">Pagado</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 d-none" id="modalAccountSelectContainer">
                                                <label class="form-label fw-bold">Cuenta de Pago <span class="text-danger">*</span></label>
                                                <select name="account_id" id="modalAccountSelect" class="form-control">
                                                    @foreach ($accounts ?? [] as $account)
                                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 d-none" id="modalReferenceContainer">
                                                <label class="form-label fw-bold">Referencia / Observación</label>
                                                <input type="text" name="reference" id="modalReferenceInput" class="form-control" placeholder="Ej. Transacción 1234">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Comprobante de pago (opcional)</label>
                                                <input type="file" name="payment_receipt" id="modalPaymentReceiptInput" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                            </div>

                                            <!-- Costo de Inscripción / Opciones de pago -->
                                            <div class="col-12 mt-3 text-start">
                                                
                                                <label class="form-label fw-bold">Costo de Inscripción</label>
                                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                                    <div class="form-check">
                                                        <input class="form-check-input modal-fee-option" type="radio" name="enrollment_fee_type" id="modalFeeStandard" value="standard" checked>
                                                        <label class="form-check-label" for="modalFeeStandard">
                                                            Monto sugerido (<span id="modalStandardFeeLabel">${{ number_format(($course->program->enrollment_fee ?? 50.00) + ($course->monthly_fee ?? 0.00), 2) }}</span>)
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input modal-fee-option" type="radio" name="enrollment_fee_type" id="modalFeeCustom" value="custom">
                                                        <label class="form-check-label" for="modalFeeCustom">
                                                            Monto personalizado
                                                        </label>
                                                    </div>
                                                    <div class="d-none" id="modalCustomAmountContainer" style="width: 180px;">
                                                        <label class="form-label small fw-bold mb-1">Monto total personalizado ($)</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" name="custom_total_amount" id="modalCustomAmount" class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Breakdown -->
                                                <div class="mt-3 p-3 bg-light rounded border">
                                                    <div class="d-flex justify-content-between mb-1"><span>Inscripción:</span> <span id="modalEnrollmentFeeDisplay">$0.00</span></div>
                                                    <div class="d-flex justify-content-between mb-1"><span>1er Mensualidad:</span> <span id="modalMonthlyFeesDisplay">$0.00</span></div>
                                                    <hr class="my-1">
                                                    <div class="d-flex justify-content-between fw-bold"><span>Total a pagar:</span> <span id="modalTotalAmountDisplay">$0.00</span></div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mt-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_free_trial" value="1" id="modalIsFreeTrial">
                                                    <label class="form-check-label fw-bold" for="modalIsFreeTrial">
                                                        Clase de prueba gratuita
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mt-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="image_consent_accepted" value="1" id="modalImageConsentAccepted" checked>
                                                    <label class="form-check-label fw-bold" for="modalImageConsentAccepted">
                                                        Consentimiento de uso de imagen
                                                    </label>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary" id="modalEnrollmentSubmitBtn">
                                            <span class="spinner-border spinner-border-sm d-none me-1" id="modalEnrollmentSubmitSpinner" role="status"></span>
                                            Inscribir Estudiante
                                        </button>
                                    </div>
                                </form>
                        </div>
                    </div>

                    <!-- Modal Estudiantes Inscritos -->
                    <div class="modal fade" id="enrolledStudentsModal" tabindex="-1" aria-labelledby="enrolledStudentsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="enrolledStudentsModalLabel">Estudiantes Inscritos en {{ $course->title }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @if($course->enrollments->isEmpty())
                                        <p class="text-muted text-center my-3">No hay estudiantes inscritos en esta clase todavía.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Estudiante</th>
                                                        <th class="text-center">Consentimiento de Imagen</th>
                                                        <th>Representante</th>
                                                        <th>Teléfono / Correo</th>
                                                        <th class="text-center">Estado de Pago</th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($course->enrollments as $index => $enrollment)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <div>
                                                                    <a href="{{ route('students.show', $enrollment->student->id) }}" target="_blank" class="text-primary text-decoration-none fw-bold" title="Ver perfil del estudiante">
                                                                        {{ $enrollment->student->name ?? 'N/A' }} <i class="feather icon-external-link text-primary ms-1" style="font-size: 0.8rem;"></i>
                                                                    </a>
                                                                </div>
                                                                @if($enrollment->student->birthdate)
                                                                    <small class="text-muted d-block mt-1">Edad: {{ \Carbon\Carbon::parse($enrollment->student->birthdate)->age }} años</small>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($enrollment->image_consent_accepted)
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.78rem;" title="Consentimiento de imagen otorgado">
                                                                        <i class="fas fa-check-circle me-1"></i> Autorizado
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 0.78rem;" title="Sin consentimiento de uso de imagen">
                                                                        <i class="fas fa-times-circle me-1"></i> No Autorizado
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $enrollment->parent->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <div>{{ $enrollment->parent->dial_code ?? '' }} {{ $enrollment->parent->whatsapp ?? 'N/A' }}</div>
                                                                <small class="text-muted">{{ $enrollment->parent->email ?? '' }}</small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($enrollment->is_free_trial)
                                                                    <span class="badge bg-info px-3 py-2 text-white">Clase de prueba gratis</span>
                                                                @elseif($enrollment->payment_status === 'paid')
                                                                    <span class="badge bg-success px-3 py-2 text-white">Pagado</span>
                                                                @else
                                                                    <div>
                                                                        <span class="badge bg-warning text-dark px-3 py-2">Pendiente</span>
                                                                        <button class="btn btn-sm btn-outline-primary d-block mx-auto mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#attach-payment-{{ $enrollment->id }}" aria-expanded="false" aria-controls="attach-payment-{{ $enrollment->id }}">
                                                                            <i class="fas fa-file-invoice-dollar"></i> Registrar Pago
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <form action="{{ route('enrollment.status', $enrollment->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="cancelled">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas retirar a {{ $enrollment->student->name }} de este curso? Su inscripción se marcará como cancelada.')" title="Retirar estudiante del curso">
                                                                        <i class="fas fa-user-minus"></i> Retirar
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        @if($enrollment->payment_status !== 'paid')
                                                            <tr class="collapse" id="attach-payment-{{ $enrollment->id }}">
                                                                <td colspan="7" class="bg-light p-0">
                                                                    <div class="p-3 border rounded m-2 bg-white shadow-sm">
                                                                        <form action="{{ route('enrollment.attach-payment', $enrollment->id) }}" method="POST" enctype="multipart/form-data">
                                                                            @csrf
                                                                            
                                                                            <!-- Visualización de montos -->
                                                                            <div class="row g-3 mb-3">
                                                                                <div class="col-md-12 text-start">
                                                                                    @php
                                                                                        $enrollmentFee = $enrollment->getEnrollmentFee();
                                                                                        $monthlyFees = 0.0;
                                                                                        foreach ($enrollment->courses as $c) {
                                                                                            $monthlyFees += (float) ($c->monthly_fee ?? 0);
                                                                                        }
                                                                                        $suggestedTotal = $enrollmentFee + $monthlyFees;
                                                                                    @endphp
                                                                                    <div class="p-3 border rounded bg-light mb-0" style="border-left: 4px solid #0d6efd !important;">
                                                                                        <h6 class="fw-bold mb-2 small text-dark"><i class="fas fa-file-invoice-dollar text-primary me-1"></i> Detalle de la Factura (Monto Sugerido)</h6>
                                                                                        <div class="d-flex justify-content-between small mb-1">
                                                                                            <span class="text-muted">Inscripción:</span>
                                                                                            <span class="fw-semibold">${{ number_format($enrollmentFee, 2) }}</span>
                                                                                        </div>
                                                                                        <div class="d-flex justify-content-between small mb-1">
                                                                                            <span class="text-muted">Mensualidad:</span>
                                                                                            <span class="fw-semibold">${{ number_format($monthlyFees, 2) }}</span>
                                                                                        </div>
                                                                                        <div class="d-flex justify-content-between small mb-1 text-primary fw-bold">
                                                                                            <span>Total Sugerido:</span>
                                                                                            <span>${{ number_format($suggestedTotal, 2) }}</span>
                                                                                        </div>
                                                                                        @if($enrollment->receivable)
                                                                                            <hr class="my-1">
                                                                                            <div class="d-flex justify-content-between small mb-0 text-danger fw-bold">
                                                                                                <span>Saldo Total Pendiente:</span>
                                                                                                <span>${{ number_format($enrollment->receivable->balance_due, 2) }}</span>
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Opciones de pago -->
                                                                            <div class="row g-3 mb-3">
                                                                                <div class="col-md-12 text-start">
                                                                                    <label class="form-label small fw-bold d-block">Costo de Inscripción</label>
                                                                                    <div class="form-check form-check-inline">
                                                                                        <input class="form-check-input payment-option-radio" type="radio" name="amount_option" id="amount_opt_suggested_{{ $enrollment->id }}" value="suggested" checked data-enrollment-id="{{ $enrollment->id }}">
                                                                                        <label class="form-check-label" for="amount_opt_suggested_{{ $enrollment->id }}">
                                                                                            Monto sugerido (${{ number_format($suggestedTotal, 2) }})
                                                                                        </label>
                                                                                    </div>
                                                                                    <div class="form-check form-check-inline">
                                                                                        <input class="form-check-input payment-option-radio" type="radio" name="amount_option" id="amount_opt_custom_{{ $enrollment->id }}" value="custom" data-enrollment-id="{{ $enrollment->id }}">
                                                                                        <label class="form-check-label" for="amount_opt_custom_{{ $enrollment->id }}">
                                                                                            Monto total personalizado
                                                                                        </label>
                                                                                    </div>
                                                                                    
                                                                                    <div class="mt-2 d-none" id="custom-amount-container-{{ $enrollment->id }}">
                                                                                        <label class="form-label small fw-bold">Monto personalizado ($)</label>
                                                                                        <input type="number" name="custom_amount" id="custom_amount_{{ $enrollment->id }}" class="form-control form-control-sm w-50" step="0.01" min="0.01" max="{{ $enrollment->receivable ? $enrollment->receivable->balance_due : '' }}" placeholder="Ingrese el monto personalizado">
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="row g-3">
                                                                                <div class="col-md-4 text-start">
                                                                                    <label class="form-label small fw-bold">Cuenta de Pago</label>
                                                                                    <select name="account_id" class="form-control form-control-sm" required>
                                                                                        @foreach ($accounts ?? [] as $account)
                                                                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-4 text-start">
                                                                                    <label class="form-label small fw-bold">Referencia / Observación</label>
                                                                                    <input type="text" name="reference" class="form-control form-control-sm" placeholder="Ej. Transacción 1234">
                                                                                </div>
                                                                                <div class="col-md-4 text-start">
                                                                                    <label class="form-label small fw-bold">Comprobante de Pago</label>
                                                                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                                                                </div>
                                                                                <div class="col-md-12 text-end mt-2">
                                                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                                                        <i class="fas fa-check"></i> Registrar Pago y Confirmar
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
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
        function toggleModalUserForm() {
            const select = document.getElementById('modalUserSelect');
            const form = document.getElementById('modalUserForm');
            
            if (form.classList.contains('d-none')) {
                form.classList.remove('d-none');
                select.value = '';
                $(select).val('').trigger('change');
                $(select).prop('disabled', true);
                
                document.getElementById('modal_user_name').setAttribute('required', 'required');
                document.getElementById('modal_user_email').setAttribute('required', 'required');
                document.getElementById('modal_user_password').setAttribute('required', 'required');
            } else {
                form.classList.add('d-none');
                $(select).prop('disabled', false);
                
                document.getElementById('modal_user_name').removeAttribute('required');
                document.getElementById('modal_user_email').removeAttribute('required');
                document.getElementById('modal_user_password').removeAttribute('required');
            }
        }

        function toggleModalStudentForm() {
            const select = document.getElementById('modalStudentSelect');
            const form = document.getElementById('modalStudentForm');
            
            if (form.classList.contains('d-none')) {
                form.classList.remove('d-none');
                select.value = '';
                $(select).val('').trigger('change');
                $(select).prop('disabled', true);
                
                document.getElementById('modal_student_name').setAttribute('required', 'required');
                document.getElementById('modal_student_birthdate').setAttribute('required', 'required');
            } else {
                form.classList.add('d-none');
                $(select).prop('disabled', false);
                
                document.getElementById('modal_student_name').removeAttribute('required');
                document.getElementById('modal_student_birthdate').removeAttribute('required');
            }
        }

        $(document).ready(function() {
            if ($.fn.select2) {
                $('#coach_ids').select2({
                    placeholder: 'Selecciona uno o más entrenadores',
                    width: '100%'
                });

                $('.select2-modal').select2({
                    dropdownParent: $('#inscribirEstudianteModal'),
                    width: '100%'
                });
            }

            $('#monthly_fee').on('input', function() {
                let value = parseFloat($(this).val());
                if (isNaN(value)) value = 0;
                $('#monthly-fee-preview').text('$' + value.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }).trigger('input');

            $('.payment-option-radio').on('change', function() {
                let enrollmentId = $(this).data('enrollment-id');
                let customContainer = $('#custom-amount-container-' + enrollmentId);
                let customInput = $('#custom_amount_' + enrollmentId);

                if ($(this).val() === 'custom') {
                    customContainer.removeClass('d-none');
                    customInput.prop('required', true);
                } else {
                    customContainer.addClass('d-none');
                    customInput.prop('required', false).val('');
                }
            });

            let modalStudentHasPaidFee = false;

            async function checkModalEnrollmentFee() {
                const studentId = $('#modalStudentSelect').val();
                const programId = "{{ $course->program_id }}";

                if (!studentId || !programId) {
                    modalStudentHasPaidFee = false;
                    recalculateModalTotal();
                    return;
                }

                try {
                    const response = await fetch(`{{ route('enrollment.check-fee') }}?student_id=${studentId}&program_id=${programId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    modalStudentHasPaidFee = !!data.has_paid;
                } catch (e) {
                    console.error(e);
                    modalStudentHasPaidFee = false;
                }
                recalculateModalTotal();
            }

            $('#modalStudentSelect').on('change', function() {
                checkModalEnrollmentFee();
            });

            function recalculateModalTotal() {
                const monthlyFee = parseFloat("{{ $course->monthly_fee ?? 0.00 }}");
                let standardFee = parseFloat("{{ $course->program->enrollment_fee ?? 50.00 }}");
                
                if (modalStudentHasPaidFee) {
                    standardFee = 0;
                }
                
                const standardTotal = standardFee + monthlyFee;
                
                let total = standardTotal;
                let enrollmentFee = standardFee;

                if ($('#modalFeeCustom').is(':checked')) {
                    total = parseFloat($('#modalCustomAmount').val()) || 0;
                    enrollmentFee = Math.max(0, total - monthlyFee);
                }

                $('#modalStandardFeeLabel').text('$' + standardTotal.toFixed(2));

                $('#modalEnrollmentFeeDisplay').text('$' + enrollmentFee.toFixed(2));
                $('#modalMonthlyFeesDisplay').text('$' + monthlyFee.toFixed(2));
                $('#modalTotalAmountDisplay').text('$' + total.toFixed(2));
            }

            function updateModalPaymentReceiptState() {
                const isFreeTrial = $('#modalIsFreeTrial').is(':checked');
                const paymentStatus = $('#modalPaymentStatusSelect').val();

                if (isFreeTrial) {
                    $('#modalPaymentStatusSelect').val('paid');
                    $('#modalAccountSelectContainer').addClass('d-none');
                    $('#modalAccountSelect').prop('required', false);
                    $('#modalReferenceContainer').addClass('d-none');
                    $('#modalReferenceInput').val('');
                    return;
                }

                if (paymentStatus === 'paid') {
                    $('#modalAccountSelectContainer').removeClass('d-none');
                    $('#modalAccountSelect').prop('required', true);
                    $('#modalReferenceContainer').removeClass('d-none');
                } else {
                    $('#modalAccountSelectContainer').addClass('d-none');
                    $('#modalAccountSelect').prop('required', false);
                    $('#modalReferenceContainer').addClass('d-none');
                    $('#modalReferenceInput').val('');
                }
            }

            $('#modalIsFreeTrial, #modalPaymentStatusSelect').on('change', function() {
                updateModalPaymentReceiptState();
            });

            $('#inscribirEstudianteModal').on('shown.bs.modal', function() {
                updateModalPaymentReceiptState();
                checkModalEnrollmentFee();
            });

            $('#inscribirEstudianteModal').on('hidden.bs.modal', function() {
                const form = document.getElementById('inscribirEstudianteForm');
                if (form) {
                    form.reset();
                }
                $('#enrollmentErrorAlert').addClass('d-none').text('');

                // Ocultar y limpiar formulario de nuevo representante
                $('#modalUserForm').addClass('d-none');
                $('#modalUserSelect').prop('disabled', false).val('').trigger('change');
                $('#modal_user_name, #modal_user_email, #modal_user_whatsapp, #modal_user_password').prop('required', false).val('');

                // Ocultar y limpiar formulario de nuevo estudiante
                $('#modalStudentForm').addClass('d-none');
                $('#modalStudentSelect').prop('disabled', false).val('').trigger('change');
                $('#modal_student_name, #modal_student_birthdate, #modal_student_medical_notes').prop('required', false).val('');

                // Habilitar todas las opciones de estudiantes
                $('#modalStudentSelect option').prop('disabled', false);

                // Restablecer opciones de pago y costo
                $('#modalFeeStandard').prop('checked', true);
                $('#modalCustomAmountContainer').addClass('d-none');
                $('#modalCustomAmount').val('');
                $('#modalIsFreeTrial').prop('checked', false);
                $('#modalPaymentStatusSelect').val('pending');

                updateModalPaymentReceiptState();
                recalculateModalTotal();
            });

            $('.modal-fee-option').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#modalCustomAmountContainer').removeClass('d-none');
                } else {
                    $('#modalCustomAmountContainer').addClass('d-none');
                }
                recalculateModalTotal();
            });

            $('#modalCustomAmount').on('input', function() {
                recalculateModalTotal();
            });

            // Filter students based on selected representative in modal
            $('#modalUserSelect').on('change', function() {
                const userId = $(this).val();
                $('#modalStudentSelect option').each(function() {
                    const studentUser = $(this).data('user');
                    if (!userId || !studentUser || String(studentUser) === String(userId)) {
                        $(this).prop('disabled', false);
                    } else {
                        $(this).prop('disabled', true);
                    }
                });
                $('#modalStudentSelect').val('').trigger('change');
            });

            // AJAX submit for student enrollment
            $('#inscribirEstudianteForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#modalEnrollmentSubmitSpinner').removeClass('d-none');
                $('#modalEnrollmentSubmitBtn').prop('disabled', true);
                $('#enrollmentErrorAlert').addClass('d-none').text('');

                // Temporarily enable disabled selects to send their values
                $('#modalUserSelect, #modalStudentSelect').prop('disabled', false);

                let formData = new FormData(this);

                if ($('#modalUserForm').hasClass('d-none')) {
                    formData.delete('user[name]');
                    formData.delete('user[email]');
                    formData.delete('user[dial_code]');
                    formData.delete('user[whatsapp]');
                    formData.delete('user[password]');
                }
                if ($('#modalStudentForm').hasClass('d-none')) {
                    formData.delete('student[name]');
                    formData.delete('student[birthdate]');
                    formData.delete('student[medical_notes]');
                }

                $.ajax({
                    url: "{{ route('enrollment.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#modalEnrollmentSubmitSpinner').addClass('d-none');
                        window.location.reload();
                    },
                    error: function(xhr) {
                        $('#modalEnrollmentSubmitSpinner').addClass('d-none');
                        $('#modalEnrollmentSubmitBtn').prop('disabled', false);
                        
                        if (!$('#modalUserForm').hasClass('d-none')) {
                            $('#modalUserSelect').prop('disabled', true);
                        }
                        if (!$('#modalStudentForm').hasClass('d-none')) {
                            $('#modalStudentSelect').prop('disabled', true);
                        }

                        let errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                        let message = xhr.responseJSON ? xhr.responseJSON.message : 'Error al registrar la inscripción.';
                        
                        if (errors) {
                            let errorList = '<ul>';
                            for (let field in errors) {
                                errorList += '<li>' + errors[field][0] + '</li>';
                            }
                            errorList += '</ul>';
                            $('#enrollmentErrorAlert').removeClass('d-none').html(errorList);
                        } else {
                            $('#enrollmentErrorAlert').removeClass('d-none').text(message);
                        }

                        // Auto-scroll modal body to top so user immediately sees the error alert
                        $('#inscribirEstudianteModal .modal-body').animate({ scrollTop: 0 }, 300);
                    }
                });
            });
        });
    </script>
@endsection
