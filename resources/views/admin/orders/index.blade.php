@extends('layouts.admin')
@section('title', 'Manage Orders - Admin')
@section('content')

<div class="mb-4">
    <h1 class="h3 fw-bold text-dark mb-1">Manage Orders</h1>
    <p class="text-muted small mb-0">Filter by date, status, payment or method, or search by reference and customer.</p>
</div>

@if($pendingBookings->isNotEmpty())
<div class="card border-warning mb-4">
    <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-calendar2-heart me-1" aria-hidden="true"></i> Catering requests awaiting review ({{ $pendingBookings->count() }})</strong>
        <a class="small" href="{{ route('admin.catering-bookings.index') }}">View all requests</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Customer</th><th>Event</th><th>Package</th><th>Guests</th><th class="text-end">Decision</th></tr></thead>
            <tbody>
            @foreach($pendingBookings as $booking)
                <tr>
                    <td><div class="fw-semibold">{{ $booking->user->full_name }}</div><div class="small text-muted">{{ $booking->user->phone }}</div></td>
                    <td><div class="fw-semibold">{{ $booking->calendar->slot_date->format('M j, Y') }} &middot; {{ substr($booking->event_time, 0, 5) }}</div><div class="small text-muted">{{ $booking->event_type }} &middot; {{ $booking->event_location }}</div></td>
                    <td>{{ $booking->foodPackage->package_name }}@if($booking->drink_option)<div class="small text-muted">{{ $booking->drink_option }}</div>@endif</td>
                    <td>{{ $booking->guest_count }} pax</td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.catering-bookings.show', $booking) }}">Details</a>
                            <form method="POST" action="{{ route('admin.catering-bookings.confirm', $booking) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-jq" type="submit">Approve</button></form>
                            <form method="POST" action="{{ route('admin.catering-bookings.reject', $booking) }}" data-confirm="Reject this catering request? The date is released." data-confirm-label="Yes, reject">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger" type="submit">Reject</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" data-autosubmit role="search">
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="filter-date">Fulfillment date</label>
                    <input class="form-control" type="date" id="filter-date" name="date" value="{{ request('date') }}">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="filter-status">Order status</label>
                    <select class="form-select" id="filter-status" name="status">
                        <option value="">All statuses</option>
                        @foreach(['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for Delivery', 'Completed', 'Cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="filter-payment">Payment</label>
                    <select class="form-select" id="filter-payment" name="payment">
                        <option value="">Any payment</option>
                        @foreach(['Pending' => 'Pending review', 'Verified' => 'Verified', 'Rejected' => 'Rejected'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('payment') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="filter-method">Method</label>
                    <select class="form-select" id="filter-method" name="method">
                        <option value="">Pickup &amp; Delivery</option>
                        <option value="Pickup" @selected(request('method') === 'Pickup')>Pickup</option>
                        <option value="Delivery" @selected(request('method') === 'Delivery')>Delivery</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="search">Reference or customer</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-muted" aria-hidden="true"></i></span>
                        <input class="form-control" type="search" id="search" name="search" value="{{ request('search') }}" placeholder="JQ-0001 or name">
                    </div>
                </div>
                <div class="col-lg-1 d-grid">
                    <button class="btn btn-jq" type="submit" aria-label="Apply filters"><i class="bi bi-funnel-fill" aria-hidden="true"></i></button>
                </div>
            </div>
            @if(request()->hasAny(['date', 'status', 'search', 'payment', 'method']))
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between">
                    <span class="small text-muted" role="status">{{ $orders->total() }} {{ Str::plural('order', $orders->total()) }} found</span>
                    <a href="{{ route('admin.orders.index') }}" class="small text-danger text-decoration-none fw-semibold">
                        <i class="bi bi-x-circle" aria-hidden="true"></i> Clear filters
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card overflow-hidden mb-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Fulfillment</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th style="min-width: 230px;">Next step</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                @php
                    $ref = 'JQ-'.str_pad($order->order_id, 4, '0', STR_PAD_LEFT);
                    $allowed = collect($transitions->allowedTransitions($order));
                    $forward = $allowed->reject(fn ($s) => $s === 'Cancelled')->values();
                    $canCancel = $allowed->contains('Cancelled');
                    [$payLabel, $payClass] = match (true) {
                        $order->payments->contains('status', 'Pending') => ['Pending review', 'badge-warning'],
                        $order->payments->contains('status', 'Verified') => ['Verified', 'badge-success'],
                        $order->payments->contains('status', 'Rejected') => ['Rejected', 'badge-danger'],
                        default => ['No payment yet', 'badge-secondary'],
                    };
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold text-dark text-nowrap"><i class="bi bi-receipt text-danger me-1" aria-hidden="true"></i>{{ $ref }}</div>
                        <span class="badge bg-light text-secondary border small">{{ $order->order_type }}</span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $order->user->full_name }}</div>
                        <div class="small text-muted">{{ $order->user->email }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark text-nowrap">{{ $order->calendar->slot_date->format('M j, Y') }}@if($order->fulfillment_time) <span class="fw-normal text-muted">&middot; {{ substr($order->fulfillment_time, 0, 5) }}</span>@endif</div>
                        <div class="small text-muted"><i class="bi {{ $order->fulfillment_method === 'Delivery' ? 'bi-truck' : 'bi-shop' }} me-1" aria-hidden="true"></i>{{ $order->fulfillment_method }}</div>
                    </td>
                    <td class="fw-bold text-danger text-nowrap">₱{{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        <span class="badge-status {{ $payClass }}">{{ $payLabel }}</span>
                        @if($order->payments->isNotEmpty())
                            <div class="small text-muted mt-1">{{ $order->payments->last()->payment_method }}</div>
                        @endif
                        @foreach($order->payments->where('status', 'Pending') as $pendingPayment)
                            <div class="small mt-2 p-2 border rounded bg-light" style="min-width: 190px;">
                                <div class="fw-semibold">{{ $pendingPayment->payment_type }}: ₱{{ number_format($pendingPayment->amount, 2) }}</div>
                                <div class="text-muted">{{ $pendingPayment->payment_method }}@if($pendingPayment->reference_no) &middot; Ref {{ $pendingPayment->reference_no }}@endif</div>
                                @if($pendingPayment->proof_image)
                                    <a href="{{ asset('storage/'.$pendingPayment->proof_image) }}" target="_blank" rel="noopener"><i class="bi bi-image" aria-hidden="true"></i> View proof</a>
                                @elseif($pendingPayment->payment_method !== 'Cash')
                                    <div class="text-muted fst-italic">No proof uploaded yet</div>
                                @endif
                                <div class="d-flex gap-1 mt-2">
                                    <form method="POST" action="{{ route('admin.payments.verify', $pendingPayment) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-jq" type="submit" @disabled($pendingPayment->payment_method !== 'Cash' && ! $pendingPayment->proof_image)>Verify</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.payments.reject', $pendingPayment) }}" data-confirm="Reject this payment? The customer will be asked to send a new proof." data-confirm-label="Yes, reject">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </td>
                    <td>@include('partials.status-badge', ['status' => $order->status])</td>
                    <td>
                        @if($allowed->isNotEmpty())
                            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="d-flex flex-wrap gap-2">
                                @csrf @method('PATCH')
                                @foreach($forward as $index => $status)
                                    <button class="btn btn-sm {{ $index === 0 ? 'btn-jq' : 'btn-outline-dark' }}" type="submit" name="status" value="{{ $status }}">
                                        {{ $index === 0 ? 'Mark' : '' }} {{ $status }}
                                    </button>
                                @endforeach
                                @if($canCancel)
                                    <button class="btn btn-sm btn-outline-danger" type="submit" name="status" value="Cancelled"
                                            data-confirm="Cancel order {{ $ref }}? If it was already confirmed, its stock is returned."
                                            data-confirm-label="Yes, cancel order">
                                        Cancel
                                    </button>
                                @endif
                            </form>
                        @else
                            <span class="text-muted small fst-italic">Finalized</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-2 text-muted d-block mb-2" aria-hidden="true"></i>
                        No orders found matching the filter.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center">
    {{ $orders->links() }}
</div>

@endsection
