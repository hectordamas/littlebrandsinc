@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Usuarios</title>
@endsection

@section('content')
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form method="POST" action="{{ url('users/store') }}">
                    @csrf

                    <!-- HEADER -->
                    <div class="modal-header">
                        <h6 class="mb-0">Registrar Usuario</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">

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

                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Guardar Usuario
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
                    <h5>Usuarios Inscritos</h5>
                    <span class="text-muted">Gestión y seguimiento de usuarios activos en el sistema</span>
                </div>
                <div>
                    <a href="javascript:void(0);" class="btn btn-inverse btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exampleModal"><i class="far fa-address-book text-light"></i> Registrar
                        Usuario</a>
                </div>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Role</th>
                                <th>E-Mail</th>
                                <th>Whatsapp</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->role }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ format_phone($user->whatsapp) }}</td>
                                    <td class="d-flex justify-content-center align-items-center gap-2">

                                        <a href="{{ url('users/' . $user->id . '/edit') }}" class="btn btn-success">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="" method="post" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
