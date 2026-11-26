@extends('layouts.public')
@section('title', 'Order Details - JQ Food Hub')
@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}" class="text-decoration-none text-muted">My Orders</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}</li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h2 mb-0 fw-bold">Order JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}</h1>
    @include('partials.status-badge', ['status' => $order->status])
</div>

<div class="card mb-4"><div class="card-body">
    <div class="row g-3">
        <div class="col-md-6"><div class="small text-muted">Type</div><strong>{{ $order->order_type }}</strong></div>
        <div class="col-md-6"><div class="small text-muted">Fulfillment</div><strong>{{ $order->fulfillment_method }}</strong></div>
        <div class="col-md-6"><div class="small text-muted">Date</div><strong>{{ $order->calendar->slot_date->format('M j, Y') }}</strong></div>
        <div class="col-md-6"><div class="small text-muted">Time</div><strong>{{ substr($order->fulfillment_time, 0, 5) }}</strong></div>
        @if($order->delivery_address)<div class="col-12"><div class="small text-muted">Delivery address</div><strong>{{ $order->delivery_address }}</strong></div>@endif
    </div>
</div></div>

<div class="card mb-4"><div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Item</th><th>Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>@foreach($order->items as $item)<tr><td>{{ $item->product->product_name ?? $item->foodPackage->package_name ?? 'Item unavailable' }}</td><td>{{ $item->quantity }}</td><td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td><td class="text-end">₱{{ number_format($item->subtotal, 2) }}</td></tr>@endforeach</tbody>
    <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th class="text-end text-danger">₱{{ number_format($order->total_amount, 2) }}</th></tr>
        <tr><th colspan="3" class="text-end">Down Payment</th><th class="text-end">₱{{ number_format($order->downpayment_amount, 2) }}</th></tr>
    </tfoot>
</table></div></div>

@include('orders._payment', ['order' => $order])

<div class="card mb-4"><div class="card-body">
    <h2 class="h5 fw-bold mb-3">Status Timeline</h2>
    <ol class="timeline">
        @foreach($timeline as $entry)
            <li>
                <strong>{{ $entry['status'] }}</strong> &mdash; {{ $entry['message'] }}
                <div class="small text-muted">{{ $entry['created_at']?->format('M j, Y g:i A') }}</div>
            </li>
        @endforeach
    </ol>
</div></div>
<a class="btn btn-outline-dark" href="{{ route('orders.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to My Orders</a>
@endsection
