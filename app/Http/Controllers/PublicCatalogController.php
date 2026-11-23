<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FoodPackage;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    public function products(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('product_name', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->boolean('available_today'), function ($query) {
                $query->where('is_available', true);
            })
            ->when($request->input('sort') === 'price_low', function ($query) {
                $query->orderBy('unit_price');
            })
            ->when($request->input('sort') === 'price_high', function ($query) {
                $query->orderByDesc('unit_price');
            })
            ->when($request->input('sort') === 'name_desc', function ($query) {
                $query->orderByDesc('product_name');
            })
            ->when(! in_array($request->input('sort'), ['price_low', 'price_high', 'name_desc'], true), function ($query) {
                $query->orderBy('product_name');
            })
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'categories' => Category::orderBy('category_name')->get(),
        ]);
    }

    public function product(Product $product): View
    {
        return view('catalog.show', compact('product'));
    }

    public function packages(): View
    {
        $packages = FoodPackage::with('items')
            ->where('is_available', true)
            ->orderBy('package_name')
            ->paginate(12);

        return view('catalog.packages', compact('packages'));
    }

    public function about(): View
    {
        return view('pages.about');
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
