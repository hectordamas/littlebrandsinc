@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Usuarios</title>
@endsection

@section('content')
    <!-- Modal -->
    <div class="modal fade" id="usersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form method="POST" action="{{ route('users.store') }}">
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
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rol</label>
                                <select name="role" class="form-control" required>
                                    <option value="">Seleccione un rol</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Coach">Coach</option>
                                    <option value="Administrador">Administrador</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6 mb-3">
                                <label for="whatsapp">WhatsApp</label>

                                <div class="input-group phone-group">
                                    <select name="dial_code" id="dial_code" class="form-select">
                                        @include('partials.dialcode_create')
                                    </select>

                                    <input type="text" name="whatsapp" id="whatsapp" class="form-control"
                                        placeholder="4121234567">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
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
                        data-bs-target="#usersModal"><i class="far fa-address-book text-light"></i> Registrar
                        Usuario</a>
                </div>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Role</th>
                                <th>E-Mail</th>
                                <th>Whatsapp</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->role }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->dial_code }}{{ $user->whatsapp }}</td>
                                    <td class="d-flex justify-content-center align-items-center gap-1">

                                        <a href="{{ url('users/' . $user->id . '/edit') }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('users.destroy', $user->id) }}" method="post"
                                            class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirmDelete(event)">
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


@section('scripts')
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                order: [[0, 'desc']],
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        text: 'Copiar',
                        className: 'btn btn-inverse btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        className: 'btn btn-inverse btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-inverse btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        className: 'btn btn-inverse btn-sm'
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir',
                        className: 'btn btn-inverse btn-sm'
                    }
                ]
            });
        });
    </script>



    <script>
        function confirmDelete(event) {
            event.preventDefault();
            const form = (event.currentTarget || event.target).closest('form');

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'No podrás revertir esta acción.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endsection
