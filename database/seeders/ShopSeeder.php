<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Shop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $barbershops = Category::where('slug', 'barbershops')->first();

        // Get service provider users to assign as owners
        $shopProviders = User::where('is_service_provider', true)->get();
        $providerIndex = 0;

        $shops = [
            // ── Barbershops ─────────────────────────────────────────────────────
            [
                'category_id' => $barbershops->id,
                'name' => 'Classic Cuts Barbershop',
                'description' => 'Traditional barbershop service classic haircuts, beard trims, and hot towel shaves. Our experienced barbers bring old-school craftsmanship to modern styling.',
                'price_range' => 2,
                'badge' => 'BEST VALUE',
                'badge_color' => 'green',
                'is_available' => true,
                'available_at' => 'Available today at 10:00 AM',
                'rating' => 4.8,
                'reviews_count' => 342,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '123 Main St, New York, NY 10001',
                'is_online_only' => false,
                'latitude' => 40.7484,
                'longitude' => -73.9857,
            ],
        ];

        foreach ($shops as $data) {
            $slug = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $slug;
            while (Shop::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            // Assign a service provider as owner (cycle through available providers)
            $userId = $shopProviders->isNotEmpty()
                ? $shopProviders[$providerIndex % $shopProviders->count()]->id
                : null;
            $providerIndex++;

            Shop::create(array_merge($data, [
                'slug' => $slug,
                'user_id' => $userId,
            ]));
        }
    }
}
