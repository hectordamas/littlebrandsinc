@extends('layouts.app')

@section('title')
    <title>Inscripción - {{ config('app.name') }}</title>
@endsection

@section('content')
    <style>
        .form-card {
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
        }

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

        .user-type-btn {
            cursor: pointer;
        }

        .user-type-btn:hover {
            opacity: 0.85;
        }
    </style>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <form method="POST" action="{{ route('students.register') }}" id="inscripcionForm" novalidate>
                    @csrf

                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/img/logo-littlebrandsinc.png') }}" 
                             alt="{{ config('app.name') }}" 
                             style="max-width: 180px;"
                             loading="lazy">
                    </div>

                    <div class="card form-card">
                        <div class="card-block p-4">

                            <h4 class="text-center mb-4">Inscripción</h4>

                            <div class="mb-4 text-center">
                                <div class="btn-group" role="group" aria-label="Tipo de usuario">
                                    <input type="radio" 
                                           class="btn-check" 
                                           name="user_type" 
                                           id="user_type_new"
                                           value="new" 
                                           {{ old('user_type', 'new') === 'new' ? 'checked' : '' }}
                                           autocomplete="off">
                                    <label class="btn btn-outline-primary user-type-btn" for="user_type_new">
                                        Nuevo usuario
                                    </label>
                                    
                                    <input type="radio" 
                                           class="btn-check" 
                                           name="user_type" 
                                           id="user_type_existing"
                                           value="existing"
                                           {{ old('user_type') === 'existing' ? 'checked' : '' }}
                                           autocomplete="off">
                                    <label class="btn btn-outline-primary user-type-btn" for="user_type_existing">
                                        Ya tengo cuenta
                                    </label>
                                </div>
                            </div>

                            <div id="existingUserFields" class="{{ old('user_type') !== 'existing' ? 'd-none' : '' }}">

                                <h6 class="mb-3 fw-bold">Acceso</h6>

                                <div class="mb-3">
                                    <label for="email_login" class="form-label">Correo</label>
                                    <input type="email" 
                                           name="email_login" 
                                           id="email_login"
                                           class="form-control{{ $errors->has('email_login') ? ' is-invalid' : '' }}"
                                           value="{{ old('email_login') }}"
                                           autocomplete="email"
                                           aria-describedby="emailLoginHelp">
                                    @error('email_login')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="emailLoginHelp" class="form-text text-muted">Ingresa el correo de tu cuenta existente</small>
                                </div>

                                <div class="mb-3">
                                    <label for="password_login" class="form-label">Contraseña</label>
                                    <input type="password" 
                                           name="password_login" 
                                           id="password_login"
                                           class="form-control{{ $errors->has('password_login') ? ' is-invalid' : '' }}"
                                           autocomplete="current-password"
                                           aria-describedby="passwordLoginHelp">
                                    @error('password_login')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="passwordLoginHelp" class="form-text text-muted">Ingresa tu contraseña</small>
                                </div>

                            </div>

                            <div id="newUserFields" class="{{ old('user_type') === 'existing' ? 'd-none' : '' }}">

                                <h6 class="mb-3 fw-bold">Datos del Representante</h6>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre completo</label>
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                           value="{{ old('name') }}"
                                           required
                                           minlength="2"
                                           maxlength="255"
                                           autocomplete="name"
                                           aria-describedby="nameHelp">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="nameHelp" class="form-text text-muted">Nombre del padre, madre o representante</small>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email"
                                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                           value="{{ old('email') }}"
                                           required
                                           autocomplete="email"
                                           aria-describedby="emailHelp">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="emailHelp" class="form-text text-muted">Este correo será tu usuario</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="whatsapp" class="form-label">WhatsApp</label>

                                    <div class="input-group phone-group">
                                        <select name="dial_code" 
                                                id="dial_code" 
                                                class="form-select{{ $errors->has('dial_code') ? ' is-invalid' : '' }}"
                                                style="max-width: 100px;"
                                                required>
                                            @include('partials.dialcode_create')
                                        </select>

                                        <input type="tel" 
                                               name="whatsapp" 
                                               id="whatsapp"
                                               class="form-control{{ $errors->has('whatsapp') ? ' is-invalid' : '' }}"
                                               value="{{ old('whatsapp') }}"
                                               required
                                               pattern="[0-9]{7,10}"
                                               minlength="7"
                                               maxlength="10"
                                               placeholder="4121234567"
                                               aria-describedby="whatsappHelp">
                                    </div>
                                    @error('whatsapp')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small id="whatsappHelp" class="form-text text-muted">Sin el 0 inicial (ej: 4121234567)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                           required
                                           minlength="8"
                                           autocomplete="new-password"
                                           aria-describedby="passwordHelp">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="passwordHelp" class="form-text text-muted">Mínimo 8 caracteres</small>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation"
                                           class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                                           required
                                           minlength="8"
                                           autocomplete="new-password"
                                           aria-describedby="passwordConfirmHelp">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="passwordConfirmHelp" class="form-text text-muted">Repite tu contraseña</small>
                                </div>

                            </div>

                            <hr>

                            <h6 class="mb-3 fw-bold">Datos del Estudiante</h6>

                            <div class="mb-3">
                                <label for="student_name" class="form-label">Nombre del estudiante</label>
                                <input type="text" 
                                       name="student_name" 
                                       id="student_name"
                                       class="form-control{{ $errors->has('student_name') ? ' is-invalid' : '' }}"
                                       value="{{ old('student_name') }}"
                                       required
                                       minlength="2"
                                       maxlength="255"
                                       aria-describedby="studentNameHelp">
                                @error('student_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small id="studentNameHelp" class="form-text text-muted">Nombre completo del niño/niña</small>
                            </div>

                            <div class="mb-3">
                                <label for="birthdate" class="form-label">Fecha de nacimiento</label>
                                <input type="date" 
                                       name="birthdate" 
                                       id="birthdate"
                                       class="form-control{{ $errors->has('birthdate') ? ' is-invalid' : '' }}"
                                       value="{{ old('birthdate') }}"
                                       required
                                       max="{{ now()->format('Y-m-d') }}"
                                       aria-describedby="birthdateHelp">
                                @error('birthdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small id="birthdateHelp" class="form-text text-muted">Selecciona la fecha de nacimiento</small>
                            </div>

                            <div class="mb-3">
                                <label for="medical_notes" class="form-label">Condiciones médicas <span class="text-muted">(opcional)</span></label>
                                <textarea name="medical_notes" 
                                          id="medical_notes"
                                          class="form-control{{ $errors->has('medical_notes') ? ' is-invalid' : '' }}"
                                          rows="3"
                                          aria-describedby="medicalNotesHelp">{{ old('medical_notes') }}</textarea>
                                @error('medical_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small id="medicalNotesHelp" class="form-text text-muted">Alergias, medicamentos, o condiciones relevantes</small>
                            </div>

                            <hr>

                            <div class="mb-3 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input{{ $errors->has('terms') ? ' is-invalid' : '' }}" 
                                           type="checkbox" 
                                           name="terms" 
                                           id="terms"
                                           {{ old('terms') ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="terms">
                                        Acepto los
                                        <a href="{{ url('terms') }}" target="_blank" rel="noopener noreferrer">Términos y Condiciones</a>
                                        y la
                                        <a href="{{ url('privacy') }}" target="_blank" rel="noopener noreferrer">Política de Privacidad</a>
                                    </label>
                                    @error('terms')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

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
        (function() {
            'use strict';

            const form = document.getElementById('inscripcionForm');

            document.querySelectorAll('input[name="user_type"]').forEach(function(radio) {
                radio.addEventListener('change', toggleForm);
            });

            if (form) {
                form.addEventListener('submit', function(event) {
                    const userType = document.querySelector('input[name="user_type"]:checked').value;
                    const password = document.getElementById('password');
                    const passwordConfirmation = document.getElementById('password_confirmation');

                    if (userType === 'new' && password && passwordConfirmation) {
                        if (password.value !== passwordConfirmation.value) {
                            event.preventDefault();
                            passwordConfirmation.setCustomValidity('Las contraseñas no coinciden');
                            passwordConfirmation.reportValidity();
                            passwordConfirmation.setCustomValidity('');
                        }
                    }
                });
            }

            function toggleForm() {
                const type = document.querySelector('input[name="user_type"]:checked').value;
                const newFields = document.getElementById('newUserFields');
                const existingFields = document.getElementById('existingUserFields');

                if (type === 'existing') {
                    newFields.classList.add('d-none');
                    existingFields.classList.remove('d-none');
                } else {
                    newFields.classList.remove('d-none');
                    existingFields.classList.add('d-none');
                }
            }

            window.toggleForm = toggleForm;

            const dialCode = document.getElementById('dial_code');
            if (dialCode && dialCode.value) {
                // Dial code already has value from server render
            }
        })();
    </script>
@endsection