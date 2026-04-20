@extends('layouts.app')

@section('title')
    <title>Inscripción - {{ env('APP_NAME') }}</title>
@endsection

@section('content')
    <style>
        /* 🧊 CARD BONITA */
        .form-card {
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        /* INPUTS */
        .form-control {
            border-radius: 8px;
            padding: 10px;
        }

        /* HOVER BOTÓN */
        .btn-primary {
            transition: all 0.25s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }


        .form-check-label a {
            color: #0d6efd;
            font-weight: 500;
            text-decoration: none;
        }

        .form-check-label a:hover {
            text-decoration: underline;
        }
    </style>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <form method="POST" action="{{ route('students.register') }}">
                    @csrf

                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/img/logo-littlebrandsinc.png') }}" style="max-width: 180px;">
                    </div>

                    <div class="card form-card">
                        <div class="card-block p-4">

                            <h4 class="text-center mb-4">Inscripción</h4>

                            <!-- 🔁 TIPO DE USUARIO -->
                            <div class="mb-4 text-center">
                                <label class="me-3">
                                    <input type="radio" name="user_type" value="new" checked onclick="toggleForm()">
                                    Nuevo usuario
                                </label>
                                <label>
                                    <input type="radio" name="user_type" value="existing" onclick="toggleForm()"> Ya tengo
                                    cuenta
                                </label>
                            </div>

                            <div id="existingUserFields" style="display:none;">

                                <h6 class="mb-3 fw-bold">Acceso</h6>

                                <div class="mb-3">
                                    <label>Correo</label>
                                    <input type="email" name="email_login" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label>Contraseña</label>
                                    <input type="password" name="password_login" class="form-control">
                                </div>

                            </div>

                            <div id="newUserFields">

                                <h6 class="mb-3 fw-bold">Datos del Representante</h6>

                                <div class="mb-3">
                                    <label>Nombre</label>
                                    <input type="text" name="name" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label>Correo</label>
                                    <input type="email" name="email" class="form-control">
                                </div>


                                <div class="form-group mb-3">
                                    <label for="whatsapp">WhatsApp</label>

                                    <div class="input-group phone-group">
                                        <select name="dial_code" id="dial_code" class="form-select" style="max-width: 100px;">
                                            @include('partials.dialcode_create')
                                        </select>

                                        <input type="text" name="whatsapp" id="whatsapp" class="form-control"
                                            placeholder="4121234567">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label>Contraseña</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label>Confirmar contraseña</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>

                            </div>

                            <hr>

                            <!-- 👶 ESTUDIANTE -->
                            <h6 class="mb-3 fw-bold">Datos del Estudiante</h6>

                            <div class="mb-3">
                                <label class="form-label">Nombre del estudiante</label>
                                <input type="text" name="student_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input type="date" name="birthdate" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Condiciones médicas</label>
                                <textarea name="medical_notes" class="form-control"></textarea>
                            </div>


                            <!-- 📜 TÉRMINOS -->
                            <div class="mb-3 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        Acepto los
                                        <a href="{{ url('terms') }}" target="_blank">Términos y Condiciones</a>
                                        y la
                                        <a href="{{ url('privacy') }}" target="_blank">Política de Privacidad</a>
                                    </label>
                                </div>

                                @error('terms')
                                    <small class="text-danger">Debes aceptar los términos para continuar</small>
                                @enderror
                            </div>

                            <!-- 🚀 BOTÓN -->
                            <div class="d-grid mt-4">
                                <button type="submit"
                                    class="btn btn-grd-inverse btn-md waves-effect waves-light text-center m-b-20">
                                    Completar Inscripción
                                </button>
                            </div>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            const type = document.querySelector('input[name="user_type"]:checked').value;

            const newFields = document.getElementById('newUserFields');
            const existingFields = document.getElementById('existingUserFields');

            if (type === 'existing') {
                newFields.style.display = 'none';
                existingFields.style.display = 'block';
            } else {
                newFields.style.display = 'block';
                existingFields.style.display = 'none';
            }
        }
    </script>
@endsection
