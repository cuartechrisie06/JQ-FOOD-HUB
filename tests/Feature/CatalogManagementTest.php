<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoodPackage;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $adminRole = Role::where('role_name', 'Admin')->firstOrFail();
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->role_id,
            'status' => 'Active',
            'customer_type' => null,
        ]);
    }

    public function test_category_crud(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/categories', [
                'category_name' => 'Frozen Foods',
                'description' => 'Frozen products',
            ])
            ->assertRedirect('/admin/categories');

        $category = Category::where('category_name', 'Frozen Foods')->firstOrFail();

        $this->actingAs($this->admin)
            ->put("/admin/categories/{$category->category_id}", [
                'category_name' => 'Frozen Items',
                'description' => 'Updated',
            ])
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', ['category_name' => 'Frozen Items']);

        $this->actingAs($this->admin)
            ->delete("/admin/categories/{$category->category_id}")
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('categories', ['category_id' => $category->category_id]);
    }

    public function test_product_crud(): void
    {
        Storage::fake('public');
        $category = Category::create(['category_name' => 'Lumpia', 'description' => null]);

        $this->actingAs($this->admin)
            ->post('/admin/products', [
                'category_id' => $category->category_id,
                'product_name' => 'Fresh Lumpia',
                'description' => 'Freshly made',
                'unit' => 'pack',
                'unit_price' => '250.00',
                'stock_qty' => 20,
                'lead_time_days' => 2,
                'is_available' => '1',
            ])
            ->assertRedirect('/admin/products');

        $product = $category->products()->where('product_name', 'Fresh Lumpia')->firstOrFail();

        $this->actingAs($this->admin)
            ->put("/admin/products/{$product->product_id}", [
                'category_id' => $category->category_id,
                'product_name' => 'Fresh Lumpia Large',
                'description' => 'Updated',
                'unit' => 'box',
                'unit_price' => '300.00',
                'stock_qty' => 15,
                'lead_time_days' => 3,
                'is_available' => '1',
            ])
            ->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', ['product_name' => 'Fresh Lumpia Large', 'stock_qty' => 15]);

        $this->actingAs($this->admin)
            ->patch("/admin/products/{$product->product_id}/toggle")
            ->assertRedirect();
        $this->assertFalse((bool) $product->fresh()->is_available);
    }

    public function test_food_package_crud_with_items(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/packages', [
                'package_name' => 'Birthday Package',
                'description' => 'Catering package',
                'price' => '8000.00',
                'min_pax' => 20,
                'max_pax' => 50,
                'event_type' => 'Birthday',
                'is_available' => '1',
                'items' => [
                    ['item_description' => 'Fresh Lumpia', 'quantity' => '50 pieces'],
                    ['item_description' => 'Rice', 'quantity' => '50 cups'],
                ],
            ])
            ->assertRedirect('/admin/packages');

        $package = FoodPackage::where('package_name', 'Birthday Package')->firstOrFail();
        $this->assertDatabaseCount('package_items', 2);

        $this->actingAs($this->admin)
            ->put("/admin/packages/{$package->package_id}", [
                'package_name' => 'Birthday Package Deluxe',
                'description' => 'Updated',
                'price' => '9000.00',
                'min_pax' => 20,
                'max_pax' => 60,
                'event_type' => 'Birthday',
                'is_available' => '1',
                'items' => [
                    ['item_description' => 'Shanghai Lumpia', 'quantity' => '60 pieces'],
                ],
            ])
            ->assertRedirect('/admin/packages');

        $this->assertDatabaseHas('food_packages', ['package_name' => 'Birthday Package Deluxe']);
        $this->assertSame(1, $package->items()->count());
    }

    public function test_csv_import_handles_valid_duplicate_and_invalid_rows(): void
    {
        Storage::fake('public');
        $category = Category::create(['category_name' => 'Frozen', 'description' => null]);
        $category->products()->create([
            'product_name' => 'Cheesesticks',
            'description' => 'Existing',
            'unit' => 'pack',
            'unit_price' => 200,
            'stock_qty' => 5,
            'lead_time_days' => 1,
            'image_path' => null,
            'is_available' => true,
            'created_at' => now(),
        ]);

        $csv = implode("\n", [
            'category_name,product_name,description,unit,unit_price,lead_time_days,is_available,image_filename',
            'Lumpia,Fresh Lumpia,Fresh lumpia,pack,250.00,2,1,fresh.jpg',
            'Frozen,Cheesesticks,Existing,pack,200.00,1,1,cheese.jpg',
            'Lumpia,,Missing name,pack,100.00,1,1,missing.jpg',
            'Lumpia,Bad Price,Bad row,pack,-5,1,1,bad.jpg',
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        $response = $this->actingAs($this->admin)
            ->post('/admin/products/import', ['csv_file' => $file])
            ->assertRedirect('/admin/products');

        $response->assertSessionHas('import_report');

        $report = $response->getSession()->get('import_report');
        $this->assertContains('imported', array_column($report, 'status'));
        $this->assertContains('duplicate', array_column($report, 'status'));
        $this->assertGreaterThanOrEqual(2, count(array_filter($report, fn ($item) => $item['status'] === 'rejected')));

        $this->assertDatabaseHas('products', [
            'product_name' => 'Fresh Lumpia',
            'unit_price' => 250,
        ]);
        $this->assertDatabaseMissing('products', ['product_name' => 'Bad Price']);
    }

    public function test_customer_cannot_open_catalog_management(): void
    {
        $customerRole = Role::where('role_name', 'Customer')->firstOrFail();
        $customer = User::factory()->create([
            'role_id' => $customerRole->role_id,
            'status' => 'Active',
        ]);

        $this->actingAs($customer)->get('/admin/products')->assertRedirect('/');
        $this->actingAs($customer)->get('/admin/categories')->assertRedirect('/');
        $this->actingAs($customer)->get('/admin/packages')->assertRedirect('/');
    }
}
