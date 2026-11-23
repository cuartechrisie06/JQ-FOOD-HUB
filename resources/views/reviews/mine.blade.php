@extends('layouts.public')
@section('title', 'My Reviews')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h2 mb-0">My Reviews</h1>
    <a class="btn btn-outline-dark" href="{{ route('orders.index') }}">My Orders</a>
</div>
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
@if($orders->isEmpty())
    <div class="alert alert-light border">You can review an order after it is completed.</div>
@else
    <div class="row g-3">
        @foreach($orders as $order)
            <div class="col-12">
                <section class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                            <h2 class="h5 mb-0">Order JQ-{{ str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT) }}</h2>
                            <span class="badge text-bg-success">Completed</span>
                        </div>
                        @if($order->review)
                            @if($order->review->status === 'Pending')
                                <form method="POST" action="{{ route('reviews.update', $order->review) }}">
                                    @csrf @method('PUT')
                                    <div class="row g-3">
                                        <div class="col-sm-4 col-md-3">
                                            <label class="form-label" for="rating-{{ $order->review->review_id }}">Rating</label>
                                            <select class="form-select" id="rating-{{ $order->review->review_id }}" name="rating" required>
                                                @for($rating = 5; $rating >= 1; $rating--)
                                                    <option value="{{ $rating }}" @selected(old('rating', $order->review->rating) == $rating)>{{ $rating }} / 5</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="comment-{{ $order->review->review_id }}">Comment (optional)</label>
                                            <textarea class="form-control" id="comment-{{ $order->review->review_id }}" name="comment" rows="3" maxlength="5000">{{ old('comment', $order->review->comment) }}</textarea>
                                        </div>
                                        <div class="col-12 d-flex flex-wrap align-items-center gap-3">
                                            <button class="btn btn-jq" type="submit">Update Pending Review</button>
                                            <span class="badge text-bg-warning">Pending approval</span>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <p class="mb-0">Your review is {{ strtolower($order->review->status) }} and can no longer be edited.</p>
                            @endif
                        @else
                            <form method="POST" action="{{ route('reviews.store', $order) }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-sm-4 col-md-3">
                                        <label class="form-label" for="rating-order-{{ $order->order_id }}">Rating</label>
                                        <select class="form-select" id="rating-order-{{ $order->order_id }}" name="rating" required>
                                            <option value="">Choose rating</option>
                                            @for($rating = 5; $rating >= 1; $rating--)
                                                <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} / 5</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="comment-order-{{ $order->order_id }}">Comment (optional)</label>
                                        <textarea class="form-control" id="comment-order-{{ $order->order_id }}" name="comment" rows="3" maxlength="5000">{{ old('comment') }}</textarea>
                                    </div>
                                    <div class="col-12"><button class="btn btn-jq" type="submit">Submit Review</button></div>
                                </div>
                            </form>
                        @endif
                    </div>
                </section>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
