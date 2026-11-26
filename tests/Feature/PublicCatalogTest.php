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

    public function test_menu_searches_products_by_name(): void
    {
        $response = $this->get('/menu?tab=lumpias&search=Fresh');

        $response->assertOk();
        $response->assertSee('Fresh Lumpia');
        $response->assertDontSee('Chicken Packed Lunch');
    }

    public function test_menu_hides_unavailable_products(): void
    {
        $response = $this->get('/menu?tab=lumpias');

        $response->assertOk();
        $response->assertSee('Fresh Lumpia');
        $response->assertSee('Chicken Packed Lunch');
        $response->assertDontSee('Shanghai Lumpia');
        $response->assertDontSee('chip-row');
    }

    public function test_menu_sorts_products_by_name_and_price(): void
    {
        $nameResponse = $this->get('/menu?tab=lumpias&sort=name');
        $nameResponse->assertOk();
        $nameResponse->assertSeeInOrder(['Chicken Packed Lunch', 'Fresh Lumpia']);
        $nameResponse->assertDontSee('Shanghai Lumpia');

        $lowPriceResponse = $this->get('/menu?tab=lumpias&sort=price_low');
        $lowPriceResponse->assertOk();
        $lowPriceResponse->assertSeeInOrder(['Chicken Packed Lunch', 'Fresh Lumpia']);

        $highPriceResponse = $this->get('/menu?tab=lumpias&sort=price_high');
        $highPriceResponse->assertOk();
        $highPriceResponse->assertSeeInOrder(['Fresh Lumpia', 'Chicken Packed Lunch']);
    }

    public function test_old_products_list_redirects_to_the_lumpias_menu_tab(): void
    {
        $this->get('/products')
            ->assertRedirect(route('menu.index', ['tab' => 'lumpias']));

        $this->get('/products?search=Fresh')
            ->assertRedirect(route('menu.index', ['search' => 'Fresh', 'tab' => 'lumpias']));
    }

    public function test_product_details_and_placeholder_are_available(): void
    {
        $product = Product::where('product_name', 'Fresh Lumpia')->firstOrFail();

        $response = $this->get('/products/'.$product->product_id);

        $response->assertOk();
        $response->assertSee('Fresh Lumpia');
        $response->assertSee('₱250.00');
        $response->assertSee('placeholder-product.svg');
        $response->assertSee('Add to Cart');
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Best Sellers');
        $this->get('/packages')->assertRedirect(route('menu.index', ['tab' => 'packages']));
        $this->get('/menu')->assertOk();
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
