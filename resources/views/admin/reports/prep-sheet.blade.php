@extends('layouts.admin')
@section('title', 'Daily Prep Sheet')
@section('content')
<style>
    @media print {
        .sidebar, .topbar, .no-print { display: none !important; }
        .admin-main { width: 100% !important; }
        .admin-main > .p-4 { padding: 0 !important; }
    }
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h3 mb-0">Daily Prep Sheet</h1>
    <div class="d-flex gap-2 no-print">
        <button class="btn btn-outline-dark" onclick="window.print()">Print</button>
        <a class="btn btn-outline-danger" href="{{ route('admin.prep-sheet.pdf', ['date' => $date->toDateString()]) }}">Export PDF</a>
        <a class="btn btn-outline-success" href="{{ route('admin.prep-sheet.csv', ['date' => $date->toDateString()]) }}">Export CSV</a>
    </div>
</div>
<form class="row g-2 align-items-end mb-4 no-print" method="GET" action="{{ route('admin.prep-sheet') }}">
    <div class="col-auto"><label class="form-label" for="date">Preparation date</label>
        <input class="form-control" id="date" name="date" type="date" value="{{ $date->toDateString() }}">
    </div>
    <div class="col-auto"><button class="btn btn-jq">Show</button></div>
</form>
<p class="fw-semibold">{{ $date->format('l, F j, Y') }}</p>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Product Preparation</h2>
    <div class="table-responsive"><table class="table table-bordered">
        <thead><tr><th>Product</th><th>Total quantity</th></tr></thead>
        <tbody>
        @forelse($sheet['products'] as $product)
            <tr><td>{{ $product['name'] }}</td><td>{{ $product['quantity'] }}</td></tr>
        @empty
            <tr><td colspan="2" class="text-center text-muted">No product preparation for this date.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div></div>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Packages</h2>
    <div class="table-responsive"><table class="table table-bordered">
        <thead><tr><th>Package</th><th>Quantity</th><th>Order</th><th>Customer</th></tr></thead>
        <tbody>
        @forelse($sheet['packages'] as $package)
            <tr><td>{{ $package['name'] }}</td><td>{{ $package['quantity'] }}</td><td>{{ $package['order'] }}</td><td>{{ $package['customer'] }}</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No packages for this date.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div></div>
<div class="card"><div class="card-body">
    <h2 class="h5">Catering Events</h2>
    <div class="table-responsive"><table class="table table-bordered">
        <thead><tr><th>Package</th><th>Guests</th><th>Time</th><th>Location</th><th>Order</th><th>Customer</th></tr></thead>
        <tbody>
        @forelse($sheet['events'] as $event)
            <tr><td>{{ $event['package'] }}</td><td>{{ $event['guests'] }}</td><td>{{ $event['time'] }}</td><td>{{ $event['location'] }}</td><td>{{ $event['order'] }}</td><td>{{ $event['customer'] }}</td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No catering events for this date.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div></div>
@endsection
