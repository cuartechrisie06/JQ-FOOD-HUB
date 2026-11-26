<?php

namespace App\Http\Controllers;

use App\Models\FoodPackage;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    /**
     * Menu tabs: slug => [label, icon, package filter]. "lumpias" lists products.
     */
    public const MENU_TABS = [
        'packages' => ['label' => 'Food Packages', 'icon' => 'bi-box-seam', 'group' => null],
        'budget' => ['label' => 'Budget Meals', 'icon' => 'bi-cash-coin', 'group' => 'Budget Meals'],
        'snacks' => ['label' => 'Snacks', 'icon' => 'bi-cup-hot', 'group' => 'Snacks'],
        'buffet' => ['label' => 'Buffet Meals', 'icon' => 'bi-egg-fried', 'group' => 'Buffet Meals'],
        'lumpias' => ['label' => 'Lumpias', 'icon' => 'bi-bag-heart', 'group' => null],
    ];

    /**
     * Customer-facing menu, split into tabs. Catering tabs list packages;
     * the Lumpias tab lists orderable products.
     */
    public function menu(Request $request): View
    {
        // Older links used ?type=products|packages.
        $legacy = ['products' => 'lumpias', 'packages' => 'packages'];
        $requested = $request->input('tab', $legacy[$request->input('type')] ?? 'packages');
        $tab = array_key_exists($requested, self::MENU_TABS) ? $requested : 'packages';
        $search = trim((string) $request->input('search'));

        $packages = collect();
        $products = collect();

        if ($tab === 'lumpias') {
            $products = Product::with('category')
                ->where('is_available', true)
                ->when($search !== '', fn ($query) => $query->where('product_name', 'like', "%{$search}%"))
                ->when($request->input('sort') === 'price_low', fn ($query) => $query->orderBy('unit_price'))
                ->when($request->input('sort') === 'price_high', fn ($query) => $query->orderByDesc('unit_price'))
                ->when(! in_array($request->input('sort'), ['price_low', 'price_high'], true), fn ($query) => $query->orderBy('product_name'))
                ->paginate(12, ['*'], 'page')
                ->withQueryString();
        } else {
            $packages = $this->packageQuery($tab)
                ->with('items')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('package_name', 'like', "%{$search}%")
                        ->orWhere('package_group', 'like', "%{$search}%")
                        ->orWhereHas('items', fn ($items) => $items->where('item_description', 'like', "%{$search}%"));
                }))
                ->orderBy('price')
                ->orderBy('package_id')
                ->get();
        }

        $counts = [];
        foreach (array_keys(self::MENU_TABS) as $slug) {
            $counts[$slug] = $slug === 'lumpias'
                ? Product::where('is_available', true)->count()
                : $this->packageQuery($slug)->count();
        }

        $selectedPackage = $tab !== 'lumpias' && $request->filled('package')
            ? FoodPackage::with('items')->where('is_available', true)->find($request->integer('package'))
            : null;

        return view('catalog.menu', [
            'selectedPackage' => $selectedPackage,
            'earliestDate' => today()->addDays(3)->toDateString(),
            'tab' => $tab,
            'tabs' => self::MENU_TABS,
            'counts' => $counts,
            'products' => $products,
            'packages' => $packages,
        ]);
    }

    private function packageQuery(string $tab)
    {
        $query = FoodPackage::where('is_available', true);

        return $tab === 'packages'
            ? $query->where('pricing_type', 'Fixed')
            : $query->where('package_group', self::MENU_TABS[$tab]['group']);
    }

    /**
     * The old standalone product list. Products now live on the menu's
     * Lumpias tab; this keeps old links and bookmarks working.
     */
    public function products(Request $request): RedirectResponse
    {
        return redirect()->route('menu.index', array_merge(
            $request->only(['search', 'sort', 'page']),
            ['tab' => 'lumpias']
        ));
    }

    public function product(Product $product): View
    {
        return view('catalog.show', compact('product'));
    }

    public function packages(): RedirectResponse
    {
        return redirect()->route('menu.index', ['tab' => 'packages']);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function faq(): View
    {
        return view('pages.faq');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function reviews(): View
    {
        $approvedReviews = Review::with(['user', 'order'])
            ->where('status', 'Approved')
            ->orderByDesc('created_at')
            ->paginate(12);
        $approvedReviewCount = Review::where('status', 'Approved')->count();
        $averageRating = Review::where('status', 'Approved')->avg('rating');

        return view('pages.reviews', [
            'reviews' => $approvedReviews,
            'approvedReviewCount' => $approvedReviewCount,
            'averageRating' => $averageRating,
        ]);
    }
}
