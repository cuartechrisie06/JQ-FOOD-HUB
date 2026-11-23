<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public array $report = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $categoryName = trim((string) ($row['category_name'] ?? ''));
            $productName = trim((string) ($row['product_name'] ?? ''));
            $unit = trim((string) ($row['unit'] ?? ''));
            $price = $row['unit_price'] ?? null;
            $leadTime = $row['lead_time_days'] ?? null;
            $isAvailable = trim((string) ($row['is_available'] ?? ''));

            if ($categoryName === '' || $productName === '' || $unit === '') {
                $this->report[] = [
                    'row' => $rowNumber,
                    'status' => 'rejected',
                    'message' => 'Missing category_name, product_name, or unit.',
                ];

                continue;
            }

            if (! is_numeric($price) || (float) $price < 0) {
                $this->report[] = [
                    'row' => $rowNumber,
                    'status' => 'rejected',
                    'message' => 'unit_price must be numeric and non-negative.',
                ];

                continue;
            }

            if (! is_numeric($leadTime) || (float) $leadTime < 0 || floor((float) $leadTime) != (float) $leadTime) {
                $this->report[] = [
                    'row' => $rowNumber,
                    'status' => 'rejected',
                    'message' => 'lead_time_days must be a non-negative whole number.',
                ];

                continue;
            }

            if (! in_array($isAvailable, ['0', '1'], true)) {
                $this->report[] = [
                    'row' => $rowNumber,
                    'status' => 'rejected',
                    'message' => 'is_available must be 0 or 1.',
                ];

                continue;
            }

            $category = Category::firstOrCreate(
                ['category_name' => $categoryName],
                ['description' => null]
            );

            if (Product::where('category_id', $category->category_id)
                ->where('product_name', $productName)
                ->exists()) {
                $this->report[] = [
                    'row' => $rowNumber,
                    'status' => 'duplicate',
                    'message' => 'Duplicate product name in this category was skipped.',
                ];

                continue;
            }

            Product::create([
                'category_id' => $category->category_id,
                'product_name' => $productName,
                'description' => trim((string) ($row['description'] ?? '')) ?: null,
                'unit' => $unit,
                'unit_price' => (float) $price,
                'stock_qty' => 0,
                'lead_time_days' => (int) $leadTime,
                'image_path' => trim((string) ($row['image_filename'] ?? '')) ?: null,
                'is_available' => (int) $isAvailable,
                'created_at' => now(),
            ]);

            $this->report[] = [
                'row' => $rowNumber,
                'status' => 'imported',
                'message' => 'Product imported.',
            ];
        }
    }
}
