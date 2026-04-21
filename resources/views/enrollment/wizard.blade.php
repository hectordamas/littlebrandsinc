{{--
  Wizard de inscripción (5 pasos). Estado: sesión Laravel + Alpine en cliente.
  Estructura esperada (wizardPayload / API):
  - student: { id, name, birthdate }
  - course: { id, title, price, min_age, max_age, spots_left, can_enroll, enroll_error }
  - enrollment final: student_id, course_id, payment_method (card|pending), terms
--}}
@extends('layouts.app')

@php
    $wizardConfig = [
        'initialStep' => (int) $initialStep,
        'authenticated' => auth()->check(),
        'submitUrl' => route('enrollment.wizard.submit'),
        'paymentIntentUrl' => route('enrollment.wizard.payment-intent'),
        'stripeKey' => $stripeKey,
        'wizardPayload' => $wizardPayload,
    ];
@endphp

@section('title')
    <title>Inscripción - {{ config('app.name') }}</title>
@endsection

@section('styles')
    <style>
        [x-cloak] { display: none !important; }
        .form-card {
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .form-control, .form-select { border-radius: 8px; padding: 10px; }
        .wizard-progress {
            height: 10px;
            border-radius: 999px;
            background: #e9ecef;
            overflow: hidden;
        }
        .wizard-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #0d6efd, #198754);
            transition: width 0.35s ease;
        }
        .step-pill {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .step-pill.active { color: #0d6efd; font-weight: 600; }
        .step-pill.done { color: #198754; }
        .student-card, .course-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }
        .student-card:hover, .course-card:hover:not(.disabled) { border-color: #0d6efd; }
        .student-card.selected, .course-card.selected { border-color: #0d6efd; background: #e7f1ff; }
        .course-card.disabled { opacity: 0.65; cursor: not-allowed; }
        #card-element { padding: 12px; border: 1px solid #ced4da; border-radius: 8px; background: #fff; }
        .stripe-loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.82);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            z-index: 5;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5"
         x-data="enrollmentWizard(@js($wizardConfig))"
         x-init="init()"
         x-cloak>

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
                        <h4 class="text-center mb-2">Inscripción</h4>
                        <p class="text-center text-muted small mb-4">
                            Completa cada paso. Puedes volver atrás sin perder el progreso guardado en sesión.
                        </p>

                        <div class="mb-2 d-flex justify-content-between small text-muted">
                            <span>Paso <span x-text="step"></span> de 5</span>
                            <a class="text-decoration-none" href="{{ route('enrollment.wizard.reset') }}">Reiniciar</a>
                        </div>
                        <div class="wizard-progress mb-2">
                            <div class="wizard-progress-bar" :style="'width:' + progressPercent + '%'"></div>
                        </div>
                        <div class="d-flex justify-content-between flex-wrap gap-1 mb-4">
                            <template x-for="(label, idx) in stepLabels" :key="idx">
                                <span class="step-pill"
                                      :class="{
                                          'active': (idx + 1) === step,
                                          'done': (idx + 1) < step || (authenticated && idx === 0)
                                      }"
                                      x-text="label"></span>
                            </template>
                        </div>

                        <div x-show="globalError" x-transition class="alert alert-danger" x-text="globalError"></div>

                        {{-- STEP 1: Auth --}}
                        <div x-show="step === 1 && !authenticated">
                            <h5 class="mb-3 text-center">1. Cuenta</h5>
                            <div class="mb-4 text-center">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" id="wt_new" value="new" x-model="userType">
                                    <label class="btn btn-outline-primary" for="wt_new">Nuevo usuario</label>
                                    <input type="radio" class="btn-check" id="wt_ex" value="existing" x-model="userType">
                                    <label class="btn btn-outline-primary" for="wt_ex">Ya tengo cuenta</label>
                                </div>
                            </div>

                            <div x-show="userType === 'existing'">
                                <div class="mb-3">
                                    <label class="form-label">Correo</label>
                                    <input type="email" class="form-control" :class="fieldClass('email_login')" x-model="emailLogin" autocomplete="email">
                                    <div class="invalid-feedback d-block" x-show="formErrors.email_login" x-text="formErrors.email_login?.[0]"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" :class="fieldClass('password_login')" x-model="passwordLogin" autocomplete="current-password">
                                    <div class="invalid-feedback d-block" x-show="formErrors.password_login" x-text="formErrors.password_login?.[0]"></div>
                                </div>
                            </div>

                            <div x-show="userType === 'new'">
                                <div class="mb-3">
                                    <label class="form-label">Nombre completo</label>
                                    <input type="text" class="form-control" :class="fieldClass('name')" x-model="name" minlength="2">
                                    <div class="invalid-feedback d-block" x-show="formErrors.name" x-text="formErrors.name?.[0]"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" :class="fieldClass('email')" x-model="emailReg" autocomplete="email">
                                    <div class="invalid-feedback d-block" x-show="formErrors.email" x-text="formErrors.email?.[0]"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">WhatsApp</label>
                                    <div class="input-group">
                                        <select class="form-select" style="max-width:110px" x-model="dialCode">
                                            @include('partials.dialcode_create')
                                        </select>
                                        <input type="tel" class="form-control" :class="fieldClass('whatsapp')" x-model="whatsapp" pattern="[0-9]{7,10}" placeholder="4121234567">
                                    </div>
                                    <div class="invalid-feedback d-block" x-show="formErrors.whatsapp" x-text="formErrors.whatsapp?.[0]"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" :class="fieldClass('password')" x-model="password" minlength="8" autocomplete="new-password">
                                    <div class="invalid-feedback d-block" x-show="formErrors.password" x-text="formErrors.password?.[0]"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirmar contraseña</label>
                                    <input type="password" class="form-control" :class="fieldClass('password_confirmation')" x-model="passwordConfirmation" minlength="8" autocomplete="new-password">
                                    <div class="invalid-feedback d-block" x-show="formErrors.password_confirmation" x-text="formErrors.password_confirmation?.[0]"></div>
                                </div>
                            </div>

                            <p class="text-center small mb-0">
                                <a href="{{ route('login') }}" class="text-decoration-none">Ir al inicio de sesión clásico</a>
                            </p>
                        </div>

                        {{-- STEP 2: Estudiante --}}
                        <div x-show="step === 2">
                            <h5 class="mb-3 text-center">2. Estudiante</h5>
                            <p class="text-muted small text-center">Elige un estudiante o registra uno nuevo.</p>

                            <template x-if="students.length">
                                <div class="mb-3">
                                    <template x-for="s in students" :key="s.id">
                                        <div class="student-card mb-2"
                                             :class="{ 'selected': selectedStudentId === s.id && !newStudentMode }"
                                             @click="selectExistingStudent(s)">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong x-text="s.name"></strong><br>
                                                    <small class="text-muted" x-text="s.birthdate || '—'"></small>
                                                </div>
                                                <input type="radio" class="form-check-input" :value="s.id" x-model.number="selectedStudentId" @click.stop>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <button type="button"
                                    class="btn btn-outline-primary w-100 mb-3"
                                    @click="startNewStudent()">
                                + Registrar nuevo estudiante
                            </button>

                            <div x-show="newStudentMode" x-transition>
                                <div class="mb-3">
                                    <label class="form-label">Nombre del estudiante</label>
                                    <input type="text" class="form-control" :class="fieldClass('student_name')" x-model="newStudentName">
                                    <div class="invalid-feedback d-block" x-show="formErrors.student_name" x-text="formErrors.student_name?.[0]"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fecha de nacimiento</label>
                                    <input type="date" class="form-control" :class="fieldClass('student_birthdate')" x-model="newStudentBirthdate" max="{{ now()->format('Y-m-d') }}">
                                    <div class="invalid-feedback d-block" x-show="formErrors.student_birthdate" x-text="formErrors.student_birthdate?.[0]"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Condiciones médicas <span class="text-muted">(opcional)</span></label>
                                    <textarea class="form-control" rows="2" x-model="newStudentMedical"></textarea>
                                </div>
                            </div>
                            <div class="invalid-feedback d-block mb-2" x-show="formErrors.selected_student" x-text="formErrors.selected_student?.[0]"></div>
                        </div>

                        {{-- STEP 3: Curso --}}
                        <div x-show="step === 3">
                            <h5 class="mb-3 text-center">3. Programa</h5>
                            <p class="text-muted small text-center" x-show="lockedCourseId">
                                La inscripción está limitada al curso indicado en el enlace.
                            </p>

                            <template x-if="!courses.length">
                                <div class="alert alert-warning">No hay programas disponibles.</div>
                            </template>

                            <template x-for="c in courses" :key="c.id">
                                <div class="course-card mb-3"
                                     :class="{ 'selected': selectedCourseId === c.id, 'disabled': !c.can_enroll }"
                                     @click="c.can_enroll && selectCourse(c.id)">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <h6 class="mb-1" x-text="c.title"></h6>
                                            <p class="mb-1 small text-muted" x-text="c.description"></p>
                                            <div class="small">
                                                <span class="text-dark">Precio:</span>
                                                <strong x-text="formatMoney(c.price)"></strong>
                                            </div>
                                            <div class="small" :class="c.spots_left > 0 ? 'text-success' : 'text-danger'">
                                                Cupos: <span x-text="c.spots_left"></span>
                                            </div>
                                            <div class="small text-danger" x-show="!c.can_enroll && c.enroll_error" x-text="c.enroll_error"></div>
                                        </div>
                                        <input type="radio" class="form-check-input mt-1" :disabled="!c.can_enroll"
                                               :value="c.id" x-model.number="selectedCourseId" @click.stop>
                                    </div>
                                </div>
                            </template>
                            <div class="invalid-feedback d-block" x-show="formErrors.selected_course" x-text="formErrors.selected_course?.[0]"></div>
                        </div>

                        {{-- STEP 4: Pago --}}
                        <div x-show="step === 4">
                            <h5 class="mb-3 text-center">4. Pago</h5>

                            <div class="card mb-3 bg-light">
                                <div class="card-body small">
                                    <div class="d-flex justify-content-between"><span>Estudiante</span><span x-text="summaryStudent"></span></div>
                                    <div class="d-flex justify-content-between"><span>Programa</span><span x-text="summaryCourseTitle"></span></div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between"><strong>Total</strong><strong x-text="summaryTotal"></strong></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Método de pago</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" id="pay_card" value="card" x-model="paymentMethod" @change="onPaymentMethodChange()">
                                    <label class="form-check-label" for="pay_card">Tarjeta (Stripe)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="pay_man" value="pending" x-model="paymentMethod" @change="onPaymentMethodChange()">
                                    <label class="form-check-label" for="pay_man">Pago manual / transferencia</label>
                                </div>
                            </div>

                            <div x-show="paymentMethod === 'card'" x-transition>
                                <template x-if="stripeKey">
                                    <div class="position-relative">
                                        <div id="card-element" class="mb-2"></div>
                                        <div class="stripe-loading-overlay" x-show="processingStripe" x-transition>
                                            <div class="text-center">
                                                <div class="spinner-border text-primary mb-2" role="status" aria-hidden="true"></div>
                                                <div class="small text-muted">Procesando pago con Stripe...</div>
                                            </div>
                                        </div>
                                        <p class="small text-muted">Datos procesados por Stripe. No almacenamos el número completo en nuestro servidor.</p>
                                    </div>
                                </template>
                                <template x-if="!stripeKey">
                                    <div class="alert alert-secondary small">
                                        Sin <code>STRIPE_KEY</code> en entorno: modo simulación.
                                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" @click="useSimulatedCard()">
                                            Generar pago simulado
                                        </button>
                                        <span class="ms-2 small text-success" x-show="simulatedPi" x-text="'Listo: ' + simulatedPi"></span>
                                    </div>
                                </template>
                                <div class="invalid-feedback d-block" x-show="formErrors.stripe_payment_intent_id" x-text="formErrors.stripe_payment_intent_id?.[0]"></div>
                            </div>

                            <div x-show="paymentMethod === 'pending'" class="alert alert-info small">
                                El administrador validará tu pago. La inscripción quedará en estado pendiente hasta la confirmación.
                            </div>
                        </div>

                        {{-- STEP 5: Confirmación --}}
                        <div x-show="step === 5">
                            <h5 class="mb-3 text-center">5. Confirmación</h5>
                            <p class="text-muted small">Revisa los datos y acepta los términos para finalizar.</p>

                            <ul class="list-group mb-3 small">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Estudiante</span><span x-text="summaryStudent"></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Programa</span><span x-text="summaryCourseTitle"></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Pago</span>
                                    <span x-text="paymentMethod === 'card' ? 'Tarjeta (Stripe)' : 'Manual / pendiente de validación'"></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Total</strong><strong x-text="summaryTotal"></strong>
                                </li>
                            </ul>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms_w" x-model="termsAccepted" :class="{ 'is-invalid': formErrors.terms }">
                                <label class="form-check-label" for="terms_w">
                                    Acepto los
                                    <a href="{{ url('terms') }}" target="_blank" rel="noopener">Términos</a>
                                    y la
                                    <a href="{{ url('privacy') }}" target="_blank" rel="noopener">Privacidad</a>
                                </label>
                                <div class="invalid-feedback d-block" x-show="formErrors.terms" x-text="formErrors.terms?.[0]"></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary" x-show="canGoBack" @click="goBack()" :disabled="loading || processingStripe">Atrás</button>
                            <button type="button" class="btn btn-primary flex-grow-1" x-show="step < 5" @click="goNext()" :disabled="loading || processingStripe">
                                <span x-show="!loading">Siguiente</span>
                                <span x-show="loading && !processingStripe">Procesando…</span>
                                <span x-show="processingStripe">Procesando pago…</span>
                            </button>
                            <button type="button" class="btn btn-success flex-grow-1" x-show="step === 5" @click="finalize()" :disabled="loading || processingStripe">
                                <span x-show="!loading">Confirmar inscripción</span>
                                <span x-show="loading">Guardando…</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function enrollmentWizard(cfg) {
    const p = cfg.wizardPayload;
    return {
        step: cfg.initialStep,
        authenticated: cfg.authenticated,
        submitUrl: cfg.submitUrl,
        paymentIntentUrl: cfg.paymentIntentUrl,
        stripeKey: cfg.stripeKey || '',
        courses: p.courses || [],
        students: p.students || [],
        lockedCourseId: p.locked_course_id,
        selectedStudentId: p.selected_student_id,
        selectedCourseId: p.selected_course_id,
        selectedCourse: p.selected_course,
        paymentMethod: p.payment_method || 'card',
        userType: 'new',
        emailLogin: '', passwordLogin: '',
        name: '', emailReg: '', dialCode: '+58', whatsapp: '',
        password: '', passwordConfirmation: '',
        newStudentMode: false,
        newStudentName: '', newStudentBirthdate: '', newStudentMedical: '',
        termsAccepted: false,
        formErrors: {},
        globalError: '',
        loading: false,
        processingStripe: false,
        stripe: null,
        cardEl: null,
        stripeMounted: false,
        simulatedPi: '',
        stepLabels: ['Cuenta', 'Estudiante', 'Programa', 'Pago', 'Confirmación'],

        get progressPercent() { return Math.min(100, (this.step / 5) * 100); },
        get canGoBack() {
            if (this.authenticated) return this.step > 2;
            return this.step > 1;
        },

        init() {
            if (this.authenticated && this.step < 2) this.step = 2;
            this.hydrateFromPayload(cfg.wizardPayload);
            this.$nextTick(() => {
                if (this.step === 4 && this.paymentMethod === 'card') this.mountStripeIfNeeded();
            });
        },

        hydrateFromPayload(data) {
            if (!data) return;
            if (data.courses) this.courses = data.courses;
            if (data.students) this.students = data.students;
            if (data.selected_student_id !== undefined) {
                this.selectedStudentId = data.selected_student_id ? Number(data.selected_student_id) : null;
            }
            if (data.selected_course_id !== undefined) {
                this.selectedCourseId = data.selected_course_id ? Number(data.selected_course_id) : null;
            }
            if (data.selected_course) this.selectedCourse = data.selected_course;
            if (data.locked_course_id !== undefined) this.lockedCourseId = data.locked_course_id;
            if (data.payment_method) this.paymentMethod = data.payment_method;
        },

        fieldClass(name) {
            return this.formErrors[name] ? 'is-invalid' : '';
        },

        formatMoney(n) {
            if (n === null || n === undefined) return '—';
            return '$' + Number(n).toFixed(2);
        },

        get summaryStudent() {
            const s = this.students.find(st => st.id === this.selectedStudentId);
            return s ? s.name : '—';
        },
        get summaryCourseTitle() {
            const c = this.courses.find(cc => cc.id === this.selectedCourseId);
            return c ? c.title : (this.selectedCourse?.title || '—');
        },
        get summaryTotal() {
            const c = this.courses.find(cc => cc.id === this.selectedCourseId);
            const price = c ? c.price : this.selectedCourse?.price;
            return this.formatMoney(price);
        },

        selectExistingStudent(s) {
            this.newStudentMode = false;
            this.selectedStudentId = s.id;
        },
        startNewStudent() {
            this.newStudentMode = true;
            this.selectedStudentId = null;
        },
        selectCourse(id) {
            this.selectedCourseId = id;
        },

        onPaymentMethodChange() {
            this.formErrors = { ...this.formErrors, stripe_payment_intent_id: null };
            this.$nextTick(() => {
                if (this.paymentMethod === 'card') this.mountStripeIfNeeded();
            });
        },

        useSimulatedCard() {
            this.simulatedPi = 'pi_simulated_' + Date.now();
        },

        async mountStripeIfNeeded() {
            if (!this.stripeKey || this.paymentMethod !== 'card') return;
            if (this.stripeMounted && this.cardEl) return;
            await this.loadScript('https://js.stripe.com/v3/');
            this.stripe = window.Stripe(this.stripeKey);
            const el = this.stripe.elements();
            this.cardEl = el.create('card', { hidePostalCode: true });
            await new Promise((resolve) => {
                this.$nextTick(() => {
                    const dom = document.getElementById('card-element');
                    if (dom && dom.childElementCount === 0) {
                        this.cardEl.mount('#card-element');
                    }
                    this.stripeMounted = true;
                    resolve();
                });
            });
        },

        loadScript(src) {
            return new Promise((resolve, reject) => {
                if (document.querySelector('script[src="' + src + '"]')) return resolve();
                const s = document.createElement('script');
                s.src = src;
                s.onload = resolve;
                s.onerror = reject;
                document.body.appendChild(s);
            });
        },

        /** Tras login/registro el servidor rota la sesión; el meta debe coincidir con el token actual. */
        syncCsrfFromResponse(json) {
            if (!json || !json.csrf_token) return;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', json.csrf_token);
        },

        validateClient() {
            this.formErrors = {};
            this.globalError = '';
            if (this.step === 1 && !this.authenticated) {
                if (this.userType === 'existing') {
                    if (!this.emailLogin) this.formErrors.email_login = ['El correo es obligatorio'];
                    if (!this.passwordLogin) this.formErrors.password_login = ['La contraseña es obligatoria'];
                } else {
                    if (!this.name) this.formErrors.name = ['Requerido'];
                    if (!this.emailReg) this.formErrors.email = ['Requerido'];
                    if (!this.whatsapp) this.formErrors.whatsapp = ['Requerido'];
                    if (!this.password) this.formErrors.password = ['Requerido'];
                    if (this.password !== this.passwordConfirmation) this.formErrors.password_confirmation = ['No coinciden'];
                }
            }
            if (this.step === 2) {
                if (this.newStudentMode) {
                    if (!this.newStudentName) this.formErrors.student_name = ['Requerido'];
                    if (!this.newStudentBirthdate) this.formErrors.student_birthdate = ['Requerido'];
                } else if (!this.selectedStudentId) {
                    this.formErrors.selected_student = ['Selecciona o crea un estudiante'];
                }
            }
            if (this.step === 3) {
                if (!this.selectedCourseId) this.formErrors.selected_course = ['Elige un programa'];
                else {
                    const c = this.courses.find(cc => cc.id === this.selectedCourseId);
                    if (c && !c.can_enroll) this.formErrors.selected_course = [c.enroll_error || 'No disponible'];
                }
            }
            if (this.step === 4) {
                if (this.paymentMethod === 'card') {
                    let pm = '';
                    if (this.stripeKey) {
                        /* se rellena en goNext con createPaymentMethod */
                    } else if (!this.simulatedPi) {
                        this.formErrors.stripe_payment_intent_id = ['Usa el botón de simulación o configura Stripe'];
                    }
                }
            }
            if (this.step === 5) {
                if (!this.termsAccepted) this.formErrors.terms = ['Debes aceptar los términos'];
            }
            return Object.keys(this.formErrors).length === 0;
        },

        buildFormDataForStep() {
            const fd = new FormData();
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fd.append('_token', token);
            fd.append('wizard_json', '1');
            fd.append('current_step', String(this.step));

            if (this.step === 1) {
                fd.append('user_type', this.userType);
                if (this.userType === 'existing') {
                    fd.append('email_login', this.emailLogin);
                    fd.append('password_login', this.passwordLogin);
                } else {
                    fd.append('name', this.name);
                    fd.append('email', this.emailReg);
                    fd.append('dial_code', this.dialCode);
                    fd.append('whatsapp', this.whatsapp);
                    fd.append('password', this.password);
                    fd.append('password_confirmation', this.passwordConfirmation);
                }
            }
            if (this.step === 2) {
                if (this.selectedStudentId && !this.newStudentMode) {
                    fd.append('selected_student', String(this.selectedStudentId));
                } else {
                    fd.append('student_name', this.newStudentName);
                    fd.append('student_birthdate', this.newStudentBirthdate);
                    fd.append('student_medical_notes', this.newStudentMedical || '');
                }
            }
            if (this.step === 3) {
                fd.append('selected_course', String(this.selectedCourseId));
            }
            if (this.step === 4) {
                fd.append('payment_method', this.paymentMethod);
                if (this.paymentMethod === 'card') {
                    fd.append('stripe_payment_intent_id', this.pendingPaymentIntentId || this.simulatedPi || '');
                }
            }
            if (this.step === 5) {
                if (this.termsAccepted) fd.append('terms', '1');
            }
            return fd;
        },

        pendingPaymentIntentId: '',

        async goNext() {
            if (this.step === 4 && this.paymentMethod === 'card' && this.stripeKey) {
                this.pendingPaymentIntentId = '';
                this.processingStripe = true;
                try {
                    if (!this.stripe || !this.cardEl) {
                        await this.mountStripeIfNeeded();
                    }
                    if (!this.stripe || !this.cardEl) {
                        this.globalError = 'No se pudo inicializar el formulario de pago.';
                        return;
                    }
                    const intentRes = await fetch(this.paymentIntentUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ course_id: this.selectedCourseId }),
                    });
                    const intentJson = await intentRes.json().catch(() => ({}));
                    if (!intentRes.ok || !intentJson.success || !intentJson.client_secret) {
                        this.globalError = intentJson.message || 'No se pudo iniciar el pago con Stripe.';
                        return;
                    }
                    const { error, paymentIntent } = await this.stripe.confirmCardPayment(intentJson.client_secret, {
                        payment_method: { card: this.cardEl },
                    });
                    if (error) {
                        this.globalError = error.message;
                        return;
                    }
                    if (!paymentIntent || paymentIntent.status !== 'succeeded') {
                        this.globalError = 'El pago no fue confirmado. Intenta nuevamente.';
                        return;
                    }
                    this.pendingPaymentIntentId = paymentIntent.id;
                } finally {
                    this.processingStripe = false;
                }
            } else if (this.step === 4 && this.paymentMethod === 'card' && !this.stripeKey) {
                this.pendingPaymentIntentId = this.simulatedPi;
            }

            if (!this.validateClient()) return;

            this.loading = true;
            this.globalError = '';
            try {
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    body: this.buildFormDataForStep(),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const json = await res.json().catch(() => ({}));
                this.syncCsrfFromResponse(json);
                if (!res.ok || !json.success) {
                    this.formErrors = json.errors || {};
                    this.globalError = json.message || 'No se pudo continuar';
                    this.loading = false;
                    return;
                }
                if (json.data) this.hydrateFromPayload(json.data);
                this.formErrors = {};
                this.step = json.next_step;
                this.authenticated = json.data?.authenticated ?? this.authenticated;
                if (this.step === 4) this.$nextTick(() => this.mountStripeIfNeeded());
            } catch (e) {
                this.globalError = 'Error de red. Intenta de nuevo.';
            }
            this.loading = false;
        },

        goBack() {
            if (this.step <= 1) return;
            if (this.authenticated && this.step === 2) return;
            this.step--;
            this.formErrors = {};
        },

        async finalize() {
            if (!this.validateClient()) return;
            this.loading = true;
            this.globalError = '';
            try {
                const fd = this.buildFormDataForStep();
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const json = await res.json().catch(() => ({}));
                this.syncCsrfFromResponse(json);
                if (!res.ok || !json.success) {
                    this.formErrors = json.errors || {};
                    this.globalError = json.message || 'No se pudo completar';
                    this.loading = false;
                    return;
                }
                if (json.redirect_url) {
                    window.location.href = json.redirect_url;
                    return;
                }
            } catch (e) {
                this.globalError = 'Error de red.';
            }
            this.loading = false;
        },
    };
}
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
@endsection
