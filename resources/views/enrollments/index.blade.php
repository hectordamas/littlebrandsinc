@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Inscripciones</title>
@endsection

@section('styles')
    <style>
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
    </style>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="modal fade" id="inscripcionesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form method="POST" action="{{ route('enrollment.store') }}" id="enrollmentForm">
                    @csrf

                    <div class="modal-header">
                        <h6 class="mb-0 fw-bold">Registrar Inscripción</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
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
                                        <input type="text" name="user[name]" class="form-control" placeholder="Nombre">
                                        @error('user.name')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" name="user[email]" class="form-control" placeholder="Email">
                                        @error('user.email')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="user[whatsapp]" class="form-control"
                                            placeholder="WhatsApp">
                                        @error('user.whatsapp')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" name="user[password]" class="form-control"
                                            placeholder="Contraseña">
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
                                        <input type="text" name="student[name]" class="form-control"
                                            placeholder="Nombre">
                                        @error('student.name')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" name="student[birthdate]" class="form-control">
                                        @error('student.birthdate')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="student[medical_notes]" class="form-control"
                                            placeholder="Notas médicas">
                                        @error('student.medical_notes')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Curso</label>
                                <select name="course_id" class="form-control select2">
                                    <option value="">-- Seleccionar Programa --</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">
                                            {{ $course->title }} ({{ max(0, $course->capacity - $course->enrollments_count) }} cupos)
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mt-3">
                                <label>Estado de pago</label>
                                <select name="payment_status" class="form-control">
                                    <option value="pending">Pendiente</option>
                                    <option value="paid">Pagado</option>
                                </select>
                                @error('payment_status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary">Guardar</button>
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
                    <span class="text-muted">Gestión y seguimiento de inscripciones activas en el sistema</span>
                </div>
                <div>
                    <a href="javascript:void(0);" class="btn btn-inverse btn-sm" data-bs-toggle="modal"
                        data-bs-target="#inscripcionesModal"><i class="far fa-address-book text-light"></i> Registrar
                        Inscripción</a>
                </div>
            </div>
            <div class="card-block">
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
                                <th>Curso</th>
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
                                    <td class="enrollment-course">{{ $enrollment->course->title }}</td>
                                    <td class="enrollment-payment" data-payment-status="{{ $enrollment->payment_status }}">
                                        @if ($enrollment->payment_status === 'paid')
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
                                    <label class="form-label text-muted">Curso inscrito</label>
                                    <input type="text" class="form-control" id="detailCourseTitle" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Sede</label>
                                    <input type="text" class="form-control" id="detailCourseBranch" readonly>
                                </div>
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
                                    Para ver horarios, clases y datos completos, abre el detalle completo.
                                </div>
                            </div>
                        </div>

                        <div id="detailFormError" class="alert alert-danger mt-3 d-none"></div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <a href="#" target="_blank" id="openFullDetail" class="btn btn-sm btn-outline-secondary">Abrir detalle completo</a>
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

            row.find('.enrollment-course').text(enrollment.course_title || '');
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
                throw new Error('No se pudo cargar el detalle de la inscripcion.');
            }

            return response.json();
        }

        function hydrateDetailModal(payload, url) {
            const enrollment = payload.enrollment;

            $('#detailEnrollmentId').val(enrollment.id);
            $('#detailStudentName').val(enrollment.student_name || '');
            $('#detailParentName').val(enrollment.parent_name || '');
            $('#detailPaymentStatus').val(enrollment.payment_status);
            $('#detailCourseTitle').val(enrollment.course_title || '');
            $('#detailCourseBranch').val(enrollment.course_branch_name || '');

            const formAction = updateUrlTemplate.replace('__ID__', enrollment.id);
            $('#enrollmentDetailForm').attr('action', formAction);
            $('#openFullDetail').attr('href', url);
            $('#openFullDetailHeader').attr('href', url);
            $('#detailFormError').addClass('d-none').text('');
        }

        const selectedIds = new Set();
        let detailModal;
        let table;

        $(document).ready(function() {
            $('#inscripcionesModal').on('shown.bs.modal', function() {
                $('.select2').select2({
                    dropdownParent: $('#inscripcionesModal'),
                    allowClear: true
                });
            });

            $('#userSelect').on('change', function() {
                filterStudentsByParent();
            });

            table = $('#enrollmentsTable').DataTable({
                order: [
                    [1, 'desc']
                ],
                pageLength: 10,
                columnDefs: [{
                    targets: [0, 7],
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
                        text: 'Se actualizara el estado de pago de ' + selectedIds.size + ' inscripciones.',
                        showCancelButton: true,
                        confirmButtonText: 'Si, aplicar',
                        cancelButtonText: 'Cancelar'
                    });
                    confirmed = result.isConfirmed;
                } else {
                    confirmed = confirm('Se actualizara el estado de pago de ' + selectedIds.size + ' inscripciones.');
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
                        throw new Error(data.message || 'No se pudo actualizar la inscripcion.');
                    }

                    updateRowVisual(data.enrollment);
                    detailModal.hide();
                } catch (error) {
                    $('#detailFormError').removeClass('d-none').text(error.message ||
                        'No se pudo actualizar la inscripcion.');
                } finally {
                    submitButton.prop('disabled', false).text('Guardar cambios');
                }
            });

            applySelectionsToCurrentPage();
        });
    </script>
@endsection
