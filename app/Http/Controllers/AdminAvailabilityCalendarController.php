<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityCalendarRequest;
use App\Models\AvailabilityCalendar;
use App\Models\CateringBooking;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAvailabilityCalendarController extends Controller
{
    public function index(): View
    {
        return view('admin.availability.index', [
            'calendars' => AvailabilityCalendar::withBookedCounts()->orderByDesc('slot_date')->limit(100)->get(),
        ]);
    }

    /**
     * One feed for the admin Order Calendar: every active order (regular, bulk,
     * packed lunch, catering) plus catering requests still waiting for review.
     */
    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);
        $start = Carbon::parse($validated['start'])->toDateString();
        $end = Carbon::parse($validated['end'])->toDateString();
        $inRange = fn ($calendar) => $calendar->whereDate('slot_date', '>=', $start)->whereDate('slot_date', '<', $end);

        $events = [];

        $orders = Order::with(['user', 'calendar', 'payments'])
            ->where('status', '!=', 'Cancelled')
            ->whereHas('calendar', $inRange)
            ->get();

        foreach ($orders as $order) {
            $ref = 'JQ-'.str_pad($order->order_id, 4, '0', STR_PAD_LEFT);
            $payment = match (true) {
                $order->payments->contains('status', 'Verified') => 'Payment verified',
                $order->payments->contains('status', 'Pending') => 'Payment pending',
                $order->payments->contains('status', 'Rejected') => 'Payment rejected',
                default => 'No payment',
            };
            $events[] = [
                'id' => 'order-'.$order->order_id,
                'title' => "{$ref} · {$order->order_type}",
                'start' => $order->calendar->slot_date->toDateString(),
                'allDay' => true,
                'classNames' => ['ev-'.str($order->order_type)->slug()],
                'extendedProps' => [
                    'kind' => 'order',
                    'ref' => $ref,
                    'type' => $order->order_type,
                    'status' => $order->status,
                    'payment' => $payment,
                    'customer' => $order->user->full_name,
                    'time' => $order->fulfillment_time ? substr($order->fulfillment_time, 0, 5) : null,
                    'total' => number_format($order->total_amount, 2),
                    'url' => route('admin.orders.index', ['search' => $ref]),
                ],
            ];
        }

        $bookings = CateringBooking::with(['user', 'foodPackage', 'calendar'])
            ->where('status', 'Pending')
            ->whereNull('order_id')
            ->whereHas('calendar', $inRange)
            ->get();

        foreach ($bookings as $booking) {
            $events[] = [
                'id' => 'booking-'.$booking->booking_id,
                'title' => 'Request · '.$booking->foodPackage->package_name,
                'start' => $booking->calendar->slot_date->toDateString(),
                'allDay' => true,
                'classNames' => ['ev-request'],
                'extendedProps' => [
                    'kind' => 'booking',
                    'ref' => 'Catering request',
                    'type' => 'Catering',
                    'status' => 'Awaiting review',
                    'payment' => null,
                    'customer' => $booking->user->full_name,
                    'time' => substr($booking->event_time, 0, 5),
                    'total' => null,
                    'url' => route('admin.catering-bookings.show', $booking),
                ],
            ];
        }

        $days = AvailabilityCalendar::withBookedCounts()
            ->whereDate('slot_date', '>=', $start)
            ->whereDate('slot_date', '<', $end)
            ->get()
            ->mapWithKeys(fn (AvailabilityCalendar $calendar) => [
                $calendar->slot_date->toDateString() => [
                    'booked' => $calendar->bookedCount(),
                    'max' => (int) $calendar->max_bookings,
                    'blocked' => (bool) $calendar->is_blocked,
                    'remarks' => $calendar->remarks,
                ],
            ]);

        return response()->json([
            'events' => $events,
            'days' => $days,
            'default_max' => (int) config('jqfood.default_max_bookings', 2),
        ]);
    }

    public function store(AvailabilityCalendarRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $calendar = AvailabilityCalendar::getOrCreateForDate($validated['slot_date']);
        $calendar->update([
            'max_bookings' => $validated['max_bookings'],
            'is_blocked' => $request->boolean('is_blocked'),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('admin.availability.index')->with('status', 'Calendar availability updated.');
    }
}
