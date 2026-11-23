@extends('layouts.public')
@section('title', 'Product Catalog')
@section('content')
<h1 class="h2 mb-4">Product Catalog</h1>

<form method="GET" action="{{ route('products.index') }}" class="card card-body mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="search">Search</label>
            <input id="search" class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Product name">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="category_id">Category</label>
            <select id="category_id" class="form-select" name="category_id">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->category_id }}" @selected((string) request('category_id') === (string) $category->category_id)>{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="sort">Sort</label>
            <select id="sort" class="form-select" name="sort">
                <option value="name" @selected(request('sort', 'name') === 'name')>Name A-Z</option>
                <option value="name_desc" @selected(request('sort') === 'name_desc')>Name Z-A</option>
                <option value="price_low" @selected(request('sort') === 'price_low')>Price Low-High</option>
                <option value="price_high" @selected(request('sort') === 'price_high')>Price High-Low</option>
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="available_today" name="available_today" value="1" @checked(request()->boolean('available_today'))>
                <label class="form-check-label" for="available_today">Available today only</label>
            </div>
        </div>
        <div class="col-md-1 d-grid">
            <button class="btn btn-jq">Apply</button>
        </div>
    </div>
</form>

@if($products->isEmpty())
    <div class="alert alert-light border">No products match your filters.</div>
@else
    <div class="row g-4">
        @foreach($products as $product)
            <div class="col-sm-6 col-lg-3">
                @include('catalog.card', ['product' => $product])
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endif
@endsection
