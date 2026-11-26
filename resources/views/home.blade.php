@extends('layouts.public')
@section('title', 'JQ Food Hub - Authentic Lumpia & Catering in Tagum City')
@section('content')

@php($spotlight = $bestSellers->first())

{{-- Hero: the single bold element on the site --}}
<div class="hero-canva mb-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="hero-tag">
                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Mankilam, Tagum City &bull; Fresh Daily
            </span>
            <h1 class="mb-3">
                Crispy Lumpia &amp; <span class="text-danger">Specialty Catering</span>
            </h1>
            <p class="lead mb-4 col-lg-11">
                Handmade lumpia rolls, packed meals, and full-service catering packages for family meals, birthdays, meetings, and gatherings across Davao Region.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a class="btn btn-jq btn-lg px-4" href="{{ route('products.index') }}">
                    <i class="bi bi-bag-check-fill" aria-hidden="true"></i> Order Fresh Lumpia
                </a>
                <a class="btn btn-jq-dark btn-lg px-4" href="{{ route('menu.index', ['tab' => 'packages']) }}">
                    <i class="bi bi-box-seam-fill text-warning" aria-hidden="true"></i> Book Catering
                </a>
            </div>
        </div>

        <div class="col-lg-5 d-flex justify-content-lg-end">
            @if($spotlight)
                <div class="spotlight-card shadow">
                    <a href="{{ route('products.show', $spotlight) }}" tabindex="-1" aria-hidden="true">
                        <img src="{{ $spotlight->image_url }}" data-fallback="{{ asset('images/placeholder-product.svg') }}" alt="" width="400" height="300">
                    </a>
                    <div class="p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            @if(($spotlight->units_sold ?? 0) > 0)
                                <span class="badge bg-danger text-white fw-bold px-3 py-1">Top seller</span>
                            @else
                                <span class="badge bg-dark text-white fw-bold px-3 py-1">Fresh pick</span>
                            @endif
                            <span class="small fw-bold" style="color:#0b6b4d"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Available now</span>
                        </div>
                        <h2 class="h5 fw-bold mb-1">
                            <a href="{{ route('products.show', $spotlight) }}" class="text-dark text-decoration-none stretched-link">{{ $spotlight->product_name }}</a>
                        </h2>
                        <p class="text-muted small mb-3">{{ Str::limit($spotlight->description ?: 'Crispy, flavorful, and rolled fresh in our Mankilam kitchen.', 90) }}</p>
                        <div class="fs-5 fw-extrabold text-danger">₱{{ number_format($spotlight->unit_price, 2) }} <span class="fs-6 fw-normal text-muted">/ {{ $spotlight->unit }}</span></div>
                    </div>
                </div>
            @else
                <div class="spotlight-card shadow p-4 text-center">
                    <img src="{{ asset('images/logo.png') }}" alt="JQ Food Hub logo" class="mx-auto mb-3" style="width:140px;height:140px;aspect-ratio:1;object-fit:contain;background:transparent">
                    <h2 class="h5 fw-bold mb-1">Rolled fresh in Tagum City</h2>
                    <p class="text-muted small mb-0">Our menu is being updated. Check back soon.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Value props --}}
<div class="row g-3 mb-5">
    @foreach([
        ['bi-patch-check-fill', 'bg-danger text-white', 'Freshly Rolled Daily', 'Handmade with quality local meats and savory seasonings.'],
        ['bi-truck', 'bg-warning text-dark', 'Pickup & Tagum Delivery', 'Villa Patricia pickup or delivery right to your door.'],
        ['bi-cake2-fill', 'bg-dark text-warning', 'Event Catering Packages', 'Complete food packages from 10 to 100+ guests.'],
    ] as [$icon, $tone, $heading, $copy])
        <div class="col-md-4">
            <div class="card h-100 p-3 d-flex flex-row align-items-center gap-3">
                <div class="stat-icon {{ $tone }}"><i class="bi {{ $icon }} fs-4" aria-hidden="true"></i></div>
                <div>
                    <h2 class="h6 fw-bold mb-1">{{ $heading }}</h2>
                    <p class="small text-muted mb-0">{{ $copy }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Best sellers --}}
<section class="mb-5" aria-labelledby="best-sellers-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
        <div>
            <p class="small fw-bold text-danger mb-1"><i class="bi bi-fire" aria-hidden="true"></i> Most popular</p>
            <h2 class="h3 fw-extrabold mb-0" id="best-sellers-heading">Best Sellers</h2>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 d-inline-flex align-items-center gap-1 fw-bold">
            View all products <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    @if($bestSellers->isEmpty())
        <div class="card p-5 text-center">
            <i class="bi bi-basket text-muted fs-1 mb-2" aria-hidden="true"></i>
            <h3 class="h5 fw-semibold text-secondary">No products available at the moment</h3>
            <p class="text-muted small mb-0">Check back soon for freshly rolled batches.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($bestSellers as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('catalog.card', ['product' => $product])
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- Catering callout --}}
<section class="mb-5" aria-labelledby="catering-heading">
    <div class="card border-0 overflow-hidden" style="background: var(--jq-black);">
        <div class="card-body p-4 p-md-5 text-white">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="small fw-bold text-warning mb-2"><i class="bi bi-stars" aria-hidden="true"></i> Full catering services</p>
                    <h2 class="display-6 fw-bold mb-2 text-warning" id="catering-heading">Planning an event or gathering?</h2>
                    <p class="text-white-50 lead mb-4">
                        Customizable catering packages for birthdays, office meetings, and weddings, with flexible guest capacity across Tagum City and Davao Region.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('menu.index', ['tab' => 'packages']) }}" class="btn btn-jq-gold px-4">
                            <i class="bi bi-boxes" aria-hidden="true"></i> Explore Packages
                        </a>
                        <a href="{{ auth()->check() && auth()->user()->role->role_name === 'Customer' ? route('menu.index', ['tab' => 'packages']) : route('login') }}" class="btn btn-outline-light px-4 fw-bold">
                            <i class="bi bi-calendar-check" aria-hidden="true"></i> {{ auth()->check() ? 'Book a Date' : 'Log in to Book a Date' }}
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-center mt-4 mt-lg-0 d-none d-lg-block">
                    <div class="display-1 text-warning opacity-75"><i class="bi bi-cup-hot-fill" aria-hidden="true"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Reviews --}}
<section class="mb-4" aria-labelledby="reviews-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
        <div>
            <h2 class="h3 fw-extrabold mb-1" id="reviews-heading">What Customers Say</h2>
            @if($averageRating !== null)
                <p class="text-muted small mb-0 d-flex align-items-center gap-1">
                    <span class="text-warning" aria-hidden="true"><i class="bi bi-star-fill"></i></span>
                    <strong>{{ number_format($averageRating, 1) }} out of 5</strong> from verified customer reviews
                </p>
            @endif
        </div>
        <a href="{{ route('reviews') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 d-inline-flex align-items-center gap-1 fw-bold">
            Read all reviews <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    @if($approvedReviews->isEmpty())
        <div class="card p-4 text-center">
            <p class="text-muted mb-0">Customer reviews will appear here after approval.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach($approvedReviews as $review)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('reviews.card', ['review' => $review])
                </div>
            @endforeach
        </div>
    @endif
</section>

@endsection
