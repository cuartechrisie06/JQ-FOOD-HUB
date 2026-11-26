<?php

namespace Tests\Feature;

use App\Models\FoodPackage;
use App\Models\PackageItem;
use Database\Seeders\FoodPackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodPackageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_food_package_seeder_loads_35_packages_and_226_package_items(): void
    {
        $this->seed(FoodPackageSeeder::class);

        $this->assertSame(35, FoodPackage::count());
        $this->assertSame(20, FoodPackage::where('pricing_type', 'Fixed')->count());
        $this->assertSame(15, FoodPackage::where('pricing_type', 'Per Pax')->count());

        // Verify package items count (226 total inclusions across all 35 packages)
        $this->assertSame(226, PackageItem::count());

        // Verify quantity vs item_description parsing for counts with pcs/slice
        $itemWithQty = PackageItem::where('quantity', '!=', '1')->first();
        $this->assertNotNull($itemWithQty);
        $this->assertMatchesRegularExpression('/^\d+\s*(?:pcs|pc|slice|slices)$/i', $itemWithQty->quantity);

        // Fixed packages have freebie and null pax range
        $fixedPkg = FoodPackage::where('pricing_type', 'Fixed')->firstOrFail();
        $this->assertNotNull($fixedPkg->freebie);
        $this->assertNull($fixedPkg->min_pax);
        $this->assertNull($fixedPkg->max_pax);
        $this->assertSame('Any', $fixedPkg->event_type);

        // Per pax packages have min_pax set and max_pax null
        $perPaxPkg = FoodPackage::where('pricing_type', 'Per Pax')->firstOrFail();
        $this->assertNotNull($perPaxPkg->min_pax);
        $this->assertNull($perPaxPkg->max_pax);
        $this->assertNotNull($perPaxPkg->price_with_water);
        $this->assertNotNull($perPaxPkg->price_with_softdrinks);

        // Idempotency: re-running should not duplicate packages or items
        $this->seed(FoodPackageSeeder::class);
        $this->assertSame(35, FoodPackage::count());
        $this->assertSame(226, PackageItem::count());
    }
}
