@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Cursos</title>
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

        .course-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .wizard-link-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #bfdbfe;
            background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            border-radius: 999px;
            padding: 0.3rem 0.55rem;
            font-size: 0.72rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .wizard-link-chip:hover {
            border-color: #93c5fd;
            color: #1e3a8a;
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Todos los Cursos</h5>
                            <span class="text-muted">Gestión y seguimiento de cursos activos en el sistema</span>
                        </div>

                        <a href="{{ route('courses.create') }}" class="btn btn-inverse btn-sm">
                            <i class="fas fa-plus"></i> Agregar Curso</a>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="coursesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Edad Mínima</th>
                                    <th>Edad Máxima</th>
                                    <th>Inscripción</th>
                                    <th>Mensualidad</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Activo</th>
                                    <th>Sede</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courses as $course)
                                    <tr>
                                        <td>{{ $course->id }}</td>
                                        <td>{{ $course->title }}</td>
                                        <td>{{ $course->min_age ?? 'N/A' }}</td>
                                        <td>{{ $course->max_age ?? 'N/A' }}</td>
                                        <td>{{ $course->price ? '$' . number_format($course->price, 2) : 'N/A' }}</td>
                                        <td>{{ $course->monthly_fee ? '$' . number_format($course->monthly_fee, 2) : 'N/A' }}</td>
                                        <td>{{ $course->start_date ? \Carbon\Carbon::parse($course->start_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td>{{ $course->end_date ? \Carbon\Carbon::parse($course->end_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td>{{ $course->active ? 'Sí' : 'No' }}</td>
                                        <td>{{ $course->branch->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="course-actions">
                                                <button type="button"
                                                    class="wizard-link-chip copy-wizard-link-btn"
                                                    data-wizard-link="{{ route('enrollment.wizard', ['course_id' => $course->id]) }}"
                                                    title="Copiar enlace del wizard de este curso">
                                                    <i class="fas fa-link"></i>
                                                    Wizard
                                                </button>
                                                <a href="{{ route('courses.edit', $course->id) }}"
                                                    class="btn btn-sm btn-success"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('courses.destroy', $course->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Estás seguro?')"><i
                                                            class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12">No se encontraron cursos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function courseExportColumns() {
            return [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        }

        $(document).ready(function() {
            const table = $('#coursesTable').DataTable({
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
                            columns: courseExportColumns()
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: courseExportColumns()
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: courseExportColumns()
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: courseExportColumns()
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
                            columns: courseExportColumns()
                        }
                    }
                ],
                columnDefs: [{
                    targets: [10],
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

            $(document).on('click', '.copy-wizard-link-btn', function() {
                const link = $(this).data('wizard-link');
                if (!link) {
                    return;
                }

                navigator.clipboard.writeText(link).then(function() {
                    Swal.fire({
                        icon: 'success',
                        text: 'Enlace del wizard copiado al portapapeles.',
                        confirmButtonColor: '#198754'
                    });
                }).catch(function() {
                    Swal.fire({
                        icon: 'error',
                        text: 'No fue posible copiar el enlace automáticamente.'
                    });
                });
            });
        });
    </script>
@endsection
