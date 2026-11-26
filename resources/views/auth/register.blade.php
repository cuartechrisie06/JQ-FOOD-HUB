@extends('layouts.public')
@section('title', 'Register - JQ Food Hub')
@section('content')

<div class="row justify-content-center py-4">
    <div class="col-md-7 col-lg-6">
        <div class="card p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="JQ Food Hub Logo" class="rounded-circle shadow-sm mx-auto mb-3 border" style="width: 72px; height: 72px; object-fit: contain; background: #ffffff; padding: 4px;">
                <h2 class="h4 fw-bold text-dark mb-1">Create Customer Account</h2>
                <p class="text-muted small">Join JQ Food Hub to order faster, track deliveries, and book catering</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
                    @foreach($errors->all() as $error)
                        <div><i class="bi bi-exclamation-circle me-1"></i> {{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input id="full_name" class="form-control" name="full_name" value="{{ old('full_name') }}" placeholder="Juan Dela Cruz" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="juan@example.com" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input id="phone" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="09171234567" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="customer_type">Customer Type</label>
                    <select id="customer_type" class="form-select" name="customer_type" required>
                        <option value="Individual" @selected(old('customer_type') === 'Individual')>Individual</option>
                        <option value="Business" @selected(old('customer_type') === 'Business')>Business / Corporate</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="business_name">Business Name <span class="text-muted fw-normal small">(if business)</span></label>
                    <input id="business_name" class="form-control" name="business_name" value="{{ old('business_name') }}" placeholder="Company or organization">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" class="form-control" name="password" placeholder="••••••••" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="••••••••" required>
                </div>

                <div class="col-12 pt-2">
                    <button class="btn btn-jq w-100 py-2 mb-3" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> Register Account
                    </button>
                </div>

                <div class="col-12 text-center pt-2 border-top">
                    <span class="small text-muted">Already registered?</span>
                    <a class="small fw-bold text-danger text-decoration-none ms-1" href="{{ route('login') }}">Sign In</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
