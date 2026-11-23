@extends('layouts.public')
@section('title', $product->product_name)
@section('content')
@php
    $hasImage = $product->image_path
        && (str_starts_with($product->image_path, 'http://') || str_starts_with($product->image_path, 'https://') || file_exists(storage_path('app/public/' . $product->image_path)));
    $imageUrl = $hasImage
        ? (str_starts_with($product->image_path, 'http') ? $product->image_path : asset('storage/' . $product->image_path))
        : asset('images/placeholder-product.svg');
@endphp
<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li><li class="breadcrumb-item active">{{ $product->product_name }}</li></ol></nav>
<div class="row g-4">
    <div class="col-md-5">
        <img class="product-image rounded border" style="height:360px" src="{{ $imageUrl }}" alt="{{ $product->product_name }}">
    </div>
    <div class="col-md-7">
        <h1 class="h2">{{ $product->product_name }}</h1>
        <p class="text-muted">{{ $product->category->category_name ?? 'Uncategorized' }}</p>
        <p>{{ $product->description ?: 'No description available.' }}</p>
        <dl class="row">
            <dt class="col-sm-4">Unit</dt><dd class="col-sm-8">{{ $product->unit }}</dd>
            <dt class="col-sm-4">Price</dt><dd class="col-sm-8 fw-bold">PHP {{ number_format($product->unit_price, 2) }}</dd>
            <dt class="col-sm-4">Availability</dt><dd class="col-sm-8"><span class="badge {{ $product->is_available ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->is_available ? 'Available' : 'Unavailable' }}</span></dd>
            <dt class="col-sm-4">Lead time</dt><dd class="col-sm-8">{{ $product->lead_time_days }} day(s)</dd>
        </dl>
        @if($product->is_available)
            @auth
                @if(auth()->user()->role->role_name === 'Customer')
                    <form class="d-flex gap-2 align-items-end" method="POST" action="{{ route('cart.store', $product) }}">
                        @csrf
                        <div><label class="form-label" for="quantity">Quantity</label><input id="quantity" name="quantity" class="form-control" type="number" min="1" max="999" value="1" style="width:120px" required></div>
                        <button class="btn btn-jq" type="submit">Add to Cart</button>
                    </form>
                @else
                    <a class="btn btn-jq" href="{{ route('login') }}">Add to Cart</a>
                @endif
            @else
                <a class="btn btn-jq" href="{{ route('login') }}">Add to Cart</a>
            @endauth
        @else
            <button class="btn btn-secondary" disabled>Unavailable</button>
        @endif
    </div>
</div>
@endsection
