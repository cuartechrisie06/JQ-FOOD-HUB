@extends('layouts.admin')
@section('title', 'Edit Food Package')
@section('content')
<h1 class="h3 mb-3">Edit Food Package</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.packages.update', $package) }}" enctype="multipart/form-data">
@csrf @method('PUT')
@include('admin.packages.form', ['package' => $package])
<button class="btn btn-jq">Update Package</button>
</form>
</div></div>
@endsection
