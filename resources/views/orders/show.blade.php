@extends('layouts.public')
@section('title', 'Order Details')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h1 class="h2 mb-0">Order JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}</h1>
    <span class="badge text-bg-warning fs-6">{{ $order->status }}</span>
</div>
<div class="card mb-4"><div class="card-body">
    <div class="row">
        <div class="col-md-6"><strong>Type:</strong> {{ $order->order_type }}</div>
        <div class="col-md-6"><strong>Fulfillment:</strong> {{ $order->fulfillment_method }}</div>
        <div class="col-md-6"><strong>Date:</strong> {{ $order->calendar->slot_date->format('M j, Y') }}</div>
        <div class="col-md-6"><strong>Time:</strong> {{ substr($order->fulfillment_time, 0, 5) }}</div>
        @if($order->delivery_address)<div class="col-12"><strong>Delivery Address:</strong> {{ $order->delivery_address }}</div>@endif
    </div>
</div></div>
<div class="card mb-4"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
    <tbody>@foreach($order->items as $item)<tr><td>{{ $item->product->product_name ?? 'Product unavailable' }}</td><td>{{ $item->quantity }}</td><td>PHP {{ number_format($item->unit_price, 2) }}</td><td>PHP {{ number_format($item->subtotal, 2) }}</td></tr>@endforeach</tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total</th><th>PHP {{ number_format($order->total_amount, 2) }}</th></tr><tr><th colspan="3" class="text-end">Down Payment</th><th>PHP {{ number_format($order->downpayment_amount, 2) }}</th></tr></tfoot>
</table></div></div>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Status Timeline</h2>
    <ol class="mb-0">
        @foreach($timeline as $entry)
            <li class="mb-2"><strong>{{ $entry['status'] }}</strong> — {{ $entry['message'] }} <small class="text-muted">{{ $entry['created_at']?->format('M j, Y g:i A') }}</small></li>
        @endforeach
    </ol>
</div></div>
<a class="btn btn-outline-dark" href="{{ route('orders.index') }}">Back to My Orders</a>
@endsection
