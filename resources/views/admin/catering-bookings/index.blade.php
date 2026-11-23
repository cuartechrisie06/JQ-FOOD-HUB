@extends('layouts.admin')
@section('title', 'Catering Bookings')
@section('content')
<h1 class="h3 mb-3">Catering Bookings</h1>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Customer</th><th>Date</th><th>Event</th><th>Package</th><th>Guests</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($bookings as $booking)
        <tr>
            <td>{{ $booking->user->full_name }}</td>
            <td>{{ $booking->calendar->slot_date->format('M j, Y') }}</td>
            <td>{{ $booking->event_type }}</td>
            <td>{{ $booking->foodPackage->package_name }}</td>
            <td>{{ $booking->guest_count }}</td>
            <td>{{ $booking->status }}</td>
            <td><a class="btn btn-sm btn-outline-dark" href="{{ route('admin.catering-bookings.show', $booking) }}">Details</a></td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No catering requests yet.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
