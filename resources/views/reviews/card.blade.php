<article class="card h-100 card-hover border-0 shadow-sm p-3">
    <div class="card-body p-2 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex text-warning gap-1" aria-label="Rated {{ $review->rating }} out of 5">
                @for($star = 1; $star <= 5; $star++)
                    <i class="bi {{ $star <= $review->rating ? 'bi-star-fill text-warning' : 'bi-star text-secondary opacity-25' }}"></i>
                @endfor
            </div>
            <span class="badge bg-success-subtle text-success small rounded-pill px-2 py-1">
                <i class="bi bi-patch-check-fill"></i> Verified Order
            </span>
        </div>

        @if($review->comment)
            <p class="mb-3 text-secondary fst-italic">"{{ $review->comment }}"</p>
        @else
            <p class="mb-3 text-muted fst-italic small">No written comment provided.</p>
        @endif

        <div class="mt-auto pt-2 border-top border-light-subtle d-flex align-items-center gap-2">
            <div class="rounded-circle bg-warning bg-opacity-25 text-warning-emphasis fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                {{ strtoupper(substr($review->user->full_name ?? 'C', 0, 1)) }}
            </div>
            <div class="small">
                <strong class="text-dark d-block">{{ $review->user->full_name }}</strong>
                @if($review->order)
                    <span class="text-muted">Order JQ-{{ str_pad((string) $review->order->order_id, 4, '0', STR_PAD_LEFT) }}</span>
                @endif
            </div>
        </div>
    </div>
</article>
