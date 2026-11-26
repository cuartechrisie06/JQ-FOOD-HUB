@php
    $perPax = $package->pricing_type === 'Per Pax';
    $shortName = $package->package_group && str_starts_with($package->package_name, $package->package_group.' - ')
        ? substr($package->package_name, strlen($package->package_group) + 3)
        : $package->package_name;
@endphp
<div class="card h-100 card-hover pkg-card {{ ($selected?->package_id ?? null) === $package->package_id ? 'border-warning border-2' : '' }}">
    <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <h3 class="h5 fw-bold mb-0">{{ $shortName }}</h3>
            @if($perPax && $package->min_pax)
                <span class="badge text-bg-light border flex-shrink-0">Min. {{ $package->min_pax }} pax</span>
            @endif
        </div>

        @if($package->items->isNotEmpty())
            <ul class="pkg-items list-unstyled mb-3">
                @foreach($package->items as $item)
                    <li><i class="bi bi-check2 text-success" aria-hidden="true"></i>
                        @if($item->quantity && $item->quantity !== '1')<strong>{{ $item->quantity }}</strong> @endif{{ $item->item_description }}
                    </li>
                @endforeach
            </ul>
        @endif

        @if($package->freebie)
            <p class="small mb-3"><i class="bi bi-gift-fill text-success" aria-hidden="true"></i> <strong>Freebie:</strong> {{ $package->freebie }}</p>
        @endif

        <div class="mt-auto pt-3 border-top">
            @if($perPax)
                <dl class="pkg-prices mb-3">
                    <div><dt>Solo</dt><dd>₱{{ number_format($package->price, 0) }}</dd></div>
                    @if($package->price_with_water)<div><dt>+ Water</dt><dd>₱{{ number_format($package->price_with_water, 0) }}</dd></div>@endif
                    @if($package->price_with_softdrinks)<div><dt>+ Softdrinks</dt><dd>₱{{ number_format($package->price_with_softdrinks, 0) }}</dd></div>@endif
                </dl>
                <p class="small text-muted mb-3">Price per person</p>
            @else
                <div class="mb-3">
                    <span class="small text-muted d-block">Package price</span>
                    <span class="fs-4 fw-bold text-danger">₱{{ number_format($package->price, 2) }}</span>
                </div>
            @endif

            @if($role === null)
                <a class="btn btn-jq w-100" href="{{ route('login') }}">Log in to book</a>
            @elseif($role === 'Customer')
                <a class="btn btn-jq w-100" href="{{ route('menu.index', ['tab' => $tab, 'package' => $package->package_id]) }}#booking"><i class="bi bi-calendar-plus" aria-hidden="true"></i> Book this package</a>
            @endif
        </div>
    </div>
</div>
