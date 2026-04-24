@extends('layouts.admin')
@section('title')
    <title>{{ config('app.name') }} - Crear Curso</title>
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Agregar Nuevo Curso</h5>
                <span class="text-muted">Llena los siguientes campos para registrar un nuevo curso</span>
            </div>
            <div class="card-block">
                <form action="{{ route('courses.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-9">
                            <label for="title" class="form-label">Título del curso</label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="{{ old('title') }}" required>
                        </div>
                        <div class="col-md-3"></div>

                        <div class="mb-3 col-md-9">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea name="description" id="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-3"></div>

                        <div class="mb-3 col-md-3">
                            <label for="min_age" class="form-label">Edad Mínima</label>
                            <input type="number" name="min_age" id="min_age" class="form-control"
                                value="{{ old('min_age') }}">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="max_age" class="form-label">Edad Máxima</label>
                            <input type="number" name="max_age" id="max_age" class="form-control"
                                value="{{ old('max_age') }}">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="capacity" class="form-label">Capacidad</label>
                            <input type="number" name="capacity" id="capacity" class="form-control"
                                value="{{ old('capacity') }}">
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="price" class="form-label">Precio de inscripción</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01"
                                value="{{ old('price') }}">
                            <span id="price-preview" class="fw-bold text-success">$0.00</span>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="monthly_fee" class="form-label">Mensualidad</label>
                            <input type="number" name="monthly_fee" id="monthly_fee" class="form-control" step="0.01"
                                value="{{ old('monthly_fee') }}">
                            <span id="monthly-fee-preview" class="fw-bold text-primary">$0.00</span>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="branch_id" class="form-label">Sede</label>
                            <select name="branch_id" id="branch_id" class="form-control" required>
                                <option value="">Selecciona una sede</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="start_date" class="form-label">Fecha de Inicio</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ old('start_date') }}" required>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="end_date" class="form-label">Fecha de Fin</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ old('end_date') }}" required>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="active" class="form-label">Activo</label>
                            <select name="active" id="active" class="form-control" required>
                                <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="coach_id" class="form-label">Coach</label>
                            <select name="coach_id" id="coach_id" class="form-control" required>
                                <option value="">Sin asignar</option>
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id }}"
                                        {{ old('coach_id') == $coach->id ? 'selected' : '' }}>
                                        {{ $coach->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <h5 class="my-3">Clases</h5>
                    </div>

                    <div id="sessions-wrapper">

                        <template id="session-template">
                            <div class="session-item border rounded p-3 mb-3 position-relative">

                                <button type="button"
                                    class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-session">
                                    ✕
                                </button>

                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label>Fecha</label>
                                        <input type="date" name="sessions[__INDEX__][date]" class="form-control"
                                            required>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Inicio</label>
                                        <input type="time" name="sessions[__INDEX__][start_time]" class="form-control"
                                            required>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Fin</label>
                                        <input type="time" name="sessions[__INDEX__][end_time]" class="form-control"
                                            required>
                                    </div>



                                </div>

                            </div>
                        </template>

                    </div>

                    <button type="button" id="add-session" class="btn btn-inverse btn-sm mb-4">
                        + Agregar clase
                    </button>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Guardar curso</button>

                            <a href="{{ route('courses.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let index = 0;
            const wrapper = document.getElementById('sessions-wrapper');
            const template = document.getElementById('session-template').innerHTML;

            function addSession() {
                let html = template.replace(/__INDEX__/g, index);
                wrapper.insertAdjacentHTML('beforeend', html);
                index++;
            }

            // primera clase automática
            addSession();

            // agregar
            document.getElementById('add-session').addEventListener('click', addSession);

            // eliminar (delegado)
            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-session')) {
                    e.target.closest('.session-item').remove();
                }
            });

        });


        $(document).ready(function() {
            $('#price').on('input', function() {
                let value = parseFloat($(this).val());
                if (isNaN(value)) value = 0;
                $('#price-preview').text('$' + value.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }).trigger('input');

            $('#monthly_fee').on('input', function() {
                let value = parseFloat($(this).val());
                if (isNaN(value)) value = 0;
                $('#monthly-fee-preview').text('$' + value.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }).trigger('input');
        });
    </script>
@endsection
