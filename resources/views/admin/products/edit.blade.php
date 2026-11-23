@extends('layouts.admin')
@section('title', 'Edit Product')
@section('content')
<h1 class="h3 mb-3">Edit Product</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
@csrf @method('PUT')
@include('admin.products.form', ['product' => $product])
<button class="btn btn-jq">Update Product</button>
</form>
</div></div>
@endsection
