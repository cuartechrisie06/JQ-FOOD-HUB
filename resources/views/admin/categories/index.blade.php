@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h1 class="h3">Categories</h1>
<a class="btn btn-jq" href="{{ route('admin.categories.create') }}">Add Category</a>
</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Name</th><th>Description</th><th>Products</th><th></th></tr></thead>
<tbody>
@forelse($categories as $category)
<tr><td>{{ $category->category_name }}</td><td>{{ $category->description }}</td><td>{{ $category->products_count }}</td>
<td class="text-end">
<a class="btn btn-sm btn-outline-dark" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
<form class="d-inline" method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button>
</form>
</td></tr>
@empty
<tr><td colspan="4" class="text-center text-muted py-4">No categories have been created yet.</td></tr>
@endforelse
</tbody>
</table></div></div>
<div class="mt-4">{{ $categories->links() }}</div>
@endsection
