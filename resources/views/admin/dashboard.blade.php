@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('content')
<h1 class="h3 mb-4">Admin Dashboard</h1>
<div class="row g-3 mb-4">
    @foreach([
        ['New Orders (Pending)', $newOrders, 'admin.orders.index'],
        ['Pending Payments', $pendingPayments, 'admin.orders.index'],
        ['Deliveries Today', $deliveriesToday, 'admin.orders.index'],
        ['Sales This Month', '₱'.number_format((float) $salesThisMonth, 2), 'admin.reports.index'],
    ] as [$label, $value, $link])
        <div class="col-sm-6 col-xl-3">
            <a class="text-decoration-none" href="{{ route($link) }}">
                <div class="card h-100 border-warning shadow-sm"><div class="card-body">
                    <div class="text-muted">{{ $label }}</div><div class="fs-3 fw-bold text-dark">{{ $value }}</div>
                </div></div>
            </a>
        </div>
    @endforeach
</div>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Verified Sales - Last 7 Days</h2>
    <canvas id="sales-chart" data-labels="{{ json_encode($chartLabels) }}" data-values="{{ json_encode($chartValues) }}" aria-label="Sales for the last seven days" role="img"></canvas>
</div></div>
<div class="card"><div class="card-body">
    <h2 class="h5">Recent Orders</h2>
    <div class="table-responsive"><table class="table table-striped align-middle mb-0">
        <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($recentOrders as $order)
            <tr>
                <td>JQ-{{ str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $order->user->full_name }}</td>
                <td>{{ $order->calendar->slot_date->format('M j, Y') }}</td>
                <td>₱{{ number_format((float) $order->total_amount, 2) }}</td>
                <td>{{ $order->status }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No orders yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div></div>
@endsection
