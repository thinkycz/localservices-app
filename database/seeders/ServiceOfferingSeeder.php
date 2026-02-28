<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceOffering;
use Illuminate\Database\Seeder;

class ServiceOfferingSeeder extends Seeder
{
    public function run(): void
    {
        $offeringsBySlug = [

            // ── Barbershops ──────────────────────────────────────────────────────
            'classic-cuts-barbershop' => [
                ['name' => 'Classic Haircut', 'description' => 'Traditional haircut with clippers and scissors. Includes hot towel and scalp massage.', 'price' => 35, 'duration_minutes' => 30, 'is_popular' => true, 'category_tag' => 'Haircut', 'staff_level' => 'Barber'],
                ['name' => 'Beard Trim & Shape', 'description' => 'Professional beard trimming and shaping with razor line-up.', 'price' => 20, 'duration_minutes' => 20, 'is_popular' => false, 'category_tag' => 'Grooming', 'staff_level' => 'Barber'],
                ['name' => 'Hot Towel Shave', 'description' => 'Relaxing hot towel treatment with straight razor shave.', 'price' => 40, 'duration_minutes' => 45, 'is_popular' => true, 'category_tag' => 'Shave', 'staff_level' => 'Senior Barber'],
                ['name' => 'Hair & Beard Combo', 'description' => 'Full haircut with beard trim and styling.', 'price' => 50, 'duration_minutes' => 45, 'is_popular' => false, 'category_tag' => 'Combo', 'staff_level' => 'Barber'],
            ],

        ];

        foreach ($offeringsBySlug as $slug => $offerings) {
            $service = Service::where('slug', $slug)->first();
            if (! $service) {
                continue;
            }
            foreach ($offerings as $data) {
                ServiceOffering::create(array_merge($data, ['service_id' => $service->id]));
            }
        }
    }
}
