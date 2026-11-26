{{-- Catering booking: lives at the bottom of the catering tabs of the menu (customers only). --}}
<section id="booking" class="mt-5" style="scroll-margin-top: 90px;">
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-calendar-heart fs-4 text-danger" aria-hidden="true"></i>
        <h2 class="h4 fw-bold mb-0">Book this for your event</h2>
    </div>

    @if(! $selectedPackage)
        <div class="card p-4 text-center">
            <p class="mb-1 fw-semibold">Choose a package above to start your booking.</p>
            <p class="text-muted small mb-0">Tap <strong>Book this package</strong> on any card. Bookings must be made at least 3 days before your event.</p>
        </div>
    @else
        @php
            $pkg = $selectedPackage;
            $perPax = $pkg->pricing_type === 'Per Pax';
            $paxLabel = ($pkg->min_pax && $pkg->max_pax) ? "{$pkg->min_pax}-{$pkg->max_pax} guests" : ($pkg->min_pax ? "Min. {$pkg->min_pax} guests" : 'No guest minimum');
        @endphp

        <div class="card mb-4 border-warning" id="selected-package"
             data-min="{{ $pkg->min_pax }}" data-max="{{ $pkg->max_pax }}"
             data-pricing-type="{{ $pkg->pricing_type }}" data-price="{{ $pkg->price }}"
             data-price-water="{{ $pkg->price_with_water }}" data-price-softdrinks="{{ $pkg->price_with_softdrinks }}"
             data-freebie="{{ $pkg->freebie }}">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="small text-muted">Selected package</div>
                    <div class="fw-bold fs-5">{{ $pkg->package_name }}</div>
                    <div class="small text-muted">{{ $paxLabel }} &middot; {{ $perPax ? '₱'.number_format($pkg->price, 2).' per person (solo)' : '₱'.number_format($pkg->price, 2).' package price' }}</div>
                </div>
                <a class="btn btn-outline-dark btn-sm" href="{{ route('menu.index', ['tab' => $tab]) }}#booking"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Change package</a>
            </div>
        </div>

        <div class="card cal-card p-3 p-md-4 mb-4">
            <h3 class="h6 fw-bold mb-3">1. Pick an available date</h3>
            <div class="cal-legend mb-3" aria-label="Calendar legend">
                <span><i class="lg-available" aria-hidden="true"></i> Open</span>
                <span><i class="lg-full" aria-hidden="true"></i> Fully booked</span>
                <span><i class="lg-blocked" aria-hidden="true"></i> Closed / too soon</span>
                <span><i class="lg-selected" aria-hidden="true"></i> Your date</span>
            </div>
            <div id="catering-calendar" data-catering-calendar data-events-url="{{ route('catering.events') }}" data-earliest-date="{{ $earliestDate }}"></div>
            <div id="calendar-selection" class="cal-selection">
                <i class="bi bi-calendar-check fs-5 text-danger" aria-hidden="true"></i>
                <span id="calendar-selection-message" aria-live="polite">Tap an open date to select it.</span>
            </div>
        </div>

        <div class="card p-4 mb-4" id="booking-form" style="scroll-margin-top: 90px;">
            <h3 class="h6 fw-bold mb-3">2. Event details</h3>

            @if($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('catering.bookings.store') }}" class="row g-3" data-once>
                @csrf
                <input type="hidden" name="package_id" value="{{ $pkg->package_id }}">
                <div class="col-md-6">
                    <label class="form-label" for="slot_date">Event date</label>
                    <input class="form-control" type="date" id="slot_date" name="slot_date" min="{{ $earliestDate }}" value="{{ old('slot_date') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="event_time">Event time</label>
                    <input class="form-control" type="time" id="event_time" name="event_time" value="{{ old('event_time') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="event_type">Event type</label>
                    <input class="form-control" id="event_type" name="event_type" maxlength="50" list="event-types" value="{{ old('event_type') }}" placeholder="Birthday, Corporate Meeting, Reunion, etc." required>
                    <datalist id="event-types"><option value="Birthday"><option value="Corporate Meeting"><option value="Family Reunion"><option value="Wedding"><option value="Christening"><option value="Graduation"></datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="guest_count">Number of guests</label>
                    <input class="form-control" type="number" id="guest_count" name="guest_count" min="{{ $pkg->min_pax ?? 1 }}" @if($pkg->max_pax) max="{{ $pkg->max_pax }}" @endif value="{{ old('guest_count') }}" placeholder="e.g. 50" required>
                    <div class="form-text" id="package-range-hint" role="status">{{ $paxLabel }}</div>
                </div>
                @if($perPax)
                    <div class="col-md-6" id="drink-option-container">
                        <label class="form-label" for="drink_option">Drink option</label>
                        <select class="form-select" id="drink_option" name="drink_option">
                            <option value="Solo" @selected(old('drink_option', 'Solo') === 'Solo')>Solo (no drinks)</option>
                            <option value="Bottled Water" @selected(old('drink_option') === 'Bottled Water')>With bottled water</option>
                            <option value="Softdrinks" @selected(old('drink_option') === 'Softdrinks')>With softdrinks</option>
                        </select>
                    </div>
                @endif
                <div class="col-12">
                    <label class="form-label" for="event_location">Event venue / address</label>
                    <input class="form-control" id="event_location" name="event_location" maxlength="255" value="{{ old('event_location') }}" placeholder="Full address or venue hall in Tagum City" required>
                </div>
                @if($pkg->freebie)
                    <div class="col-12" id="package-freebie-container">
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-0 py-2">
                            <i class="bi bi-gift-fill fs-5" aria-hidden="true"></i>
                            <div><strong>Package freebie:</strong> <span id="package-freebie-text">{{ $pkg->freebie }}</span></div>
                        </div>
                    </div>
                @endif
                <div class="col-12">
                    <label class="form-label" for="special_requests">Special requests <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea class="form-control" id="special_requests" name="special_requests" rows="3" placeholder="Allergies, extra tables, schedule notes...">{{ old('special_requests') }}</textarea>
                </div>
                <div class="col-12" id="booking-cost-breakdown" style="display: none;">
                    <div class="card bg-light border p-3"><div class="row align-items-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <span class="small text-muted d-block">Estimated total</span>
                            <span class="h4 fw-bold mb-0" id="estimated-total-amount">₱0.00</span>
                            <span class="small text-muted d-block" id="estimated-calculation-note"></span>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="small text-muted d-block">Required 50% down payment</span>
                            <span class="h4 fw-bold text-danger mb-0" id="downpayment-amount">₱0.00</span>
                            <span class="small text-muted d-block">Balance payable on event date</span>
                        </div>
                    </div></div>
                </div>
                <div class="col-12 pt-2">
                    <button class="btn btn-jq btn-lg px-4" type="submit"><i class="bi bi-send-fill" aria-hidden="true"></i> Submit booking request</button>
                    <p class="small text-muted mt-2 mb-0">We will review your request first. Once approved, you will get the down payment details.</p>
                </div>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pkg = document.getElementById('selected-package');
            const guests = document.getElementById('guest_count');
            const drink = document.getElementById('drink_option');
            const box = document.getElementById('booking-cost-breakdown');
            if (!pkg || !guests) return;
            const peso = (v) => '₱' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const price = parseFloat(pkg.dataset.price) || 0;
            const water = parseFloat(pkg.dataset.priceWater) || price;
            const soft = parseFloat(pkg.dataset.priceSoftdrinks) || price;
            const perPax = pkg.dataset.pricingType === 'Per Pax';

            function calc() {
                const count = parseInt(guests.value, 10) || 0;
                let total = price, note = 'Fixed package price';
                if (perPax) {
                    const choice = drink ? drink.value : 'Solo';
                    const unit = choice === 'Bottled Water' ? water : (choice === 'Softdrinks' ? soft : price);
                    if (drink) {
                        drink.querySelector('option[value="Solo"]').textContent = 'Solo (no drinks) — ' + peso(price) + '/pax';
                        drink.querySelector('option[value="Bottled Water"]').textContent = 'With bottled water — ' + peso(water) + '/pax';
                        drink.querySelector('option[value="Softdrinks"]').textContent = 'With softdrinks — ' + peso(soft) + '/pax';
                    }
                    total = count * unit;
                    note = count > 0 ? count + ' guests × ' + peso(unit) + ' (' + choice + ')' : peso(unit) + ' per person';
                }
                box.style.display = '';
                document.getElementById('estimated-total-amount').textContent = peso(total);
                document.getElementById('downpayment-amount').textContent = peso(total * 0.5);
                document.getElementById('estimated-calculation-note').textContent = note;
            }
            guests.addEventListener('input', calc);
            if (drink) drink.addEventListener('change', calc);
            calc();
        });
        </script>
    @endif
</section>
