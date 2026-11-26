@extends('layouts.admin')
@section('title', 'Order Calendar - Admin')
@section('content')

<div class="mb-4">
    <h1 class="h3 fw-bold text-dark mb-1">Order Calendar</h1>
    <p class="text-muted small mb-0">Every regular order, bulk order, packed lunch and catering booking in one place. Click an order or a day to see details, set capacity or block the date.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card p-3 p-md-4">
            <div class="cal-legend mb-3" aria-label="Calendar legend">
                <span><i class="lg-dot ev-regular" aria-hidden="true"></i> Regular</span>
                <span><i class="lg-dot ev-bulk" aria-hidden="true"></i> Bulk</span>
                <span><i class="lg-dot ev-packed-lunch" aria-hidden="true"></i> Packed lunch</span>
                <span><i class="lg-dot ev-catering" aria-hidden="true"></i> Catering</span>
                <span><i class="lg-dot ev-request" aria-hidden="true"></i> Catering request</span>
                <span><i class="lg-blocked" aria-hidden="true"></i> Blocked</span>
            </div>
            <div id="admin-calendar" data-admin-calendar data-events-url="{{ route('admin.availability.events') }}"></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card p-3 p-md-4 mb-4 sticky-top" style="top: 90px;">
            <h2 class="h5 fw-bold mb-1" id="day-title">Select a date</h2>
            <p class="small text-muted mb-3" id="day-capacity">Click a day on the calendar.</p>
            <div id="day-orders" class="mb-3"></div>

            <hr>
            <h3 class="h6 fw-bold">Capacity &amp; availability</h3>
            <p class="small text-muted">Capacity counts catering, bulk and packed lunch orders. Regular orders are shown but do not use it up.</p>
            <form method="POST" action="{{ route('admin.availability.store') }}" class="row g-2">
                @csrf
                <div class="col-12">
                    <label class="form-label small" for="slot_date">Date</label>
                    <input class="form-control" type="date" id="slot_date" name="slot_date" required>
                </div>
                <div class="col-6">
                    <label class="form-label small" for="max_bookings">Max per day</label>
                    <input class="form-control" type="number" min="1" max="1000" id="max_bookings" name="max_bookings" value="{{ config('jqfood.default_max_bookings', 2) }}" required>
                </div>
                <div class="col-6 d-flex align-items-end">
                    <div class="form-check p-2 border rounded-3 bg-light w-100">
                        <input type="hidden" name="is_blocked" value="0">
                        <input class="form-check-input ms-1" type="checkbox" id="is_blocked" name="is_blocked" value="1">
                        <label class="form-check-label fw-bold small text-danger ms-2" for="is_blocked">Block date</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small" for="remarks">Remarks <span class="text-muted">(optional)</span></label>
                    <input class="form-control" id="remarks" name="remarks" maxlength="150" placeholder="e.g. Holiday, heavy catering prep">
                </div>
                <div class="col-12"><button class="btn btn-jq w-100"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Save date</button></div>
            </form>
        </div>
    </div>
</div>

<div class="card overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom p-3"><h2 class="h5 fw-bold text-dark mb-0">Custom configured dates</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th>Date</th><th>Booked / Capacity</th><th>Status</th><th>Remarks</th><th style="min-width: 320px;">Quick adjust</th></tr></thead>
            <tbody>
            @forelse($calendars as $calendar)
                @php($booked = $calendar->bookedCount())
                <tr>
                    <td class="fw-bold text-dark"><i class="bi bi-calendar-event me-1 text-danger" aria-hidden="true"></i> {{ $calendar->slot_date->format('M j, Y') }}</td>
                    <td><span class="fw-bold text-dark">{{ $booked }}</span> <span class="text-muted">/ {{ $calendar->max_bookings }}</span></td>
                    <td>
                        @if($calendar->is_blocked)<span class="badge-status badge-danger">Blocked</span>
                        @elseif($booked >= $calendar->max_bookings)<span class="badge-status badge-secondary">Fully booked</span>
                        @else<span class="badge-status badge-success">Available</span>@endif
                    </td>
                    <td class="text-secondary small">{{ $calendar->remarks ?: '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.availability.store') }}" class="d-flex flex-wrap gap-2 align-items-center">
                            @csrf
                            <input type="hidden" name="slot_date" value="{{ $calendar->slot_date->toDateString() }}">
                            <input class="form-control form-control-sm text-center" style="width: 80px" type="number" name="max_bookings" min="1" max="1000" value="{{ $calendar->max_bookings }}" aria-label="Maximum per day">
                            <input class="form-control form-control-sm" style="width: 140px" name="remarks" maxlength="150" value="{{ $calendar->remarks }}" placeholder="Remarks" aria-label="Remarks">
                            <div class="form-check">
                                <input type="hidden" name="is_blocked" value="0">
                                <input class="form-check-input" type="checkbox" name="is_blocked" value="1" id="blocked-{{ $calendar->calendar_id }}" @checked($calendar->is_blocked)>
                                <label class="form-check-label small text-danger" for="blocked-{{ $calendar->calendar_id }}">Block</label>
                            </div>
                            <button class="btn btn-sm btn-outline-dark">Update</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">No custom dates yet. Dates without a record default to {{ config('jqfood.default_max_bookings', 2) }} per day.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
