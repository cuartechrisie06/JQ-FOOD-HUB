@extends('layouts.public')
@section('title', $product->product_name . ' - JQ Food Hub')
@section('meta_description', Str::limit($product->description ?: 'Freshly prepared '.$product->product_name.' from JQ Food Hub in Tagum City.', 155))
@section('content')
@php($role = auth()->check() ? auth()->user()->role->role_name : null)

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb py-2 px-3 bg-white rounded-3 border small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('menu.index', ['tab' => 'lumpias']) }}" class="text-decoration-none text-muted">Menu</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $product->product_name }}</li>
    </ol>
</nav>

<div class="card p-3 p-md-5 mb-5">
    <div class="row g-4 align-items-center">
        <div class="col-md-5">
            <div class="rounded-4 overflow-hidden border position-relative bg-light">
                <img class="w-100" style="aspect-ratio:4/3;object-fit:cover" src="{{ $product->image_url }}" data-fallback="{{ asset('images/placeholder-product.svg') }}" alt="{{ $product->product_name }}" width="600" height="450">
                <div class="position-absolute top-0 end-0 m-3">
                    @if($product->is_available)
                        <span class="badge-status badge-success shadow"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> In Stock</span>
                    @else
                        <span class="badge-status badge-secondary shadow"><i class="bi bi-x-circle-fill" aria-hidden="true"></i> Out of Stock</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="ps-md-3">
                <h1 class="h2 fw-extrabold mb-2">{{ $product->product_name }}</h1>

                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="display-6 fw-extrabold text-danger">₱{{ number_format($product->unit_price, 2) }}</span>
                    <span class="text-muted fs-6">/ {{ $product->unit }}</span>
                </div>

                <p class="text-secondary mb-4">{{ $product->description ?: 'Freshly prepared and packaged to guarantee the best quality and crunch.' }}</p>

                <div class="row g-2 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small"><i class="bi bi-box-seam me-1" aria-hidden="true"></i> Sold per</div>
                            <div class="fw-bold">{{ ucfirst($product->unit) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small"><i class="bi bi-clock-history me-1" aria-hidden="true"></i> Preparation time</div>
                            <div class="fw-bold">{{ $product->lead_time_days }} day(s) advance notice</div>
                        </div>
                    </div>
                </div>

                @if(! $product->is_available)
                    <button class="btn btn-secondary btn-lg" type="button" disabled>
                        <i class="bi bi-slash-circle me-1" aria-hidden="true"></i> Currently Unavailable
                    </button>
                @elseif($role === 'Customer')
                    <form method="POST" action="{{ route('cart.store', $product) }}" class="d-flex flex-wrap gap-3 align-items-end" data-once>
                        @csrf
                        <div>
                            <label class="form-label small fw-bold" for="quantity">Quantity</label>
                            <div>@include('partials.qty-stepper', ['id' => 'quantity', 'value' => 1, 'large' => true])</div>
                        </div>
                        <button class="btn btn-jq btn-lg px-4 flex-grow-1" type="submit">
                            <i class="bi bi-cart-plus-fill" aria-hidden="true"></i> Add to Cart
                        </button>
                    </form>
                @elseif($role === null)
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a class="btn btn-jq btn-lg px-4" href="{{ route('login') }}">
                            <i class="bi bi-cart-plus-fill" aria-hidden="true"></i> Add to Cart
                        </a>
                        <span class="text-muted small">New customer? <a href="{{ route('register') }}" class="text-danger fw-semibold">Register here</a></span>
                    </div>
                @endif

                <p class="mt-4 pt-3 border-top text-muted small mb-0">
                    <i class="bi bi-geo-alt text-danger" aria-hidden="true"></i> Made in our Mankilam, Tagum City kitchen &middot; Pickup or local delivery
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
