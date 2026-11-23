<?php

namespace Tests\Feature;

use App\Models\AvailabilityCalendar;
use App\Models\Order;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $otherCustomer;

    private User $admin;

    private AvailabilityCalendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->customer = $this->createUser('Customer', 'review-customer@example.com');
        $this->otherCustomer = $this->createUser('Customer', 'review-other@example.com');
        $this->admin = $this->createUser('Admin', 'review-admin@example.com');
        $this->calendar = AvailabilityCalendar::create([
            'slot_date' => today()->toDateString(),
            'max_bookings' => 2,
            'is_blocked' => false,
            'remarks' => null,
        ]);
    }

    public function test_customer_can_review_completed_order_and_edit_while_pending(): void
    {
        $order = $this->createOrder($this->customer, 'Completed');

        $this->actingAs($this->customer)
            ->post(route('reviews.store', $order), [
                'rating' => 5,
                'comment' => 'Fresh and delicious.',
            ])
            ->assertRedirect(route('reviews.mine'));

        $review = Review::where('order_id', $order->order_id)->firstOrFail();
        $this->assertSame('Pending', $review->status);
        $this->assertSame(5, (int) $review->rating);

        $this->actingAs($this->customer)
            ->put(route('reviews.update', $review), [
                'rating' => 4,
                'comment' => 'Updated feedback.',
            ])
            ->assertRedirect(route('reviews.mine'));

        $this->assertDatabaseHas('reviews', [
            'review_id' => $review->review_id,
            'rating' => 4,
            'comment' => 'Updated feedback.',
            'status' => 'Pending',
        ]);
        $this->actingAs($this->customer)
            ->get(route('reviews.mine'))
            ->assertOk()
            ->assertSee('Updated feedback.');
    }

    public function test_reviews_require_own_completed_orders_and_only_pending_reviews_can_be_edited(): void
    {
        $otherOrder = $this->createOrder($this->otherCustomer, 'Completed');
        $unfinishedOrder = $this->createOrder($this->customer, 'Preparing');
        $pendingReview = $this->createReview($otherOrder, 'Pending', 'Other customer review');
        $approvedReview = $this->createReview(
            $this->createOrder($this->customer, 'Completed'),
            'Approved',
            'Already approved'
        );

        $this->actingAs($this->customer)
            ->post(route('reviews.store', $otherOrder), ['rating' => 5, 'comment' => 'Not mine'])
            ->assertForbidden();
        $this->post(route('reviews.store', $unfinishedOrder), ['rating' => 5, 'comment' => 'Too early'])
            ->assertForbidden();
        $this->put(route('reviews.update', $pendingReview), ['rating' => 3, 'comment' => 'Not mine'])
            ->assertForbidden();
        $this->put(route('reviews.update', $approvedReview), ['rating' => 3, 'comment' => 'Should not change'])
            ->assertForbidden();
        $this->post(route('reviews.store', $approvedReview->order), ['rating' => 4, 'comment' => 'Duplicate'])
            ->assertForbidden();

        $this->post(route('reviews.store', $this->createOrder($this->customer, 'Completed')), [
            'rating' => 6,
            'comment' => 'Invalid rating',
        ])->assertSessionHasErrors('rating');
    }

    public function test_admin_moderates_reviews_and_only_approved_reviews_are_public(): void
    {
        $pendingReview = $this->createReview(
            $this->createOrder($this->customer, 'Completed'),
            'Pending',
            'Visible after approval'
        );
        $hiddenReview = $this->createReview(
            $this->createOrder($this->otherCustomer, 'Completed'),
            'Hidden',
            'Private hidden feedback'
        );
        $this->createReview(
            $this->createOrder($this->customer, 'Completed'),
            'Approved',
            'Already approved feedback',
            3
        );

        $this->actingAs($this->customer)
            ->get(route('admin.reviews.index'))
            ->assertRedirect(route('home'));

        $this->actingAs($this->admin)
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Manage Reviews');

        $this->patch(route('admin.reviews.status', $pendingReview), ['status' => 'Approved'])
            ->assertRedirect();
        $this->get(route('reviews'))
            ->assertOk()
            ->assertSee('Visible after approval')
            ->assertSee('Already approved feedback')
            ->assertDontSee('Private hidden feedback')
            ->assertSee('4.0');
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('What Customers Say')
            ->assertSee('Visible after approval');

        $this->patch(route('admin.reviews.status', $pendingReview), ['status' => 'Hidden'])
            ->assertRedirect();
        $this->delete(route('admin.reviews.destroy', $hiddenReview))
            ->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['review_id' => $hiddenReview->review_id]);
        $this->get(route('reviews'))->assertDontSee('Visible after approval');
    }

    public function test_custom_forbidden_and_not_found_pages_render(): void
    {
        config(['app.debug' => false]);
        $unfinishedOrder = $this->createOrder($this->customer, 'Preparing');

        $this->actingAs($this->customer)
            ->post(route('reviews.store', $unfinishedOrder), ['rating' => 5])
            ->assertForbidden()
            ->assertSee('Access denied');

        $this->get('/missing-page')
            ->assertNotFound()
            ->assertSee('Page not found');
    }

    private function createUser(string $roleName, string $email): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        return User::create([
            'role_id' => $role->role_id,
            'full_name' => $roleName.' Review Test',
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

    private function createOrder(User $customer, string $status): Order
    {
        return Order::create([
            'user_id' => $customer->user_id,
            'calendar_id' => $this->calendar->calendar_id,
            'order_type' => 'Regular',
            'fulfillment_method' => 'Pickup',
            'fulfillment_time' => '12:00:00',
            'delivery_address' => null,
            'total_amount' => 1000,
            'downpayment_amount' => 0,
            'status' => $status,
            'notes' => null,
            'created_at' => now(),
        ]);
    }

    private function createReview(Order $order, string $status, string $comment, int $rating = 5): Review
    {
        return Review::create([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'rating' => $rating,
            'comment' => $comment,
            'status' => $status,
            'created_at' => now(),
        ]);
    }
}
