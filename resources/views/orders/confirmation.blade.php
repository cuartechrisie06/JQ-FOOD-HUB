@extends('layouts.public')
@section('title', 'Order Confirmation')
@section('content')
<div class="card mb-4"><div class="card-body text-center py-5">
    <i class="bi bi-bag-check-fill text-success display-5" aria-hidden="true"></i>
    <h1 class="h2 mt-2">Order received</h1>
    <p class="lead mb-1">Reference number: <strong>JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}</strong></p>
    @if($amountDueNow > 0)
        <p class="mb-1">Amount due now: <strong class="text-danger">₱{{ number_format($amountDueNow, 2) }}</strong></p>
    @endif
    <p class="text-muted mb-4">Your order is pending review. Send your payment below so we can confirm it.</p>
    <a class="btn btn-outline-dark" href="{{ route('orders.show', $order) }}">View Order</a>
</div></div>

@include('orders._payment', ['order' => $order])
@endsection
