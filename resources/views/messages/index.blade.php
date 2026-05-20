@extends('layouts.admin')

@section('title')
    <title>{{ config('app.name') }} - Mensajes</title>
@endsection

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Mensajes de Contacto</h5>
                    <span class="text-muted">{{ $unreadCount }} mensaje(s) no leido(s)</span>
                </div>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="messagesTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Representante</th>
                                <th>Nino/a</th>
                                <th>Programa</th>
                                <th>Sede</th>
                                <th>Telefono</th>
                                <th>Email</th>
                                <th>Comentario</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($messages as $message)
                                <tr class="{{ $message->read_at ? '' : 'table-info' }}">
                                    <td>
                                        @if ($message->read_at)
                                            <span class="badge bg-secondary">Leido</span>
                                        @else
                                            <span class="badge bg-primary">No leido</span>
                                        @endif
                                    </td>
                                    <td>{{ $message->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $message->representative_name }}</td>
                                    <td>{{ $message->child_name }} ({{ $message->child_age }} anos)</td>
                                    <td>{{ optional($message->program)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($message->branch)->name ?? 'N/A' }}</td>
                                    <td>{{ $message->phone }}</td>
                                    <td>{{ $message->email }}</td>
                                    <td style="max-width: 320px; white-space: normal;">{{ $message->comment }}</td>
                                    <td>
                                        @if ($message->read_at)
                                            <form action="{{ route('messages.unread', $message) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-secondary">Marcar no leido</button>
                                            </form>
                                        @else
                                            <form action="{{ route('messages.read', $message) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-primary">Marcar leido</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No hay mensajes registrados.</td>
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
        $(document).ready(function() {
            $('#messagesTable').DataTable({
                order: [
                    [1, 'desc']
                ],
                pageLength: 10,
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
        });
    </script>
@endsection
