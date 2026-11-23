<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusChangedMail;
use App\Models\AvailabilityCalendar;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\OrderStatusService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderStatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $owner;

    private User $customer;

    private Product $product;

    private AvailabilityCalendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-22 09:00:00');
        $this->seed(DatabaseSeeder::class);
        $this->admin = $this->createUser('Admin', 'status-admin@example.com');
        $this->owner = User::where('email', env('JQ_OWNER_EMAIL', 'owner@jqfoodhub.local'))->firstOrFail();
        $this->customer = $this->createUser('Customer', 'status-customer@example.com');
        $category = Category::create(['category_name' => 'Lumpia', 'description' => null]);
        $this->product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Shanghai Lumpia',
            'description' => null,
            'unit' => 'pack',
            'unit_price' => 100,
            'stock_qty' => 20,
            'lead_time_days' => 1,
            'image_path' => null,
            'is_available' => true,
            'created_at' => now(),
        ]);
        $this->calendar = AvailabilityCalendar::create([
            'slot_date' => today()->addDays(3)->toDateString(),
            'max_bookings' => 2,
            'is_blocked' => false,
            'remarks' => null,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_regular_status_transitions_update_stock_notifications_and_queue_mail(): void
    {
        Mail::fake();
        $order = $this->createOrder('Regular');

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Confirmed'])
            ->assertRedirect();
        $this->assertSame(17, $this->product->fresh()->stock_qty);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->user_id,
            'order_id' => $order->order_id,
            'type' => 'Status Change',
            'email_sent' => 1,
        ]);
        Mail::assertQueued(OrderConfirmationMail::class, function ($mail) use ($order) {
            $mail->build();

            return $mail->order->order_id === $order->order_id
                && count($mail->rawAttachments) === 1
                && str_contains($mail->rawAttachments[0]['data'], 'BEGIN:VCALENDAR')
                && str_contains($mail->rawAttachments[0]['data'], 'DTSTART:');
        });

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Preparing'])
            ->assertRedirect();
        Mail::assertQueued(OrderStatusChangedMail::class, fn ($mail) => $mail->order->order_id === $order->order_id
            && $mail->previousStatus === 'Confirmed'
            && $mail->newStatus === 'Preparing');

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Ready'])
            ->assertRedirect();
        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Completed'])
            ->assertRedirect();
        $this->assertSame(4, $order->notifications()->where('type', 'Status Change')->count());
        $this->assertSame(17, $this->product->fresh()->stock_qty);
    }

    public function test_bulk_order_cannot_be_confirmed_without_verified_downpayment(): void
    {
        $order = $this->createOrder('Bulk');

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Confirmed'])
            ->assertSessionHasErrors('status');

        $this->assertSame('Pending', $order->fresh()->status);
        $this->assertSame(20, $this->product->fresh()->stock_qty);
        $this->assertSame(['Cancelled'], app(OrderStatusService::class)->allowedTransitions($order->fresh()));

        $order->payments()->create([
            'payment_method' => 'Cash',
            'payment_type' => 'Down Payment',
            'amount' => 50,
            'status' => 'Verified',
            'submitted_at' => now(),
            'verified_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Confirmed'])
            ->assertRedirect();
        $this->assertSame(17, $this->product->fresh()->stock_qty);
    }

    public function test_invalid_transition_is_blocked_and_delivery_uses_out_for_delivery(): void
    {
        $deliveryOrder = $this->createOrder('Regular', 'Delivery');

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$deliveryOrder->order_id}/status", ['status' => 'Ready'])
            ->assertSessionHasErrors('status');

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$deliveryOrder->order_id}/status", ['status' => 'Confirmed'])
            ->assertRedirect();
        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$deliveryOrder->order_id}/status", ['status' => 'Preparing'])
            ->assertRedirect();
        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$deliveryOrder->order_id}/status", ['status' => 'Out for Delivery'])
            ->assertRedirect();

        $this->assertSame('Out for Delivery', $deliveryOrder->fresh()->status);
    }

    public function test_cancellation_after_confirmation_restores_product_stock(): void
    {
        $order = $this->createOrder('Regular');

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Confirmed']);
        $this->assertSame(17, $this->product->fresh()->stock_qty);

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Cancelled'])
            ->assertRedirect();

        $this->assertSame(20, $this->product->fresh()->stock_qty);
    }

    public function test_new_order_and_pending_payment_notify_all_active_admins_and_owners(): void
    {
        $order = $this->createOrder('Regular');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->user_id,
            'order_id' => $order->order_id,
            'type' => 'New Order',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->owner->user_id,
            'order_id' => $order->order_id,
            'type' => 'Pending Payment',
        ]);
        $this->assertSame(4, Notification::where('order_id', $order->order_id)->count());
    }

    public function test_customer_order_page_shows_status_timeline_and_only_own_orders(): void
    {
        $order = $this->createOrder('Regular');
        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->order_id}/status", ['status' => 'Confirmed']);

        $this->actingAs($this->customer)
            ->get("/orders/{$order->order_id}")
            ->assertOk()
            ->assertSee('Status Timeline')
            ->assertSee('Confirmed');
    }

    public function test_admin_can_filter_orders_and_view_notifications(): void
    {
        $firstOrder = $this->createOrder('Regular');
        $secondOrder = $this->createOrder('Regular', 'Delivery');
        $this->actingAs($this->admin)
            ->get('/admin/orders?search=JQ-'.str_pad($firstOrder->order_id, 4, '0', STR_PAD_LEFT))
            ->assertOk()
            ->assertSee('JQ-'.str_pad($firstOrder->order_id, 4, '0', STR_PAD_LEFT))
            ->assertDontSee('JQ-'.str_pad($secondOrder->order_id, 4, '0', STR_PAD_LEFT));

        $this->actingAs($this->admin)
            ->get('/admin/notifications')
            ->assertOk()
            ->assertSee('Pending Payment')
            ->assertSee('New Order');

        $this->assertSame(
            0,
            Notification::where('user_id', $this->admin->user_id)->where('is_read', false)->count()
        );
    }

    private function createOrder(string $type, string $fulfillment = 'Pickup'): Order
    {
        $order = Order::create([
            'user_id' => $this->customer->user_id,
            'calendar_id' => $this->calendar->calendar_id,
            'order_type' => $type,
            'fulfillment_method' => $fulfillment,
            'fulfillment_time' => '12:00:00',
            'delivery_address' => $fulfillment === 'Delivery' ? '1 Delivery Road' : null,
            'total_amount' => 300,
            'downpayment_amount' => in_array($type, ['Bulk', 'Packed Lunch', 'Catering'], true) ? 150 : 0,
            'status' => 'Pending',
            'notes' => null,
            'created_at' => now(),
        ]);
        $order->items()->create([
            'product_id' => $this->product->product_id,
            'package_id' => null,
            'quantity' => 3,
            'unit_price' => 100,
            'subtotal' => 300,
        ]);
        Payment::create([
            'order_id' => $order->order_id,
            'payment_method' => 'Cash',
            'payment_type' => $type === 'Regular' ? 'Full Payment' : 'Down Payment',
            'amount' => $type === 'Regular' ? 300 : 150,
            'status' => 'Pending',
            'submitted_at' => now(),
        ]);

        return $order;
    }

    private function createUser(string $roleName, string $email): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        return User::create([
            'role_id' => $role->role_id,
            'full_name' => $roleName.' Test',
            'email' => $email,
            'phone' => '09171234567',
            'password_hash' => Hash::make('password123'),
            'address' => null,
            'customer_type' => $roleName === 'Customer' ? 'Individual' : null,
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
    }
}
