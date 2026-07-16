@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Inscripciones</title>
@endsection

@section('styles')
    <style>
        .wizard-share-box {
            border: 1px solid #cbd5e1;
            background:
                radial-gradient(circle at 10% 20%, rgba(186, 230, 253, 0.45), transparent 40%),
                linear-gradient(145deg, #f8fafc, #e2e8f0);
            border-radius: 0.8rem;
            padding: 0.95rem;
            margin-bottom: 0.95rem;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
        }

        .field-error {
            color: #dc3545;
            font-size: 0.82rem;
            margin-top: 0.25rem;
            display: block;
            animation: fadeInError 0.3s ease;
        }

        @keyframes fadeInError {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15) !important;
        }

        #enrollmentGlobalError {
            animation: fadeInError 0.3s ease;
        }

        .wizard-share-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.2rem;
            font-size: 0.92rem;
        }

        .wizard-share-subtitle {
            color: #475569;
            font-size: 0.8rem;
            margin-bottom: 0.55rem;
        }

        .wizard-share-box .form-control {
            background: #fff;
            border: 1px solid #bfdbfe;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .wizard-copy-btn {
            border: 1px solid #2563eb;
            background: linear-gradient(180deg, #3b82f6, #2563eb);
            font-weight: 600;
        }

        .wizard-copy-btn:hover {
            background: linear-gradient(180deg, #2563eb, #1d4ed8);
            border-color: #1d4ed8;
        }

        .bulk-toolbar {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .bulk-toolbar .bulk-count {
            font-weight: 600;
        }

        @keyframes rowHighlightFade {
            0% {
                background-color: #d1e7dd;
            }

            100% {
                background-color: transparent;
            }
        }

        .row-updated {
            animation: rowHighlightFade 1.4s ease-out;
        }

        .detail-quick-card {
            background: #f8fafc;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        #enrollmentDetailModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #dee2e6;
        }

        .dataTables-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .dataTables-actions .dt-button {
            border: 0;
            box-shadow: none;
        }

        #InscripcionesModal .modal-content {
            max-height: calc(100vh - 3.5rem);
            display: flex;
            flex-direction: column;
        }

        #InscripcionesModal #enrollmentForm {
            display: flex;
            flex-direction: column;
            min-height: 0;
            flex: 1;
        }

        #InscripcionesModal .modal-body {
            overflow-y: auto;
            min-height: 0;
        }

        #InscripcionesModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #dee2e6;
            z-index: 2;
        }

        #courseSelect+.select2 .select2-selection {
            min-height: 46px;
            border: 1px solid #cbd5e1;
            border-radius: 0.6rem;
            background: #f8fafc;
        }

        #courseSelect+.select2 .select2-selection__choice {
            background: #e0f2fe;
            border: 1px solid #7dd3fc;
            color: #0b1f3a !important;
            font-weight: 600;
            font-size: 0.78rem;
            max-width: 320px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border-radius: 999px;
            padding: 0.15rem 0.5rem;
        }

        #courseSelect+.select2 .select2-selection__choice .select2-selection__choice__display {
            color: #0b1f3a !important;
        }

        #courseSelect+.select2 .select2-selection__choice .select2-selection__choice__remove {
            color: #1e3a8a !important;
            margin-right: 4px;
        }

        #courseSelect+.select2 .select2-selection__choice .select2-selection__choice__remove:hover {
            color: #0b1f3a !important;
        }

        #InscripcionesModal .select2-container--default .select2-results__option {
            padding: 0.55rem 0.65rem;
        }

        .course-select2-option {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .course-select2-title {
            color: #0f172a;
            font-weight: 700;
            font-size: 0.88rem;
            line-height: 1.2;
        }

        .course-select2-meta {
            color: #334155;
            font-size: 0.76rem;
            line-height: 1.2;
        }

        #InscripcionesModal .select2-container--default .select2-results__option--highlighted[aria-selected] .course-select2-title,
        #InscripcionesModal .select2-container--default .select2-results__option--highlighted[aria-selected] .course-select2-meta {
            color: #0f172a;
        }

        .payment-proof-hint {
            background: #f8fafc;
            border: 1px solid #dbeafe;
            border-radius: 0.5rem;
            padding: 0.6rem 0.75rem;
        }

        #totalDisplay {
            border-left: 4px solid #0d6efd;
        }

        #totalDisplay hr {
            opacity: 0.4;
        }

        #InscripcionesModal .select2-results__option[aria-disabled="true"],
        #InscripcionesModal .select2-results__option--disabled {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="modal fade" id="InscripcionesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <form method="POST" action="{{ route('enrollment.store') }}" id="enrollmentForm" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-header">
                        <h6 class="mb-0 fw-bold">Registrar Inscripción</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div id="enrollmentGlobalError" class="alert alert-danger d-none mb-3"></div>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Representante</label>
                                <select name="user_id" id="userSelect" class="form-control select2">
                                    <option value="">-- Seleccionar representante --</option>
                                    @foreach ($parents as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} - {{ $user->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                                <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="toggleUserForm()">
                                    + Crear nuevo representante
                                </button>
                            </div>
                            <div class="col-md-6"></div>

                            <div id="userForm" class="col-12 d-none">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Nombre del representante</label>
                                        <input type="text" name="user[name]" class="form-control"
                                            placeholder="Ej: Maria Fernanda Perez">
                                        @error('user.name')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Correo del representante</label>
                                        <input type="email" name="user[email]" class="form-control"
                                            placeholder="Ej: madre@email.com">
                                        @error('user.email')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">WhatsApp del representante</label>
                                        <div class="input-group">
                                            <select name="user[dial_code]" class="form-select" style="max-width: 130px;">
                                                @include('partials.dialcode_create')
                                            </select>
                                            <input type="text" name="user[whatsapp]" class="form-control"
                                                placeholder="Ej: 4121234567">
                                        </div>
                                        @error('user.dial_code')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        @error('user.whatsapp')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Contrasena temporal</label>
                                        <input type="password" name="user[password]" class="form-control"
                                            placeholder="Ej: Temporal2026">
                                        @error('user.password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="form-label">Estudiante</label>
                                <select name="student_id" id="studentSelect" class="form-control select2">
                                    <option value="">-- Seleccionar estudiante --</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" data-user="{{ $student->user_id }}">
                                            {{ $student->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                                <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="toggleStudentForm()">
                                    + Crear nuevo estudiante
                                </button>
                            </div>
                            <div class="col-md-6"></div>

                            <div id="studentForm" class="col-12 d-none">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Nombre del estudiante</label>
                                        <input type="text" name="student[name]" class="form-control"
                                            placeholder="Ej: Sofia Martinez">
                                        @error('student.name')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Fecha de nacimiento</label>
                                        <input type="date" name="student[birthdate]" class="form-control">
                                        @error('student.birthdate')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Notas medicas (opcional)</label>
                                        <input type="text" name="student[medical_notes]" class="form-control"
                                            placeholder="Ej: Alergia al mani">
                                        @error('student.medical_notes')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label">Programa <span class="text-danger">*</span></label>
                                <select name="program_id" id="programSelect" class="form-control select2" required>
                                    <option value="">-- Seleccionar programa --</option>
                                    @foreach ($programs as $program)
                                        <option value="{{ $program->id }}" data-enrollment-fee="{{ $program->enrollment_fee }}">
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('program_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label">Sede de filtrado (opcional)</label>
                                <select id="branchFilterSelect" class="form-control select2">
                                    <option value="">-- Todas las sedes --</option>
                                    @foreach ($branches ?? [] as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label">Estado de pago</label>
                                <select name="payment_status" id="paymentStatusSelect" class="form-control">
                                    <option value="pending">Pendiente</option>
                                    <option value="paid">Pagado</option>
                                </select>
                                @error('payment_status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mt-3 d-none" id="accountSelectContainer">
                                <label class="form-label">Cuenta de Pago <span class="text-danger">*</span></label>
                                <select name="account_id" id="accountSelect" class="form-control">
                                    @foreach ($accounts ?? [] as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mt-3 d-none" id="referenceContainer">
                                <label class="form-label">Referencia / Observación</label>
                                <input type="text" name="reference" id="referenceInput" class="form-control" placeholder="Ej. Transacción 1234">
                                @error('reference')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold">Monto Inicial (Inscripción + 1er Mes)</label>
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="enrollment_fee_type" id="feeStandard" value="standard" checked>
                                        <label class="form-check-label" for="feeStandard">
                                            Monto sugerido (<span id="standardFeeLabel">$0.00</span>)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="enrollment_fee_type" id="feeCustom" value="custom">
                                        <label class="form-check-label" for="feeCustom">
                                            Monto total personalizado
                                        </label>
                                    </div>
                                    <div class="d-none" id="customFeeInputContainer" style="width: 180px;">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="custom_total_amount" id="customTotalAmount" class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00" value="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label">Clases <span class="text-danger">*</span></label>
                                <select name="course_ids[]" id="courseSelect" class="form-control" multiple required>
                                    @foreach ($courses as $course)
                                        @php
                                            $daysEs = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
                                            $scheduleText = '';
                                            if ($course->relationLoaded('classes') && $course->classes->isNotEmpty()) {
                                                $scheduleGroups = $course->classes->groupBy(function ($c) use ($daysEs) {
                                                    return $daysEs[\Carbon\Carbon::parse($c->date)->dayOfWeek];
                                                });
                                                $scheduleText = $scheduleGroups->map(function ($group, $day) {
                                                    $times = $group->map(function ($c) {
                                                        return \Carbon\Carbon::parse($c->start_time)->format('H:i') . '-' . \Carbon\Carbon::parse($c->end_time)->format('H:i');
                                                    })->unique()->join(', ');
                                                    return $day . ' ' . $times;
                                                })->join(' | ');
                                            }
                                            $spotsLeft = max(0, (int) $course->capacity - (int) $course->enrollments_count);
                                            $branchName = $course->branch->name ?? 'N/A';
                                            $monthlyFeeText = number_format($course->monthly_fee, 2);
                                        @endphp
                                        <option
                                            value="{{ $course->id }}"
                                            data-program-id="{{ $course->program_id }}"
                                            data-branch-id="{{ $course->branch_id }}"
                                            data-monthly-fee="{{ $course->monthly_fee }}"
                                            data-title="{{ e($course->title) }}"
                                            data-branch="{{ e($branchName) }}"
                                            data-spots-left="{{ $spotsLeft }}"
                                            data-schedule="{{ e($scheduleText) }}"
                                            data-monthly-fee-text="{{ $monthlyFeeText }}">
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <small id="courseSelectHelp" class="text-muted d-block mt-1">Selecciona un programa para habilitar sus clases.</small>
                                @error('course_ids')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('course_ids.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-2">
                                <div class="alert alert-info d-none" id="totalDisplay">
                                    <div class="d-flex justify-content-between">
                                        <span>Inscripción:</span>
                                        <span id="enrollmentFeeDisplay">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Mensualidades:</span>
                                        <span id="monthlyFeesDisplay">$0.00</span>
                                    </div>
                                    <hr class="my-1">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span id="totalAmountDisplay">$0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_free_trial" value="1" id="isFreeTrial">
                                    <label class="form-check-label" for="isFreeTrial">
                                        Clase de prueba gratuita
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="image_consent_accepted" value="1" id="imageConsentAccepted">
                                    <label class="form-check-label" for="imageConsentAccepted">
                                        Consentimiento de uso de imagen
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="form-label">Comprobante de pago</label>
                                <input id="paymentReceiptInput" type="file" name="payment_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                @error('payment_receipt')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small id="paymentReceiptHelp" class="text-muted d-block">Formatos: JPG, PNG, PDF. Max 6 MB.</small>
                                <div class="payment-proof-hint mt-2 small text-muted">
                                    Si marcas pago como <strong>Pagado</strong> y no es clase gratuita, se recomienda adjuntar comprobante para que quede reflejado en finanzas.
                                </div>
                            </div>
                            <div class="col-md-6"></div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="enrollmentSubmitBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="enrollmentSubmitSpinner" role="status"></span>
                            Guardar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>Inscripciones</h5>
                    <span class="text-muted">Gestion y seguimiento de Inscripciones activas en el sistema</span>
                </div>
                <div>
                    <a href="javascript:void(0);" class="btn btn-inverse btn-sm" data-bs-toggle="modal"
                        data-bs-target="#InscripcionesModal"><i class="far fa-address-book text-light"></i> Registrar
                        Inscripción</a>
                </div>
            </div>
            <div class="card-block">
                <div class="wizard-share-box">
                    <div class="wizard-share-title">
                        <i class="fas fa-wand-magic-sparkles me-1"></i>
                        Enlace de Inscripción para compartir
                    </div>
                    <div class="wizard-share-subtitle">
                        Copia y envia este enlace por WhatsApp o correo para que los usuarios se inscriban directamente.
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-9">
                            <label for="wizardEnrollmentLink" class="form-label mb-1">Wizard de Inscripción</label>
                            <input id="wizardEnrollmentLink" type="text" class="form-control"
                                value="{{ route('enrollment.wizard') }}" readonly>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button type="button" id="copyEnrollmentWizardLink" class="btn btn-primary wizard-copy-btn w-100">
                                <i class="fas fa-copy"></i> Copiar enlace
                            </button>
                        </div>
                    </div>
                </div>

                <div id="bulkToolbar" class="bulk-toolbar d-none">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <span class="bulk-count" id="selectedCounter">0 seleccionados</span>
                        </div>
                        <div class="col-md-4">
                            <select id="bulkPaymentAction" class="form-control form-control-sm">
                                <option value="">Pago: sin cambios</option>
                                <option value="paid">Marcar pago como pagado</option>
                                <option value="pending">Marcar pago como pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button id="applyBulkChanges" type="button" class="btn btn-sm btn-primary" disabled>
                                Aplicar cambios
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="enrollmentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input type="checkbox" id="selectAllEnrollments">
                                </th>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Representante</th>
                                <th>Programa</th>
                                <th>Clases</th>
                                <th>Pago</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollments as $enrollment)
                                <tr data-enrollment-id="{{ $enrollment->id }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="enrollment-checkbox" value="{{ $enrollment->id }}">
                                    </td>
                                    <td>{{ $enrollment->id }}</td>
                                    <td class="enrollment-student">{{ $enrollment->student->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($enrollment->student->birthdate)->age }} años</td>
                                    <td class="enrollment-parent">{{ $enrollment->student->user->name }}</td>
                                    <td class="enrollment-program">{{ $enrollment->program->name ?? 'N/A' }}</td>
                                    <td class="enrollment-courses">
                                        <span class="badge bg-info" title="{{ $enrollment->courses->pluck('title')->join(', ') }}">
                                            {{ $enrollment->courses->count() }} Clases
                                        </span>
                                    </td>
                                    <td class="enrollment-payment" data-payment-status="{{ $enrollment->payment_status }}">
                                        @if ($enrollment->is_free_trial)
                                            <span class="badge bg-warning text-dark">Clase de prueba</span>
                                        @elseif ($enrollment->payment_status === 'paid')
                                            <span class="badge bg-success">Pagado</span>
                                        @else
                                            <span class="badge bg-secondary">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('enrollment.show', $enrollment) }}"
                                            class="btn btn-sm btn-info view-enrollment-btn"
                                            data-url="{{ route('enrollment.show', $enrollment) }}"><i class="far fa-eye"></i> Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="enrollmentDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="enrollmentDetailForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h6 class="mb-0">Detalle de Inscripción</h6>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="detailEnrollmentId" value="">

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Estudiante</label>
                                <input type="text" class="form-control" id="detailStudentName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Representante</label>
                                <input type="text" class="form-control" id="detailParentName" readonly>
                            </div>
                        </div>

                        <div class="detail-quick-card mb-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Programa</label>
                                    <input type="text" class="form-control" id="detailProgramName" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Clases</label>
                                    <input type="text" class="form-control" id="detailCoursesList" readonly>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Sede</label>
                                    <input type="text" class="form-control" id="detailBranchName" readonly>
                                </div>
                                <div class="col-md-6"></div>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Estado de pago</label>
                                <select name="payment_status" id="detailPaymentStatus" class="form-control">
                                    <option value="pending">Pendiente</option>
                                    <option value="paid">Pagado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">
                                    Para ver horarios, sesiones de clase y datos completos, abre el detalle completo.
                                </div>
                            </div>
                        </div>

                        <div id="detailFormError" class="alert alert-danger mt-3 d-none"></div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <a href="#" id="openFullDetail" class="btn btn-sm btn-outline-secondary">Abrir detalle completo</a>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" id="saveDetailChanges">Guardar cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        const bulkUpdateUrl = '{{ route('enrollment.bulk-update') }}';
        const updateUrlTemplate = '{{ route('enrollment.update', ['enrollment' => '__ID__']) }}';
        const storeUrl = '{{ route('enrollment.store') }}';
        const csrfToken = '{{ csrf_token() }}';

        function filterStudentsByParent() {
            const parentId = $('#userSelect').val();
            $('#studentSelect option').each(function() {
                const optionParentId = $(this).data('user');
                if (!$(this).val()) return;
                if (!parentId || Number(optionParentId) === Number(parentId)) {
                    $(this).prop('disabled', false).show();
                } else {
                    $(this).prop('disabled', true).hide();
                }
            });
            $('#studentSelect').val(null).trigger('change');
        }

        function toggleStudentForm() {
            document.getElementById('studentForm').classList.toggle('d-none');
            document.getElementById('studentSelect').value = '';
        }

        function toggleUserForm() {
            document.getElementById('userForm').classList.toggle('d-none');
            document.getElementById('userSelect').value = '';
            filterStudentsByParent();
        }

        function paymentBadgeHtml(paymentStatus) {
            if (paymentStatus === 'paid') {
                return '<span class="badge bg-success">Pagado</span>';
            }
            return '<span class="badge bg-secondary">Pendiente</span>';
        }

        function coursesBadgeHtml(courses) {
            if (!courses || !courses.length) {
                return '<span class="badge bg-info">0 Clases</span>';
            }
            const titles = courses.map(function(c) { return c.title; }).join(', ');
            return '<span class="badge bg-info" title="' + titles + '">' + courses.length + ' Clases</span>';
        }

        function flashUpdatedRow(row) {
            row.addClass('row-updated');
            setTimeout(function() {
                row.removeClass('row-updated');
            }, 1450);
        }

        function updateRowVisual(enrollment) {
            const row = $('#enrollmentsTable tbody tr[data-enrollment-id="' + enrollment.id + '"]');
            if (!row.length) {
                return;
            }

            row.find('.enrollment-program').text(enrollment.program_name || '');
            row.find('.enrollment-courses').html(coursesBadgeHtml(enrollment.courses));
            row.find('.enrollment-payment')
                .attr('data-payment-status', enrollment.payment_status)
                .html(paymentBadgeHtml(enrollment.payment_status));
            flashUpdatedRow(row);
        }

        function setActionState() {
            const selectedCount = selectedIds.size;
            $('#selectedCounter').text(selectedCount + ' seleccionados');
            $('#bulkToolbar').toggleClass('d-none', selectedCount === 0);
            $('#applyBulkChanges').prop('disabled', selectedCount === 0);
        }

        function setSelectAllState() {
            const checkboxes = $('#enrollmentsTable tbody .enrollment-checkbox:visible');
            if (!checkboxes.length) {
                $('#selectAllEnrollments').prop('checked', false);
                return;
            }

            const checkedVisible = checkboxes.filter(':checked').length;
            $('#selectAllEnrollments').prop('checked', checkedVisible === checkboxes.length);
        }

        function applySelectionsToCurrentPage() {
            $('#enrollmentsTable tbody .enrollment-checkbox').each(function() {
                const id = Number($(this).val());
                $(this).prop('checked', selectedIds.has(id));
            });

            setSelectAllState();
            setActionState();
        }

        async function loadEnrollmentDetail(url) {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('No se pudo cargar el detalle de la Inscripción.');
            }

            return response.json();
        }

        function hydrateDetailModal(payload, url) {
            const enrollment = payload.enrollment;

            $('#detailEnrollmentId').val(enrollment.id);
            $('#detailStudentName').val(enrollment.student_name || '');
            $('#detailParentName').val(enrollment.parent_name || '');
            $('#detailPaymentStatus').val(enrollment.payment_status);
            $('#detailProgramName').val(enrollment.program_name || '');

            if (enrollment.courses && enrollment.courses.length) {
                $('#detailCoursesList').val(enrollment.courses.map(function(c) { return c.title; }).join(', '));
                $('#detailBranchName').val(enrollment.courses[0].branch_name || '');
            } else {
                $('#detailCoursesList').val('');
                $('#detailBranchName').val('');
            }

            const formAction = updateUrlTemplate.replace('__ID__', enrollment.id);
            $('#enrollmentDetailForm').attr('action', formAction);
            $('#openFullDetail').attr('href', url);
            $('#detailFormError').addClass('d-none').text('');
        }

        function exportColumnsIndexes() {
            return [1, 2, 3, 4, 5, 6, 7];
        }

        function filterCourses() {
            const programId = $('#programSelect').val();
            const branchId = $('#branchFilterSelect').val();
            const selectedCourseIds = ($('#courseSelect').val() || []).map(function(v) {
                return String(v);
            });

            $('#courseSelect option').each(function() {
                const optionProgramId = String($(this).data('program-id'));
                const optionBranchId = String($(this).data('branch-id'));

                const programMatches = !!programId && optionProgramId === String(programId);
                const branchMatches = !branchId || optionBranchId === String(branchId);
                const keepVisible = programMatches && branchMatches;

                $(this).prop('disabled', !keepVisible);

                if (!keepVisible && selectedCourseIds.includes(String($(this).val()))) {
                    $(this).prop('selected', false);
                }
            });

            $('#courseSelect').trigger('change');
            
            let helpText = '';
            if (!programId) {
                helpText = 'Selecciona un programa para habilitar sus clases.';
            } else if (branchId) {
                helpText = 'Selecciona una o varias clases del programa y de la sede seleccionada.';
            } else {
                helpText = 'Selecciona una o varias clases del programa.';
            }
            $('#courseSelectHelp').text(helpText);

            recalculateTotal();
        }

        let studentHasPaidFee = false;

        async function checkEnrollmentFee() {
            const studentId = $('#studentSelect').val();
            const programId = $('#programSelect').val();

            if (!studentId || !programId) {
                studentHasPaidFee = false;
                recalculateTotal();
                return;
            }

            try {
                const response = await fetch(`{{ route('enrollment.check-fee') }}?student_id=${studentId}&program_id=${programId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                studentHasPaidFee = !!data.has_paid;
            } catch (e) {
                console.error(e);
                studentHasPaidFee = false;
            }
            recalculateTotal();
        }

        function recalculateTotal() {
            const programSelect = $('#programSelect');
            const selectedOption = programSelect.find('option:selected');
            let standardFee = selectedOption.data('enrollment-fee') ? parseFloat(selectedOption.data('enrollment-fee')) : 0;

            if (studentHasPaidFee) {
                standardFee = 0;
            }

            let monthlyFeesTotal = 0;
            $('#courseSelect option:selected').each(function() {
                const fee = $(this).data('monthly-fee') ? parseFloat($(this).data('monthly-fee')) : 0;
                monthlyFeesTotal += fee;
            });

            // Update standard fee label (now it shows standard total: inscripción + monthly fees)
            const standardTotal = standardFee + monthlyFeesTotal;
            $('#standardFeeLabel').text('$' + standardTotal.toFixed(2));

            let total = standardTotal;
            let enrollmentFee = standardFee;

            if ($('#feeCustom').is(':checked')) {
                total = parseFloat($('#customTotalAmount').val()) || 0;
                enrollmentFee = Math.max(0, total - monthlyFeesTotal);
            }

            if (programSelect.val() || monthlyFeesTotal > 0) {
                $('#totalDisplay').removeClass('d-none');
            }

            $('#enrollmentFeeDisplay').text('$' + enrollmentFee.toFixed(2));
            $('#monthlyFeesDisplay').text('$' + monthlyFeesTotal.toFixed(2));
            $('#totalAmountDisplay').text('$' + total.toFixed(2));
        }

        function updatePaymentReceiptState() {
            const isFreeTrial = $('#isFreeTrial').is(':checked');
            const paymentStatus = $('#paymentStatusSelect').val();
            const receiptInput = $('#paymentReceiptInput');
            const helpEl = $('#paymentReceiptHelp');

            if (isFreeTrial) {
                receiptInput.prop('required', false).prop('disabled', true).val('');
                $('#paymentStatusSelect').val('paid');
                helpEl.text('No se requiere comprobante para clase de prueba gratuita.');
                $('#accountSelectContainer').addClass('d-none');
                $('#accountSelect').prop('required', false);
                $('#referenceContainer').addClass('d-none');
                $('#referenceInput').val('');
                return;
            }

            const mustAttach = paymentStatus === 'paid';
            receiptInput.prop('required', mustAttach).prop('disabled', false);
            helpEl.text(mustAttach ?
                'Pago marcado como pagado: adjunta el comprobante (obligatorio).' :
                'Formatos: JPG, PNG, PDF. Max 6 MB.');

            if (mustAttach) {
                $('#accountSelectContainer').removeClass('d-none');
                $('#accountSelect').prop('required', true);
                $('#referenceContainer').removeClass('d-none');
            } else {
                $('#accountSelectContainer').addClass('d-none');
                $('#accountSelect').prop('required', false);
                $('#referenceContainer').addClass('d-none');
                $('#referenceInput').val('');
            }
        }

        const selectedIds = new Set();
        let detailModal;
        let table;

        function formatCourseOption(state) {
            if (!state.id) {
                return state.text;
            }

            const option = $(state.element);
            const title = option.data('title') || state.text || 'Clase';
            const branch = option.data('branch') || 'N/A';
            const monthlyFee = option.data('monthly-fee-text') || '0.00';
            const spotsLeft = option.data('spots-left') ?? '0';
            const schedule = option.data('schedule') || '';

            const meta = schedule
                ? `${branch} | Mens: $${monthlyFee} | Cupos: ${spotsLeft} | ${schedule}`
                : `${branch} | Mens: $${monthlyFee} | Cupos: ${spotsLeft}`;

            return $(
                `<div class="course-select2-option">
                    <span class="course-select2-title"></span>
                    <span class="course-select2-meta"></span>
                </div>`
            )
            .find('.course-select2-title').text(title).end()
            .find('.course-select2-meta').text(meta).end();
        }

        function formatCourseSelection(state) {
            if (!state.id) {
                return state.text;
            }

            const option = $(state.element);
            const title = option.data('title') || state.text || 'Clase';
            const branch = option.data('branch') || 'N/A';
            return `${title} · ${branch}`;
        }

        function clearValidationErrors() {
            $('#enrollmentForm .field-error').remove();
            $('#enrollmentForm .is-invalid').removeClass('is-invalid');
            $('#enrollmentGlobalError').addClass('d-none').html('');
        }

        function showValidationErrors(errors) {
            clearValidationErrors();

            const fieldMap = {
                'user_id': '#userSelect',
                'student_id': '#studentSelect',
                'program_id': '#programSelect',
                'course_ids': '#courseSelect',
                'course_ids.*': '#courseSelect',
                'payment_status': '#paymentStatusSelect',
                'payment_receipt': '#paymentReceiptInput',
                'is_free_trial': '#isFreeTrial',
                'image_consent_accepted': '#imageConsentAccepted',
                'user.name': 'input[name="user[name]"]',
                'user.email': 'input[name="user[email]"]',
                'user.password': 'input[name="user[password]"]',
                'user.dial_code': 'select[name="user[dial_code]"]',
                'user.whatsapp': 'input[name="user[whatsapp]"]',
                'student.name': 'input[name="student[name]"]',
                'student.birthdate': 'input[name="student[birthdate]"]',
                'student.medical_notes': 'input[name="student[medical_notes]"]',
            };

            let unmappedErrors = [];

            Object.keys(errors).forEach(function(field) {
                const messages = errors[field];
                const selector = fieldMap[field];

                if (selector) {
                    const $el = $(selector);
                    if ($el.length) {
                        $el.addClass('is-invalid');

                        // For select2 elements, highlight the select2 container
                        if ($el.hasClass('select2-hidden-accessible')) {
                            $el.next('.select2').find('.select2-selection').addClass('is-invalid');
                        }

                        const errorHtml = messages.map(function(msg) {
                            return '<span class="field-error">' + $('<span>').text(msg).html() + '</span>';
                        }).join('');

                        // Insert after the element's parent (for input-groups) or after element
                        const $parent = $el.closest('.input-group');
                        if ($parent.length) {
                            $parent.after(errorHtml);
                        } else if ($el.hasClass('select2-hidden-accessible')) {
                            $el.next('.select2').after(errorHtml);
                        } else {
                            $el.after(errorHtml);
                        }
                    } else {
                        unmappedErrors = unmappedErrors.concat(messages);
                    }
                } else {
                    unmappedErrors = unmappedErrors.concat(messages);
                }
            });

            if (unmappedErrors.length) {
                const globalHtml = unmappedErrors.map(function(msg) {
                    return '<div>' + $('<span>').text(msg).html() + '</div>';
                }).join('');
                $('#enrollmentGlobalError').removeClass('d-none').html(globalHtml);
            }

            // Scroll to the first error within the modal body
            const $firstError = $('#enrollmentForm .is-invalid, #enrollmentForm .field-error').first();
            if ($firstError.length) {
                const $modalBody = $('#InscripcionesModal .modal-body');
                const scrollTo = $firstError.offset().top - $modalBody.offset().top + $modalBody.scrollTop() - 20;
                $modalBody.animate({ scrollTop: Math.max(0, scrollTo) }, 300);
            }
        }

        $(document).ready(function() {
            $('#InscripcionesModal').on('shown.bs.modal', function() {
                $('.select2').select2({
                    dropdownParent: $('#InscripcionesModal'),
                    allowClear: true
                });

                if (!$('#courseSelect').hasClass('select2-hidden-accessible')) {
                    $('#courseSelect').select2({
                        dropdownParent: $('#InscripcionesModal'),
                        placeholder: 'Selecciona una o varias clases',
                        closeOnSelect: false,
                        width: '100%',
                        templateResult: formatCourseOption,
                        templateSelection: formatCourseSelection,
                        escapeMarkup: function(markup) {
                            return markup;
                        }
                    });
                }

                filterCourses();
                updatePaymentReceiptState();
            });

            $('#InscripcionesModal').on('hidden.bs.modal', function() {
                $('#enrollmentForm')[0].reset();
                $('#userForm').addClass('d-none');
                $('#studentForm').addClass('d-none');
                $('#totalDisplay').addClass('d-none');
                $('#courseSelect option').prop('disabled', true).prop('selected', false);
                $('#courseSelect').val(null).trigger('change');
                $('#programSelect').val(null).trigger('change');
                $('#branchFilterSelect').val(null).trigger('change');
                $('#studentSelect').val(null).trigger('change');
                $('#userSelect').val(null).trigger('change');
                $('#feeStandard').prop('checked', true);
                $('#customTotalAmount').val('0.00');
                $('#customFeeInputContainer').addClass('d-none');
                clearValidationErrors();
                updatePaymentReceiptState();
            });

            // AJAX form submission with inline validation
            $('#enrollmentForm').on('submit', async function(event) {
                event.preventDefault();

                clearValidationErrors();

                const $btn = $('#enrollmentSubmitBtn');
                const $spinner = $('#enrollmentSubmitSpinner');
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');

                try {
                    const formData = new FormData(this);

                    const response = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            showValidationErrors(data.errors);
                        } else {
                            const msg = data.message || 'Ocurrio un error al registrar la Inscripción.';
                            $('#enrollmentGlobalError').removeClass('d-none').text(msg);
                        }
                        return;
                    }

                    // Success
                    const modal = bootstrap.Modal.getInstance(document.getElementById('InscripcionesModal'));
                    if (modal) modal.hide();

                    await Swal.fire({
                        icon: 'success',
                        text: data.message || 'Inscripción registrada correctamente.',
                        confirmButtonText: 'Continuar',
                        confirmButtonColor: '#28a745'
                    });

                    window.location.href = data.redirect || '{{ url('enrollment') }}';

                } catch (error) {
                    $('#enrollmentGlobalError').removeClass('d-none').text(
                        'Error de conexion. Verifica tu red e intenta de nuevo.'
                    );
                } finally {
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');
                }
            });

            $('#copyEnrollmentWizardLink').on('click', function() {
                const link = $('#wizardEnrollmentLink').val();

                navigator.clipboard.writeText(link).then(function() {
                    Swal.fire({
                        icon: 'success',
                        text: 'Enlace del wizard copiado al portapapeles.',
                        confirmButtonColor: '#198754'
                    });
                }).catch(function() {
                    Swal.fire({
                        icon: 'error',
                        text: 'No fue posible copiar el enlace automaticamente.'
                    });
                });
            });

            $('#userSelect').on('change', function() {
                filterStudentsByParent();
            });

            $('#programSelect, #branchFilterSelect').on('change', function() {
                filterCourses();
                checkEnrollmentFee();
            });

            $('#studentSelect').on('change', function() {
                checkEnrollmentFee();
            });

            $('#courseSelect').on('change', function() {
                recalculateTotal();
            });

            $('input[name="enrollment_fee_type"]').on('change', function() {
                if ($('#feeCustom').is(':checked')) {
                    $('#customFeeInputContainer').removeClass('d-none');
                } else {
                    $('#customFeeInputContainer').addClass('d-none');
                }
                recalculateTotal();
            });

            $('#customTotalAmount').on('input', function() {
                recalculateTotal();
            });

            $('#isFreeTrial, #paymentStatusSelect').on('change', function() {
                updatePaymentReceiptState();
            });

            table = $('#enrollmentsTable').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"fB>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3"lip>',
                order: [
                    [1, 'desc']
                ],
                pageLength: 10,
                buttons: [{
                        extend: 'copyHtml5',
                        text: '<i class="fas fa-copy"></i> Copiar',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: exportColumnsIndexes()
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: exportColumnsIndexes()
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: exportColumnsIndexes()
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: exportColumnsIndexes()
                        },
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function(doc) {
                            doc.pageMargins = [12, 12, 12, 12];
                            doc.defaultStyle.fontSize = 9;
                            doc.styles.tableHeader.fontSize = 10;
                            doc.styles.tableHeader.alignment = 'left';

                            const tableBody = doc.content[1].table.body;
                            const columnCount = tableBody[0].length;
                            doc.content[1].table.widths = Array(columnCount).fill('*');
                            doc.content[1].layout = {
                                hLineWidth: function() {
                                    return 0.6;
                                },
                                vLineWidth: function() {
                                    return 0.6;
                                },
                                hLineColor: function() {
                                    return '#d1d5db';
                                },
                                vLineColor: function() {
                                    return '#d1d5db';
                                },
                                paddingLeft: function() {
                                    return 6;
                                },
                                paddingRight: function() {
                                    return 6;
                                }
                            };
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: exportColumnsIndexes()
                        }
                    }
                ],
                columnDefs: [{
                    targets: [0, 8],
                    orderable: false,
                    searchable: false
                }],
                language: {
                    search: 'Buscar:',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    paginate: {
                        previous: 'Anterior',
                        next: 'Siguiente'
                    }
                }
            });

            table.buttons().container().addClass('dataTables-actions');

            detailModal = new bootstrap.Modal(document.getElementById('enrollmentDetailModal'));

            $('#enrollmentsTable').on('change', '.enrollment-checkbox', function() {
                const id = Number($(this).val());

                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }

                setSelectAllState();
                setActionState();
            });

            $('#selectAllEnrollments').on('change', function() {
                const shouldCheck = $(this).is(':checked');

                $('#enrollmentsTable tbody .enrollment-checkbox:visible').each(function() {
                    const id = Number($(this).val());
                    $(this).prop('checked', shouldCheck);

                    if (shouldCheck) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });

                setActionState();
            });

            table.on('draw', function() {
                applySelectionsToCurrentPage();
            });

            $('#applyBulkChanges').on('click', async function() {
                const paymentStatus = $('#bulkPaymentAction').val();

                if (!selectedIds.size) {
                    return;
                }

                if (!paymentStatus) {
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'warning',
                            text: 'Selecciona una accion de pago para continuar.',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert('Selecciona una accion de pago para continuar.');
                    }
                    return;
                }

                let confirmed = true;
                if (typeof Swal !== 'undefined') {
                    const result = await Swal.fire({
                        icon: 'question',
                        title: 'Aplicar cambios masivos',
                        text: 'Se actualizara el estado de pago de ' + selectedIds.size + ' Inscripciones.',
                        showCancelButton: true,
                        confirmButtonText: 'Si, aplicar',
                        cancelButtonText: 'Cancelar'
                    });
                    confirmed = result.isConfirmed;
                } else {
                    confirmed = confirm('Se actualizara el estado de pago de ' + selectedIds.size + ' Inscripciones.');
                }

                if (!confirmed) {
                    return;
                }

                $(this).prop('disabled', true).text('Aplicando...');

                try {
                    const response = await fetch(bulkUpdateUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            enrollment_ids: Array.from(selectedIds),
                            payment_status: paymentStatus || null
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'No se pudieron aplicar los cambios.');
                    }

                    (data.enrollments || []).forEach(updateRowVisual);

                    selectedIds.clear();
                    $('#bulkPaymentAction').val('');
                    applySelectionsToCurrentPage();

                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'success',
                            text: 'Cambios aplicados correctamente.',
                            confirmButtonText: 'Continuar'
                        });
                    }
                } catch (error) {
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'error',
                            text: error.message || 'Error al aplicar cambios masivos.',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(error.message || 'Error al aplicar cambios masivos.');
                    }
                } finally {
                    $('#applyBulkChanges').prop('disabled', selectedIds.size === 0).text('Aplicar cambios');
                }
            });

            $('#enrollmentsTable').on('click', '.view-enrollment-btn', async function(event) {
                event.preventDefault();
                const url = $(this).data('url');

                try {
                    const payload = await loadEnrollmentDetail(url);
                    hydrateDetailModal(payload, url);
                    detailModal.show();
                } catch (error) {
                    window.location.href = url;
                }
            });

            $('#enrollmentDetailForm').on('submit', async function(event) {
                event.preventDefault();

                const form = $(this);
                const action = form.attr('action');
                const submitButton = $('#saveDetailChanges');

                submitButton.prop('disabled', true).text('Guardando...');

                try {
                    const response = await fetch(action, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            payment_status: $('#detailPaymentStatus').val(),
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'No se pudo actualizar la Inscripción.');
                    }

                    updateRowVisual(data.enrollment);
                    detailModal.hide();
                } catch (error) {
                    $('#detailFormError').removeClass('d-none').text(error.message ||
                        'No se pudo actualizar la Inscripción.');
                } finally {
                    submitButton.prop('disabled', false).text('Guardar cambios');
                }
            });

            applySelectionsToCurrentPage();
        });
    </script>
@endsection
