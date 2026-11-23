@extends('layouts.admin')
@section('title', 'Edit Category')
@section('content')
<h1 class="h3 mb-3">Edit Category</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.categories.update', $category) }}">
@csrf @method('PUT')
@include('admin.categories.form', ['category' => $category])
<button class="btn btn-jq">Update Category</button>
</form>
</div></div>
@endsection
