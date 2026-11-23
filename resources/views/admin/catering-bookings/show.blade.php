@extends('layouts.admin')
@section('title', 'Catering Booking Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Booking #{{ $booking->booking_id }}</h1>
    <span class="badge {{ $booking->status === 'Confirmed' ? 'text-bg-success' : ($booking->status === 'Rejected' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $booking->status }}</span>
</div>
<div class="card mb-3"><div class="card-body">
    <div class="row g-3">
        <div class="col-md-6"><strong>Customer:</strong> {{ $booking->user->full_name }} ({{ $booking->user->email }})</div>
        <div class="col-md-6"><strong>Phone:</strong> {{ $booking->user->phone }}</div>
        <div class="col-md-6"><strong>Date:</strong> {{ $booking->calendar->slot_date->format('M j, Y') }}</div>
        <div class="col-md-6"><strong>Event:</strong> {{ $booking->event_type }}</div>
        <div class="col-md-6"><strong>Time:</strong> {{ substr($booking->event_time, 0, 5) }}</div>
        <div class="col-md-6"><strong>Guests:</strong> {{ $booking->guest_count }}</div>
        <div class="col-md-6"><strong>Package:</strong> {{ $booking->foodPackage->package_name }} — PHP {{ number_format($booking->foodPackage->price, 2) }}</div>
        <div class="col-md-6"><strong>Location:</strong> {{ $booking->event_location }}</div>
        <div class="col-12"><strong>Special Requests:</strong><br>{{ $booking->special_requests ?: 'None' }}</div>
        @if($booking->order)<div class="col-12"><strong>Order:</strong> JQ-{{ str_pad($booking->order_id, 4, '0', STR_PAD_LEFT) }}</div>@endif
    </div>
</div></div>
@if($booking->status === 'Pending' && ! $booking->order_id)
    <form class="d-inline" method="POST" action="{{ route('admin.catering-bookings.confirm', $booking) }}">
        @csrf @method('PATCH')
        <button class="btn btn-success" onclick="return confirm('Confirm this request and create its order?')">Confirm</button>
    </form>
    <form class="d-inline" method="POST" action="{{ route('admin.catering-bookings.reject', $booking) }}">
        @csrf @method('PATCH')
        <button class="btn btn-outline-danger" onclick="return confirm('Reject this request?')">Reject</button>
    </form>
@endif
<a class="btn btn-outline-dark" href="{{ route('admin.catering-bookings.index') }}">Back to Bookings</a>
@endsection
