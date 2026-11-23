@php
    $hasImage = $product->image_path
        && (str_starts_with($product->image_path, 'http://') || str_starts_with($product->image_path, 'https://') || file_exists(storage_path('app/public/' . $product->image_path)));
    $imageUrl = $hasImage
        ? (str_starts_with($product->image_path, 'http') ? $product->image_path : asset('storage/' . $product->image_path))
        : asset('images/placeholder-product.svg');
@endphp
<div class="card h-100 shadow-sm">
    <img class="product-image card-img-top" src="{{ $imageUrl }}" alt="{{ $product->product_name }}">
    <div class="card-body d-flex flex-column">
        <h3 class="h6">{{ $product->product_name }}</h3>
        <p class="text-muted mb-1">{{ $product->category->category_name ?? 'Uncategorized' }}</p>
        <p class="fw-bold mb-2">PHP {{ number_format($product->unit_price, 2) }} / {{ $product->unit }}</p>
        <p class="mb-3"><span class="badge {{ $product->is_available ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->is_available ? 'Available' : 'Unavailable' }}</span></p>
        <div class="mt-auto d-grid gap-2">
            <a class="btn btn-outline-dark" href="{{ route('products.show', $product) }}">View Details</a>
            @if($product->is_available)
                @auth
                    @if(auth()->user()->role->role_name === 'Customer')
                        <form method="POST" action="{{ route('cart.store', $product) }}">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button class="btn btn-jq w-100" type="submit">Add</button>
                        </form>
                    @else
                        <a class="btn btn-jq" href="{{ route('login') }}">Add</a>
                    @endif
                @else
                    <a class="btn btn-jq" href="{{ route('login') }}">Add</a>
                @endauth
            @endif
        </div>
    </div>
</div>
