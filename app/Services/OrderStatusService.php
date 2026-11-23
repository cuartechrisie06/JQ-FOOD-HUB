<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    public function transition(Order $order, string $targetStatus): Order
    {
        $previousStatus = $order->status;

        DB::transaction(function () use ($order, $previousStatus, $targetStatus) {
            $lockedOrder = Order::where('order_id', $order->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== $previousStatus) {
                throw ValidationException::withMessages([
                    'status' => 'This order status has changed. Refresh and try again.',
                ]);
            }

            if (! $this->isAllowedTransition($lockedOrder, $targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => "The order cannot transition from {$lockedOrder->status} to {$targetStatus}.",
                ]);
            }

            if ($targetStatus === 'Confirmed'
                && in_array($lockedOrder->order_type, ['Bulk', 'Packed Lunch', 'Catering'], true)
                && ! $lockedOrder->payments()
                    ->where('payment_type', 'Down Payment')
                    ->where('status', 'Verified')
                    ->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'A verified down payment is required before confirming this order.',
                ]);
            }

            if ($targetStatus === 'Confirmed') {
                $this->changeProductStock($lockedOrder, -1);
            } elseif ($targetStatus === 'Cancelled' && $lockedOrder->status !== 'Pending') {
                $this->changeProductStock($lockedOrder, 1);
            }

            $lockedOrder->update(['status' => $targetStatus]);
        });

        $order->refresh();
        app(OrderNotificationService::class)->notifyStatusChanged($order, $previousStatus, $targetStatus);

        return $order;
    }

    public function allowedTransitions(Order $order): array
    {
        if ($order->status === 'Pending') {
            $transitions = ['Confirmed', 'Cancelled'];

            if (in_array($order->order_type, ['Bulk', 'Packed Lunch', 'Catering'], true)
                && ! $order->payments()
                    ->where('payment_type', 'Down Payment')
                    ->where('status', 'Verified')
                    ->exists()) {
                return ['Cancelled'];
            }

            return $transitions;
        }

        if ($order->status === 'Confirmed') {
            return ['Preparing', 'Cancelled'];
        }

        if ($order->status === 'Preparing') {
            return [
                $order->fulfillment_method === 'Delivery' ? 'Out for Delivery' : 'Ready',
                'Cancelled',
            ];
        }

        if (in_array($order->status, ['Ready', 'Out for Delivery'], true)) {
            return ['Completed', 'Cancelled'];
        }

        return [];
    }

    private function isAllowedTransition(Order $order, string $targetStatus): bool
    {
        return in_array($targetStatus, $this->allowedTransitions($order), true);
    }

    private function changeProductStock(Order $order, int $direction): void
    {
        $items = OrderItem::where('order_id', $order->order_id)
            ->whereNotNull('product_id')
            ->get();

        foreach ($items as $item) {
            $product = Product::where('product_id', $item->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($direction < 0 && $product->stock_qty < $item->quantity) {
                throw ValidationException::withMessages([
                    'status' => "Insufficient stock for {$product->product_name}.",
                ]);
            }

            if ($direction < 0) {
                $product->decrement('stock_qty', $item->quantity);
            } else {
                $product->increment('stock_qty', $item->quantity);
            }
        }
    }
}
