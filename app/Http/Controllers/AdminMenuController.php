<?php

namespace App\Http\Controllers;

use App\Models\FoodPackage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMenuController extends Controller
{
    /**
     * Provide one catalog workspace while retaining the distinct rules for
     * individually sold products and catering packages.
     */
    public function index(Request $request): View
    {
        $type = $request->input('type', 'products');
        $type = in_array($type, ['products', 'packages'], true) ? $type : 'products';
        $search = trim((string) $request->input('search'));
        $availability = $request->input('availability');

        $products = Product::with('category')
            ->when($search !== '', fn ($query) => $query->where('product_name', 'like', "%{$search}%"))
            ->when(in_array($availability, ['active', 'inactive'], true), fn ($query) => $query->where('is_available', $availability === 'active'))
            ->orderBy('product_name')
            ->paginate(20, ['*'], 'products_page')
            ->withQueryString();

        $packages = FoodPackage::withCount('items')
            ->when($search !== '', fn ($query) => $query->where('package_name', 'like', "%{$search}%"))
            ->when(in_array($availability, ['active', 'inactive'], true), fn ($query) => $query->where('is_available', $availability === 'active'))
            ->orderBy('package_name')
            ->paginate(20, ['*'], 'packages_page')
            ->withQueryString();

        return view('admin.menu.index', [
            'type' => $type,
            'search' => $search,
            'availability' => $availability,
            'products' => $products,
            'packages' => $packages,
            'productCount' => Product::count(),
            'packageCount' => FoodPackage::count(),
        ]);
    }
}
