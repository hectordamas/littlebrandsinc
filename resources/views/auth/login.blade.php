@extends('layouts.app')

@section('title')
<title>Inicia Sesión - Little Brands Inc</title>
@endsection

@section('content')
    <!-- Container-fluid starts -->
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <form method="POST" action="{{ route('login') }}" class="md-float-material form-material mt-5">
                    @csrf

                    <div class="text-center mb-3">
                        <img src="{{ asset('assets/img/logo-littlebrandsinc.png') }}" style="max-width: 200px;"
                            alt="Littlebrandsinc logo">
                    </div>
                    <div class="auth-box card">
                        <div class="card-block">

                            <div class="row m-b-20">
                                <div class="col-md-12">

                                    <h4 class="text-center">Sign In</h4>
                                </div>
                            </div>

                            <!-- EMAIL -->
                            <div class="mb-3 form-primary">
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                    placeholder="Your Email Address" required autofocus>
                                <span class="form-bar"></span>

                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- PASSWORD -->
                            <div class="mb-3 form-primary">
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" placeholder="Password"
                                    required>
                                <span class="form-bar"></span>

                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- REMEMBER -->
                            <div class="row m-t-25 text-start">
                                <div class="col-12">
                                    <div class="checkbox-fade fade-in-primary">
                                        <label class="form-label">
                                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <span class="cr">
                                                <i class="cr-icon icofont icofont-ui-check txt-primary"></i>
                                            </span>
                                            <span class="text-inverse">Remember me</span>
                                        </label>
                                    </div>

                                    <div class="forgot-phone text-end f-right">
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="f-w-600">
                                                Forgot Password?
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- BUTTON -->
                            <div class="row m-t-30">
                                <div class="col-md-12">
                                    <div class="d-grid">
                                        <button type="submit"
                                            class="btn btn-grd-inverse btn-md waves-effect waves-light text-center m-b-20">
                                            Sign in
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <!-- end of col-sm-12 -->
        </div>
        <!-- end of row -->
    </div>
    <!-- end of container-fluid -->
@endsection
