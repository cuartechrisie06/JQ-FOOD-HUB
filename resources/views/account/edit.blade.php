@extends('layouts.public')
@section('title', 'My Account')
@section('content')
<h1 class="h2 mb-4">My Account</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('account.update') }}">
    @csrf @method('PUT')
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="full_name">Full Name</label><input class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="customer_type">Customer Type</label><select class="form-select" id="customer_type" name="customer_type" required><option value="Individual" @selected(old('customer_type', $user->customer_type) === 'Individual')>Individual</option><option value="Business" @selected(old('customer_type', $user->customer_type) === 'Business')>Business</option></select></div>
        <div class="col-12"><label class="form-label" for="business_name">Business Name</label><input class="form-control" id="business_name" name="business_name" value="{{ old('business_name', $user->business_name) }}"></div>
        <div class="col-12"><label class="form-label" for="address">Default Delivery Address</label><input class="form-control" id="address" name="address" value="{{ old('address', $user->address) }}" maxlength="255"></div>
        <div class="col-12"><button class="btn btn-jq">Save Profile</button></div>
    </div>
</form>
</div></div>
@endsection
