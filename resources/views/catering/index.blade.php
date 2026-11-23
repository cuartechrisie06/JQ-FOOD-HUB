@extends('layouts.public')
@section('title', 'Book Catering')
@section('content')
<h1 class="h2 mb-2">Catering Booking Request</h1>
<p class="text-muted">Choose an available date at least three days from today, then send a request. Pending requests do not reserve capacity.</p>

<div class="d-flex flex-wrap gap-3 mb-3" aria-label="Calendar legend">
    <span><span class="badge" style="background:#198754">&nbsp;</span> Available</span>
    <span><span class="badge" style="background:#dc3545">&nbsp;</span> Fully booked</span>
    <span><span class="badge" style="background:#6c757d">&nbsp;</span> Not available</span>
</div>
<div id="catering-calendar"
     data-catering-calendar
     data-events-url="{{ route('catering.events') }}"
     data-earliest-date="{{ $earliestDate }}"
     class="card p-3 mb-4"></div>
<p id="calendar-selection-message" class="small text-muted" aria-live="polite">Select an available calendar date.</p>

<div class="card mb-5">
    <div class="card-body">
        <h2 class="h4">Request a Catering Booking</h2>
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif
        @if($packages->isEmpty())
            <div class="alert alert-warning">There are no available packages to book right now.</div>
        @else
            <form method="POST" action="{{ route('catering.bookings.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label" for="slot_date">Event Date</label>
                    <input class="form-control" type="date" id="slot_date" name="slot_date" min="{{ $earliestDate }}" value="{{ old('slot_date') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="event_type">Event Type</label>
                    <input class="form-control" id="event_type" name="event_type" maxlength="50" value="{{ old('event_type') }}" placeholder="Birthday, meeting, etc." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="package_id">Food Package</label>
                    <select class="form-select" id="package_id" name="package_id" required>
                        <option value="">Select a package</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->package_id }}" @selected(old('package_id') == $package->package_id)>
                                {{ $package->package_name }} ({{ $package->min_pax }}-{{ $package->max_pax }} guests)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_count">Number of Guests</label>
                    <input class="form-control" type="number" id="guest_count" name="guest_count" min="1" value="{{ old('guest_count') }}" required>
                    <div class="form-text">Must fit within the selected package's guest range.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="event_time">Event Time</label>
                    <input class="form-control" type="time" id="event_time" name="event_time" value="{{ old('event_time') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="event_location">Event Location</label>
                    <input class="form-control" id="event_location" name="event_location" maxlength="255" value="{{ old('event_location') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="special_requests">Special Requests</label>
                    <textarea class="form-control" id="special_requests" name="special_requests" rows="3">{{ old('special_requests') }}</textarea>
                </div>
                <div class="col-12"><button class="btn btn-jq" type="submit">Submit Booking Request</button></div>
            </form>
        @endif
    </div>
</div>

<h2 class="h3 mb-3">My Catering Requests</h2>
@if($bookings->isEmpty())
    <div class="alert alert-light border">You have no catering requests yet.</div>
@else
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Date</th><th>Event</th><th>Package</th><th>Guests</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->calendar->slot_date->format('M j, Y') }}</td>
                <td>{{ $booking->event_type }}</td>
                <td>{{ $booking->foodPackage->package_name }}</td>
                <td>{{ $booking->guest_count }}</td>
                <td><span class="badge {{ $booking->status === 'Confirmed' ? 'text-bg-success' : ($booking->status === 'Rejected' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $booking->status }}</span></td>
                <td>
                    @if($booking->status === 'Pending')
                        <form method="POST" action="{{ route('catering.bookings.cancel', $booking) }}" onsubmit="return confirm('Cancel this pending request?')">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-danger">Cancel</button>
                        </form>
                    @elseif($booking->order_id)
                        <a class="btn btn-sm btn-outline-dark" href="{{ route('orders.show', $booking->order_id) }}">View Order</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table></div></div>
    <div class="mt-4">{{ $bookings->links() }}</div>
@endif
@endsection
