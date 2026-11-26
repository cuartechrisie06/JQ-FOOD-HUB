@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h1 class="h3">Products</h1>
<a class="btn btn-jq" href="{{ route('admin.products.create') }}">Add Product</a>
</div>

<div class="card mb-4"><div class="card-body">
<h2 class="h5">CSV Import</h2>
<form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-end" data-once>
@csrf
<div class="flex-grow-1"><label class="form-label" for="csv_file">CSV File</label><input id="csv_file" class="form-control" type="file" name="csv_file" accept=".csv,text/csv" required></div>
<button class="btn btn-jq">Import</button>
<a class="btn btn-outline-dark" href="{{ route('admin.products.import.template') }}">Download Template</a>
</form>
@if(session('import_report'))
<hr>
<h3 class="h6">Import Report</h3>
<ul class="mb-0">
@foreach(session('import_report') as $result)
<li>Row {{ $result['row'] }}: <strong>{{ ucfirst($result['status']) }}</strong> - {{ $result['message'] }}</li>
@endforeach
</ul>
@endif
</div></div>

<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
<thead><tr><th>Photo</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Lead Time</th><th>Available</th><th></th></tr></thead>
<tbody>
@forelse($products as $product)
<tr>
<td><img src="{{ $product->image_url }}" alt="" class="rounded-2" style="width:56px;height:42px;object-fit:cover">@unless($product->has_image)<div class="badge-status badge-warning mt-1"><i class="bi bi-camera" aria-hidden="true"></i> No photo</div>@endunless</td><td>{{ $product->product_name }}</td><td>{{ $product->category->category_name }}</td><td>₱{{ number_format($product->unit_price, 2) }}</td><td>{{ $product->stock_qty }}</td><td>{{ $product->lead_time_days }} day(s)</td>
<td><span class="badge-status {{ $product->is_available ? 'badge-success' : 'badge-secondary' }}">{{ $product->is_available ? 'Active' : 'Deactivated' }}</span></td>
<td class="text-end">
<a class="btn btn-sm btn-outline-dark" href="{{ route('admin.products.edit', $product) }}">Edit</a>
<form class="d-inline" method="POST" action="{{ route('admin.products.toggle', $product) }}">
@csrf @method('PATCH')<button class="btn btn-sm {{ $product->is_available ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $product->is_available ? 'Deactivate' : 'Activate' }}</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="8" class="text-center text-muted py-4">No products found. Add your first product above.</td></tr>
@endforelse
</tbody>
</table></div></div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
