<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kadeřnictví a holičství', 'slug' => 'kadernictvi-a-holicstvi', 'icon' => 'scissors'],
            ['name' => 'Autoservis', 'slug' => 'autoservis', 'icon' => 'car-front'],
            ['name' => 'Fitness a pohyb', 'slug' => 'fitness-a-pohyb', 'icon' => 'dumbbell'],
            ['name' => 'Péče o zvířata', 'slug' => 'pece-o-zvirata', 'icon' => 'paw-print'],
            ['name' => 'Úklid', 'slug' => 'uklid', 'icon' => 'sparkles'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
