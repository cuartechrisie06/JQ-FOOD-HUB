@extends('layouts.admin')
@section('title', 'Add Food Package')
@section('content')
<h1 class="h3 mb-3">Add Food Package</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.packages.store') }}" enctype="multipart/form-data">
@csrf
@include('admin.packages.form')
<button class="btn btn-jq">Save Package</button>
</form>
</div></div>
@endsection
