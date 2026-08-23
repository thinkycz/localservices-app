<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $providers = User::where('is_vendor', true)->orderBy('id')->get();
        $shops = [
            ['category' => 'kadernictvi-a-holicstvi', 'provider' => 0, 'name' => 'Holičství U Tří křesel', 'slug' => 'holicstvi-u-tri-kresel', 'description' => 'Tradiční pánské holičství v centru Prahy. Stříhání, úprava vousů a péče bez zbytečného spěchu.', 'price_range' => 2, 'currency' => 'CZK', 'city' => 'Praha', 'state' => 'Česko', 'address' => 'Vinohradská 42, Praha 2'],
            ['category' => 'autoservis', 'provider' => 1, 'name' => 'Autoservis Rychlá dohoda', 'slug' => 'autoservis-rychla-dohoda', 'description' => 'Běžný servis, diagnostika a opravy brzd s předem domluveným rozsahem práce a cenou.', 'price_range' => 3, 'currency' => 'CZK', 'city' => 'Brno', 'state' => 'Česko', 'address' => 'Vídeňská 118, Brno'],
            ['category' => 'fitness-a-pohyb', 'provider' => 2, 'name' => 'Pohyb Studio Letná', 'slug' => 'pohyb-studio-letna', 'description' => 'Individuální tréninky a malé skupinové lekce zaměřené na zdravý pohyb a dlouhodobou kondici.', 'price_range' => 3, 'currency' => 'CZK', 'city' => 'Praha', 'state' => 'Česko', 'address' => 'Milady Horákové 76, Praha 7'],
            ['category' => 'pece-o-zvirata', 'provider' => 0, 'name' => 'Salon Čtyři tlapky', 'slug' => 'salon-ctyri-tlapky', 'description' => 'Šetrné koupání, stříhání a péče o srst psů v klidném prostředí po předchozí rezervaci.', 'price_range' => 2, 'currency' => 'CZK', 'city' => 'Plzeň', 'state' => 'Česko', 'address' => 'Klatovská 33, Plzeň'],
            ['category' => 'uklid', 'provider' => 1, 'name' => 'Čistý domov', 'slug' => 'cisty-domov', 'description' => 'Jednorázový i pravidelný úklid domácností. Rozsah práce a použité prostředky si potvrdíme před termínem.', 'price_range' => 2, 'currency' => 'EUR', 'city' => 'Bratislava', 'state' => 'Slovensko', 'address' => 'Špitálska 21, Bratislava'],
        ];

        foreach ($shops as $data) {
            $category = Category::where('slug', $data['category'])->firstOrFail();
            $provider = $providers[$data['provider'] % $providers->count()];
            unset($data['category'], $data['provider']);

            $shop = Shop::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'category_id' => $category->id,
                    'user_id' => $provider->id,
                    'timezone' => 'Europe/Prague',
                    'contact_email' => $provider->email,
                    'contact_phone' => $provider->phone,
                    'is_available' => true,
                    'is_online_only' => false,
                    'available_at' => null,
                    'rating' => 0,
                    'reviews_count' => 0,
                ],
            );

            $shop->businessHours()->delete();
            foreach ([1, 2, 3, 4, 5] as $day) {
                BusinessHour::create([
                    'shop_id' => $shop->id,
                    'day_of_week' => $day,
                    'time_from' => '09:00',
                    'time_to' => '17:00',
                ]);
            }
        }
    }
}
