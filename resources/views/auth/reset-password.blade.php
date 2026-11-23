@extends('layouts.public')
@section('title', 'Reset Password')
@section('content')
<div class="row justify-content-center"><div class="col-md-5"><div class="card"><div class="card-body">
<h2 class="h4">Choose a New Password</h2>
<form method="POST" action="{{ route('password.update') }}">
@csrf
<input type="hidden" name="token" value="{{ $token }}">
<div class="mb-3"><label class="form-label" for="email">Email</label><input id="email" type="email" class="form-control" name="email" value="{{ old('email', $email) }}" required></div>
<div class="mb-3"><label class="form-label" for="password">New Password</label><input id="password" type="password" class="form-control" name="password" required></div>
<div class="mb-3"><label class="form-label" for="password_confirmation">Confirm Password</label><input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required></div>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<button class="btn btn-jq" type="submit">Reset Password</button>
</form>
</div></div></div></div>
@endsection
