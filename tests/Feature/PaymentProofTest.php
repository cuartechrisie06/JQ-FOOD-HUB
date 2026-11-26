<?php

namespace Tests\Feature;

use App\Models\AvailabilityCalendar;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\OrderStatusService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-22 09:00:00');
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $this->customer = $this->makeUser('Customer', 'proof-customer@example.com');
        $this->admin = $this->makeUser('Admin', 'proof-admin@example.com');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_customer_uploads_proof_and_admin_verifies_it_which_unlocks_confirmation(): void
    {
        Mail::fake();
        [$order, $payment] = $this->makeOrder('Bulk', 'E-Wallet');

        $this->actingAs($this->customer)
            ->post(route('payments.proof', [$order, $payment]), [
                'payment_method' => 'E-Wallet',
                'reference_no' => 'GC-12345',
                'proof_image' => UploadedFile::fake()->image('proof.jpg'),
            ])
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('Pending', $payment->status);
        $this->assertSame('GC-12345', $payment->reference_no);
        $this->assertNotNull($payment->proof_image);
        Storage::disk('public')->assertExists($payment->proof_image);

        // Bulk orders cannot be confirmed before the down payment is verified.
        $this->assertSame(['Cancelled'], app(OrderStatusService::class)->allowedTransitions($order->fresh()));

        $this->actingAs($this->admin)
            ->patch(route('admin.payments.verify', $payment))
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('Verified', $payment->status);
        $this->assertSame($this->admin->user_id, $payment->verified_by);
        $this->assertContains('Confirmed', app(OrderStatusService::class)->allowedTransitions($order->fresh()));
    }

    public function test_online_payment_requires_proof_image(): void
    {
        [$order, $payment] = $this->makeOrder('Bulk', 'E-Wallet');

        $this->actingAs($this->customer)
            ->post(route('payments.proof', [$order, $payment]), ['payment_method' => 'E-Wallet'])
            ->assertSessionHasErrors('proof_image');
    }

    public function test_admin_cannot_verify_online_payment_without_proof_but_can_verify_cash(): void
    {
        [, $online] = $this->makeOrder('Bulk', 'E-Wallet');
        [, $cash] = $this->makeOrder('Bulk', 'Cash');

        $this->actingAs($this->admin)->patch(route('admin.payments.verify', $online))
            ->assertSessionHas('error');
        $this->assertSame('Pending', $online->fresh()->status);

        $this->actingAs($this->admin)->patch(route('admin.payments.verify', $cash));
        $this->assertSame('Verified', $cash->fresh()->status);
    }

    public function test_rejected_payment_can_be_resubmitted(): void
    {
        [$order, $payment] = $this->makeOrder('Bulk', 'E-Wallet');
        $payment->update(['proof_image' => 'payment-proofs/old.jpg']);

        $this->actingAs($this->admin)->patch(route('admin.payments.reject', $payment));
        $this->assertSame('Rejected', $payment->fresh()->status);

        $this->actingAs($this->customer)
            ->post(route('payments.proof', [$order, $payment]), [
                'payment_method' => 'E-Wallet',
                'proof_image' => UploadedFile::fake()->image('new.png'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Pending', $payment->fresh()->status);
    }

    public function test_customer_cannot_upload_proof_for_someone_elses_order(): void
    {
        [$order, $payment] = $this->makeOrder('Bulk', 'E-Wallet');
        $other = $this->makeUser('Customer', 'other-customer@example.com');

        $this->actingAs($other)
            ->post(route('payments.proof', [$order, $payment]), [
                'payment_method' => 'E-Wallet',
                'proof_image' => UploadedFile::fake()->image('proof.jpg'),
            ])
            ->assertNotFound();
    }

    public function test_packed_meals_category_gets_packed_lunch_order_type_and_downpayment(): void
    {
        $category = Category::create(['category_name' => 'Packed Meals', 'description' => null]);
        $product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Packed Lunch',
            'description' => null,
            'unit' => 'box',
            'unit_price' => 100,
            'stock_qty' => 50,
            'lead_time_days' => 1,
            'image_path' => null,
            'is_available' => true,
            'created_at' => now(),
        ]);

        $this->actingAs($this->customer)->post("/cart/{$product->product_id}", ['quantity' => 2]);
        $this->actingAs($this->customer)->post('/checkout', [
            'fulfillment_method' => 'Pickup',
            'fulfillment_date' => today()->addDays(2)->toDateString(),
            'fulfillment_time' => '10:00',
            'payment_method' => 'Cash',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('Packed Lunch', $order->order_type);
        $this->assertSame('100.00', $order->downpayment_amount);
    }

    public function test_faq_page_loads(): void
    {
        $this->get('/faq')->assertOk()->assertSee('Frequently Asked Questions');
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
            'customer_type' => 'Individual',
            'business_name' => null,
            'status' => 'Active',
            'created_at' => now(),
        ]);
    }

    /** @return array{0: Order, 1: Payment} */
    private function makeOrder(string $type, string $method): array
    {
        $calendar = AvailabilityCalendar::getOrCreateForDate(today()->addDays(4)->toDateString());
        $order = Order::create([
            'user_id' => $this->customer->user_id,
            'calendar_id' => $calendar->calendar_id,
            'order_type' => $type,
            'fulfillment_method' => 'Pickup',
            'fulfillment_time' => '10:00',
            'delivery_address' => null,
            'total_amount' => 1000,
            'downpayment_amount' => 500,
            'status' => 'Pending',
            'notes' => null,
            'created_at' => now(),
        ]);
        $payment = Payment::create([
            'order_id' => $order->order_id,
            'verified_by' => null,
            'payment_method' => $method,
            'payment_type' => 'Down Payment',
            'amount' => 500,
            'reference_no' => null,
            'proof_image' => null,
            'status' => 'Pending',
            'submitted_at' => now(),
            'verified_at' => null,
        ]);

        return [$order, $payment];
    }
}
