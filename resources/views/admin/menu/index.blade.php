@extends('layouts.admin')
@section('title', 'Menu')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-danger fw-semibold small mb-1"><i class="bi bi-journal-richtext" aria-hidden="true"></i> Store catalog</p>
        <h1 class="h3 mb-1">Menu</h1>
        <p class="text-muted mb-0">Manage individually sold products and catering packages in one place.</p>
    </div>
    <div class="btn-group" role="group" aria-label="Add menu item">
        <a class="btn btn-outline-dark" href="{{ route('admin.products.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Product</a>
        <a class="btn btn-jq" href="{{ route('admin.packages.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Package</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <a class="card text-decoration-none h-100 {{ $type === 'products' ? 'border-danger shadow-sm' : '' }}" href="{{ route('admin.menu.index', request()->except(['type', 'products_page', 'packages_page'])) }}">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px"><i class="bi bi-bag fs-4" aria-hidden="true"></i></span>
                <div><div class="h4 mb-0 text-dark">{{ $productCount }}</div><div class="text-muted small">Products</div></div>
            </div>
        </a>
    </div>
    <div class="col-sm-6">
        <a class="card text-decoration-none h-100 {{ $type === 'packages' ? 'border-danger shadow-sm' : '' }}" href="{{ route('admin.menu.index', array_merge(request()->except(['products_page', 'packages_page']), ['type' => 'packages'])) }}">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px"><i class="bi bi-box-seam fs-4" aria-hidden="true"></i></span>
                <div><div class="h4 mb-0 text-dark">{{ $packageCount }}</div><div class="text-muted small">Catering packages</div></div>
            </div>
        </a>
    </div>
</div>

<div class="card mb-4"><div class="card-body">
    <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.menu.index') }}">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="col-md-7"><label class="form-label" for="search">Search {{ $type }}</label><input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="Search by name"></div>
        <div class="col-md-3"><label class="form-label" for="availability">Availability</label><select class="form-select" id="availability" name="availability"><option value="">All statuses</option><option value="active" @selected($availability === 'active')>Active</option><option value="inactive" @selected($availability === 'inactive')>Deactivated</option></select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-jq">Filter</button></div>
    </form>
</div></div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $type === 'products' ? 'active' : '' }}" href="{{ route('admin.menu.index', request()->except(['type', 'products_page', 'packages_page'])) }}">Products</a></li>
    <li class="nav-item"><a class="nav-link {{ $type === 'packages' ? 'active' : '' }}" href="{{ route('admin.menu.index', array_merge(request()->except(['products_page', 'packages_page']), ['type' => 'packages'])) }}">Packages</a></li>
</ul>

@if($type === 'products')
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($products as $product)<tr><td><div class="fw-semibold">{{ $product->product_name }}</div><div class="small text-muted">{{ $product->unit }}</div></td><td>{{ $product->category?->category_name ?? '—' }}</td><td>₱{{ number_format($product->unit_price, 2) }}</td><td>{{ $product->stock_qty }}</td><td><span class="badge-status {{ $product->is_available ? 'badge-success' : 'badge-secondary' }}">{{ $product->is_available ? 'Active' : 'Deactivated' }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-dark" href="{{ route('admin.products.edit', $product) }}">Edit</a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">No products found.</td></tr>@endforelse
    </tbody></table></div></div><div class="mt-4">{{ $products->links() }}</div>
@else
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Package</th><th>Event</th><th>Price</th><th>Inclusions</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($packages as $package)<tr><td><div class="fw-semibold">{{ $package->package_name }}</div><div class="small text-muted">{{ $package->pricing_type }}</div></td><td>{{ $package->event_type }}</td><td>₱{{ number_format($package->price, 2) }}</td><td>{{ $package->items_count }}</td><td><span class="badge-status {{ $package->is_available ? 'badge-success' : 'badge-secondary' }}">{{ $package->is_available ? 'Active' : 'Deactivated' }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-dark" href="{{ route('admin.packages.edit', $package) }}">Edit</a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-4">No packages found.</td></tr>@endforelse
    </tbody></table></div></div><div class="mt-4">{{ $packages->links() }}</div>
@endif
@endsection
