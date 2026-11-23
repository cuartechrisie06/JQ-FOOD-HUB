@extends('layouts.admin')
@section('title', 'Availability Calendar')
@section('content')
<h1 class="h3 mb-3">Availability Calendar</h1>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Set Availability for a Date</h2>
    <form class="row g-3" method="POST" action="{{ route('admin.availability.store') }}">
        @csrf
        <div class="col-md-3"><label class="form-label" for="slot_date">Date</label><input class="form-control" type="date" id="slot_date" name="slot_date" required></div>
        <div class="col-md-3"><label class="form-label" for="max_bookings">Maximum Confirmed Bookings</label><input class="form-control" type="number" min="1" max="1000" id="max_bookings" name="max_bookings" value="{{ config('jqfood.default_max_bookings', 2) }}" required></div>
        <div class="col-md-3"><label class="form-label" for="remarks">Remarks</label><input class="form-control" id="remarks" name="remarks" maxlength="150"></div>
        <div class="col-md-3 d-flex align-items-end"><div class="form-check mb-2"><input type="hidden" name="is_blocked" value="0"><input class="form-check-input" type="checkbox" id="is_blocked" name="is_blocked" value="1"><label class="form-check-label" for="is_blocked">Block this date</label></div></div>
        <div class="col-12"><button class="btn btn-jq">Save Date</button></div>
    </form>
</div></div>

<h2 class="h5 mb-3">Configured Dates</h2>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Date</th><th>Confirmed / Capacity</th><th>Status</th><th>Remarks</th><th>Update</th></tr></thead>
    <tbody>
    @forelse($calendars as $calendar)
        <tr>
            <td>{{ $calendar->slot_date->format('M j, Y') }}</td>
            <td>{{ $calendar->confirmed_bookings_count }} / {{ $calendar->max_bookings }}</td>
            <td>{{ $calendar->is_blocked ? 'Not Available' : ($calendar->confirmed_bookings_count >= $calendar->max_bookings ? 'Fully Booked' : 'Available') }}</td>
            <td>{{ $calendar->remarks }}</td>
            <td>
                <form method="POST" action="{{ route('admin.availability.store') }}" class="d-flex flex-wrap gap-2">
                    @csrf
                    <input type="hidden" name="slot_date" value="{{ $calendar->slot_date->toDateString() }}">
                    <input class="form-control form-control-sm" style="width:100px" type="number" name="max_bookings" min="1" max="1000" value="{{ $calendar->max_bookings }}" aria-label="Maximum bookings">
                    <input class="form-control form-control-sm" style="width:180px" name="remarks" maxlength="150" value="{{ $calendar->remarks }}" placeholder="Remarks" aria-label="Remarks">
                    <div class="form-check align-self-center"><input type="hidden" name="is_blocked" value="0"><input class="form-check-input" type="checkbox" name="is_blocked" value="1" id="blocked-{{ $calendar->calendar_id }}" @checked($calendar->is_blocked)><label class="form-check-label" for="blocked-{{ $calendar->calendar_id }}">Blocked</label></div>
                    <button class="btn btn-sm btn-outline-dark">Save</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No dates have custom availability yet. Dates without a record use the default capacity of {{ config('jqfood.default_max_bookings', 2) }}.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
