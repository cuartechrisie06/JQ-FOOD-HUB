<?php

namespace Database\Seeders;

use App\Models\FoodPackage;
use App\Models\PackageItem;
use Illuminate\Database\Seeder;
use RuntimeException;

class FoodPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('docs/menu_data.json');

        if (! file_exists($jsonPath)) {
            throw new RuntimeException("menu_data.json not found at: {$jsonPath}");
        }

        $menuData = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($menuData)) {
            throw new RuntimeException("Invalid JSON in: {$jsonPath}");
        }

        // 1. Fixed-Price Packages (20 packages across 5 tiers)
        foreach ($menuData['fixed_groups'] ?? [] as $group) {
            $groupName = $group['group_name'];
            $price = (float) $group['price'];
            $freebie = $group['freebie'] ?? null;
            $description = $freebie ? "Includes {$freebie}" : null;

            foreach ($group['packages'] ?? [] as $pkg) {
                $packageName = "{$groupName} - {$pkg['name']}";

                $foodPackage = FoodPackage::updateOrCreate(
                    ['package_name' => $packageName],
                    [
                        'package_group' => $groupName,
                        'pricing_type' => 'Fixed',
                        'description' => $description,
                        'price' => $price,
                        'price_with_water' => null,
                        'price_with_softdrinks' => null,
                        'freebie' => $freebie,
                        'min_pax' => null,
                        'max_pax' => null,
                        'event_type' => 'Any',
                        'image_path' => null,
                        'is_available' => true,
                    ]
                );

                // Re-sync package items
                $foodPackage->items()->delete();
                foreach ($pkg['items'] ?? [] as $dish) {
                    [$qty, $desc] = $this->parseDishItem($dish);
                    PackageItem::create([
                        'package_id' => $foodPackage->package_id,
                        'item_description' => $desc,
                        'quantity' => $qty,
                    ]);
                }
            }
        }

        // 2. Per Pax Groups (15 packages: 7 Budget Meals, 5 Snacks, 3 Buffet Meals)
        foreach ($menuData['per_pax_groups'] ?? [] as $group) {
            $groupName = $group['group_name'];
            $minPax = isset($group['min_pax']) ? (int) $group['min_pax'] : 25;

            foreach ($group['options'] ?? [] as $option) {
                $packageName = "{$groupName} - {$option['name']}";
                $soloPrice = (float) ($option['solo'] ?? 0);
                $waterPrice = isset($option['water']) ? (float) $option['water'] : null;
                $softdrinksPrice = isset($option['softdrinks']) ? (float) $option['softdrinks'] : null;

                $descParts = ["Per pax package (min. {$minPax} guests). Solo: ₱" . number_format($soloPrice, 2)];
                if ($waterPrice !== null) {
                    $descParts[] = "w/ Bottled Water: ₱" . number_format($waterPrice, 2);
                }
                if ($softdrinksPrice !== null) {
                    $descParts[] = "w/ Softdrinks: ₱" . number_format($softdrinksPrice, 2);
                }
                $description = implode(' | ', $descParts);

                $foodPackage = FoodPackage::updateOrCreate(
                    ['package_name' => $packageName],
                    [
                        'package_group' => $groupName,
                        'pricing_type' => 'Per Pax',
                        'description' => $description,
                        'price' => $soloPrice,
                        'price_with_water' => $waterPrice,
                        'price_with_softdrinks' => $softdrinksPrice,
                        'freebie' => null,
                        'min_pax' => $minPax,
                        'max_pax' => null,
                        'event_type' => 'Any',
                        'image_path' => null,
                        'is_available' => true,
                    ]
                );

                // Re-sync package items
                $foodPackage->items()->delete();
                foreach ($option['items'] ?? [] as $dish) {
                    [$qty, $desc] = $this->parseDishItem($dish);
                    PackageItem::create([
                        'package_id' => $foodPackage->package_id,
                        'item_description' => $desc,
                        'quantity' => $qty,
                    ]);
                }
            }
        }
    }

    private function parseDishItem(string $item): array
    {
        $item = trim($item);
        if (preg_match('/^(\d+\s*(?:pcs|pc|slice|slices))\s+(.*)$/i', $item, $matches)) {
            return [trim($matches[1]), trim($matches[2])];
        }

        return ['1', $item];
    }
}
