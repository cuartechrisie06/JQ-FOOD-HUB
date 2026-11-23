<?php

namespace Tests\Feature;

use App\Models\CateringBooking;
use App\Models\Order;
use App\Models\Review;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_is_repeatable_and_refuses_production(): void
    {
        $seeder = new DemoSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertSame(2, Review::count());
        $this->assertSame(3, Order::whereIn('notes', [
            'Class demo completed order',
            'Class demo pending review order',
            'Class demo new order',
        ])->count());
        $this->assertSame(1, CateringBooking::where('event_type', 'Class Demo Celebration')->count());

        $this->app->detectEnvironment(fn () => 'production');
        try {
            $this->expectException(RuntimeException::class);
            $seeder->run();
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }
}
