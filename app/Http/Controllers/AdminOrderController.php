<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\CateringBooking;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:Pending,Confirmed,Preparing,Ready,Out for Delivery,Completed,Cancelled'],
            'search' => ['nullable', 'string', 'max:100'],
            'payment' => ['nullable', 'in:Pending,Verified,Rejected'],
            'method' => ['nullable', 'in:Pickup,Delivery'],
        ]);

        $orders = Order::with(['user', 'calendar', 'items.product', 'payments'])
            ->when($request->filled('date'), fn ($query) => $query->whereHas(
                'calendar',
                fn ($calendar) => $calendar->whereDate('slot_date', $validated['date'])
            ))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $validated['status']))
            ->when($request->filled('payment'), fn ($query) => $query->whereHas(
                'payments',
                fn ($payments) => $payments->where('status', $validated['payment'])
            ))
            ->when($request->filled('method'), fn ($query) => $query->where('fulfillment_method', $validated['method']))
            ->when($request->filled('search'), function ($query) use ($validated) {
                $search = trim($validated['search']);
                $orderNumber = strtoupper(preg_replace('/^JQ[- ]?/i', '', $search));

                $query->where(function ($searchQuery) use ($search, $orderNumber) {
                    if (ctype_digit($orderNumber)) {
                        $searchQuery->where('order_id', (int) $orderNumber);
                    }

                    $searchQuery->orWhereHas('user', fn ($user) => $user
                        ->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'transitions' => app(OrderStatusService::class),
            'pendingBookings' => CateringBooking::with(['user', 'foodPackage', 'calendar'])
                ->where('status', 'Pending')
                ->whereNull('order_id')
                ->orderBy('created_at')
                ->get(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        app(OrderStatusService::class)->transition($order, $request->validated('status'));

        return back()->with('status', 'Order status updated.');
    }
}
