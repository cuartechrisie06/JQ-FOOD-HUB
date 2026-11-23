@extends('layouts.public')
@section('title', 'JQ Food Hub')
@section('content')
<div class="p-5 rounded" style="background:#f7c948;">
    <h1>JQ Food Hub</h1>
    <p class="lead">Lumpia and catering for Tagum City and the Davao Region.</p>
    <a class="btn btn-jq me-2" href="{{ route('products.index') }}">Order Now</a>
    <a class="btn btn-dark" href="{{ route('packages.index') }}">Book Catering</a>
</div>

<section class="mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h3">Best Sellers</h2>
        <a href="{{ route('products.index') }}">View all products</a>
    </div>
    @if($bestSellers->isEmpty())
        <div class="alert alert-light border">No available products yet.</div>
    @else
        <div class="row g-4">
            @foreach($bestSellers as $product)
                <div class="col-sm-6 col-lg-3">
                    @include('catalog.card', ['product' => $product])
                </div>
            @endforeach
        </div>
    @endif
</section>
<section class="mt-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h3 mb-1">What Customers Say</h2>
            @if($averageRating !== null)
                <p class="text-muted mb-0">Rated {{ number_format($averageRating, 1) }} out of 5 by our customers.</p>
            @endif
        </div>
        <a href="{{ route('reviews') }}">Read all reviews</a>
    </div>
    @if($approvedReviews->isEmpty())
        <div class="alert alert-light border">Customer reviews will appear here after approval.</div>
    @else
        <div class="row g-3">
            @foreach($approvedReviews as $review)
                <div class="col-12 col-md-6 col-lg-4">@include('reviews.card', ['review' => $review])</div>
            @endforeach
        </div>
    @endif
</section>
@endsection
