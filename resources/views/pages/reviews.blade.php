@extends('layouts.public')
@section('title', 'Customer Reviews')
@section('content')
<h1 class="h2 mb-4">Customer Reviews</h1>
<section class="card border-warning mb-4" aria-label="Customer rating summary">
    <div class="card-body">
        <h2 class="h5 mb-1">Customer Rating</h2>
        @if($approvedReviewCount)
            <p class="mb-0"><span class="fs-3 fw-bold">{{ number_format($averageRating, 1) }}</span> / 5
                <span class="text-muted">from {{ $approvedReviewCount }} approved {{ \Illuminate\Support\Str::plural('review', $approvedReviewCount) }}</span>
            </p>
        @else
            <p class="text-muted mb-0">No approved ratings yet.</p>
        @endif
    </div>
</section>
@if($reviews->isEmpty())
    <div class="alert alert-light border">No approved reviews yet. Customer reviews will appear here after approval.</div>
@else
    <div class="row g-3">
        @foreach($reviews as $review)
            <div class="col-12 col-md-6 col-lg-4">@include('reviews.card', ['review' => $review])</div>
        @endforeach
    </div>
    <div class="mt-4">{{ $reviews->links() }}</div>
@endif
@endsection
