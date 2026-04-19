@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Inscripciones</title>
@endsection

@section('content')
    <div class="modal fade" id="inscripcionesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form method="POST" action="{{ url('enrollment/store') }}">
                    @csrf

                    <div class="modal-header">
                        <h6 class="mb-0 fw-bold">Registrar Inscripción</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <!-- ===================== -->
                            <!-- 👨 REPRESENTANTE (USER) -->
                            <!-- ===================== -->
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

                                <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="toggleUserForm()">
                                    + Crear nuevo representante
                                </button>
                            </div>
                            <div class="col-md-6"></div>

                            <!-- FORM NUEVO USER -->
                            <div id="userForm" class="col-12 d-none">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="text" name="user[name]" class="form-control" placeholder="Nombre">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" name="user[email]" class="form-control" placeholder="Email">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="user[whatsapp]" class="form-control"
                                            placeholder="WhatsApp">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" name="user[password]" class="form-control"
                                            placeholder="Contraseña">
                                    </div>
                                </div>
                            </div>

                            <!-- ===================== -->
                            <!-- 👦 ESTUDIANTE -->
                            <!-- ===================== -->
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

                                <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="toggleStudentForm()">
                                    + Crear nuevo estudiante
                                </button>
                            </div>
                            <div class="col-md-6"></div>

                            <!-- FORM NUEVO STUDENT -->
                            <div id="studentForm" class="col-12 d-none">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="text" name="student[name]" class="form-control"
                                            placeholder="Nombre">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" name="student[birthdate]" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="student[level]" class="form-control"
                                            placeholder="Nivel">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="student[medical_notes]" class="form-control"
                                            placeholder="Notas médicas">
                                    </div>
                                </div>
                            </div>

                            <!-- ===================== -->
                            <!-- 📘 INSCRIPCIÓN -->
                            <!-- ===================== -->
                            <div class="col-md-6 mt-3">
                                <label>Curso</label>
                                <select name="course_id" class="form-control select2">
                                    <option value="">-- Seleccionar Programa --</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Representante</th>
                                <th>Curso</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        function toggleStudentForm() {
            document.getElementById('studentForm').classList.toggle('d-none');
            document.getElementById('studentSelect').value = '';
        }

        function toggleUserForm() {
            document.getElementById('userForm').classList.toggle('d-none');
            document.getElementById('userSelect').value = '';
        }

        $(document).ready(function() {
            $('#inscripcionesModal').on('shown.bs.modal', function() {
                $('.select2').select2({
                    dropdownParent: $('#inscripcionesModal'),
                    allowClear: true
                });
            });
        });
    </script>
@endsection
