<?php

namespace Tests\Feature;

use App\Models\AvailabilityCalendar;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerOrderingTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Category $regularCategory;

    private Category $lunchCategory;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-22 09:00:00');
        $this->seed(DatabaseSeeder::class);

        $role = Role::where('role_name', 'Customer')->firstOrFail();
        $this->customer = User::create([
            'role_id' => $role->role_id,
            'full_name' => 'Ordering Customer',
            'email' => 'ordering@example.com',
            'phone' => '09171234567',
            'password_hash' => Hash::make('password123'),
            'address' => '1 Customer Street',
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
        $this->regularCategory = Category::create(['category_name' => 'Lumpia', 'description' => null]);
        $this->lunchCategory = Category::create(['category_name' => 'Packed Lunch', 'description' => null]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_cart_total_order_type_downpayment_and_price_snapshot(): void
    {
        $product = $this->createProduct($this->regularCategory, 'Shanghai', 100, 1);
        $secondProduct = $this->createProduct($this->regularCategory, 'Ubod', 50, 0);

        $this->actingAs($this->customer)
            ->post("/cart/{$product->product_id}", ['quantity' => 10])
            ->assertRedirect('/cart');
        $this->actingAs($this->customer)
            ->post("/cart/{$secondProduct->product_id}", ['quantity' => 2])
            ->assertRedirect('/cart');

        $this->get('/cart')->assertOk()->assertSee('PHP 1,100.00');
        $this->placeOrder(today()->addDay()->toDateString())->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $this->assertSame('Bulk', $order->order_type);
        $this->assertSame('1100.00', $order->total_amount);
        $this->assertSame('550.00', $order->downpayment_amount);
        $this->assertSame('Pending', $order->status);
        $this->assertSame('100.00', $order->items->firstWhere('product_id', $product->product_id)->unit_price);
        $this->assertSame('Delivery', $order->fulfillment_method);
        $this->assertSame('1 Customer Street', $order->delivery_address);
        $this->assertSame(550.0, (float) $order->payments->first()->amount);
        $this->assertSame([], session('cart', []));
        $ownerId = User::where('email', env('JQ_OWNER_EMAIL', 'owner@jqfoodhub.local'))->value('user_id');
        $this->assertDatabaseHas('notifications', [
            'user_id' => $ownerId,
            'order_id' => $order->order_id,
            'type' => 'New Order',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $ownerId,
            'order_id' => $order->order_id,
            'type' => 'Pending Payment',
        ]);

        $product->update(['unit_price' => 999]);
        $this->assertSame('100.00', $order->items->firstWhere('product_id', $product->product_id)->fresh()->unit_price);
        $this->get(route('orders.confirmation', $order))
            ->assertOk()
            ->assertSee('JQ-0001')
            ->assertSee('550.00');
    }

    public function test_regular_order_has_no_downpayment_and_lunch_order_requires_half(): void
    {
        $regular = $this->createProduct($this->regularCategory, 'Fresh Lumpia', 80, 1);
        $this->actingAs($this->customer)->post("/cart/{$regular->product_id}", ['quantity' => 2]);
        $this->placeOrder(today()->addDay()->toDateString())->assertSessionHasNoErrors();

        $regularOrder = Order::firstOrFail();
        $this->assertSame('Regular', $regularOrder->order_type);
        $this->assertSame('0.00', $regularOrder->downpayment_amount);
        $this->assertSame('160.00', $regularOrder->payments()->firstOrFail()->amount);
        $this->assertSame('Full Payment', $regularOrder->payments()->first()->payment_type);

        $lunch = $this->createProduct($this->lunchCategory, 'Chicken Lunch', 200, 1);
        $this->actingAs($this->customer)->post("/cart/{$lunch->product_id}", ['quantity' => 1]);
        $this->placeOrder(today()->addDay()->toDateString());

        $lunchOrder = Order::where('order_type', 'Packed Lunch')->firstOrFail();
        $this->assertSame('100.00', $lunchOrder->downpayment_amount);
        $this->assertSame('Down Payment', $lunchOrder->payments()->firstOrFail()->payment_type);
    }

    public function test_checkout_rejects_dates_inside_longest_cart_lead_time(): void
    {
        $product = $this->createProduct($this->regularCategory, 'Long Lead Product', 100, 4);
        $this->actingAs($this->customer)->post("/cart/{$product->product_id}", ['quantity' => 1]);

        $this->placeOrder(today()->addDays(3)->toDateString())
            ->assertSessionHasErrors('fulfillment_date');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_blocked_fulfillment_date(): void
    {
        $product = $this->createProduct($this->regularCategory, 'Blocked Date Product', 100, 0);
        $this->actingAs($this->customer)->post("/cart/{$product->product_id}", ['quantity' => 1]);
        $blockedDate = today()->addDays(2)->toDateString();
        AvailabilityCalendar::create([
            'slot_date' => $blockedDate,
            'max_bookings' => 0,
            'is_blocked' => true,
            'remarks' => 'Maintenance',
        ]);

        $this->placeOrder($blockedDate)->assertSessionHasErrors('fulfillment_date');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $product = $this->createProduct($this->regularCategory, 'Private Order Product', 100, 0);
        $this->actingAs($this->customer)->post("/cart/{$product->product_id}", ['quantity' => 1]);
        $this->placeOrder(today()->addDay()->toDateString());
        $order = Order::firstOrFail();

        $otherCustomer = User::create([
            'role_id' => $this->customer->role_id,
            'full_name' => 'Other Customer',
            'email' => 'other@example.com',
            'phone' => '09170000000',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);

        $this->actingAs($otherCustomer)->get("/orders/{$order->order_id}")->assertNotFound();
        $this->actingAs($otherCustomer)->get("/orders/{$order->order_id}/confirmation")->assertNotFound();
    }

    public function test_guest_is_redirected_to_login_for_cart_and_checkout(): void
    {
        $this->get('/cart')->assertRedirect('/login');
        $this->get('/checkout')->assertRedirect('/login');
    }

    public function test_unavailable_product_cannot_be_added_to_cart(): void
    {
        $product = $this->createProduct($this->regularCategory, 'Sold Out', 100, 0);
        $product->update(['is_available' => false]);

        $this->actingAs($this->customer)
            ->post("/cart/{$product->product_id}", ['quantity' => 1])
            ->assertSessionHasErrors('cart');

        $this->assertSame([], session('cart', []));
    }

    public function test_customer_can_update_profile_and_default_address(): void
    {
        $this->actingAs($this->customer)
            ->put('/account', [
                'full_name' => 'Updated Customer',
                'email' => $this->customer->email,
                'phone' => '09179876543',
                'address' => 'Updated Delivery Address',
                'customer_type' => 'Individual',
            ])
            ->assertRedirect('/account');

        $this->assertDatabaseHas('users', [
            'user_id' => $this->customer->user_id,
            'full_name' => 'Updated Customer',
            'address' => 'Updated Delivery Address',
        ]);
    }

    private function placeOrder(string $date)
    {
        return $this->post('/checkout', [
            'fulfillment_method' => 'Delivery',
            'fulfillment_date' => $date,
            'fulfillment_time' => '12:00',
            'payment_method' => 'E-Wallet',
            'notes' => 'Please call on arrival.',
        ]);
    }

    private function createProduct(Category $category, string $name, float $price, int $leadTime): Product
    {
        return Product::create([
            'category_id' => $category->category_id,
            'product_name' => $name,
            'description' => null,
            'unit' => 'pack',
            'unit_price' => $price,
            'stock_qty' => 50,
            'lead_time_days' => $leadTime,
            'image_path' => null,
            'is_available' => true,
            'created_at' => now(),
        ]);
    }
}
