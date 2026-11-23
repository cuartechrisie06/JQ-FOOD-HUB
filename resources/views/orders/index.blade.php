@extends('layouts.public')
@section('title', 'My Orders')
@section('content')
<h1 class="h2 mb-4">My Orders</h1>
@if($orders->isEmpty())
    <div class="alert alert-light border">You have no orders yet. <a href="{{ route('products.index') }}">Browse products</a>.</div>
@else
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Reference</th><th>Date</th><th>Fulfillment</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $order->calendar->slot_date->format('M j, Y') }}</td>
                <td>{{ $order->fulfillment_method }}</td>
                <td>PHP {{ number_format($order->total_amount, 2) }}</td>
                <td><span class="badge text-bg-warning">{{ $order->status }}</span></td>
                <td><a class="btn btn-sm btn-outline-dark" href="{{ route('orders.show', $order) }}">Details</a></td>
            </tr>
        @endforeach
        </tbody>
    </table></div></div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
