<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurantId = 1; // Assuming RESTAURANT 1 from the previous seeder

        // Safety check to ensure the restaurant exists
        if (!Restaurant::find($restaurantId)) {
            $this->command->error("Restaurant ID {$restaurantId} not found. Please run RestaurantSetupSeeder first.");
            return;
        }

        // 1. Create Categories
        $categories = [
            ['id' => 1, 'name' => 'GUJARATI', 'sort_order' => 1],
            ['id' => 2, 'name' => 'PUNJABI', 'sort_order' => 2],
            ['id' => 6, 'name' => 'BURGER', 'sort_order' => 3],
            ['id' => 3, 'name' => 'CHINESE', 'sort_order' => 4],
            ['id' => 4, 'name' => 'SOUTH INDIAN', 'sort_order' => 5],
            ['id' => 5, 'name' => 'COLD DRINKS', 'sort_order' => 6],
        ];

        foreach ($categories as $catData) {
            Category::updateOrCreate(
                [
                    'id' => $catData['id'], 
                    'restaurant_id' => $restaurantId
                ],
                [
                    'name' => $catData['name'],
                    'sort_order' => $catData['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Create Menu Items
        $menuItems = [
            [
                'id' => 1,
                'category_id' => 1, // Belongs to GUJARATI
                'name' => 'THALI',
                'description' => 'FULL THALI',
                'price' => 100.00,
            ],
            [
                'id' => 2,
                'category_id' => 2, // Belongs to PUNJABI
                'name' => 'THALI',
                'description' => 'FULL PUNJABI THALI',
                'price' => 120.00,
            ],
            [
                'id' => 3,
                'category_id' => 6, // Belongs to BURGER
                'name' => 'BURGER',
                'description' => null,
                'price' => 50.00,
            ]
        ];

        foreach ($menuItems as $itemData) {
            MenuItem::updateOrCreate(
                [
                    'id' => $itemData['id'], 
                    'restaurant_id' => $restaurantId
                ],
                [
                    'category_id' => $itemData['category_id'],
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'price' => $itemData['price'],
                    'is_available' => true,
                ]
            );
        }

        $this->command->info('Categories and Menu Items created successfully!');
    }
}