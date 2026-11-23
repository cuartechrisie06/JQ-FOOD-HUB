<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartQuantityRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::with('category')
            ->whereIn('product_id', array_keys($cart))
            ->orderBy('product_name')
            ->get();

        $items = $products->map(function (Product $product) use ($cart) {
            $quantity = (int) $cart[$product->product_id];

            return [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $quantity * (float) $product->unit_price,
            ];
        });

        return view('orders.cart', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
        ]);
    }

    public function store(CartQuantityRequest $request, Product $product): RedirectResponse
    {
        if (! $product->is_available) {
            return back()->withErrors(['cart' => 'This product is currently unavailable.']);
        }

        $cart = $request->session()->get('cart', []);
        $cart[$product->product_id] = min(
            999,
            (int) ($cart[$product->product_id] ?? 0) + (int) $request->validated('quantity')
        );
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Product added to cart.');
    }

    public function update(CartQuantityRequest $request, Product $product): RedirectResponse
    {
        if (! $product->is_available) {
            return back()->withErrors(['cart' => 'Unavailable products cannot remain in the cart. Remove this item to continue.']);
        }

        $cart = $request->session()->get('cart', []);
        if (! array_key_exists($product->product_id, $cart)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'This product is not in your cart.']);
        }

        $cart[$product->product_id] = (int) $request->validated('quantity');
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Cart quantity updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->product_id]);
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Product removed from cart.');
    }
}
