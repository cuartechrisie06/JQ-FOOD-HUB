@extends('layouts.public')
@section('title', 'Order Confirmation')
@section('content')
<div class="card"><div class="card-body text-center py-5">
    <h1 class="h2">Order received</h1>
    <p class="lead">Reference number: <strong>JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}</strong></p>
    <p>Amount due now: <strong>PHP {{ number_format($amountDueNow, 2) }}</strong></p>
    <p class="text-muted">Your order is pending review. Payment proof upload will be available in a later step.</p>
    <a class="btn btn-jq" href="#">Upload Payment (coming soon)</a>
    <a class="btn btn-outline-dark" href="{{ route('orders.show', $order) }}">View Order</a>
</div></div>
@endsection
