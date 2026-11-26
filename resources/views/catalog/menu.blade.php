@extends('layouts.public')
@section('title', 'Menu - JQ Food Hub')
@section('meta_description', 'Browse JQ Food Hub food packages, budget meals, snacks, buffet meals and lumpias.')
@section('content')
@php
    $role = auth()->check() ? auth()->user()->role->role_name : null;
    $intro = [
        'packages' => 'Fixed-price catering trays with fruits included. Pick the tier that fits your crowd.',
        'budget' => 'Complete rice meals priced per person. Minimum of 25 guests.',
        'snacks' => 'Light merienda sets priced per person. Minimum of 25 guests.',
        'buffet' => 'Full buffet spreads priced per person. Minimum of 25 guests.',
        'lumpias' => 'Freshly rolled lumpia and ready-to-cook favorites you can order for pickup or delivery.',
    ][$tab];
@endphp

<div class="mb-4">
    <p class="small fw-bold text-danger mb-1"><i class="bi bi-journal-richtext" aria-hidden="true"></i> JQ Food Hub</p>
    <h1 class="h2 fw-bold mb-1">Our Menu</h1>
    <p class="text-muted mb-0">{{ $intro }}</p>
</div>

@if($tab !== 'lumpias' && $errors->any() && $role === 'Customer')
    <div class="alert alert-danger" role="alert">Your booking request needs attention. <a class="alert-link" href="#booking">Go to the booking form</a>.</div>
@endif

<nav class="menu-tabs mb-4" aria-label="Menu sections">
    <ul class="menu-tabs-list" role="list">
        @foreach($tabs as $slug => $meta)
            <li>
                <a class="menu-tab {{ $tab === $slug ? 'active' : '' }}" href="{{ route('menu.index', ['tab' => $slug]) }}" @if($tab === $slug) aria-current="page" @endif>
                    <i class="bi {{ $meta['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $meta['label'] }}</span>
                    <span class="menu-tab-count">{{ $counts[$slug] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

<div class="card mb-4"><div class="card-body p-3">
    <form method="GET" action="{{ route('menu.index') }}" class="row g-3 align-items-end">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="col-md-{{ $tab === 'lumpias' ? '7' : '10' }}">
            <label class="form-label" for="search">Search {{ strtolower($tabs[$tab]['label']) }}</label>
            <input class="form-control" id="search" type="search" name="search" value="{{ request('search') }}" placeholder="{{ $tab === 'lumpias' ? 'Shanghai, cheesesticks…' : 'Chicken, pancit, lumpia…' }}">
        </div>
        @if($tab === 'lumpias')
            <div class="col-md-3"><label class="form-label" for="sort">Sort by</label><select class="form-select" id="sort" name="sort"><option value="name">Name (A–Z)</option><option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option><option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option></select></div>
        @endif
        <div class="col-md-2 d-grid"><button class="btn btn-jq"><i class="bi bi-search" aria-hidden="true"></i> Search</button></div>
    </form>
</div></div>

@if($tab === 'lumpias')
    @if($products->isEmpty())
        <div class="card p-5 text-center"><h2 class="h4">No items found</h2><p class="text-muted mb-0">Try another search or category.</p></div>
    @else
        <div class="row g-4">@foreach($products as $product)<div class="col-6 col-md-4 col-lg-3">@include('catalog.card', ['product' => $product])</div>@endforeach</div>
        <div class="mt-4 d-flex justify-content-center">{{ $products->links() }}</div>
    @endif
@else
    @if($packages->isEmpty())
        <div class="card p-5 text-center"><h2 class="h4">Nothing found</h2><p class="text-muted mb-0">Try another search, or contact us for a custom event package.</p></div>
    @else
        @php($sections = $tab === 'packages' ? $packages->groupBy('package_group') : collect(['' => $packages]))
        @foreach($sections as $groupName => $group)
            @if($groupName !== '')
                <div class="d-flex align-items-baseline justify-content-between gap-2 mb-3 {{ $loop->first ? '' : 'mt-5' }}">
                    <h2 class="h5 fw-bold mb-0">{{ $groupName }}</h2>
                    <span class="small text-muted">{{ $group->count() }} {{ \Illuminate\Support\Str::plural('choice', $group->count()) }}</span>
                </div>
            @endif
            <div class="row g-4">
                @foreach($group as $package)
                    <div class="col-md-6 col-lg-4">
                        @include('catalog.package-card', ['package' => $package, 'role' => $role, 'tab' => $tab, 'selected' => $selectedPackage])
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif
@endif

@if($tab !== 'lumpias' && $role === 'Customer')
    @include('catalog._booking')
@endif
@endsection
