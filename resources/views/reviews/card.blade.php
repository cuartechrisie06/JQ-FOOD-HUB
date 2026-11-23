<article class="card h-100 shadow-sm">
    <div class="card-body">
        <div class="text-warning mb-2" aria-label="Rated {{ $review->rating }} out of 5">
            @for($star = 1; $star <= 5; $star++)
                <span aria-hidden="true">{{ $star <= $review->rating ? '★' : '☆' }}</span>
            @endfor
        </div>
        @if($review->comment)
            <p class="mb-3">{{ $review->comment }}</p>
        @endif
        <div class="small text-muted">
            <strong>{{ $review->user->full_name }}</strong>
            @if($review->order)
                <span> · Order JQ-{{ str_pad((string) $review->order->order_id, 4, '0', STR_PAD_LEFT) }}</span>
            @endif
        </div>
    </div>
</article>
