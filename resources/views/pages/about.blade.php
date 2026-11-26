@extends('layouts.public')
@section('title', 'About Us - JQ Food Hub')
@section('content')

<div class="mb-4 text-center col-lg-8 mx-auto">
    <img src="{{ asset('images/logo.png') }}" alt="JQ Food Hub Logo" class="rounded-circle shadow-sm mb-3 border border-2 border-dark" style="width: 100px; height: 100px; object-fit: contain; background: #ffffff; padding: 6px;">
    <div>
        <span class="badge bg-danger-subtle text-danger fw-bold mb-2">
            <i class="bi bi-heart-fill"></i> Our Journey
        </span>
    </div>
    <h1 class="display-6 fw-extrabold text-dark mb-2">The Story Behind JQ Food Hub</h1>
    <p class="lead text-muted">From homemade cravings to Tagum City's trusted lumpia and catering choice.</p>
</div>

<div class="row g-4 align-items-center mb-5">
    <div class="col-lg-6">
        <div class="card p-4 p-md-5 h-100">
            <h3 class="h4 fw-bold text-dark mb-3">Our Roots in Mankilam</h3>
            <p class="text-secondary leading-relaxed mb-3">
                JQ Food Products began its journey as <strong>Deliciousnest in 2018</strong>, crafting handmade lumpia rolls with an authentic recipe cherished by friends and neighbors. Through passion, consistent quality, and word of mouth, we evolved and rebranded in <strong>2023 as JQ Food Hub</strong>.
            </p>
            <p class="text-secondary leading-relaxed mb-0">
                Today, our family-run kitchen in <strong>Villa Patricia Subd., Mankilam, Tagum City</strong> prepares daily fresh lumpia batches and complete catering services for birthdays, corporate functions, family reunions, and school events across the Davao Region.
            </p>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="card p-4 text-center h-100">
                    <div class="stat-icon bg-warning-subtle text-warning-emphasis mx-auto mb-3">
                        <i class="bi bi-calendar2-check-fill text-warning fs-3"></i>
                    </div>
                    <div class="fs-3 fw-extrabold text-dark">2018</div>
                    <div class="small text-muted">Founded as Deliciousnest</div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card p-4 text-center h-100">
                    <div class="stat-icon bg-danger-subtle text-danger mx-auto mb-3">
                        <i class="bi bi-fire fs-3"></i>
                    </div>
                    <div class="fs-3 fw-extrabold text-dark">100%</div>
                    <div class="small text-muted">Fresh Daily Homemade</div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card p-4 text-center h-100">
                    <div class="stat-icon bg-success-subtle text-success mx-auto mb-3">
                        <i class="bi bi-geo-alt-fill fs-3"></i>
                    </div>
                    <div class="fs-4 fw-extrabold text-dark">Tagum City</div>
                    <div class="small text-muted">Davao Region Hub</div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card p-4 text-center h-100">
                    <div class="stat-icon bg-primary-subtle text-primary mx-auto mb-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div class="fs-4 fw-extrabold text-dark">Events</div>
                    <div class="small text-muted">Full-service Catering</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card p-4 p-md-5 text-center bg-light">
    <h3 class="h4 fw-bold text-dark mb-2">Taste the JQ Difference Today</h3>
    <p class="text-muted col-md-6 mx-auto mb-4">Explore our delicious selection of lumpia rolls or inquire about our catering packages for your next event.</p>
    <div class="d-flex justify-content-center gap-3">
        <a href="{{ route('products.index') }}" class="btn btn-jq px-4">Browse Menu</a>
        <a href="{{ route('contact') }}" class="btn btn-outline-dark px-4">Get in Touch</a>
    </div>
</div>

@endsection
