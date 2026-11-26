<?php

namespace App\Http\Controllers;

use App\Models\AvailabilityCalendar;
use App\Models\CateringBooking;
use App\Models\Order;
use App\Models\Payment;
use App\Mail\BookingConfirmedMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminCateringBookingController extends Controller
{
    public function index(): View
    {
        return view('admin.catering-bookings.index', [
            'bookings' => CateringBooking::with(['user', 'foodPackage', 'calendar'])
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }

    public function show(CateringBooking $booking): View
    {
        $booking->load(['user', 'foodPackage.items', 'calendar', 'order']);

        return view('admin.catering-bookings.show', compact('booking'));
    }

    public function confirm(CateringBooking $booking): RedirectResponse
    {
        $order = DB::transaction(function () use ($booking) {
            $booking = CateringBooking::where('booking_id', $booking->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->order_id !== null) {
                return null;
            }

            if ($booking->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'booking' => 'Only pending bookings without an order can be confirmed.',
                ]);
            }

            $calendar = AvailabilityCalendar::where('calendar_id', $booking->calendar_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($calendar->is_blocked) {
                throw ValidationException::withMessages([
                    'booking' => 'This booking date is blocked and cannot be confirmed.',
                ]);
            }

            if ($calendar->confirmedBookingCount() >= $calendar->max_bookings) {
                throw ValidationException::withMessages([
                    'booking' => 'The date has reached its confirmed booking capacity.',
                ]);
            }

            $package = $booking->foodPackage()->firstOrFail();
            $unitPrice = $package->computePricePerPax($booking->drink_option);
            $total = $package->computeTotal($booking->guest_count, $booking->drink_option);
            $downpayment = round($total * 0.5, 2);

            $order = Order::create([
                'user_id' => $booking->user_id,
                'calendar_id' => $booking->calendar_id,
                'order_type' => 'Catering',
                'fulfillment_method' => 'Delivery',
                'fulfillment_time' => $booking->event_time,
                'delivery_address' => $booking->event_location,
                'total_amount' => $total,
                'downpayment_amount' => $downpayment,
                'status' => 'Pending',
                'notes' => $booking->special_requests,
                'created_at' => now(),
            ]);

            $order->items()->create([
                'product_id' => null,
                'package_id' => $package->package_id,
                'quantity' => $package->pricing_type === 'Per Pax' ? $booking->guest_count : 1,
                'unit_price' => $unitPrice,
                'subtotal' => $total,
            ]);

            Payment::create([
                'order_id' => $order->order_id,
                'verified_by' => null,
                'payment_method' => 'Cash',
                'payment_type' => 'Down Payment',
                'amount' => $order->downpayment_amount,
                'reference_no' => null,
                'proof_image' => null,
                'status' => 'Pending',
                'submitted_at' => now(),
                'verified_at' => null,
            ]);

            $booking->update([
                'status' => 'Confirmed',
                'order_id' => $order->order_id,
            ]);

            return $order;
        });

        if ($order) {
            $order->loadMissing('user');
            Mail::to($order->user->email)->queue(new BookingConfirmedMail($order));
        }

        return back()->with('status', $order ? 'Booking confirmed and order created.' : 'This booking already has an order.');
    }

    public function reject(CateringBooking $booking): RedirectResponse
    {
        DB::transaction(function () use ($booking) {
            $booking = CateringBooking::where('booking_id', $booking->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status !== 'Pending' || $booking->order_id !== null) {
                throw ValidationException::withMessages([
                    'booking' => 'Only pending bookings without an order can be rejected.',
                ]);
            }

            $booking->update(['status' => 'Rejected']);
        });

        return back()->with('status', 'Booking rejected.');
    }
}
