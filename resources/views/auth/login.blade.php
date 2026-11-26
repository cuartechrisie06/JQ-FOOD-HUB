@extends('layouts.public')
@section('title', 'Login - JQ Food Hub')
@section('content')

<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="JQ Food Hub Logo" class="rounded-circle shadow-sm mx-auto mb-3 border" style="width: 72px; height: 72px; object-fit: contain; background: #ffffff; padding: 4px;">
                <h2 class="h4 fw-bold text-dark mb-1">Welcome Back</h2>
                <p class="text-muted small">Sign in to manage your orders & catering bookings</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
                    @foreach($errors->all() as $error)
                        <div><i class="bi bi-exclamation-circle me-1"></i> {{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0" for="password">Password</label>
                        <a class="small text-danger text-decoration-none" href="{{ route('password.request') }}">Forgot?</a>
                    </div>
                    <div class="input-group mt-1">
                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                        <input id="password" type="password" class="form-control" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-muted" for="remember">
                        Remember me on this device
                    </label>
                </div>

                <button class="btn btn-jq w-100 py-2 mb-3" type="submit">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </button>

                <div class="text-center pt-2 border-top">
                    <span class="small text-muted">Don't have an account yet?</span>
                    <a class="small fw-bold text-danger text-decoration-none ms-1" href="{{ route('register') }}">Create an account</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
