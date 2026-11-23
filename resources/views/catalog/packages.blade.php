@extends('layouts.public')
@section('title', 'Food Packages')
@section('content')
<h1 class="h2 mb-4">Food Packages and Catering Services</h1>
@if($packages->isEmpty())
    <div class="alert alert-light border">No catering packages are available yet.</div>
@else
    <div class="row g-4">
        @foreach($packages as $package)
            @php
                $hasImage = $package->image_path
                    && (str_starts_with($package->image_path, 'http://') || str_starts_with($package->image_path, 'https://') || file_exists(storage_path('app/public/' . $package->image_path)));
                $imageUrl = $hasImage
                    ? (str_starts_with($package->image_path, 'http') ? $package->image_path : asset('storage/' . $package->image_path))
                    : asset('images/placeholder-product.svg');
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <img class="product-image card-img-top" src="{{ $imageUrl }}" alt="{{ $package->package_name }}">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h5">{{ $package->package_name }}</h2>
                        <p class="text-muted">{{ $package->event_type }} | {{ $package->min_pax }}-{{ $package->max_pax }} guests</p>
                        <p>{{ $package->description ?: 'Catering package.' }}</p>
                        @if($package->items->isNotEmpty())
                            <ul class="mb-3">
                                @foreach($package->items as $item)
                                    <li>{{ $item->item_description }} - {{ $item->quantity }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <p class="fw-bold mt-auto">PHP {{ number_format($package->price, 2) }}</p>
                        <a class="btn btn-jq" href="{{ auth()->check() && auth()->user()->role->role_name === 'Customer' ? route('catering.index') : route('login') }}">Book Now</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $packages->links() }}</div>
@endif
@endsection
