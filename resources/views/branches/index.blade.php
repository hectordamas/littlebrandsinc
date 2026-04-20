@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Sucursales</title>
@endsection

@section('content')

    <div class="modal fade" id="createBranchModal">
        <div class="modal-dialog">
            <form action="{{ route('branches.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Sucursal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        @csrf
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="logo">Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="address">Dirección</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="active">Activa</label>
                            <select class="form-control" id="active" name="active">
                                <option value="1" {{ old("active") ? "selected" : "" }}>Sí</option>
                                <option value="0" {{ !old("active") ? "selected" : "" }}>No</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Guardar Sede
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Todas las Sucursales</h5>
                            <span class="text-muted">Gestión y seguimiento de sucursales activas en el sistema</span>
                        </div>

                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#createBranchModal" class="btn btn-inverse btn-sm">
                            <i class="fas fa-plus"></i> Agregar Sucursal</a>
                    </div>
                    <div class="card-block">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Logo</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Correo Electrónico</th>
                                    <th>Activa</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $branch)
                                    <tr>
                                        <td>{{ $branch->id }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td>
                                            @if ($branch->logo)
                                                <img src="{{ asset($branch->logo) }}" alt="Logo" style="max-width: 100px;">
                                            @endif
                                        </td>
                                        <td>{{ $branch->address }}</td>
                                        <td>{{ $branch->phone }}</td>
                                        <td>{{ $branch->email }}</td>
                                        <td>{{ $branch->active ? 'Sí' : 'No' }}</td>
                                        <td>
                                            <a href="{{ route('branches.edit', $branch->id) }}"
                                                class="btn btn-sm btn-success">Editar</a>
                                            <form action="{{ route('branches.destroy', $branch->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">No se encontraron sucursales.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
