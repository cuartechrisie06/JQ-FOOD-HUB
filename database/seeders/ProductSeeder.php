<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/products.csv');

        if (! file_exists($csvPath)) {
            $this->command?->warn("Products CSV not found at: {$csvPath}");
            Log::warning("ProductSeeder: CSV not found at {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command?->error("Unable to open: {$csvPath}");
            return;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return;
        }

        // Normalize header column names
        $header = array_map(fn ($col) => strtolower(trim($col)), $header);

        $insertedCount = 0;
        $skippedCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count($row) < count($header)) {
                continue;
            }

            $data = array_combine($header, $row);
            $productName = trim($data['product_name'] ?? '');
            $categoryName = trim($data['category_name'] ?? '');
            $unitPriceRaw = trim($data['unit_price'] ?? '');
            $unit = trim($data['unit'] ?? '') ?: 'pack';
            $description = trim($data['description'] ?? '') ?: null;
            $leadTimeRaw = trim($data['lead_time_days'] ?? '');
            $isAvailable = isset($data['is_available']) ? (bool) trim($data['is_available']) : true;

            if (empty($productName) || empty($categoryName)) {
                $skippedCount++;
                Log::info("ProductSeeder (row {$rowNumber}): Skipped because product_name or category_name is empty.");
                $this->command?->line("Row {$rowNumber}: Skipped (empty product or category name).");
                continue;
            }

            // Only insert rows that have a valid unit_price greater than 0
            if ($unitPriceRaw === '' || ! is_numeric($unitPriceRaw) || (float) $unitPriceRaw <= 0) {
                $skippedCount++;
                Log::info("ProductSeeder (row {$rowNumber}): Skipped '{$productName}' - unit_price ('{$unitPriceRaw}') is missing, zero, or non-numeric.");
                $this->command?->line("Row {$rowNumber}: Skipped '{$productName}' (unit_price is missing or 0).");
                continue;
            }

            $unitPrice = (float) $unitPriceRaw;
            $leadTimeDays = is_numeric($leadTimeRaw) && (int) $leadTimeRaw >= 0 ? (int) $leadTimeRaw : 1;

            $category = Category::firstOrCreate(
                ['category_name' => $categoryName],
                ['description' => "Category for {$categoryName}"]
            );

            Product::updateOrCreate(
                [
                    'category_id' => $category->category_id,
                    'product_name' => $productName,
                ],
                [
                    'description' => $description,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'stock_qty' => 50,
                    'lead_time_days' => $leadTimeDays,
                    'image_path' => null,
                    'is_available' => $isAvailable,
                    'created_at' => now(),
                ]
            );

            $insertedCount++;
        }

        fclose($handle);

        $this->command?->info("ProductSeeder completed: {$insertedCount} inserted/updated, {$skippedCount} skipped.");
        Log::info("ProductSeeder completed: {$insertedCount} inserted/updated, {$skippedCount} skipped.");
    }
}
