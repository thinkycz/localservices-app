<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $servicesBySlug = [

            // ── Barbershops ──────────────────────────────────────────────────────
            'classic-cuts-barbershop' => [
                ['name' => 'Classic Haircut', 'description' => 'Traditional haircut with clippers and scissors. Includes hot towel and scalp massage.', 'duration_minutes' => 30, 'is_popular' => true, 'category_tag' => 'Haircut', 'staff_level' => 'Barber'],
                ['name' => 'Beard Trim & Shape', 'description' => 'Professional beard trimming and shaping with razor line-up.', 'duration_minutes' => 20, 'is_popular' => false, 'category_tag' => 'Grooming', 'staff_level' => 'Barber'],
                ['name' => 'Hot Towel Shave', 'description' => 'Relaxing hot towel treatment with straight razor shave.', 'duration_minutes' => 45, 'is_popular' => true, 'category_tag' => 'Shave', 'staff_level' => 'Senior Barber'],
                ['name' => 'Hair & Beard Combo', 'description' => 'Full haircut with beard trim and styling.', 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Combo', 'staff_level' => 'Barber'],
            ],

        ];

        foreach ($servicesBySlug as $slug => $services) {
            $shop = Shop::where('slug', $slug)->first();
            if (! $shop) {
                continue;
            }
            foreach ($services as $data) {
                Service::create(array_merge($data, ['shop_id' => $shop->id]));
            }
        }
    }
}
