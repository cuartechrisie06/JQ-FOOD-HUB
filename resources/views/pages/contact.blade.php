@extends('layouts.public')
@section('title', 'Contact & Location - JQ Food Hub')
@section('content')

<div class="mb-4">
    <span class="badge bg-danger-subtle text-danger fw-bold mb-1">
        <i class="bi bi-geo-alt-fill"></i> Location & Contact
    </span>
    <h1 class="h2 fw-bold text-dark mb-1">Visit or Contact JQ Food Hub</h1>
    <p class="text-muted">We are based in Mankilam, Tagum City. Reach out to us for lumpia pickups, deliveries, and catering reservations.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card p-4 h-100">
            <h2 class="h5 fw-bold text-dark mb-3">Kitchen & Store Information</h2>
            
            <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                <div class="stat-icon fs-5 bg-danger-subtle text-danger rounded-3">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <strong class="text-dark d-block">Store Address</strong>
                    <span class="text-secondary small">Villa Patricia Subd., Mankilam, Tagum City, Davao del Norte</span>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                <div class="stat-icon fs-5 bg-warning-subtle text-warning-emphasis rounded-3">
                    <i class="bi bi-clock-fill text-warning"></i>
                </div>
                <div>
                    <strong class="text-dark d-block">Operating Hours</strong>
                    <span class="text-secondary small">Monday – Saturday: 8:00 AM – 6:00 PM<br>Sunday: By advance reservation only</span>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3 mb-4">
                <div class="stat-icon fs-5 bg-primary-subtle text-primary rounded-3">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div>
                    <strong class="text-dark d-block">Quick Inquiries & Messaging</strong>
                    <span class="text-secondary small">Chat directly with our team for quick orders and special catering quotes.</span>
                </div>
            </div>

            <div class="mt-auto pt-2 d-flex flex-wrap gap-2">
                <a class="btn btn-jq flex-grow-1" href="https://m.me/100085901478582" target="_blank" rel="noopener">
                    <i class="bi bi-messenger"></i> Chat on Messenger
                </a>
                <a class="btn btn-outline-dark" href="https://www.facebook.com/profile.php?id=100085901478582" target="_blank" rel="noopener">
                    <i class="bi bi-facebook text-primary" aria-hidden="true"></i> Facebook Page
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card overflow-hidden h-100">
            <div class="card-body p-0 d-flex flex-column">
                <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <span class="small fw-bold text-dark"><i class="bi bi-map me-1 text-danger"></i> Tagum City Kitchen Map</span>
                    
                </div>
                <div class="ratio ratio-16x9 flex-grow-1" style="min-height: 340px;">
                    <iframe 
                        src="https://maps.google.com/maps?q=Mankilam%20Tagum%20City&t=&z=14&ie=UTF8&iwloc=&output=embed" 
                        title="JQ Food Hub Location Map" 
                        aria-label="JQ Food Hub Location Map"
                        style="border:0;"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
