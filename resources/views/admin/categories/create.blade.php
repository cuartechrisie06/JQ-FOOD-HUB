@extends('layouts.admin')
@section('title', 'Add Category')
@section('content')
<h1 class="h3 mb-3">Add Category</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.categories.store') }}">
@csrf
@include('admin.categories.form')
<button class="btn btn-jq">Save Category</button>
</form>
</div></div>
@endsection
