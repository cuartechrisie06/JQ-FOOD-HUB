@extends('layouts.public')
@section('title', 'Login')
@section('content')
<div class="row justify-content-center"><div class="col-md-5">
<div class="card shadow-sm"><div class="card-body">
<h2 class="h4 mb-3">Login</h2>
<form method="POST" action="{{ route('login') }}">
@csrf
<div class="mb-3"><label class="form-label" for="email">Email</label><input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus></div>
<div class="mb-3"><label class="form-label" for="password">Password</label><input id="password" type="password" class="form-control" name="password" required></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<button class="btn btn-jq" type="submit">Login</button>
<a class="ms-3" href="{{ route('password.request') }}">Forgot password?</a>
</form>
</div></div>
</div></div>
@endsection
