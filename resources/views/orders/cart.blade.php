@extends('layouts.public')
@section('title', 'Shopping Cart')
@section('content')
<h1 class="h2 mb-4">Shopping Cart</h1>
@if($items->isEmpty())
    <div class="alert alert-light border">Your cart is empty. <a href="{{ route('products.index') }}">Browse products</a>.</div>
@else
    @if($errors->has('cart'))<div class="alert alert-danger">{{ $errors->first('cart') }}</div>@endif
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Product</th><th>Price</th><th style="width: 240px">Quantity</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>
                    {{ $item['product']->product_name }}
                    @unless($item['product']->is_available)<span class="badge text-bg-danger">Unavailable</span>@endunless
                </td>
                <td>PHP {{ number_format($item['product']->unit_price, 2) }}</td>
                <td>
                    <form class="d-flex gap-2" method="POST" action="{{ route('cart.update', $item['product']) }}">
                        @csrf @method('PATCH')
                        <input class="form-control" type="number" name="quantity" min="1" max="999" value="{{ $item['quantity'] }}" required>
                        <button class="btn btn-outline-dark">Update</button>
                    </form>
                </td>
                <td>PHP {{ number_format($item['subtotal'], 2) }}</td>
                <td>
                    <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><th colspan="3" class="text-end">Total</th><th colspan="2">PHP {{ number_format($total, 2) }}</th></tr></tfoot>
    </table></div></div>
    @if($items->every(fn ($item) => $item['product']->is_available))
        <div class="text-end mt-3"><a class="btn btn-jq" href="{{ route('checkout.create') }}">Proceed to Checkout</a></div>
    @else
        <div class="alert alert-warning mt-3">Remove unavailable products before checkout.</div>
    @endif
@endif
@endsection
