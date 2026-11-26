@extends('layouts.admin')
@section('title', 'Admin Dashboard - JQ Food Hub')
@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Executive Dashboard</h1>
        <p class="text-muted small mb-0">Daily operations overview, verified sales, and pending customer orders.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-jq">
            <i class="bi bi-receipt me-1" aria-hidden="true"></i> Manage All Orders
        </a>
        <a href="{{ route('admin.prep-sheet') }}" class="btn btn-sm btn-outline-dark">
            <i class="bi bi-card-checklist me-1" aria-hidden="true"></i> Daily Prep Sheet
        </a>
    </div>
</div>

<!-- Modern Stat KPI Cards -->
<div class="row g-3 mb-4">
    @php
        $stats = [
            [
                'label' => 'New Orders (Pending)',
                'value' => $newOrders,
                'url' => route('admin.orders.index', ['status' => 'Pending']),
                'icon' => 'bi-hourglass-split',
                'color' => 'bg-warning-subtle text-warning-emphasis',
                'border' => 'border-start border-warning border-4'
            ],
            [
                'label' => 'Pending Payments',
                'value' => $pendingPayments,
                'url' => route('admin.orders.index', ['payment' => 'Pending']),
                'icon' => 'bi-cash-coin',
                'color' => 'bg-danger-subtle text-danger',
                'border' => 'border-start border-danger border-4'
            ],
            [
                'label' => 'Deliveries Today',
                'value' => $deliveriesToday,
                'url' => route('admin.orders.index', ['method' => 'Delivery', 'date' => now()->toDateString()]),
                'icon' => 'bi-truck',
                'color' => 'bg-info-subtle text-primary',
                'border' => 'border-start border-primary border-4'
            ],
            [
                'label' => 'Sales This Month',
                'value' => '₱' . number_format((float) $salesThisMonth, 2),
                'url' => route('admin.reports.index'),
                'icon' => 'bi-graph-up-arrow',
                'color' => 'bg-success-subtle text-success',
                'border' => 'border-start border-success border-4'
            ],
        ];
    @endphp

    @foreach($stats as $stat)
        <div class="col-sm-6 col-xl-3">
            <a class="text-decoration-none" href="{{ $stat['url'] }}">
                <div class="card h-100 card-hover p-3 {{ $stat['border'] }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold mb-1">{{ $stat['label'] }}</div>
                            <div class="fs-3 fw-extrabold text-dark">{{ $stat['value'] }}</div>
                        </div>
                        <div class="stat-icon {{ $stat['color'] }}">
                            <i class="bi {{ $stat['icon'] }} fs-4" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Sales Analytics Chart -->
<div class="card mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h2 class="h5 fw-bold text-dark mb-0">Verified Sales — Last 7 Days</h2>
                <div class="small text-muted">Daily completed and confirmed order revenue in Philippine Pesos</div>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-2 small">
                <i class="bi bi-calendar3 me-1" aria-hidden="true"></i> Weekly Trend
            </span>
        </div>
        <div style="max-height: 320px;">
            <canvas id="sales-chart" data-labels="{{ json_encode($chartLabels) }}" data-values="{{ json_encode($chartValues) }}" aria-label="Sales for the last seven days" role="img"></canvas>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card">
    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
        <h2 class="h5 fw-bold text-dark mb-0">Recent Orders</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-dark">
            View All <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Order Ref</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentOrders as $order)
                <tr>
                    <td class="fw-bold text-dark">
                        <i class="bi bi-receipt text-danger me-1" aria-hidden="true"></i> JQ-{{ str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $order->user->full_name }}</div>
                        <div class="small text-muted">{{ $order->user->email }}</div>
                    </td>
                    <td>{{ $order->calendar->slot_date->format('M j, Y') }}</td>
                    <td class="fw-bold text-danger">₱{{ number_format((float) $order->total_amount, 2) }}</td>
                    <td>
                        @include('partials.status-badge', ['status' => $order->status])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-2 text-muted d-block mb-2" aria-hidden="true"></i>
                        No orders recorded yet.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
