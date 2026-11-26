@extends('layouts.admin')
@section('title', 'Catering Booking Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Booking #{{ $booking->booking_id }}</h1>
    @include('partials.status-badge', ['status' => $booking->status])
</div>
<div class="card mb-3"><div class="card-body">
    <div class="row g-3">
        <div class="col-md-6"><strong>Customer:</strong> {{ $booking->user->full_name }} ({{ $booking->user->email }})</div>
        <div class="col-md-6"><strong>Phone:</strong> {{ $booking->user->phone }}</div>
        <div class="col-md-6"><strong>Date:</strong> {{ $booking->calendar->slot_date->format('M j, Y') }}</div>
        <div class="col-md-6"><strong>Event:</strong> {{ $booking->event_type }}</div>
        <div class="col-md-6"><strong>Time:</strong> {{ substr($booking->event_time, 0, 5) }}</div>
        <div class="col-md-6"><strong>Guests:</strong> {{ $booking->guest_count }} pax</div>
        <div class="col-md-6">
            <strong>Package:</strong> {{ $booking->foodPackage->package_name }}
            <span class="badge bg-secondary ms-1">{{ $booking->foodPackage->pricing_type }}</span>
        </div>
        <div class="col-md-6">
            <strong>Drink Option:</strong> {{ $booking->drink_option ?: ($booking->foodPackage->pricing_type === 'Per Pax' ? 'Solo' : 'N/A') }}
        </div>
        @if($booking->foodPackage->freebie)
            <div class="col-md-6"><strong>Package Freebie:</strong> {{ $booking->foodPackage->freebie }}</div>
        @endif
        <div class="col-md-6"><strong>Location:</strong> {{ $booking->event_location }}</div>
        <div class="col-md-6">
            <strong>Estimated Total:</strong> ₱{{ number_format($booking->computeTotal(), 2) }}
            @if($booking->foodPackage->pricing_type === 'Per Pax')
                <small class="text-muted">({{ $booking->guest_count }} × ₱{{ number_format($booking->foodPackage->computePricePerPax($booking->drink_option), 2) }})</small>
            @endif
        </div>
        <div class="col-md-6">
            <strong>50% Down Payment:</strong> ₱{{ number_format($booking->computeDownpayment(), 2) }}
        </div>
        <div class="col-12"><strong>Special Requests:</strong><br>{{ $booking->special_requests ?: 'None' }}</div>
        @if($booking->order)<div class="col-12"><strong>Order:</strong> JQ-{{ str_pad($booking->order_id, 4, '0', STR_PAD_LEFT) }}</div>@endif
    </div>
</div></div>
@if($booking->status === 'Pending' && ! $booking->order_id)
    <form class="d-inline" method="POST" action="{{ route('admin.catering-bookings.confirm', $booking) }}">
        @csrf @method('PATCH')
        <button class="btn btn-success" data-confirm="Confirm this request and create its order?" data-confirm-label="Yes, confirm">Confirm</button>
    </form>
    <form class="d-inline" method="POST" action="{{ route('admin.catering-bookings.reject', $booking) }}">
        @csrf @method('PATCH')
        <button class="btn btn-outline-danger" data-confirm="Reject this catering request? The customer will be notified." data-confirm-label="Yes, reject">Reject</button>
    </form>
@endif
<a class="btn btn-outline-dark" href="{{ route('admin.catering-bookings.index') }}">Back to Bookings</a>
@endsection
