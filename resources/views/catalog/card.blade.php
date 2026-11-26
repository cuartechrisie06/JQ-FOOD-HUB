@php($role = auth()->check() ? auth()->user()->role->role_name : null)
<div class="card h-100 card-hover position-relative">
    <div class="image-container-overflow position-relative bg-light">
        <a href="{{ route('products.show', $product) }}" class="d-block" tabindex="-1" aria-hidden="true">
            <img class="product-image" src="{{ $product->image_url }}" data-fallback="{{ asset('images/placeholder-product.svg') }}" alt="" loading="lazy" width="600" height="450">
        </a>
        <div class="position-absolute top-0 end-0 m-2">
            @if($product->is_available)
                <span class="badge-status badge-success shadow-sm"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Available</span>
            @else
                <span class="badge-status badge-secondary shadow-sm"><i class="bi bi-x-circle-fill" aria-hidden="true"></i> Sold out</span>
            @endif
        </div>
    </div>

    <div class="card-body d-flex flex-column p-3">
        <h3 class="h6 fw-bold mb-1">
            <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark hover-red">{{ $product->product_name }}</a>
        </h3>

        <div class="d-flex align-items-baseline gap-1 my-2">
            <span class="fs-5 fw-extrabold text-danger">₱{{ number_format($product->unit_price, 2) }}</span>
            <span class="text-muted small">/ {{ $product->unit }}</span>
        </div>

        <div class="mt-auto pt-2">
            @if(! $product->is_available)
                <button class="btn btn-sm btn-outline-secondary w-100" type="button" disabled>Sold out</button>
            @elseif($role === 'Customer')
                <form method="POST" action="{{ route('cart.store', $product) }}" data-once>
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button class="btn btn-sm btn-jq w-100" type="submit">
                        <i class="bi bi-cart-plus" aria-hidden="true"></i> Add to cart
                    </button>
                </form>
            @elseif($role === null)
                <a class="btn btn-sm btn-jq w-100" href="{{ route('login') }}">
                    <i class="bi bi-cart-plus" aria-hidden="true"></i> Add to cart
                    <span class="visually-hidden">(log in required)</span>
                </a>
            @endif
        </div>
    </div>
</div>
