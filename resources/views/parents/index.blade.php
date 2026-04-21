@extends('layouts.admin')
@section('title')
    <title>{{ env('APP_NAME') }} - Padres</title>
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
                    <h5>Padres / Representantes</h5>
                    <span class="text-muted">Listado de usuarios con rol Padre registrados en el sistema</span>
                </div>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="parentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>WhatsApp</th>
                                <th>Rol</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($parents as $parent)
                                <tr>
                                    <td>{{ $parent->id }}</td>
                                    <td>{{ $parent->name }}</td>
                                    <td>{{ $parent->email }}</td>
                                    <td>{{ trim(($parent->dial_code ?? '') . ' ' . ($parent->whatsapp ?? '')) ?: 'N/A' }}</td>
                                    <td><span class="badge bg-primary">{{ $parent->role }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('users.edit', $parent->id) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay padres registrados.</td>
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
        function parentExportColumns() {
            return [0, 1, 2, 3, 4];
        }

        $(document).ready(function() {
            const table = $('#parentsTable').DataTable({
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
                            columns: parentExportColumns()
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: parentExportColumns()
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: parentExportColumns()
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-sm btn-inverse',
                        exportOptions: {
                            columns: parentExportColumns()
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
                            columns: parentExportColumns()
                        }
                    }
                ],
                columnDefs: [{
                    targets: [5],
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
