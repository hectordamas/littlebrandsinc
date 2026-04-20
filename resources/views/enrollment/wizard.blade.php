@extends('layouts.app')

@section('title')
    <title>Inscripción - {{ config('app.name') }}</title>
@endsection

@section('styles')
    <style>
        .form-card {
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .step-indicator {
            position: relative;
            padding: 0 1rem;
        }

        .step-indicator .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step-indicator .step::after {
            content: '';
            position: absolute;
            top: 20px;
            left: -50%;
            width: 100%;
            height: 3px;
            background: #dee2e6;
            z-index: 0;
        }

        .step-indicator .step:first-child::after {
            display: none;
        }

        .step-indicator .step.active .step-circle {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .step-indicator .step.completed .step-circle {
            background: #198754;
            border-color: #198754;
            color: white;
        }

        .step-indicator .step.active::after,
        .step-indicator .step.completed::after {
            background: #0d6efd;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #dee2e6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }

        .step-label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        .step.active .step-label {
            color: #0d6efd;
            font-weight: 600;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
        }

        .btn-primary {
            transition: all 0.25s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .student-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .student-card:hover {
            border-color: #0d6efd;
            background: #f8f9fa;
        }

        .student-card.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }

        .course-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .course-card:hover {
            border-color: #0d6efd;
        }

        .course-card.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }

        .course-card.unavailable {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .course-card .age-range {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .course-card .spots-left {
            font-size: 0.875rem;
            font-weight: 600;
        }

        .course-card .spots-left.full {
            color: #dc3545;
        }

        .course-card .spots-left.available {
            color: #198754;
        }

        .course-card .age-error {
            color: #dc3545;
            font-size: 0.875rem;
        }

        .course-card .capacity-full {
            color: #dc3545;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .fade-enter {
            opacity: 0;
            transform: translateX(20px);
        }

        .fade-enter-active {
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .fade-leave-active {
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="text-center mb-4">
                    <img src="{{ asset('assets/img/logo-littlebrandsinc.png') }}" 
                         alt="{{ config('app.name') }}" 
                         style="max-width: 180px;"
                         loading="lazy">
                </div>

                <div class="card form-card">
                    <div class="card-block p-4">

                        <h4 class="text-center mb-4">Inscripción</h4>

                        <div class="step-indicator d-flex mb-5">
                            <div class="step active" id="step-indicator-1">
                                <span class="step-circle">1</span>
                                <span class="step-label">Cuenta</span>
                            </div>
                            <div class="step" id="step-indicator-2">
                                <span class="step-circle">2</span>
                                <span class="step-label">Estudiante</span>
                            </div>
                            <div class="step" id="step-indicator-3">
                                <span class="step-circle">3</span>
                                <span class="step-label">Programa</span>
                            </div>
                            <div class="step" id="step-indicator-4">
                                <span class="step-circle">4</span>
                                <span class="step-label">Pago</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('enrollment.wizard.submit') }}" id="enrollmentWizard" novalidate>
                            @csrf
                            <input type="hidden" name="current_step" id="currentStep" value="{{ $currentStep ?? 1 }}">

                            <div class="step-content active" id="step-1">
                                <h5 class="mb-4 text-center">Paso 1: Inicia sesión o crea una cuenta</h5>

                                <div class="mb-4 text-center">
                                    <div class="btn-group" role="group" aria-label="Tipo de usuario">
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="user_type" 
                                               id="user_type_new"
                                               value="new" 
                                               {{ old('user_type', session('user_type', 'new')) === 'new' ? 'checked' : '' }}
                                               autocomplete="off"
                                               onchange="toggleUserFields()">
                                        <label class="btn btn-outline-primary" for="user_type_new">
                                            Nuevo usuario
                                        </label>
                                        
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="user_type" 
                                               id="user_type_existing"
                                               value="existing"
                                               {{ old('user_type', session('user_type')) === 'existing' ? 'checked' : '' }}
                                               autocomplete="off"
                                               onchange="toggleUserFields()">
                                        <label class="btn btn-outline-primary" for="user_type_existing">
                                            Ya tengo cuenta
                                        </label>
                                    </div>
                                </div>

                                <div id="existingUserFields" class="{{ old('user_type', session('user_type')) !== 'existing' ? 'd-none' : '' }}">
                                    <div class="mb-3">
                                        <label for="email_login" class="form-label">Correo electrónico</label>
                                        <input type="email" 
                                               name="email_login" 
                                               id="email_login"
                                               class="form-control{{ $errors->has('email_login') ? ' is-invalid' : '' }}"
                                               value="{{ old('email_login', session('email_login')) }}"
                                               autocomplete="email">
                                        @error('email_login')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_login" class="form-label">Contraseña</label>
                                        <input type="password" 
                                               name="password_login" 
                                               id="password_login"
                                               class="form-control{{ $errors->has('password_login') ? ' is-invalid' : '' }}"
                                               autocomplete="current-password">
                                        @error('password_login')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div id="newUserFields" class="{{ old('user_type', session('user_type')) === 'existing' ? 'd-none' : '' }}">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nombre completo</label>
                                        <input type="text" 
                                               name="name" 
                                               id="name"
                                               class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                               value="{{ old('name', session('name')) }}"
                                               required
                                               minlength="2"
                                               maxlength="255"
                                               autocomplete="name">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo electrónico</label>
                                        <input type="email" 
                                               name="email" 
                                               id="email"
                                               class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                               value="{{ old('email', session('email')) }}"
                                               required
                                               autocomplete="email">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="whatsapp" class="form-label">WhatsApp</label>
                                        <div class="input-group">
                                            <select name="dial_code" 
                                                    id="dial_code" 
                                                    class="form-select"
                                                    style="max-width: 100px;"
                                                    required>
                                                @include('partials.dialcode_create')
                                            </select>
                                            <input type="tel" 
                                                   name="whatsapp" 
                                                   id="whatsapp"
                                                   class="form-control{{ $errors->has('whatsapp') ? ' is-invalid' : '' }}"
                                                   value="{{ old('whatsapp', session('whatsapp')) }}"
                                                   required
                                                   pattern="[0-9]{7,10}"
                                                   placeholder="4121234567">
                                        </div>
                                        @error('whatsapp')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" 
                                               name="password" 
                                               id="password"
                                               class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                               required
                                               minlength="8">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                                        <input type="password" 
                                               name="password_confirmation" 
                                               id="password_confirmation"
                                               class="form-control"
                                               required
                                               minlength="8">
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">
                                        Continuar
                                    </button>
                                </div>
                            </div>

                            <div class="step-content" id="step-2">
                                <h5 class="mb-4 text-center">Paso 2: Selecciona o agrega un estudiante</h5>

                                <p class="text-muted text-center mb-4">Selecciona un estudiante de tu representación o agrega uno nuevo</p>

                                @if(session()->has('students') && count(session('students', [])) > 0)
                                    <div class="mb-4">
                                        <label class="form-label">Mis estudiantes:</label>
                                        <div class="student-list">
                                            @foreach(session('students', []) as $student)
                                                <div class="student-card mb-2" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" onclick="var id=this.dataset.studentId,name=this.dataset.studentName;selectStudent(id,name)">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $student->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $student->birthdate ? \Carbon\Carbon::parse($student->birthdate)->format('d/m/Y') : 'Sin fecha' }}</small>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="radio" 
                                                                   name="selected_student" 
                                                                   value="{{ $student->id }}"
                                                                   class="form-check-input"
                                                                   {{ old('selected_student') == $student->id ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="text-center my-3">
                                        <span class="text-muted">o</span>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label class="form-label">Agregar nuevo estudiante:</label>
                                    <div class="student-card" onclick="document.getElementById('newStudentFields').classList.toggle('d-none'); document.getElementById('selected_student').value = '';">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>➕ Agregar nuevo estudiante</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="newStudentFields" class="{{ session()->has('new_student_added') ? '' : 'd-none' }}">
                                    <div class="mb-3">
                                        <label for="student_name" class="form-label">Nombre del estudiante</label>
                                        <input type="text" 
                                               name="student_name" 
                                               id="student_name"
                                               class="form-control{{ $errors->has('student_name') ? ' is-invalid' : '' }}"
                                               value="{{ old('student_name', session('student_name')) }}">
                                        @error('student_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="student_birthdate" class="form-label">Fecha de nacimiento</label>
                                        <input type="date" 
                                               name="student_birthdate" 
                                               id="student_birthdate"
                                               class="form-control{{ $errors->has('student_birthdate') ? ' is-invalid' : '' }}"
                                               value="{{ old('student_birthdate', session('student_birthdate')) }}"
                                               max="{{ now()->format('Y-m-d') }}">
                                        @error('student_birthdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="student_medical_notes" class="form-label">Condiciones médicas <span class="text-muted">(opcional)</span></label>
                                        <textarea name="student_medical_notes" 
                                                  id="student_medical_notes"
                                                  class="form-control"
                                                  rows="2">{{ old('student_medical_notes', session('student_medical_notes')) }}</textarea>
                                    </div>
                                </div>

                                @error('selected_student')
                                    <div class="text-danger mb-3">{{ $message }}</div>
                                @enderror

                                <div class="d-flex gap-2 mt-4">
                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                        Atrás
                                    </button>
                                    <button type="button" class="btn btn-primary flex-grow-1" onclick="nextStep()">
                                        Continuar
                                    </button>
                                </div>
                            </div>

                            <div class="step-content" id="step-3">
                                <h5 class="mb-4 text-center">Paso 3: Selecciona un programa</h5>

                                <p class="text-muted text-center mb-4">Elige el programa al que desea inscribirse</p>

                                @if($courses->isEmpty())
                                    <div class="alert alert-warning">
                                        No hay programas disponibles en este momento. Por favor, intente más tarde.
                                    </div>
                                @else
                                    <div class="courses-list">
                                        @foreach($courses as $course)
                                            @php
                                                $studentAge = isset($studentBirthdate) ? \Carbon\Carbon::parse($studentBirthdate)->age : null;
                                                $enrollmentsCount = $course->enrollments_count ?? 0;
                                                $spotsLeft = $course->capacity - $enrollmentsCount;
                                                $isFull = $spotsLeft <= 0;
                                                $ageTooYoung = $studentAge !== null && $course->min_age !== null && $studentAge < $course->min_age;
                                                $ageTooOld = $studentAge !== null && $course->max_age !== null && $studentAge > $course->max_age;
                                                $ageError = $ageTooYoung || $ageTooOld;
                                                $canEnroll = !$isFull && !$ageError;
                                            @endphp
                                            
                                            <div class="course-card mb-3 {{ !$canEnroll ? 'unavailable' : '' }}" 
                                                 data-course-id="{{ $course->id }}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6>{{ $course->title }}</h6>
                                                        <p class="mb-1 text-muted">{{ $course->description }}</p>
                                                        <div class="age-range">
                                                            <strong>Edad:</strong> 
                                                            @if($course->min_age && $course->max_age)
                                                                {{ $course->min_age }} - {{ $course->max_age }} años
                                                            @elseif($course->min_age)
                                                                {{ $course->min_age }}+ años
                                                            @elseif($course->max_age)
                                                               Hasta {{ $course->max_age }} años
                                                            @else
                                                                todas las edades
                                                            @endif
                                                            @if($ageError)
                                                                <span class="age-error">
                                                                    @if($ageTooYoung)
                                                                        (Tu hijo/a es muy menor. Mínimo: {{ $course->min_age }} años)
                                                                    @elseif($ageTooOld)
                                                                        (Tu hijo/a es muy mayor. Máximo: {{ $course->max_age }} años)
                                                                    @endif
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="spots-left {{ $isFull ? 'full' : 'available' }}">
                                                            @if($isFull)
                                                                <span class="capacity-full">🎫 Cupo lleno</span>
                                                            @else
                                                                🎫 {{ $spotsLeft }} cup{{ $spotsLeft == 1 ? 'o' : 'os' }} disponibles
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="radio" 
                                                               name="selected_course" 
                                                               value="{{ $course->id }}"
                                                               class="form-check-input"
                                                               {{ !$canEnroll ? 'disabled' : '' }}
                                                               {{ old('selected_course') == $course->id ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <strong>Precio:</strong> ${{ number_format($course->price, 2) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @error('selected_course')
                                    <div class="text-danger mb-3">{{ $message }}</div>
                                @enderror

                                <div class="d-flex gap-2 mt-4">
                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                        Atrás
                                    </button>
                                    <button type="button" class="btn btn-primary flex-grow-1" onclick="nextStep()">
                                        Continuar
                                    </button>
                                </div>
                            </div>

                            <div class="step-content" id="step-4">
                                <h5 class="mb-4 text-center">Paso 4: Método de pago</h5>

                                <p class="text-muted text-center mb-4">Selecciona tu método de pago preferido</p>

                                <div class="mb-4">
                                    <label class="form-label">Resumen de inscripción:</label>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <span>Estudiante:</span>
                                                <span id="summaryStudentName">-</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Programa:</span>
                                                <span id="summaryCourseName">-</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Total:</strong>
                                                <strong id="summaryTotal">$0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Método de pago:</label>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="payment_method" 
                                               id="payment_card"
                                               value="card"
                                               {{ old('payment_method') !== 'pending' ? 'checked' : '' }}
                                               onchange="togglePaymentFields()">
                                        <label class="form-check-label" for="payment_card">
                                            💳 Pagar con tarjeta de crédito/débito
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="payment_method" 
                                               id="payment_pending"
                                               value="pending"
                                               {{ old('payment_method') === 'pending' ? 'checked' : '' }}
                                               onchange="togglePaymentFields()">
                                        <label class="form-check-label" for="payment_pending">
                                            ⏳ Pending payment (El administrador validará manually)
                                        </label>
                                    </div>
                                </div>

                                <div id="cardPaymentFields" class="{{ old('payment_method') === 'pending' ? 'd-none' : '' }}">
                                    <div class="alert alert-info">
                                        <small>Serás redirigido a la pasarela de pago segura para completar tu transacción.</small>
                                    </div>
                                </div>

                                <div id="pendingPaymentFields" class="{{ old('payment_method') !== 'pending' ? 'd-none' : '' }}">
                                    <div class="alert alert-warning">
                                        <strong>Nota:</strong> Tu inscripción estará pendiente hasta que el administrador valide el pago. Te contactaremos por WhatsApp cuando tu inscripción sea confirmada.
                                    </div>
                                </div>

                                @error('payment_method')
                                    <div class="text-danger mb-3">{{ $message }}</div>
                                @enderror

                                <div class="mb-3 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input{{ $errors->has('terms') ? ' is-invalid' : '' }}" 
                                               type="checkbox" 
                                               name="terms" 
                                               id="terms"
                                               {{ old('terms') ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label" for="terms">
                                            Acepto los
                                            <a href="{{ url('terms') }}" target="_blank" rel="noopener noreferrer">Términos y Condiciones</a>
                                            y la
                                            <a href="{{ url('privacy') }}" target="_blank" rel="noopener noreferrer">Política de Privacidad</a>
                                        </label>
                                        @error('terms')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep()">
                                        Atrás
                                    </button>
                                    <button type="submit" class="btn btn-success flex-grow-1">
                                        Completar Inscripción
                                    </button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let studentData = {};
        let courseData = {};

        document.addEventListener('DOMContentLoaded', function() {
            updateStepIndicators();
            loadSummary();
        });

        function toggleUserFields() {
            const type = document.querySelector('input[name="user_type"]:checked').value;
            const existingFields = document.getElementById('existingUserFields');
            const newFields = document.getElementById('newUserFields');
            
            if (type === 'existing') {
                existingFields.classList.remove('d-none');
                newFields.classList.add('d-none');
            } else {
                existingFields.classList.add('d-none');
                newFields.classList.remove('d-none');
            }
        }

        function selectStudent(id, name) {
            document.querySelectorAll('.student-card').forEach(card => card.classList.remove('selected'));
            document.querySelector(`[data-student-id="${id}"]`).classList.add('selected');
            document.getElementById('selected_student').value = id;
            studentData.id = id;
            studentData.name = name;
            document.getElementById('newStudentFields').classList.add('d-none');
            loadSummary();
        }

        function selectCourse(id) {
            document.querySelectorAll('.course-card').forEach(card => card.classList.remove('selected'));
            document.querySelector(`[data-course-id="${id}"]`).classList.add('selected');
            document.querySelector(`input[name="selected_course"][value="${id}"]`).checked = true;
            courseData.id = id;
            loadSummary();
        }

        function togglePaymentFields() {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            const cardFields = document.getElementById('cardPaymentFields');
            const pendingFields = document.getElementById('pendingPaymentFields');
            
            if (method === 'pending') {
                cardFields.classList.add('d-none');
                pendingFields.classList.remove('d-none');
            } else {
                cardFields.classList.remove('d-none');
                pendingFields.classList.add('d-none');
            }
        }

        function loadSummary() {
            document.getElementById('summaryStudentName').textContent = studentData.name || '-';
        }

        function nextStep() {
            if (!validateCurrentStep()) {
                return;
            }
            
            if (currentStep < 4) {
                currentStep++;
                document.getElementById('currentStep').value = currentStep;
                showStep(currentStep);
                updateStepIndicators();
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                document.getElementById('currentStep').value = currentStep;
                showStep(currentStep);
                updateStepIndicators();
            }
        }

        function showStep(step) {
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`step-${step}`).classList.add('active');
        }

        function updateStepIndicators() {
            document.querySelectorAll('.step').forEach((indicator, index) => {
                const stepNum = index + 1;
                indicator.classList.remove('active', 'completed');
                
                if (stepNum === currentStep) {
                    indicator.classList.add('active');
                } else if (stepNum < currentStep) {
                    indicator.classList.add('completed');
                }
            });
        }

        function validateCurrentStep() {
            const form = document.getElementById('enrollmentWizard');
            let isValid = true;
            let firstError = null;

            if (currentStep === 1) {
                const userType = document.querySelector('input[name="user_type"]:checked').value;
                
                if (userType === 'new') {
                    const name = document.getElementById('name');
                    const email = document.getElementById('email');
                    const password = document.getElementById('password');
                    const passwordConfirmation = document.getElementById('password_confirmation');
                    
                    if (!name.value) { name.classList.add('is-invalid'); isValid = false; }
                    if (!email.value) { email.classList.add('is-invalid'); isValid = false; }
                    if (!password.value) { password.classList.add('is-invalid'); isValid = false; }
                    if (password.value !== passwordConfirmation.value) {
                        passwordConfirmation.classList.add('is-invalid');
                        isValid = false;
                    }
                } else {
                    const emailLogin = document.getElementById('email_login');
                    const passwordLogin = document.getElementById('password_login');
                    
                    if (!emailLogin.value) { emailLogin.classList.add('is-invalid'); isValid = false; }
                    if (!passwordLogin.value) { passwordLogin.classList.add('is-invalid'); isValid = false; }
                }

                if (!isValid) {
                    form.submit();
                }
            }

            if (currentStep === 2) {
                const selectedStudent = document.querySelector('input[name="selected_student"]:checked');
                const newStudentName = document.getElementById('student_name');
                const newStudentBirthdate = document.getElementById('student_birthdate');
                
                const hasExistingStudent = selectedStudent && selectedStudent.value;
                const hasNewStudent = newStudentName.value && newStudentBirthdate.value;
                
                if (!hasExistingStudent && !hasNewStudent) {
                    alert('Por favor selecciona un estudiante o agrega uno nuevo');
                    isValid = false;
                }
            }

            if (currentStep === 3) {
                const selectedCourse = document.querySelector('input[name="selected_course"]:checked');
                
                if (!selectedCourse || !selectedCourse.value) {
                    alert('Por favor selecciona un programa');
                    isValid = false;
                }
            }

            return isValid;
        }

        window.toggleUserFields = toggleUserFields;
        window.selectStudent = selectStudent;
        window.selectCourse = selectCourse;
        window.togglePaymentFields = togglePaymentFields;
        window.nextStep = nextStep;
        window.prevStep = prevStep;
    </script>
@endsection