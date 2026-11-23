@extends('layouts.admin')
@section('title', 'Reports')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h3 mb-0">Reports</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-danger" href="{{ route('admin.reports.pdf', request()->only('from', 'to')) }}">Export PDF</a>
        <a class="btn btn-outline-success" href="{{ route('admin.reports.csv', request()->only('from', 'to')) }}">Export CSV</a>
    </div>
</div>
<form class="row g-2 align-items-end mb-4" method="GET" action="{{ route('admin.reports.index') }}">
    <div class="col-auto"><label class="form-label" for="from">From</label><input class="form-control" id="from" name="from" type="date" value="{{ $from->toDateString() }}"></div>
    <div class="col-auto"><label class="form-label" for="to">To</label><input class="form-control" id="to" name="to" type="date" value="{{ $to->toDateString() }}"></div>
    <div class="col-auto"><button class="btn btn-jq">Apply</button></div>
</form>
<p class="text-muted">Report period: {{ $from->format('M j, Y') }} – {{ $to->format('M j, Y') }}</p>
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card h-100"><div class="card-body"><div class="text-muted">Orders</div><div class="fs-3 fw-bold">{{ $ordersCount }}</div></div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-body"><div class="text-muted">Verified Sales</div><div class="fs-3 fw-bold">₱{{ number_format((float) $totalSales, 2) }}</div></div></div></div>
</div>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Orders by Status</h2>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Status</th><th>Orders</th></tr></thead><tbody>
    @foreach($statusCounts as $status => $count)<tr><td>{{ $status }}</td><td>{{ $count }}</td></tr>@endforeach
    </tbody></table></div>
</div></div>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Verified Sales by Date</h2>
    <canvas id="sales-chart" data-labels="{{ json_encode($chartLabels) }}" data-values="{{ json_encode($chartValues) }}" aria-label="Verified sales for report period" role="img"></canvas>
</div></div>
<div class="card"><div class="card-body">
    <h2 class="h5">Best-Selling Products</h2>
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Product</th><th>Quantity sold</th></tr></thead><tbody>
    @forelse($bestSellers as $product)<tr><td>{{ $product->product_name }}</td><td>{{ $product->quantity_sold }}</td></tr>
    @empty<tr><td colspan="2" class="text-center text-muted">No product sales in this period.</td></tr>@endforelse
    </tbody></table></div>
</div></div>
@endsection
