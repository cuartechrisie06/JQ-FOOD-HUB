@extends('layouts.public')
@section('title', 'Forgot Password')
@section('content')
<div class="row justify-content-center"><div class="col-md-5"><div class="card"><div class="card-body">
<h2 class="h4">Reset Password</h2>
<form method="POST" action="{{ route('password.email') }}">
@csrf
<div class="mb-3"><label class="form-label" for="email">Email</label><input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<button class="btn btn-jq" type="submit">Send Reset Link</button>
</form>
</div></div></div></div>
@endsection
