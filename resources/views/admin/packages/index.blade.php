@extends('layouts.admin')
@section('title', 'Food Packages')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h1 class="h3">Food Packages</h1>
<a class="btn btn-jq" href="{{ route('admin.packages.create') }}">Add Package</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
<thead><tr><th>Package</th><th>Event</th><th>Price</th><th>Pax</th><th>Available</th><th></th></tr></thead>
<tbody>
@forelse($packages as $package)
<tr><td>{{ $package->package_name }}</td><td>{{ $package->event_type }}</td><td>₱{{ number_format($package->price, 2) }}</td><td>{{ ($package->min_pax && $package->max_pax) ? "{$package->min_pax}-{$package->max_pax}" : ($package->min_pax ? "Min {$package->min_pax}" : '—') }}</td>
<td><span class="badge-status {{ $package->is_available ? 'badge-success' : 'badge-secondary' }}">{{ $package->is_available ? 'Active' : 'Deactivated' }}</span></td>
<td class="text-end">
<a class="btn btn-sm btn-outline-dark" href="{{ route('admin.packages.edit', $package) }}">Edit</a>
<form class="d-inline" method="POST" action="{{ route('admin.packages.toggle', $package) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $package->is_available ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $package->is_available ? 'Deactivate' : 'Activate' }}</button></form>
</td></tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-4">No food packages have been created yet.</td></tr>
@endforelse
</tbody>
</table></div></div>
<div class="mt-4">{{ $packages->links() }}</div>
@endsection
