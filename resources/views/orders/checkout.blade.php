@extends('layouts.public')
@section('title', 'Checkout - JQ Food Hub')
@section('content')

@php
    $earliest = \Illuminate\Support\Carbon::parse($minimumDate);
    $pay = config('jqfood.payments', []);
    $selectedMethod = old('fulfillment_method', 'Pickup');
@endphp

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none text-muted">Cart</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Checkout</li>
        </ol>
    </nav>
    <h1 class="h2 fw-bold text-dark mb-1">Checkout</h1>
    <p class="text-muted">Choose how and when you want your order, then how you will pay.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-exclamation-octagon-fill fs-5" aria-hidden="true"></i>
            <span class="fw-bold">Please correct the following errors:</span>
        </div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('checkout.store') }}" data-once>
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Step 1 --}}
            <div class="card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="step-dot" aria-hidden="true">1</span>
                    <h2 class="h5 fw-bold text-dark mb-0">Fulfillment &amp; Schedule</h2>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="fulfillment_method">Fulfillment Method</label>
                        <select class="form-select" name="fulfillment_method" id="fulfillment_method" data-fulfillment required>
                            <option value="Pickup" @selected($selectedMethod === 'Pickup')>Pickup at Store</option>
                            <option value="Delivery" @selected($selectedMethod === 'Delivery')>Delivery to Address</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="fulfillment_date">Fulfillment Date</label>
                        <input class="form-control" type="date" name="fulfillment_date" id="fulfillment_date" min="{{ $minimumDate }}" value="{{ old('fulfillment_date') }}" required aria-describedby="date-help">
                        <div class="form-text" id="date-help"><i class="bi bi-info-circle" aria-hidden="true"></i> Earliest available: <strong>{{ $earliest->format('D, M j, Y') }}</strong></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="fulfillment_time">Preferred Time</label>
                        <input class="form-control" type="time" name="fulfillment_time" id="fulfillment_time" value="{{ old('fulfillment_time') }}" required aria-describedby="time-help">
                        <div class="form-text" id="time-help">Store hours: Mon&ndash;Sat, 8:00 AM &ndash; 6:00 PM.</div>
                    </div>

                    <div class="col-12" data-delivery-fields @if($selectedMethod !== 'Delivery') hidden @endif>
                        <label class="form-label" for="delivery_address">Delivery Address</label>
                        <input class="form-control" name="delivery_address" id="delivery_address" maxlength="255" value="{{ old('delivery_address', $defaultAddress) }}" placeholder="House/Unit #, Street, Barangay, Tagum City" autocomplete="street-address">
                        <div class="form-text">Where should we bring your order?</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="notes">Special Instructions or Notes (optional)</label>
                        <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="e.g., Extra sweet &amp; sour sauce, gate code, landmarks...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Step 2 --}}
            <fieldset class="card p-4 mb-4">
                <legend class="d-flex align-items-center gap-2 mb-3 fs-6 float-none w-auto p-0">
                    <span class="step-dot" aria-hidden="true">2</span>
                    <span class="h5 fw-bold text-dark mb-0">Payment Method</span>
                </legend>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="Cash" @checked(old('payment_method', 'Cash') === 'Cash') required>
                            <span class="pay-body d-block">
                                <i class="bi bi-cash-stack text-success fs-4" aria-hidden="true"></i>
                                <span class="fw-bold d-block text-dark">Cash</span>
                                <span class="small text-muted d-block">Pay when you receive your order</span>
                            </span>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="E-Wallet" @checked(old('payment_method') === 'E-Wallet')>
                            <span class="pay-body d-block">
                                <i class="bi bi-phone text-primary fs-4" aria-hidden="true"></i>
                                <span class="fw-bold d-block text-dark">{{ $pay['ewallet_name'] ?? 'GCash' }} / E-Wallet</span>
                                <span class="small text-muted d-block">Send via mobile wallet</span>
                            </span>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="pay-option">
                            <input type="radio" name="payment_method" value="Bank Transfer" @checked(old('payment_method') === 'Bank Transfer')>
                            <span class="pay-body d-block">
                                <i class="bi bi-bank text-info fs-4" aria-hidden="true"></i>
                                <span class="fw-bold d-block text-dark">Bank Transfer</span>
                                <span class="small text-muted d-block">Deposit or online transfer</span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Payment instructions appear for the selected method --}}
                <div class="alert alert-warning border-0 mt-3 mb-0" data-pay-panel="E-Wallet" hidden role="note">
                    <div class="fw-bold mb-1"><i class="bi bi-phone me-1" aria-hidden="true"></i> {{ $pay['ewallet_name'] ?? 'E-Wallet' }} payment details</div>
                    @if(! empty($pay['ewallet_number']))
                        <div>Number: <strong class="user-select-all">{{ $pay['ewallet_number'] }}</strong></div>
                        @if(! empty($pay['ewallet_account_name']))<div>Account name: <strong>{{ $pay['ewallet_account_name'] }}</strong></div>@endif
                        <div class="small mt-1">Keep your reference number; you will need it to confirm payment.</div>
                    @else
                        <div class="small">We will confirm your order first. Please keep your payment reference number handy.</div>
                    @endif
                </div>
                <div class="alert alert-info border-0 mt-3 mb-0" data-pay-panel="Bank Transfer" hidden role="note">
                    <div class="fw-bold mb-1"><i class="bi bi-bank me-1" aria-hidden="true"></i> Bank transfer details</div>
                    @if(! empty($pay['bank_account_number']))
                        @if(! empty($pay['bank_name']))<div>Bank: <strong>{{ $pay['bank_name'] }}</strong></div>@endif
                        <div>Account number: <strong class="user-select-all">{{ $pay['bank_account_number'] }}</strong></div>
                        @if(! empty($pay['bank_account_name']))<div>Account name: <strong>{{ $pay['bank_account_name'] }}</strong></div>@endif
                        <div class="small mt-1">Keep your reference number; you will need it to confirm payment.</div>
                    @else
                        <div class="small">We will confirm your order first. Please keep your payment reference number handy.</div>
                    @endif
                </div>
            </fieldset>
        </div>

        <div class="col-lg-4">
            <div class="card p-4 sticky-top" style="top: 100px;">
                <h2 class="h5 fw-bold text-dark mb-3">Order Summary</h2>

                <div class="bg-light p-3 rounded-3 mb-3 border">
                    <ul class="list-unstyled mb-0 small">
                        @foreach($items as $item)
                            <li class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <span class="fw-semibold text-dark">{{ $item['product']->product_name }}</span>
                                    <span class="text-muted">&times; {{ $item['quantity'] }}</span>
                                </div>
                                <span class="text-nowrap fw-bold text-dark">₱{{ number_format($item['subtotal'], 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold fs-6">Total due</span>
                    <span class="fs-4 fw-extrabold text-danger">₱{{ number_format($total, 2) }}</span>
                </div>

                <button class="btn btn-jq btn-lg w-100 py-3 mb-2" type="submit">
                    <i class="bi bi-check2-circle" aria-hidden="true"></i> Place Order
                </button>
                <a class="btn btn-outline-dark w-100" href="{{ route('cart.index') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Modify Cart
                </a>

                <p class="mt-4 pt-3 border-top text-muted small mb-0">
                    <i class="bi bi-info-circle text-primary me-1" aria-hidden="true"></i> A down payment may apply to bulk orders.
                </p>
            </div>
        </div>
    </div>
</form>
@endsection
