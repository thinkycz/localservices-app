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
            ['name' => 'Auto Repair',    'icon' => '🔧'],
            ['name' => 'Fitness',        'icon' => '💪'],
            ['name' => 'Pet Care',       'icon' => '🐾'],
            ['name' => 'Cleaning',       'icon' => '🧹'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'icon' => $cat['icon']]
            );
        }
    }
}
