<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Lumpia Specials',
                'description' => 'Our signature fresh, fried, and authentic handmade lumpia selections.',
            ],
            [
                'category_name' => 'Frozen & Ready-to-Cook',
                'description' => 'Convenient packs of lumpia and cheesesticks ready to fry anytime at home.',
            ],
            [
                'category_name' => 'Packed Meals',
                'description' => 'Complete, delicious rice box meals perfect for office lunches and gatherings.',
            ],
            [
                'category_name' => 'Party Platters',
                'description' => 'Large trays and platters for birthdays, meetings, and family get-togethers.',
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['category_name' => $data['category_name']],
                ['description' => $data['description']]
            );
        }
    }
}
