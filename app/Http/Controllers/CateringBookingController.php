<?php

namespace App\Http\Controllers;

use App\Http\Requests\CateringBookingRequest;
use App\Models\AvailabilityCalendar;
use App\Models\CateringBooking;
use App\Models\FoodPackage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CateringBookingController extends Controller
{
    /**
     * Catering is booked from the Food Packages tabs on the menu. This route
     * only keeps old links and bookmarks working.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->to(route('menu.index', array_filter([
            'tab' => 'packages',
            'package' => $request->query('package'),
        ])).'#booking');
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $start = Carbon::parse($validated['start'])->startOfDay();
        $end = Carbon::parse($validated['end'])->startOfDay();
        $calendars = AvailabilityCalendar::withBookedCounts()
            ->whereDate('slot_date', '>=', $start->toDateString())
            ->whereDate('slot_date', '<', $end->toDateString())
            ->get()
            ->keyBy(fn (AvailabilityCalendar $calendar) => $calendar->slot_date->toDateString());

        $events = [];
        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $dateString = $date->toDateString();
            $calendar = $calendars->get($dateString);
            $isBlocked = $calendar?->is_blocked ?? false;
            $maxBookings = $calendar?->max_bookings ?? config('jqfood.default_max_bookings', 2);
            $confirmedCount = $calendar?->bookedCount() ?? 0;
            $status = $isBlocked
                ? 'Not Available'
                : ($confirmedCount >= $maxBookings ? 'Fully Booked' : 'Available');
            $color = match ($status) {
                'Not Available' => '#6c757d',
                'Fully Booked' => '#dc3545',
                default => '#198754',
            };

            $events[] = [
                'title' => $status,
                'start' => $dateString,
                'allDay' => true,
                'display' => 'background',
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => ['status' => $status],
            ];
        }

        return response()->json($events);
    }

    public function store(CateringBookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $booking = DB::transaction(function () use ($request, $validated) {
            $package = FoodPackage::where('package_id', $validated['package_id'])
                ->where('is_available', true)
                ->lockForUpdate()
                ->first();

            if (! $package
                || ($package->min_pax !== null && $validated['guest_count'] < $package->min_pax)
                || ($package->max_pax !== null && $validated['guest_count'] > $package->max_pax)) {
                throw ValidationException::withMessages([
                    'guest_count' => 'The selected package is unavailable or does not support this guest count.',
                ]);
            }

            $calendar = AvailabilityCalendar::getOrCreateForDate($validated['slot_date']);
            $calendar = AvailabilityCalendar::where('calendar_id', $calendar->calendar_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($calendar->is_blocked) {
                throw ValidationException::withMessages([
                    'slot_date' => 'This date is not available for catering bookings.',
                ]);
            }

            if ($calendar->confirmedBookingCount() >= $calendar->max_bookings) {
                throw ValidationException::withMessages([
                    'slot_date' => 'This date is fully booked.',
                ]);
            }

            return CateringBooking::create([
                'user_id' => $request->user()->user_id,
                'package_id' => $validated['package_id'],
                'calendar_id' => $calendar->calendar_id,
                'order_id' => null,
                'event_type' => $validated['event_type'],
                'event_time' => $validated['event_time'],
                'event_location' => $validated['event_location'],
                'guest_count' => $validated['guest_count'],
                'drink_option' => $package->pricing_type === 'Per Pax' ? ($validated['drink_option'] ?? 'Solo') : null,
                'special_requests' => $validated['special_requests'] ?? null,
                'status' => 'Pending',
                'created_at' => now(),
            ]);
        });

        return redirect()->route('orders.index')
            ->with('status', 'Catering request submitted. We will review it and message you the down payment details once confirmed.')
            ->with('booking_id', $booking->booking_id);
    }

    public function cancel(CateringBooking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === auth()->id(), 404);

        DB::transaction(function () use ($booking) {
            $booking = CateringBooking::where('booking_id', $booking->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'booking' => 'Only pending bookings can be cancelled.',
                ]);
            }

            $booking->update(['status' => 'Cancelled']);
        });

        return back()->with('status', 'Catering request cancelled.');
    }
}
