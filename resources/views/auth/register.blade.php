@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-12">

            <form method="POST" action="{{ route('register') }}" class="md-float-material form-material mt-5">
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
                                <h4 class="text-center">Register</h4>
                            </div>
                        </div>


                        <!-- NAME -->
                        <div class="mb-3 form-primary">
                            <input 
                                type="text" 
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="Full Name"
                                required
                                autofocus
                            >
                            <span class="form-bar"></span>

                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- EMAIL -->
                        <div class="mb-3 form-primary">
                            <input 
                                type="email" 
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                placeholder="Email Address"
                                required
                            >
                            <span class="form-bar"></span>

                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- PASSWORD -->
                        <div class="mb-3 form-primary">
                            <input 
                                type="password" 
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Password"
                                required
                            >
                            <span class="form-bar"></span>

                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- CONFIRM PASSWORD -->
                        <div class="mb-3 form-primary">
                            <input 
                                type="password" 
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Confirm Password"
                                required
                            >
                            <span class="form-bar"></span>
                        </div>

                        <!-- BUTTON -->
                        <div class="row m-t-30">
                            <div class="col-md-12">
                                <div class="d-grid">
                                    <button type="submit"
                                        class="btn btn-grd-inverse btn-md waves-effect waves-light text-center m-b-20">
                                        Register
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- LINK A LOGIN -->
                        <div class="text-center">
                            <p class="mb-0">
                                Already have an account? 
                                <a href="{{ route('login') }}"><b>Login</b></a>
                            </p>
                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection