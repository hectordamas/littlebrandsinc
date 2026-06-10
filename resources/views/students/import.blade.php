@extends('layouts.admin')

@section('title')
    <title>{{ env('APP_NAME') }} - Importar Estudiantes</title>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Importar Estudiantes y Representantes</h5>
                    <span class="text-muted">Sube un archivo .xlsx, .xls o .csv para crear o actualizar registros.</span>
                </div>
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver a estudiantes
                </a>
            </div>

            <div class="card-block">
                <form action="{{ route('students.import.store') }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                    @csrf

                    <div class="col-md-8">
                        <label for="import_file" class="form-label">Archivo de importacion</label>
                        <input
                            type="file"
                            name="file"
                            id="import_file"
                            class="form-control"
                            accept=".xlsx,.xls,.csv,text/csv"
                            required>

                        <small class="text-muted d-block mt-2">
                            Encabezados requeridos: <strong>student_name</strong>, <strong>student_birthdate</strong>,
                            <strong>parent_name</strong>, <strong>parent_email</strong>.
                        </small>
                        <small class="text-muted d-block">
                            Tambien acepta alias como: nombre estudiante, fecha nacimiento, nombre representante, correo representante.
                        </small>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-file-import"></i> Ejecutar importacion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection