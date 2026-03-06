<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $barbershops = Category::where('slug', 'barbershops')->first();
        $autoRepair  = Category::where('slug', 'auto-repair')->first();
        $fitness     = Category::where('slug', 'fitness')->first();
        $petCare     = Category::where('slug', 'pet-care')->first();
        $cleaning    = Category::where('slug', 'cleaning')->first();

        $providers = User::where('is_vendor', true)->get();
        $idx = 0;

        $shops = [
            [
                'category_id'  => $barbershops->id,
                'name'         => 'Classic Cuts Barbershop',
                'description'  => 'Traditional barbershop offering classic haircuts, beard trims, and hot towel shaves. Our experienced barbers bring old-school craftsmanship to modern styling.',
                'price_range'  => 2,
                'is_available' => true,
                'available_at' => 'Available today at 10:00 AM',
                'rating'       => 4.8,
                'reviews_count' => 342,
                'city'         => 'New York',
                'state'        => 'NY',
                'address'      => '123 Main St, New York, NY 10001',
                'is_online_only' => false,
                'latitude'     => 40.7484,
                'longitude'    => -73.9857,
            ],
            [
                'category_id'  => $autoRepair->id,
                'name'         => 'QuickFix Auto Garage',
                'description'  => 'Full-service auto repair shop specializing in diagnostics, brake jobs, oil changes, and engine tuning. ASE-certified mechanics with 15+ years of experience.',
                'price_range'  => 3,
                'is_available' => true,
                'available_at' => 'Available today at 8:00 AM',
                'rating'       => 4.9,
                'reviews_count' => 218,
                'city'         => 'Brooklyn',
                'state'        => 'NY',
                'address'      => '456 Atlantic Ave, Brooklyn, NY 11217',
                'is_online_only' => false,
                'latitude'     => 40.6860,
                'longitude'    => -73.9780,
            ],
            [
                'category_id'  => $fitness->id,
                'name'         => 'Iron Peak Fitness Studio',
                'description'  => 'Boutique fitness studio offering personal training, group HIIT classes, yoga sessions, and nutrition coaching. Transform your body with our expert trainers.',
                'price_range'  => 3,
                'is_available' => true,
                'available_at' => 'Available today at 6:00 AM',
                'rating'       => 4.7,
                'reviews_count' => 156,
                'city'         => 'Manhattan',
                'state'        => 'NY',
                'address'      => '789 5th Ave, New York, NY 10022',
                'is_online_only' => false,
                'latitude'     => 40.7636,
                'longitude'    => -73.9712,
            ],
            [
                'category_id'  => $petCare->id,
                'name'         => 'Paws & Claws Pet Spa',
                'description'  => 'Premium pet grooming and daycare services. We treat your furry friends like royalty with organic shampoos, gentle handling, and lots of love.',
                'price_range'  => 2,
                'is_available' => true,
                'available_at' => 'Available tomorrow at 9:00 AM',
                'rating'       => 4.6,
                'reviews_count' => 289,
                'city'         => 'Queens',
                'state'        => 'NY',
                'address'      => '321 Queens Blvd, Queens, NY 11375',
                'is_online_only' => false,
                'latitude'     => 40.7218,
                'longitude'    => -73.8440,
            ],
            [
                'category_id'  => $cleaning->id,
                'name'         => 'Sparkle Home Cleaning Co.',
                'description'  => 'Professional residential and commercial cleaning services. Deep cleaning, move-in/move-out cleaning, and recurring weekly or bi-weekly service available.',
                'price_range'  => 2,
                'is_available' => true,
                'available_at' => 'Available today at 7:00 AM',
                'rating'       => 4.5,
                'reviews_count' => 410,
                'city'         => 'Bronx',
                'state'        => 'NY',
                'address'      => '555 Grand Concourse, Bronx, NY 10451',
                'is_online_only' => false,
                'latitude'     => 40.8265,
                'longitude'    => -73.9209,
            ],
        ];

        foreach ($shops as $data) {
            $slug = Str::slug($data['name']);
            $counter = 1;
            $original = $slug;
            while (Shop::where('slug', $slug)->exists()) {
                $slug = $original . '-' . $counter++;
            }

            $userId = $providers->isNotEmpty()
                ? $providers[$idx % $providers->count()]->id
                : null;
            $idx++;

            Shop::create(array_merge($data, [
                'slug'    => $slug,
                'user_id' => $userId,
            ]));
        }
    }
}
