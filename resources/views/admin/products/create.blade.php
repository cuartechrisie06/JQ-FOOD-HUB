@extends('layouts.admin')
@section('title', 'Add Product')
@section('content')
<h1 class="h3 mb-3">Add Product</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
@csrf
@include('admin.products.form')
<button class="btn btn-jq">Save Product</button>
</form>
</div></div>
@endsection
