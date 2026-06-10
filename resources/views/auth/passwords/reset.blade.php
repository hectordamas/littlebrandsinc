@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <form method="POST" action="{{ route('password.email') }}" class="md-float-material form-material mt-5">
                @csrf

                <!-- LOGO -->
                <div class="text-center mb-3">
                    <img src="{{ asset('assets/img/logo-littlebrandsinc.png') }}" style="max-width: 200px;" alt="logo">
                </div>

                <div class="auth-box card">
                    <div class="card-block">

                        <!-- TITLE -->
                        <div class="row m-b-20">
                            <div class="col-md-12">
                                <h4 class="text-center">Restablecer Contraseña</h4>
                            </div>
                        </div>

                        <!-- MENSAJE DE ÉXITO -->
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <!-- ERRORES GENERALES -->
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- EMAIL -->
                        <div class="mb-3 form-primary">
                            <input 
                                type="email" 
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                placeholder="Correo Electrónico"
                                required
                                autofocus
                            >
                            <span class="form-bar"></span>

                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- BUTTON -->
                        <div class="row m-t-30">
                            <div class="col-md-12">
                                <div class="d-grid">
                                    <button type="submit"
                                        class="btn btn-grd-inverse btn-md waves-effect waves-light text-center m-b-20">
                                        Enviar Enlace de Restablecimiento
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- LINK A LOGIN -->
                        <div class="text-center">
                            <p class="mb-0">
                                Volver a 
                                <a href="{{ route('login') }}"><b>Iniciar Sesión</b></a>
                            </p>
                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
