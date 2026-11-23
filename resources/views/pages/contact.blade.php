@extends('layouts.public')
@section('title', 'Contact and Location')
@section('content')
<h1 class="h2 mb-4">Contact and Location</h1>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card h-100"><div class="card-body">
            <h2 class="h5">JQ Food Products</h2>
            <p><strong>Address:</strong> Villa Patricia Subd., Mankilam, Tagum City</p>
            <p><strong>Phone:</strong> <span class="text-muted">Add official phone number</span></p>
            <p>
                <a class="btn btn-outline-dark me-2" href="https://www.facebook.com/" target="_blank" rel="noopener">Facebook</a>
                <a class="btn btn-jq" href="https://m.me/" target="_blank" rel="noopener">Messenger</a>
            </p>
        </div></div>
    </div>
    <div class="col-md-7">
        <div class="ratio ratio-16x9 border rounded">
            <iframe src="about:blank" title="Map placeholder" aria-label="Map placeholder"></iframe>
        </div>
    </div>
</div>
@endsection
