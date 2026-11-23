<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', [
            'orders' => auth()->user()->orders()->with('calendar')->latest('created_at')->paginate(10),
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 404);
        $order->load(['items.product', 'calendar', 'payments']);
        $statusNotifications = $order->notifications()
            ->where('type', 'Status Change')
            ->orderBy('created_at')
            ->get();
        $timeline = collect([[
            'status' => 'Pending',
            'message' => 'Order placed.',
            'created_at' => $order->created_at,
        ]])->concat($statusNotifications->map(function ($notification) {
            preg_match('/changed from .+ to (.+)\.$/', $notification->message, $matches);

            return [
                'status' => $matches[1] ?? $notification->type,
                'message' => $notification->message,
                'created_at' => $notification->created_at,
            ];
        }));

        return view('orders.show', compact('order', 'timeline'));
    }

    public function confirmation(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 404);
        $order->load('payments');
        $amountDueNow = $order->payments->where('status', 'Pending')->sum('amount');

        return view('orders.confirmation', compact('order', 'amountDueNow'));
    }
}
