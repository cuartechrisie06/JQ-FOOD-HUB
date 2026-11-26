@extends('layouts.public')
@section('title', 'Shopping Cart - JQ Food Hub')
@section('content')

<div class="mb-4">
    <h1 class="h2 fw-bold text-dark mb-1">Your Shopping Cart</h1>
    <p class="text-muted">Change a quantity and the cart updates by itself.</p>
</div>

@if($items->isEmpty())
    <div class="card p-5 text-center my-4">
        <div class="display-5 text-muted mb-3"><i class="bi bi-cart-x" aria-hidden="true"></i></div>
        <h2 class="h4 fw-bold text-dark mb-2">Your cart is currently empty</h2>
        <p class="text-muted col-md-5 mx-auto mb-4">Looks like you haven't added any of our delicious lumpia or food items yet.</p>
        <div>
            <a class="btn btn-jq px-4" href="{{ route('products.index') }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Browse Products
            </a>
        </div>
    </div>
@else
    @if($errors->has('cart'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5" aria-hidden="true"></i>
            <div>{{ $errors->first('cart') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- One responsive list instead of a 5-column table, so phones never scroll sideways --}}
            <div class="card overflow-hidden mb-3">
                @foreach($items as $item)
                    @php($p = $item['product'])
                    <div class="cart-line align-items-center flex-wrap">
                        <a href="{{ route('products.show', $p) }}" tabindex="-1" aria-hidden="true">
                            <img src="{{ $p->image_url }}" data-fallback="{{ asset('images/placeholder-product.svg') }}" alt="" width="72" height="72">
                        </a>

                        <div class="flex-grow-1" style="min-width: 150px;">
                            <a href="{{ route('products.show', $p) }}" class="fw-bold text-dark text-decoration-none hover-red">{{ $p->product_name }}</a>
                            <div class="small text-muted">₱{{ number_format($p->unit_price, 2) }} / {{ $p->unit }}</div>
                            @unless($p->is_available)
                                <span class="badge-status badge-danger mt-1"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> Unavailable</span>
                            @endunless
                        </div>

                        <form method="POST" action="{{ route('cart.update', $p) }}" data-qty-autosubmit>
                            @csrf @method('PATCH')
                            <label class="visually-hidden" for="qty-{{ $p->product_id }}">Quantity for {{ $p->product_name }}</label>
                            @include('partials.qty-stepper', ['id' => 'qty-'.$p->product_id, 'value' => $item['quantity']])
                            <noscript><button class="btn btn-sm btn-outline-secondary ms-1" type="submit">Update</button></noscript>
                        </form>

                        <div class="fw-extrabold text-danger text-nowrap ms-auto" style="min-width: 96px; text-align: right;">
                            ₱{{ number_format($item['subtotal'], 2) }}
                        </div>

                        <form method="POST" action="{{ route('cart.destroy', $p) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Remove {{ $p->product_name }} from cart" title="Remove item">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-sm px-3">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Continue Shopping
            </a>
        </div>

        <div class="col-lg-4">
            <div class="card p-4 sticky-top" style="top: 100px;">
                <h2 class="h5 fw-bold text-dark mb-3">Order Summary</h2>

                <div class="d-flex justify-content-between mb-2 small text-secondary">
                    <span>Items total</span>
                    <span class="fw-semibold">₱{{ number_format($total, 2) }}</span>
                </div>

                <hr class="my-2 border-secondary-subtle">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold fs-6">Estimated total</span>
                    <span class="fs-4 fw-extrabold text-danger">₱{{ number_format($total, 2) }}</span>
                </div>

                @if($items->every(fn ($item) => $item['product']->is_available))
                    <a class="btn btn-jq btn-lg w-100 py-3" href="{{ route('checkout.create') }}">
                        Proceed to Checkout <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                @else
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i> Please remove unavailable products before checking out.
                    </div>
                @endif

                <p class="mt-4 pt-3 border-top text-muted small mb-0">
                    <i class="bi bi-clock-history text-warning me-1" aria-hidden="true"></i>
                    Pickup or delivery, and any lead times, are set at checkout.
                </p>
            </div>
        </div>
    </div>
@endif

@endsection
