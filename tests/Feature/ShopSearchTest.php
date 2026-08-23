<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShopSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_service_and_czech_location_without_geolocation(): void
    {
        $vendor = User::factory()->create(['is_vendor' => true]);
        $category = Category::create(['name' => 'Péče', 'slug' => 'pece']);
        $shop = Shop::create([
            'category_id' => $category->id,
            'user_id' => $vendor->id,
            'name' => 'Studio Klid',
            'slug' => 'studio-klid',
            'city' => 'Olomouc',
            'currency' => 'CZK',
            'is_available' => true,
        ]);
        Service::create([
            'shop_id' => $shop->id,
            'name' => 'Relaxační masáž',
            'price' => 900,
            'duration_minutes' => 60,
            'is_available' => true,
        ]);

        foreach (['masáž', 'Olomouc'] as $query) {
            $this->get(route('shops.index', ['q' => $query]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Shops/Index')
                    ->where('shops.total', 1)
                    ->where('shops.data.0.name', 'Studio Klid'));
        }
    }
}
