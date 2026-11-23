@extends('layouts.admin')
@section('title', 'Manage Orders')
@section('content')
<h1 class="h3 mb-3">Manage Orders</h1>
<form method="GET" action="{{ route('admin.orders.index') }}" class="card card-body mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label" for="filter-date">Fulfillment Date</label><input class="form-control" type="date" id="filter-date" name="date" value="{{ request('date') }}"></div>
        <div class="col-md-3">
            <label class="form-label" for="filter-status">Status</label>
            <select class="form-select" id="filter-status" name="status">
                <option value="">All statuses</option>
                @foreach(['Pending', 'Confirmed', 'Preparing', 'Ready', 'Out for Delivery', 'Completed', 'Cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label" for="search">Order number or customer</label><input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="JQ-0001 or customer name"></div>
        <div class="col-md-2 d-grid"><button class="btn btn-jq">Filter</button></div>
    </div>
</form>

<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Order</th><th>Customer</th><th>Fulfillment</th><th>Date</th><th>Total</th><th>Status</th><th>Update Status</th></tr></thead>
    <tbody>
    @forelse($orders as $order)
        @php($allowedTransitions = $transitions->allowedTransitions($order))
        <tr>
            <td>JQ-{{ str_pad($order->order_id, 4, '0', STR_PAD_LEFT) }}<div class="small text-muted">{{ $order->order_type }}</div></td>
            <td>{{ $order->user->full_name }}<div class="small text-muted">{{ $order->user->email }}</div></td>
            <td>{{ $order->fulfillment_method }}</td>
            <td>{{ $order->calendar->slot_date->format('M j, Y') }}</td>
            <td>PHP {{ number_format($order->total_amount, 2) }}</td>
            <td><span class="badge {{ $order->status === 'Completed' ? 'text-bg-success' : ($order->status === 'Cancelled' ? 'text-bg-secondary' : 'text-bg-warning') }}">{{ $order->status }}</span></td>
            <td>
                @if($allowedTransitions)
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="d-flex gap-2">
                        @csrf @method('PATCH')
                        <select class="form-select form-select-sm" name="status" aria-label="Next order status">
                            @foreach($allowedTransitions as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-dark">Save</button>
                    </form>
                @else
                    <span class="text-muted">No further actions</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
