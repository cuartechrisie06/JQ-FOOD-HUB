<?php

namespace Database\Seeders;

use App\Models\AvailabilityCalendar;
use App\Models\Category;
use App\Models\CateringBooking;
use App\Models\FoodPackage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DemoDataSeeder is disabled in production.');
        }

        $this->call(DatabaseSeeder::class);

        $customerRole = Role::where('role_name', 'Customer')->firstOrFail();
        $customers = collect([
            ['name' => 'Ava Demo', 'email' => 'ava.demo@example.com'],
            ['name' => 'Ben Demo', 'email' => 'ben.demo@example.com'],
        ])->map(fn (array $customer) => User::updateOrCreate(
            ['email' => $customer['email']],
            [
                'role_id' => $customerRole->role_id,
                'full_name' => $customer['name'],
                'phone' => '09171234567',
                'password_hash' => Hash::make('password'),
                'address' => 'Mankilam, Tagum City',
                'customer_type' => 'Individual',
                'business_name' => null,
                'status' => 'Active',
                'created_at' => now(),
            ]
        ));

        $category = Category::firstOrCreate(
            ['category_name' => 'Demo Favorites'],
            ['description' => 'Sample products for a local class demo.']
        );

        $product = Product::where('is_available', true)->first() ?? Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Demo Shanghai Lumpia',
            'description' => 'Sample product used by the local demo seeder.',
            'unit' => 'pack',
            'unit_price' => 250,
            'stock_qty' => 100,
            'lead_time_days' => 1,
            'image_path' => null,
            'is_available' => true,
            'created_at' => now(),
        ]);

        $package = FoodPackage::where('is_available', true)->first() ?? FoodPackage::create([
            'package_name' => 'Demo Celebration Package',
            'description' => 'Sample package used by the local demo seeder.',
            'price' => 5000,
            'min_pax' => 10,
            'max_pax' => 100,
            'event_type' => 'Celebration',
            'image_path' => null,
            'is_available' => true,
        ]);

        $orderDate = today()->addDays(7)->toDateString();
        $bookingDate = today()->addDays(14)->toDateString();
        $orderCalendar = $this->calendarForDate($orderDate, 'Demo order date');
        $bookingCalendar = $this->calendarForDate($bookingDate, 'Demo booking date');

        $completedOrder = $this->demoOrder($customers[0], $orderCalendar, 'Completed', 'Class demo completed order');
        $completedOrder->items()->firstOrCreate(
            ['product_id' => $product->product_id],
            ['package_id' => null, 'quantity' => 3, 'unit_price' => $product->unit_price, 'subtotal' => $product->unit_price * 3]
        );

        Review::updateOrCreate(
            ['order_id' => $completedOrder->order_id],
            [
                'user_id' => $customers[0]->user_id,
                'rating' => 5,
                'comment' => 'Fresh and delicious. Great service!',
                'status' => 'Approved',
                'created_at' => now(),
            ]
        );

        $pendingReviewOrder = $this->demoOrder($customers[1], $orderCalendar, 'Completed', 'Class demo pending review order');
        $pendingReviewOrder->items()->firstOrCreate(
            ['product_id' => $product->product_id],
            ['package_id' => null, 'quantity' => 2, 'unit_price' => $product->unit_price, 'subtotal' => $product->unit_price * 2]
        );

        Review::updateOrCreate(
            ['order_id' => $pendingReviewOrder->order_id],
            [
                'user_id' => $customers[1]->user_id,
                'rating' => 4,
                'comment' => 'Easy ordering and tasty food.',
                'status' => 'Pending',
                'created_at' => now(),
            ]
        );

        $this->demoOrder($customers[0], $bookingCalendar, 'Pending', 'Class demo new order');

        CateringBooking::firstOrCreate(
            [
                'user_id' => $customers[1]->user_id,
                'calendar_id' => $bookingCalendar->calendar_id,
                'event_type' => 'Class Demo Celebration',
            ],
            [
                'package_id' => $package->package_id,
                'order_id' => null,
                'event_time' => '17:00:00',
                'event_location' => 'Mankilam, Tagum City',
                'guest_count' => min(max($package->min_pax ?? 20, 20), $package->max_pax ?? 50),
                'special_requests' => 'Sample pending booking for the class demo.',
                'status' => 'Pending',
                'created_at' => now(),
            ]
        );
    }

    private function demoOrder(User $customer, AvailabilityCalendar $calendar, string $status, string $notes): Order
    {
        return Order::firstOrCreate(
            [
                'user_id' => $customer->user_id,
                'calendar_id' => $calendar->calendar_id,
                'notes' => $notes,
            ],
            [
                'order_type' => 'Regular',
                'fulfillment_method' => 'Pickup',
                'fulfillment_time' => '12:00:00',
                'delivery_address' => null,
                'total_amount' => 1000,
                'downpayment_amount' => 0,
                'status' => $status,
                'created_at' => now(),
            ]
        );
    }

    private function calendarForDate(string $date, string $remarks): AvailabilityCalendar
    {
        return AvailabilityCalendar::whereDate('slot_date', $date)->first()
            ?? AvailabilityCalendar::create([
                'slot_date' => $date,
                'max_bookings' => 2,
                'is_blocked' => false,
                'remarks' => $remarks,
            ]);
    }
}
