<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodPackageRequest;
use App\Models\FoodPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FoodPackageController extends Controller
{
    public function index(): View
    {
        return view('admin.packages.index', [
            'packages' => FoodPackage::with('items')->orderBy('package_name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.create');
    }

    public function store(FoodPackageRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['is_available'] = $request->boolean('is_available');

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('packages', 'public');
            }

            unset($data['image'], $data['items']);
            $package = FoodPackage::create($data);
            $this->syncItems($package, $request->input('items', []));

            return redirect()->route('admin.packages.index')->with('status', 'Food package created.');
        });
    }

    public function edit(FoodPackage $package): View
    {
        $package->load('items');

        return view('admin.packages.edit', compact('package'));
    }

    public function update(FoodPackageRequest $request, FoodPackage $package): RedirectResponse
    {
        return DB::transaction(function () use ($request, $package) {
            $data = $request->validated();
            $data['is_available'] = $request->boolean('is_available');

            if ($request->hasFile('image')) {
                if ($package->image_path) {
                    Storage::disk('public')->delete($package->image_path);
                }
                $data['image_path'] = $request->file('image')->store('packages', 'public');
            }

            unset($data['image'], $data['items']);
            $package->update($data);
            $this->syncItems($package, $request->input('items', []));

            return redirect()->route('admin.packages.index')->with('status', 'Food package updated.');
        });
    }

    public function destroy(FoodPackage $package): RedirectResponse
    {
        $package->update(['is_available' => false]);

        return back()->with('status', 'Food package deactivated instead of deleted.');
    }

    public function toggle(FoodPackage $package): RedirectResponse
    {
        $package->update(['is_available' => ! $package->is_available]);

        return back()->with('status', 'Food package availability updated.');
    }

    private function syncItems(FoodPackage $package, array $items): void
    {
        $package->items()->delete();

        foreach ($items as $item) {
            if (empty($item['item_description']) || empty($item['quantity'])) {
                continue;
            }

            $package->items()->create([
                'item_description' => $item['item_description'],
                'quantity' => $item['quantity'],
            ]);
        }
    }
}
