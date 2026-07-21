@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Estudiantes</title>
@endsection

@section('styles')
    <style>
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
    </style>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>Estudiantes Inscritos</h5>
                    <span class="text-muted">Consulta y seguimiento de alumnos creados a traves de Inscripciones</span>
                </div>
                <a href="{{ route('students.import.form') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-import"></i> Importar Excel/CSV
                </a>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="studentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Representante</th>
                                <th>Programas</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                <tr>
                                    <td>{{ $student->id }}</td>

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
                                        {{ $student->enrollments->pluck('program.name')->filter()->unique()->join(', ') ?: '-' }}
                                    </td>

                                    <td>
                                        @if ($student->active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-primary">
                                            <i class="far fa-eye"></i> Ver
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

@section('scripts')
    <script>
        function studentExportColumns() {
            return [0, 1, 2, 3, 4, 5];
        }

        $(document).ready(function() {
            const table = $('#studentsTable').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"fB>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3"lip>',
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                buttons: [{
                        extend: 'copyHtml5',
                        text: '<i class="fas fa-copy"></i> Copiar',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: studentExportColumns()
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: studentExportColumns()
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: studentExportColumns()
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: studentExportColumns()
                        },
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function(doc) {
                            doc.pageMargins = [12, 12, 12, 12];
                            doc.defaultStyle.fontSize = 9;
                            doc.styles.tableHeader.fontSize = 10;
                            const tableBody = doc.content[1].table.body;
                            const columnCount = tableBody[0].length;
                            doc.content[1].table.widths = Array(columnCount).fill('*');
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: studentExportColumns()
                        }
                    }
                ],
                columnDefs: [{
                    targets: [6],
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
        });
    </script>
@endsection
