<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\AvailabilityCalendar;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $cart = request()->session()->get('cart', []);
        if ($cart === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $products = Product::whereIn('product_id', array_keys($cart))->get()->keyBy('product_id');
        $maxLeadTime = (int) $products->max('lead_time_days');
        $items = collect($cart)->map(function ($quantity, $productId) use ($products) {
            $product = $products->get($productId);

            return $product ? [
                'product' => $product,
                'quantity' => (int) $quantity,
                'subtotal' => (int) $quantity * (float) $product->unit_price,
            ] : null;
        });

        if ($items->contains(null)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'A product in your cart no longer exists. Remove it before checkout.']);
        }

        if ($items->contains(fn ($item) => ! $item['product']->is_available)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Unavailable products must be removed before checkout.']);
        }

        return view('orders.checkout', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'defaultAddress' => auth()->user()->address,
            'minimumDate' => today()->addDays($maxLeadTime)->toDateString(),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $order = DB::transaction(function () use ($request, $cart) {
            $products = Product::whereIn('product_id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages([
                    'cart' => 'A product in your cart no longer exists. Remove it before checkout.',
                ]);
            }

            if ($products->contains(fn (Product $product) => ! $product->is_available)) {
                throw ValidationException::withMessages([
                    'cart' => 'Unavailable products must be removed before checkout.',
                ]);
            }

            $maxLeadTime = $products->max('lead_time_days');
            $fulfillmentDate = Carbon::parse($request->validated('fulfillment_date'))->startOfDay();
            $earliestDate = today()->addDays((int) $maxLeadTime)->startOfDay();

            if ($fulfillmentDate->lt($earliestDate)) {
                throw ValidationException::withMessages([
                    'fulfillment_date' => "The requested date must be at least {$maxLeadTime} day(s) from today.",
                ]);
            }

            $calendar = AvailabilityCalendar::getOrCreateForDate($fulfillmentDate->toDateString());
            $calendar = AvailabilityCalendar::where('calendar_id', $calendar->calendar_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($calendar->is_blocked) {
                throw ValidationException::withMessages([
                    'fulfillment_date' => 'This date is blocked and unavailable for orders.',
                ]);
            }

            $totalCents = 0;
            $containsPackedLunch = false;
            $bulkQuantity = false;
            $lines = [];

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);
                $quantity = (int) $quantity;
                $unitCents = (int) round((float) $product->unit_price * 100);
                $subtotalCents = $unitCents * $quantity;
                $totalCents += $subtotalCents;
                $bulkQuantity = $bulkQuantity || $quantity >= 10;

                if ($product->category()->whereIn('category_name', ['Packed Lunch', 'Packed Meals'])->exists()) {
                    $containsPackedLunch = true;
                }

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitCents / 100,
                    'subtotal' => $subtotalCents / 100,
                ];
            }

            $customerIsBusiness = $request->user()->customer_type === 'Business';
            $totalQuantity = array_sum(array_map('intval', $cart));
            $orderType = $containsPackedLunch
                ? 'Packed Lunch'
                : (($bulkQuantity || ($customerIsBusiness && $totalQuantity >= 10)) ? 'Bulk' : 'Regular');
            if (in_array($orderType, ['Bulk', 'Packed Lunch'], true)
                && $calendar->bookedCount() >= $calendar->max_bookings) {
                throw ValidationException::withMessages([
                    'fulfillment_date' => 'This date is fully booked for bulk orders and packed lunches. Please choose another date.',
                ]);
            }

            $total = $totalCents / 100;
            $downpayment = in_array($orderType, ['Bulk', 'Packed Lunch'], true)
                ? round($total * 0.5, 2)
                : 0;
            $amountDueNow = $downpayment > 0 ? $downpayment : $total;
            $deliveryAddress = $request->validated('fulfillment_method') === 'Delivery'
                ? ($request->validated('delivery_address') ?: $request->user()->address)
                : null;

            if ($request->validated('fulfillment_method') === 'Delivery' && ! $deliveryAddress) {
                throw ValidationException::withMessages([
                    'delivery_address' => 'A delivery address is required.',
                ]);
            }

            $order = Order::create([
                'user_id' => $request->user()->user_id,
                'calendar_id' => $calendar->calendar_id,
                'order_type' => $orderType,
                'fulfillment_method' => $request->validated('fulfillment_method'),
                'fulfillment_time' => $request->validated('fulfillment_time'),
                'delivery_address' => $deliveryAddress,
                'total_amount' => $total,
                'downpayment_amount' => $downpayment,
                'status' => 'Pending',
                'notes' => $request->validated('notes'),
                'created_at' => now(),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->product_id,
                    'package_id' => null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            Payment::create([
                'order_id' => $order->order_id,
                'verified_by' => null,
                'payment_method' => $request->validated('payment_method'),
                'payment_type' => $downpayment > 0 ? 'Down Payment' : 'Full Payment',
                'amount' => $amountDueNow,
                'reference_no' => null,
                'proof_image' => null,
                'status' => 'Pending',
                'submitted_at' => now(),
                'verified_at' => null,
            ]);

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('orders.confirmation', $order);
    }
}
