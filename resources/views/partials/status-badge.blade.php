@php
    $badgeClass = match ($status) {
        'Completed', 'Confirmed', 'Approved', 'Verified' => 'badge-success',
        'Ready', 'Out for Delivery' => 'badge-info',
        'Rejected' => 'badge-danger',
        'Cancelled', 'Hidden' => 'badge-secondary',
        default => 'badge-warning',
    };
@endphp
<span class="badge-status {{ $badgeClass }}">{{ $status }}</span>
