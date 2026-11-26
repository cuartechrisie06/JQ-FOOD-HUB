@extends('layouts.admin')
@section('title', 'Manage Reviews')
@section('content')
<h1 class="h3 mb-4">Manage Reviews</h1>
<form class="card card-body mb-4" method="GET" action="{{ route('admin.reviews.index') }}">
    <div class="row g-2 align-items-end">
        <div class="col-sm-6 col-md-4">
            <label class="form-label" for="status">Review status</label>
            <select class="form-select" id="status" name="status">
                <option value="">All statuses</option>
                @foreach(['Pending', 'Approved', 'Hidden'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-jq">Filter</button></div>
    </div>
</form>
@if($reviews->isEmpty())
    <div class="alert alert-light border">No reviews found.</div>
@else
    <div class="row g-3">
        @foreach($reviews as $review)
            <div class="col-12">
                <article class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <h2 class="h5 mb-0">Order JQ-{{ str_pad((string) $review->order_id, 4, '0', STR_PAD_LEFT) }} · {{ $review->user->full_name }}</h2>
                            @include('partials.status-badge', ['status' => $review->status])
                        </div>
                        <p class="mb-1"><strong>{{ $review->rating }} / 5</strong> · {{ $review->order->calendar->slot_date->format('M j, Y') }}</p>
                        <p class="mb-3 text-break">{{ $review->comment ?: 'No written comment.' }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            @if($review->status !== 'Approved')
                                <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="Approved">
                                    <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
                                </form>
                            @endif
                            @if($review->status !== 'Hidden')
                                <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="Hidden">
                                    <button class="btn btn-sm btn-outline-dark" type="submit">Hide</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm="Delete this review permanently? This cannot be undone." data-confirm-label="Yes, delete">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $reviews->links() }}</div>
@endif
@endsection
