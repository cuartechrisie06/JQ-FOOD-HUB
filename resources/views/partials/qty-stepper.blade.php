{{-- Usage: @include('partials.qty-stepper', ['value' => 1, 'id' => 'quantity', 'large' => false]) --}}
<div class="qty-stepper {{ ($large ?? false) ? 'qty-lg' : '' }}" data-qty-stepper>
    <button type="button" data-step="-1" aria-label="Decrease quantity">&minus;</button>
    <input @isset($id) id="{{ $id }}" @endisset type="number" name="quantity" min="1" max="999" value="{{ $value ?? 1 }}" inputmode="numeric" aria-label="Quantity" required>
    <button type="button" data-step="1" aria-label="Increase quantity">+</button>
</div>
