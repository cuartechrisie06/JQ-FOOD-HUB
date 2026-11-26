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
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderCalendarTest extends TestCase
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
        $this->customer = $this->makeUser('Customer', 'cal-customer@example.com');
        $this->admin = $this->makeUser('Admin', 'cal-admin@example.com');
        $this->package = FoodPackage::create([
            'package_group' => 'Budget Meals',
            'package_name' => 'Budget Meals - Option 1',
            'pricing_type' => 'Per Pax',
            'description' => null,
            'price' => 100,
            'price_with_water' => 115,
            'price_with_softdrinks' => 125,
            'freebie' => null,
            'min_pax' => 25,
            'max_pax' => null,
            'event_type' => 'Any',
            'image_path' => null,
            'is_available' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_old_catering_page_redirects_to_the_food_packages_booking_section(): void
    {
        $response = $this->actingAs($this->customer)->get('/catering?package='.$this->package->package_id);

        $response->assertRedirect();
        $this->assertStringContainsString('tab=packages', $response->headers->get('Location'));
        $this->assertStringEndsWith('#booking', $response->headers->get('Location'));
    }

    public function test_customer_sees_booking_form_for_the_selected_package_on_the_menu(): void
    {
        $this->actingAs($this->customer)
            ->get('/menu?tab=budget&package='.$this->package->package_id)
            ->assertOk()
            ->assertSee('Book this for your event')
            ->assertSee('name="package_id" value="'.$this->package->package_id.'"', false)
            ->assertSee('id="catering-calendar"', false);

        auth()->logout();
        $this->flushSession();
        $this->get('/menu?tab=budget&package='.$this->package->package_id)
            ->assertOk()
            ->assertDontSee('Book this for your event')
            ->assertSee('Log in to book');
    }

    public function test_regular_order_does_not_make_a_date_fully_booked_but_bulk_orders_use_capacity(): void
    {
        $date = today()->addDays(5)->toDateString();
        $calendar = AvailabilityCalendar::getOrCreateForDate($date);
        $calendar->update(['max_bookings' => 1]);

        $this->makeOrder($calendar, 'Regular');
        $this->assertSame(0, $calendar->fresh()->bookedCount());
        $this->assertSame('Available', $calendar->fresh()->availabilityStatus());

        $bulk = $this->makeOrder($calendar, 'Bulk');
        $this->assertSame(1, $calendar->fresh()->bookedCount());
        $this->assertSame('Fully Booked', $calendar->fresh()->availabilityStatus());

        // Cancelling the order frees the slot again.
        $bulk->update(['status' => 'Cancelled']);
        $this->assertSame('Available', $calendar->fresh()->availabilityStatus());
    }

    public function test_admin_calendar_feed_lists_all_order_types_and_pending_requests(): void
    {
        $date = today()->addDays(5)->toDateString();
        $calendar = AvailabilityCalendar::getOrCreateForDate($date);
        $this->makeOrder($calendar, 'Regular');
        $this->makeOrder($calendar, 'Catering');
        CateringBooking::create([
            'user_id' => $this->customer->user_id,
            'package_id' => $this->package->package_id,
            'calendar_id' => $calendar->calendar_id,
            'order_id' => null,
            'event_type' => 'Birthday',
            'event_time' => '18:00',
            'event_location' => '12 Main Street',
            'guest_count' => 30,
            'drink_option' => 'Solo',
            'special_requests' => null,
            'status' => 'Pending',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/availability-calendar/events?start='.$date.'&end='.today()->addDays(6)->toDateString())
            ->assertOk();

        $types = collect($response->json('events'))->pluck('extendedProps.type')->all();
        $this->assertContains('Regular', $types);
        $this->assertContains('Catering', $types);
        $this->assertCount(3, $response->json('events'));
        $this->assertSame(1, $response->json("days.{$date}.booked"));
    }

    public function test_customer_cannot_read_the_admin_calendar_feed(): void
    {
        $this->actingAs($this->customer)
            ->getJson('/admin/availability-calendar/events?start=2026-11-01&end=2026-12-01')
            ->assertRedirect('/');
    }

    public function test_admin_orders_page_lists_pending_catering_requests_and_approval_emails_customer(): void
    {
        Mail::fake();
        $date = today()->addDays(5)->toDateString();
        $calendar = AvailabilityCalendar::getOrCreateForDate($date);
        $booking = CateringBooking::create([
            'user_id' => $this->customer->user_id,
            'package_id' => $this->package->package_id,
            'calendar_id' => $calendar->calendar_id,
            'order_id' => null,
            'event_type' => 'Birthday',
            'event_time' => '18:00',
            'event_location' => '12 Main Street',
            'guest_count' => 30,
            'drink_option' => 'Solo',
            'special_requests' => null,
            'status' => 'Pending',
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)->get('/admin/orders')
            ->assertOk()
            ->assertSee('Catering requests awaiting review');

        $this->actingAs($this->admin)->patch("/admin/catering-bookings/{$booking->booking_id}/confirm")->assertRedirect();

        Mail::assertQueued(\App\Mail\BookingConfirmedMail::class);
        $this->assertSame(1, $calendar->fresh()->bookedCount());
    }

    public function test_customer_sees_pending_catering_request_in_my_orders(): void
    {
        $this->actingAs($this->customer)->post('/catering/bookings', [
            'slot_date' => today()->addDays(5)->toDateString(),
            'event_type' => 'Birthday',
            'guest_count' => 30,
            'package_id' => $this->package->package_id,
            'drink_option' => 'Solo',
            'event_time' => '18:00',
            'event_location' => '12 Main Street',
        ])->assertRedirect(route('orders.index'));

        $this->actingAs($this->customer)->get('/orders')
            ->assertOk()
            ->assertSee('Catering requests')
            ->assertSee('Budget Meals - Option 1');
    }

    private function makeUser(string $roleName, string $email): User
    {
        return User::create([
            'role_id' => Role::where('role_name', $roleName)->firstOrFail()->role_id,
            'full_name' => $roleName.' User',
            'email' => $email,
            'phone' => '09171234567',
            'password_hash' => Hash::make('password123'),
            'address' => '1 Test Street',
            'customer_type' => $roleName === 'Customer' ? 'Individual' : null,
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
    }

    private function makeOrder(AvailabilityCalendar $calendar, string $type): Order
    {
        return Order::create([
            'user_id' => $this->customer->user_id,
            'calendar_id' => $calendar->calendar_id,
            'order_type' => $type,
            'fulfillment_method' => 'Pickup',
            'fulfillment_time' => '10:00',
            'delivery_address' => null,
            'total_amount' => 1000,
            'downpayment_amount' => 0,
            'status' => 'Pending',
            'notes' => null,
            'created_at' => now(),
        ]);
    }
}
