@extends('layouts.public')
@section('title', 'Checkout')
@section('content')
<h1 class="h2 mb-4">Checkout</h1>
@if($errors->any())
    <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif
<form method="POST" action="{{ route('checkout.store') }}">
    @csrf
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">1. Cart</h2>
        <ul class="mb-0">
            @foreach($items as $item)
                <li>{{ $item['product']->product_name }} × {{ $item['quantity'] }} — PHP {{ number_format($item['subtotal'], 2) }}</li>
            @endforeach
        </ul>
        <p class="fw-bold mt-3 mb-0">Total: PHP {{ number_format($total, 2) }}</p>
    </div></div>
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">2. Schedule</h2>
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="fulfillment_method">Fulfillment</label><select class="form-select" name="fulfillment_method" id="fulfillment_method" required><option value="Pickup" @selected(old('fulfillment_method') === 'Pickup')>Pickup</option><option value="Delivery" @selected(old('fulfillment_method') === 'Delivery')>Delivery</option></select></div>
            <div class="col-md-4"><label class="form-label" for="fulfillment_date">Date</label><input class="form-control" type="date" name="fulfillment_date" id="fulfillment_date" min="{{ $minimumDate }}" value="{{ old('fulfillment_date') }}" required><div class="form-text">Lead time is based on the longest-lead product in your cart.</div></div>
            <div class="col-md-4"><label class="form-label" for="fulfillment_time">Time</label><input class="form-control" type="time" name="fulfillment_time" id="fulfillment_time" value="{{ old('fulfillment_time') }}" required></div>
            <div class="col-12"><label class="form-label" for="delivery_address">Delivery Address</label><input class="form-control" name="delivery_address" id="delivery_address" maxlength="255" value="{{ old('delivery_address', $defaultAddress) }}"><div class="form-text">Required for delivery; defaults to your saved address.</div></div>
            <div class="col-12"><label class="form-label" for="notes">Order Notes</label><textarea class="form-control" name="notes" id="notes" rows="2">{{ old('notes') }}</textarea></div>
        </div>
    </div></div>
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">3. Payment</h2>
        <label class="form-label" for="payment_method">Payment Method</label>
        <select class="form-select" name="payment_method" id="payment_method" required>
            <option value="Cash" @selected(old('payment_method') === 'Cash')>Cash</option>
            <option value="E-Wallet" @selected(old('payment_method') === 'E-Wallet')>E-Wallet</option>
            <option value="Bank Transfer" @selected(old('payment_method') === 'Bank Transfer')>Bank Transfer</option>
        </select>
    </div></div>
    <div class="card mb-4"><div class="card-body">
        <h2 class="h5">4. Confirm</h2>
        <p>Order type, total, and required down payment will be calculated from your cart.</p>
        <button class="btn btn-jq" type="submit">Place Order</button>
        <a class="btn btn-outline-dark" href="{{ route('cart.index') }}">Back to Cart</a>
    </div></div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const method = document.getElementById('fulfillment_method');
    const address = document.getElementById('delivery_address');
    const updateAddress = () => { address.required = method.value === 'Delivery'; };
    method.addEventListener('change', updateAddress);
    updateAddress();
});
</script>
@endsection
