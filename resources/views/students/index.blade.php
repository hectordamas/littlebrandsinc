@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Estudiantes</title>
@endsection

@section('content')
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form method="POST" action="{{ url('students/store') }}">
                    @csrf

                    <!-- HEADER -->
                    <div class="modal-header">
                        <h6 class="mb-0">Registrar Estudiante</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">

                        <!-- 👤 REPRESENTANTE -->
                        <h6 class="fw-bold mb-3">Representante</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="parent_name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Correo</label>
                                <input type="email" name="parent_email" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="parent_whatsapp" class="form-control">
                            </div>
                        </div>


                        <hr>

                        <!-- 👶 ESTUDIANTE -->
                        <h6 class="fw-bold mb-3">Estudiante</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="student_name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input type="date" name="birthdate" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Notas médicas</label>
                                <textarea name="medical_notes" class="form-control"></textarea>
                            </div>

                            {{--
                            <!-- 🎯 CURSO -->
                            <div class="mb-3">
                                <label class="form-label">Curso</label>
                                <select name="course_id" class="form-control" required>
                                    <option value="">Seleccionar curso</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            -->
                        --}}
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Guardar Estudiante
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
                    <h5>Estudiantes Inscritos</h5>
                    <span class="text-muted">Gestión y seguimiento de alumnos activos en el sistema</span>
                </div>
                <div>
                    <a href="javascript:void(0);" class="btn btn-inverse btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exampleModal"><i class="far fa-address-book text-light"></i> Registrar
                        Estudiante</a>
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
                            @forelse($students as $student)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>{{ $student->name }}</strong>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($student->birthdate)->age }} años
                                    </td>

                                    <td>
                                        {{ $student->user->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{-- Ejemplo simple --}}
                                        {{ $student->enrollments->first()->course->name ?? '-' }}
                                    </td>

                                    <td>
                                        @if ($student->active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-primary">
                                            Ver
                                        </a>
                                        <a href="#" class="btn btn-sm btn-warning">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No hay estudiantes registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
