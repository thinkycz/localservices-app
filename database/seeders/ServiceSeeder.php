<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $servicesByShop = [
            'holicstvi-u-tri-kresel' => [
                ['name' => 'Pánský střih', 'description' => 'Konzultace, střih nůžkami nebo strojkem a závěrečný styling.', 'duration_minutes' => 45, 'price' => 690, 'is_popular' => true],
                ['name' => 'Úprava vousů', 'description' => 'Tvarování vousů, kontury a péče horkým ručníkem.', 'duration_minutes' => 30, 'price' => 490],
                ['name' => 'Střih a vousy', 'description' => 'Kompletní úprava vlasů a vousů v jednom termínu.', 'duration_minutes' => 75, 'price' => 1090],
            ],
            'autoservis-rychla-dohoda' => [
                ['name' => 'Základní diagnostika', 'description' => 'Načtení závad, základní kontrola a srozumitelné doporučení dalšího postupu.', 'duration_minutes' => 60, 'price' => 1200, 'is_popular' => true],
                ['name' => 'Výměna oleje', 'description' => 'Práce na výměně motorového oleje a filtru; materiál se domlouvá zvlášť.', 'duration_minutes' => 60, 'price' => 1500],
                ['name' => 'Kontrola brzd', 'description' => 'Kontrola destiček, kotoučů a základní zkouška brzdové soustavy.', 'duration_minutes' => 90, 'price' => 1900],
            ],
            'pohyb-studio-letna' => [
                ['name' => 'Osobní trénink', 'description' => 'Individuální lekce podle kondice, omezení a dlouhodobého cíle.', 'duration_minutes' => 60, 'price' => 1100, 'is_popular' => true],
                ['name' => 'Úvodní konzultace', 'description' => 'Rozhovor, základní pohybová diagnostika a návrh realistického plánu.', 'duration_minutes' => 45, 'price' => 750],
                ['name' => 'Malá skupinová lekce', 'description' => 'Vedený trénink v malé skupině s důrazem na správnou techniku.', 'duration_minutes' => 60, 'price' => 450],
            ],
            'salon-ctyri-tlapky' => [
                ['name' => 'Kompletní péče o psa', 'description' => 'Koupání, sušení, střih a základní péče o drápky a uši.', 'duration_minutes' => 90, 'price' => 1400, 'is_popular' => true],
                ['name' => 'Koupání a vyčesání', 'description' => 'Šetrné koupání, sušení a důkladné vyčesání srsti.', 'duration_minutes' => 60, 'price' => 900],
                ['name' => 'Stříhání drápků', 'description' => 'Klidné a bezpečné zkrácení drápků po předchozí rezervaci.', 'duration_minutes' => 30, 'price' => 250],
            ],
            'cisty-domov' => [
                ['name' => 'Pravidelný úklid', 'description' => 'Běžný úklid bytu v předem dohodnutém rozsahu.', 'duration_minutes' => 120, 'price' => 45, 'is_popular' => true],
                ['name' => 'Hloubkový úklid', 'description' => 'Důkladný jednorázový úklid kuchyně, koupelny a obytných místností.', 'duration_minutes' => 240, 'price' => 95],
                ['name' => 'Úklid před předáním', 'description' => 'Úklid prázdného bytu před stěhováním nebo předáním majiteli.', 'duration_minutes' => 180, 'price' => 75],
            ],
        ];

        foreach ($servicesByShop as $slug => $services) {
            $shop = Shop::where('slug', $slug)->firstOrFail();
            foreach ($services as $service) {
                Service::updateOrCreate(
                    ['shop_id' => $shop->id, 'name' => $service['name']],
                    [
                        ...$service,
                        'is_popular' => $service['is_popular'] ?? false,
                        'is_available' => true,
                    ],
                );
            }
        }
    }
}
