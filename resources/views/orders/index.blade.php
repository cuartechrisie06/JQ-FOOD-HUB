@extends('layouts.public')
@section('title', 'My Orders - JQ Food Hub')
@section('content')

<div class="mb-4">
    <h1 class="h2 fw-bold text-dark mb-1">My Orders</h1>
    <p class="text-muted">Track your food orders and catering reservations.</p>
</div>

@if($bookings->isNotEmpty())
    <h2 class="h5 fw-bold mb-3">Catering requests</h2>
    <div class="card overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light"><tr><th>Event date</th><th>Package</th><th>Guests</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td><div class="fw-semibold text-dark">{{ $booking->calendar->slot_date->format('M j, Y') }}</div><div class="small text-muted">{{ $booking->event_type }} &middot; {{ substr($booking->event_time, 0, 5) }}</div></td>
                            <td>{{ $booking->foodPackage->package_name }}@if($booking->drink_option)<div class="small text-muted">{{ $booking->drink_option }}</div>@endif</td>
                            <td>{{ $booking->guest_count }} pax</td>
                            <td>@include('partials.status-badge', ['status' => $booking->status === 'Pending' ? 'Pending review' : $booking->status])</td>
                            <td class="text-end">
                                @if($booking->status === 'Pending')
                                    <form method="POST" action="{{ route('catering.bookings.cancel', $booking) }}" data-confirm="Cancel this pending catering request? Your selected date will be released." data-confirm-label="Yes, cancel request">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-x-circle" aria-hidden="true"></i> Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <h2 class="h5 fw-bold mb-3">Orders</h2>
@endif

@if($orders->isEmpty())
    <div class="card p-5 text-center my-4">
        <div class="display-5 text-muted mb-3"><i class="bi bi-receipt" aria-hidden="true"></i></div>
        <h2 class="h4 fw-bold text-dark mb-2">No orders placed yet</h2>
        <p class="text-muted col-md-5 mx-auto mb-4">You haven't placed any orders with us yet. Check out our delicious lumpia and catering packages!</p>
        <div>
            <a class="btn btn-jq px-4" href="{{ route('products.index') }}">
                <i class="bi bi-bag-plus" aria-hidden="true"></i> Order Now
            </a>
        </div>
    </div>
@else
    <div class="card overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Order Reference</th>
                        <th>Fulfillment Date</th>
                        <th>Method</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">
                                    <i class="bi bi-receipt me-1 text-danger" aria-hidden="true"></i> JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="small text-muted">{{ $order->order_type }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->calendar->slot_date->format('M j, Y') }}</div>
                                <div class="small text-muted">{{ $order->fulfillment_time ? substr($order->fulfillment_time, 0, 5) : '' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="bi {{ $order->fulfillment_method === 'Delivery' ? 'bi-truck' : 'bi-shop' }} me-1" aria-hidden="true"></i>
                                    {{ $order->fulfillment_method }}
                                </span>
                            </td>
                            <td class="fw-bold text-danger text-nowrap">
                                ₱{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td>
                                @include('partials.status-badge', ['status' => $order->status])
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-dark" href="{{ route('orders.show', $order) }}">
                                    Details <i class="bi bi-chevron-right ms-1" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
@endif

@endsection
