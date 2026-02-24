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
            ['name' => 'Plumbing',     'icon' => '🔧'],
            ['name' => 'Electrical',   'icon' => '⚡'],
            ['name' => 'HVAC Repair',  'icon' => '❄️'],
            ['name' => 'Cleaning',     'icon' => '🧹'],
            ['name' => 'Landscaping',  'icon' => '🌿'],
            ['name' => 'Painting',     'icon' => '🎨'],
            ['name' => 'Carpentry',    'icon' => '🪚'],
            ['name' => 'Moving',       'icon' => '📦'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'icon' => $cat['icon']]
            );
        }
    }
}
