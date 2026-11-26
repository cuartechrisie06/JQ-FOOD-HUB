<?php

namespace Tests\Feature;

use App\Models\AvailabilityCalendar;
use App\Models\CateringBooking;
use App\Models\FoodPackage;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CateringBookingTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    private FoodPackage $package;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-22 09:00:00');
        $this->seed(DatabaseSeeder::class);

        $customerRole = Role::where('role_name', 'Customer')->firstOrFail();
        $adminRole = Role::where('role_name', 'Admin')->firstOrFail();
        $this->customer = User::create([
            'role_id' => $customerRole->role_id,
            'full_name' => 'Booking Customer',
            'email' => 'booking-customer@example.com',
            'phone' => '09171234567',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
        $this->admin = User::create([
            'role_id' => $adminRole->role_id,
            'full_name' => 'Booking Admin',
            'email' => 'booking-admin@example.com',
            'phone' => '09170000001',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => null,
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
        $this->package = FoodPackage::create([
            'package_name' => 'Party Package',
            'description' => 'Catering package',
            'price' => 10000,
            'min_pax' => 10,
            'max_pax' => 50,
            'event_type' => 'Birthday',
            'image_path' => null,
            'is_available' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_default_capacity_and_pending_bookings_do_not_count_toward_full_status(): void
    {
        $date = today()->addDays(4)->toDateString();
        $first = $this->createBooking($date);
        $this->createBooking($date);

        $this->assertSame(2, config('jqfood.default_max_bookings'));
        $this->assertSame(0, $first->calendar->confirmedBookingCount());

        $this->actingAs($this->customer)
            ->get('/catering/availability-events?start='.$date.'&end='.today()->addDays(5)->toDateString())
            ->assertOk()
            ->assertJsonPath('0.title', 'Available');
    }

    public function test_blocked_date_rejects_customer_booking(): void
    {
        $date = today()->addDays(4)->toDateString();
        AvailabilityCalendar::create([
            'slot_date' => $date,
            'max_bookings' => 2,
            'is_blocked' => true,
            'remarks' => 'Private event',
        ]);

        $this->actingAs($this->customer)
            ->post('/catering/bookings', $this->bookingData($date))
            ->assertSessionHasErrors('slot_date');

        $this->assertDatabaseCount('catering_bookings', 0);
        $this->actingAs($this->customer)
            ->get('/catering/availability-events?start='.$date.'&end='.today()->addDays(5)->toDateString())
            ->assertJsonPath('0.title', 'Not Available');
    }

    public function test_guest_count_must_fit_selected_package_range(): void
    {
        $date = today()->addDays(4)->toDateString();

        $this->actingAs($this->customer)
            ->post('/catering/bookings', $this->bookingData($date, ['guest_count' => 51]))
            ->assertSessionHasErrors('guest_count');

        $this->assertDatabaseCount('catering_bookings', 0);
    }

    public function test_dates_within_three_days_cannot_be_requested(): void
    {
        $date = today()->addDays(2)->toDateString();

        $this->actingAs($this->customer)
            ->post('/catering/bookings', $this->bookingData($date))
            ->assertSessionHasErrors('slot_date');

        $this->assertDatabaseCount('catering_bookings', 0);
    }

    public function test_admin_confirmation_creates_exactly_one_catering_order(): void
    {
        $booking = $this->createBooking(today()->addDays(4)->toDateString());

        $this->actingAs($this->admin)
            ->patch("/admin/catering-bookings/{$booking->booking_id}/confirm")
            ->assertRedirect();
        $this->actingAs($this->admin)
            ->patch("/admin/catering-bookings/{$booking->booking_id}/confirm")
            ->assertRedirect();

        $booking->refresh();
        $order = Order::where('order_id', $booking->order_id)->with('items')->firstOrFail();

        $this->assertSame('Confirmed', $booking->status);
        $this->assertSame(1, Order::where('order_type', 'Catering')->count());
        $this->assertSame('Catering', $order->order_type);
        $this->assertSame('10000.00', $order->total_amount);
        $this->assertSame('5000.00', $order->downpayment_amount);
        $this->assertSame('Pending', $order->status);
        $this->assertSame(1, $order->items->count());
        $this->assertSame($this->package->package_id, $order->items->first()->package_id);
    }

    public function test_capacity_is_rechecked_when_admin_confirms(): void
    {
        $date = today()->addDays(4)->toDateString();
        $calendar = AvailabilityCalendar::create([
            'slot_date' => $date,
            'max_bookings' => 1,
            'is_blocked' => false,
            'remarks' => null,
        ]);
        $first = $this->createBooking($date);
        $second = $this->createBooking($date);

        $this->actingAs($this->admin)
            ->patch("/admin/catering-bookings/{$first->booking_id}/confirm")
            ->assertRedirect();

        $this->actingAs($this->customer)
            ->get('/catering/availability-events?start='.$date.'&end='.today()->addDays(5)->toDateString())
            ->assertJsonPath('0.title', 'Fully Booked');

        $this->actingAs($this->admin)
            ->patch("/admin/catering-bookings/{$second->booking_id}/confirm")
            ->assertSessionHasErrors('booking');

        $this->assertSame(1, $calendar->fresh()->confirmedBookingCount());
        $this->assertSame(1, Order::where('order_type', 'Catering')->count());
    }

    public function test_customer_can_cancel_own_pending_booking_only(): void
    {
        $booking = $this->createBooking(today()->addDays(4)->toDateString());
        $otherCustomer = User::create([
            'role_id' => $this->customer->role_id,
            'full_name' => 'Other Customer',
            'email' => 'other-booking@example.com',
            'phone' => '09170000002',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);

        $this->actingAs($otherCustomer)
            ->patch("/catering/bookings/{$booking->booking_id}/cancel")
            ->assertNotFound();

        $this->actingAs($this->customer)
            ->patch("/catering/bookings/{$booking->booking_id}/cancel")
            ->assertRedirect();
    }

    public function test_guest_range_validation_skipped_when_min_pax_or_max_pax_is_null(): void
    {
        $date = today()->addDays(4)->toDateString();
        // Fixed package has null min_pax and null max_pax
        $fixedPkg = FoodPackage::where('pricing_type', 'Fixed')->firstOrFail();

        $response = $this->actingAs($this->customer)
            ->post('/catering/bookings', $this->bookingData($date, [
                'package_id' => $fixedPkg->package_id,
                'guest_count' => 120, // High guest count should not trigger error when max_pax is null
            ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('catering_bookings', [
            'package_id' => $fixedPkg->package_id,
            'guest_count' => 120,
        ]);
    }

    public function test_per_pax_booking_computes_total_and_fifty_percent_downpayment(): void
    {
        $date = today()->addDays(4)->toDateString();
        $perPaxPkg = FoodPackage::where('pricing_type', 'Per Pax')
            ->whereNotNull('price_with_softdrinks')
            ->firstOrFail();

        $guests = max(30, (int) $perPaxPkg->min_pax);
        $drinkOption = 'Softdrinks';
        $selectedPrice = (float) $perPaxPkg->price_with_softdrinks;
        $expectedTotal = round($guests * $selectedPrice, 2);
        $expectedDownpayment = round($expectedTotal * 0.5, 2);

        $response = $this->actingAs($this->customer)
            ->post('/catering/bookings', $this->bookingData($date, [
                'package_id' => $perPaxPkg->package_id,
                'guest_count' => $guests,
                'drink_option' => $drinkOption,
            ]));

        $response->assertSessionHasNoErrors();
        $booking = CateringBooking::where('package_id', $perPaxPkg->package_id)->latest('booking_id')->firstOrFail();

        $this->assertSame($drinkOption, $booking->drink_option);
        $this->assertSame($expectedTotal, $booking->computeTotal());
        $this->assertSame($expectedDownpayment, $booking->computeDownpayment());

        // Admin confirmation should create Order with exact total and downpayment
        $this->actingAs($this->admin)
            ->patch("/admin/catering-bookings/{$booking->booking_id}/confirm")
            ->assertRedirect();

        $order = $booking->fresh()->order;
        $this->assertNotNull($order);
        $this->assertEquals($expectedTotal, (float) $order->total_amount);
        $this->assertEquals($expectedDownpayment, (float) $order->downpayment_amount);

        // Check order item
        $orderItem = $order->items->first();
        $this->assertSame($guests, $orderItem->quantity);
        $this->assertEquals($selectedPrice, (float) $orderItem->unit_price);
        $this->assertEquals($expectedTotal, (float) $orderItem->subtotal);

        // Check payment
        $payment = $order->payments->first();
        $this->assertEquals($expectedDownpayment, (float) $payment->amount);
    }


    private function createBooking(string $date): CateringBooking
    {
        $this->actingAs($this->customer)
            ->post('/catering/bookings', $this->bookingData($date))
            ->assertRedirect(route('orders.index'));

        return CateringBooking::latest('booking_id')->firstOrFail();
    }

    private function bookingData(string $date, array $overrides = []): array
    {
        return array_merge([
            'slot_date' => $date,
            'event_type' => 'Birthday',
            'guest_count' => 20,
            'package_id' => $this->package->package_id,
            'event_time' => '18:00',
            'event_location' => '12 Main Street',
            'special_requests' => 'Vegetarian option, please.',
        ], $overrides);
    }
}
