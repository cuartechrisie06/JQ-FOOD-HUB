<?php

namespace Tests\Feature;

use App\Models\AvailabilityCalendar;
use App\Models\Category;
use App\Models\CateringBooking;
use App\Models\FoodPackage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private AvailabilityCalendar $calendar;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-22 09:00:00');
        $this->seed(DatabaseSeeder::class);
        $this->admin = $this->createUser('Admin', 'reports-admin@example.com');
        $this->customer = $this->createUser('Customer', 'reports-customer@example.com');
        $this->calendar = AvailabilityCalendar::create([
            'slot_date' => today()->toDateString(),
            'max_bookings' => 2,
            'is_blocked' => false,
            'remarks' => null,
        ]);
        $category = Category::create(['category_name' => 'Snacks', 'description' => null]);
        $this->product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Shanghai Lumpia',
            'description' => null,
            'unit' => 'pack',
            'unit_price' => 100,
            'stock_qty' => 50,
            'lead_time_days' => 1,
            'image_path' => null,
            'is_available' => true,
            'created_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_daily_prep_sheet_totals_products_and_lists_packages_and_catering_events(): void
    {
        $confirmed = $this->createOrder('Confirmed');
        OrderItem::create([
            'order_id' => $confirmed->order_id,
            'product_id' => $this->product->product_id,
            'package_id' => null,
            'quantity' => 2,
            'unit_price' => 100,
            'subtotal' => 200,
        ]);
        $preparing = $this->createOrder('Preparing');
        OrderItem::create([
            'order_id' => $preparing->order_id,
            'product_id' => $this->product->product_id,
            'package_id' => null,
            'quantity' => 3,
            'unit_price' => 100,
            'subtotal' => 300,
        ]);
        $cancelled = $this->createOrder('Cancelled');
        OrderItem::create([
            'order_id' => $cancelled->order_id,
            'product_id' => $this->product->product_id,
            'package_id' => null,
            'quantity' => 10,
            'unit_price' => 100,
            'subtotal' => 1000,
        ]);

        $package = FoodPackage::create([
            'package_name' => 'Family Feast',
            'description' => null,
            'price' => 1000,
            'min_pax' => 10,
            'max_pax' => 50,
            'event_type' => 'Birthday',
            'image_path' => null,
            'is_available' => true,
        ]);
        $cateringOrder = $this->createOrder('Confirmed', 'Catering');
        OrderItem::create([
            'order_id' => $cateringOrder->order_id,
            'product_id' => null,
            'package_id' => $package->package_id,
            'quantity' => 1,
            'unit_price' => 1000,
            'subtotal' => 1000,
        ]);
        CateringBooking::create([
            'user_id' => $this->customer->user_id,
            'package_id' => $package->package_id,
            'calendar_id' => $this->calendar->calendar_id,
            'order_id' => $cateringOrder->order_id,
            'event_type' => 'Birthday',
            'event_time' => '12:00:00',
            'event_location' => 'Tagum City',
            'guest_count' => 20,
            'special_requests' => null,
            'status' => 'Confirmed',
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/prep-sheet?date='.today()->toDateString())
            ->assertOk()
            ->assertSee('Shanghai Lumpia')
            ->assertSee('Family Feast')
            ->assertSee('Tagum City')
            ->assertSee('12:00 PM')
            ->assertSee('<td>Shanghai Lumpia</td><td>5</td>', false);
    }

    public function test_reports_count_orders_and_best_sellers_and_only_verified_payments(): void
    {
        $first = $this->createOrder('Confirmed');
        $this->addProductItem($first, 2);
        $second = $this->createOrder('Preparing');
        $this->addProductItem($second, 3);
        $pending = $this->createOrder('Pending');
        $this->addProductItem($pending, 4);

        $this->createPayment($first, 250, 'Verified');
        $this->createPayment($second, 175, 'Verified');
        $this->createPayment($pending, 900, 'Pending');

        $this->actingAs($this->admin)
            ->get('/admin/reports?from='.today()->toDateString().'&to='.today()->toDateString())
            ->assertOk()
            ->assertSee('Verified Sales')
            ->assertSee('₱425.00')
            ->assertSee('<div class="fs-3 fw-bold">3</div>', false)
            ->assertSee('<tr><td>Pending</td><td>1</td></tr>', false)
            ->assertSee('<tr><td>Confirmed</td><td>1</td></tr>', false)
            ->assertSee('<tr><td>Preparing</td><td>1</td></tr>', false)
            ->assertSee('<tr><td>Shanghai Lumpia</td><td>9</td></tr>', false);
    }

    public function test_prep_sheet_and_report_exports_download(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/prep-sheet/pdf?date='.today()->toDateString())
            ->assertDownload('daily-prep-sheet-'.today()->toDateString().'.pdf');

        $this->actingAs($this->admin)
            ->get('/admin/prep-sheet/csv?date='.today()->toDateString())
            ->assertDownload('daily-prep-sheet-'.today()->toDateString().'.csv');

        $query = '?from='.today()->toDateString().'&to='.today()->toDateString();
        $this->actingAs($this->admin)
            ->get('/admin/reports/pdf'.$query)
            ->assertDownload('jq-food-hub-report.pdf');

        $this->actingAs($this->admin)
            ->get('/admin/reports/csv'.$query)
            ->assertDownload('jq-food-hub-report.csv');
    }

    private function createUser(string $roleName, string $email): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        return User::create([
            'role_id' => $role->role_id,
            'full_name' => $roleName.' Test',
            'email' => $email,
            'phone' => '09123456789',
            'password_hash' => Hash::make('password'),
            'address' => 'Tagum City',
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
    }

    private function createOrder(string $status, string $type = 'Regular'): Order
    {
        return Order::create([
            'user_id' => $this->customer->user_id,
            'calendar_id' => $this->calendar->calendar_id,
            'order_type' => $type,
            'fulfillment_method' => 'Pickup',
            'fulfillment_time' => '10:00:00',
            'delivery_address' => null,
            'total_amount' => 1000,
            'downpayment_amount' => 0,
            'status' => $status,
            'notes' => null,
            'created_at' => now(),
        ]);
    }

    private function addProductItem(Order $order, int $quantity): void
    {
        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $this->product->product_id,
            'package_id' => null,
            'quantity' => $quantity,
            'unit_price' => 100,
            'subtotal' => 100 * $quantity,
        ]);
    }

    private function createPayment(Order $order, int $amount, string $status): void
    {
        Payment::create([
            'order_id' => $order->order_id,
            'verified_by' => null,
            'payment_method' => 'Cash',
            'payment_type' => 'Full Payment',
            'amount' => $amount,
            'reference_no' => null,
            'proof_image' => null,
            'status' => $status,
            'submitted_at' => now(),
            'verified_at' => $status === 'Verified' ? now() : null,
        ]);
    }
}
