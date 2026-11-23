@extends('layouts.public')
@section('title', 'Register')
@section('content')
<div class="row justify-content-center"><div class="col-md-6">
<div class="card shadow-sm"><div class="card-body">
<h2 class="h4 mb-3">Create Customer Account</h2>
<form method="POST" action="{{ route('register') }}">
@csrf
<div class="mb-3"><label class="form-label" for="full_name">Full Name</label><input id="full_name" class="form-control" name="full_name" value="{{ old('full_name') }}" required></div>
<div class="mb-3"><label class="form-label" for="email">Email</label><input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
<div class="mb-3"><label class="form-label" for="phone">Phone</label><input id="phone" class="form-control" name="phone" value="{{ old('phone') }}" required></div>
<div class="mb-3"><label class="form-label" for="customer_type">Customer Type</label><select id="customer_type" class="form-select" name="customer_type" required><option value="Individual" @selected(old('customer_type')==='Individual')>Individual</option><option value="Business" @selected(old('customer_type')==='Business')>Business</option></select></div>
<div class="mb-3"><label class="form-label" for="business_name">Business Name (for Business accounts)</label><input id="business_name" class="form-control" name="business_name" value="{{ old('business_name') }}"></div>
<div class="mb-3"><label class="form-label" for="password">Password</label><input id="password" type="password" class="form-control" name="password" required></div>
<div class="mb-3"><label class="form-label" for="password_confirmation">Confirm Password</label><input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required></div>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<button class="btn btn-jq" type="submit">Register</button>
</form>
</div></div>
</div></div>
@endsection
