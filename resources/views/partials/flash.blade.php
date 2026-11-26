@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="status" data-autodismiss>
        <i class="bi bi-check-circle-fill text-success fs-5" aria-hidden="true"></i>
        <div>{{ session('status') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5" aria-hidden="true"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
