<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    private Category $lumpia;

    private Category $lunch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lumpia = Category::create([
            'category_name' => 'Lumpia',
            'description' => null,
        ]);
        $this->lunch = Category::create([
            'category_name' => 'Packed Lunch',
            'description' => null,
        ]);

        $this->createProduct('Fresh Lumpia', $this->lumpia, 250, true);
        $this->createProduct('Shanghai Lumpia', $this->lumpia, 300, false);
        $this->createProduct('Chicken Packed Lunch', $this->lunch, 150, true);
    }

    public function test_catalog_searches_by_product_name(): void
    {
        $response = $this->get('/products?search=Fresh');

        $response->assertOk();
        $response->assertSee('Fresh Lumpia');
        $response->assertDontSee('Chicken Packed Lunch');
    }

    public function test_catalog_filters_by_category_and_availability(): void
    {
        $response = $this->get('/products?category_id='
            .$this->lumpia->category_id
            .'&available_today=1');

        $response->assertOk();
        $response->assertSee('Fresh Lumpia');
        $response->assertDontSee('Shanghai Lumpia');
        $response->assertDontSee('Chicken Packed Lunch');
    }

    public function test_catalog_sorts_by_name_and_price(): void
    {
        $nameResponse = $this->get('/products?sort=name');
        $nameResponse->assertOk();
        $nameResponse->assertSeeInOrder(['Chicken Packed Lunch', 'Fresh Lumpia', 'Shanghai Lumpia']);

        $priceResponse = $this->get('/products?sort=price_low');
        $priceResponse->assertOk();
        $priceResponse->assertSeeInOrder(['Chicken Packed Lunch', 'Fresh Lumpia', 'Shanghai Lumpia']);

        $highPriceResponse = $this->get('/products?sort=price_high');
        $highPriceResponse->assertOk();
        $highPriceResponse->assertSeeInOrder(['Shanghai Lumpia', 'Fresh Lumpia', 'Chicken Packed Lunch']);
    }

    public function test_product_details_and_placeholder_are_available(): void
    {
        $product = Product::where('product_name', 'Fresh Lumpia')->firstOrFail();

        $response = $this->get('/products/'.$product->product_id);

        $response->assertOk();
        $response->assertSee('Fresh Lumpia');
        $response->assertSee('PHP 250.00');
        $response->assertSee('placeholder-product.svg');
        $response->assertSee('Add to Cart');
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Best Sellers');
        $this->get('/packages')->assertOk();
        $this->get('/about')->assertOk()->assertSee('Our Story');
        $this->get('/contact')->assertOk()->assertSee('Mankilam');
        $this->get('/reviews')->assertOk()->assertSee('No approved reviews yet');
    }

    private function createProduct(string $name, Category $category, float $price, bool $available): void
    {
        Product::create([
            'category_id' => $category->category_id,
            'product_name' => $name,
            'description' => 'Test product',
            'unit' => 'pack',
            'unit_price' => $price,
            'stock_qty' => 10,
            'lead_time_days' => 1,
            'image_path' => null,
            'is_available' => $available,
            'created_at' => now(),
        ]);
    }
}
