<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Barbershops',    'icon' => '💇'],
            ['name' => 'Nail Salons',    'icon' => '💅'],
            ['name' => 'Restaurants',    'icon' => '🍽️'],
            ['name' => 'Coffee Shops',   'icon' => '☕'],
            ['name' => 'Pet Grooming',   'icon' => '🐕'],
            ['name' => 'Fitness & Gyms', 'icon' => '💪'],
            ['name' => 'Spa & Massage',  'icon' => '💆'],
            ['name' => 'Beauty Salons',  'icon' => '🎀'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'icon' => $cat['icon']]
            );
        }
    }
}

