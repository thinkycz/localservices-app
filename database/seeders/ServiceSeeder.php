<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $plumbing = Category::where('slug', 'plumbing')->first();
        $electrical = Category::where('slug', 'electrical')->first();
        $hvac = Category::where('slug', 'hvac-repair')->first();
        $cleaning = Category::where('slug', 'cleaning')->first();

        $services = [
            // Plumbing
            [
                'category_id' => $plumbing->id,
                'name' => 'Precision Plumbing & Drain',
                'description' => 'Expert residential and commercial plumbing services. We specialize in leak detection, water heater installation, and drain cleaning. Our licensed team is available around the clock for emergencies.',
                'price_range' => 2,
                'badge' => 'EMERGENCY SERVICE',
                'badge_color' => 'blue',
                'is_available' => true,
                'available_at' => 'Available today at 2:00 PM',
                'rating' => 4.9,
                'reviews_count' => 1240,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '45 Water St, Suite 101, New York, NY 10004',
                'opening_hours' => 'Mon - Fri: 7:00 AM - 8:00 PM | Sat: 8:00 AM - 5:00 PM',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            [
                'category_id' => $plumbing->id,
                'name' => 'Hudson Valley Pipe Masters',
                'description' => 'Over 20 years of experience serving the tri-state area. We handle high-end fixtures and complex sewer solutions with precision and care.',
                'price_range' => 3,
                'badge' => 'CERTIFIED PRO',
                'badge_color' => 'gray',
                'is_available' => true,
                'available_at' => 'Available tomorrow, 9:00 AM',
                'rating' => 4.7,
                'reviews_count' => 856,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '210 Hudson Ave, New York, NY 10013',
                'opening_hours' => 'Mon - Sat: 8:00 AM - 6:00 PM',
                'latitude' => 40.7282,
                'longitude' => -73.7949,
            ],
            [
                'category_id' => $plumbing->id,
                'name' => 'Quick-Fix Plumbers',
                'description' => 'No job too small. Affordable rates for faucet fixes, toilet repairs, and simple installations. Eco-friendly products used on every job.',
                'price_range' => 1,
                'badge' => 'ECO-FRIENDLY',
                'badge_color' => 'green',
                'is_available' => true,
                'available_at' => 'Available today at 4:30 PM',
                'rating' => 4.5,
                'reviews_count' => 210,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '88 Broad St, New York, NY 10004',
                'opening_hours' => 'Mon - Fri: 8:00 AM - 7:00 PM | Sat - Sun: 9:00 AM - 4:00 PM',
                'latitude' => 40.6892,
                'longitude' => -74.0445,
            ],
            [
                'category_id' => $plumbing->id,
                'name' => 'NYC Pro Plumbing',
                'description' => 'Full-service plumbing for residential and commercial properties. Available 24/7 for emergencies across all five boroughs.',
                'price_range' => 3,
                'badge' => 'EMERGENCY SERVICE',
                'badge_color' => 'blue',
                'is_available' => true,
                'available_at' => 'Available today at 6:00 PM',
                'rating' => 4.6,
                'reviews_count' => 532,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '350 5th Ave, Suite 2200, New York, NY 10118',
                'opening_hours' => 'Open 24 Hours, 7 Days a Week',
                'latitude' => 40.7580,
                'longitude' => -73.9855,
            ],
            [
                'category_id' => $plumbing->id,
                'name' => 'Brooklyn Pipe & Drain',
                'description' => 'Serving Brooklyn and surrounding areas with fast, reliable plumbing services at competitive prices.',
                'price_range' => 2,
                'badge' => null,
                'badge_color' => null,
                'is_available' => false,
                'available_at' => 'Available Monday, 8:00 AM',
                'rating' => 4.3,
                'reviews_count' => 178,
                'city' => 'Brooklyn',
                'state' => 'NY',
                'address' => '123 Atlantic Ave, Brooklyn, NY 11201',
                'opening_hours' => 'Mon - Fri: 8:00 AM - 6:00 PM',
                'latitude' => 40.6501,
                'longitude' => -73.9496,
            ],
            // Electrical
            [
                'category_id' => $electrical->id,
                'name' => 'Bright Spark Electricians',
                'description' => 'Licensed electricians for panel upgrades, wiring, and smart home installations. Safety first — every job is code-compliant and inspected.',
                'price_range' => 3,
                'badge' => 'CERTIFIED PRO',
                'badge_color' => 'gray',
                'is_available' => true,
                'available_at' => 'Available today at 3:00 PM',
                'rating' => 4.8,
                'reviews_count' => 920,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '1 Penn Plaza, Suite 3600, New York, NY 10119',
                'opening_hours' => 'Mon - Fri: 7:00 AM - 7:00 PM | Sat: 8:00 AM - 4:00 PM',
                'latitude' => 40.7614,
                'longitude' => -73.9776,
            ],
            [
                'category_id' => $electrical->id,
                'name' => 'PowerUp Electric NYC',
                'description' => 'Residential and commercial electrical services. Specializing in EV charger installation and solar panel wiring for a greener tomorrow.',
                'price_range' => 2,
                'badge' => 'ECO-FRIENDLY',
                'badge_color' => 'green',
                'is_available' => true,
                'available_at' => 'Available tomorrow, 10:00 AM',
                'rating' => 4.6,
                'reviews_count' => 445,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '75 Varick St, New York, NY 10013',
                'opening_hours' => 'Mon - Fri: 8:00 AM - 6:00 PM | Sat: 9:00 AM - 3:00 PM',
                'latitude' => 40.7489,
                'longitude' => -73.9680,
            ],
            // HVAC
            [
                'category_id' => $hvac->id,
                'name' => 'CoolBreeze HVAC Solutions',
                'description' => 'Expert AC installation, repair, and maintenance. Keep your home comfortable year-round with our certified HVAC technicians.',
                'price_range' => 3,
                'badge' => 'CERTIFIED PRO',
                'badge_color' => 'gray',
                'is_available' => true,
                'available_at' => 'Available today at 1:00 PM',
                'rating' => 4.7,
                'reviews_count' => 673,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '500 7th Ave, New York, NY 10018',
                'opening_hours' => 'Mon - Fri: 7:00 AM - 8:00 PM | Sat: 8:00 AM - 5:00 PM',
                'latitude' => 40.7282,
                'longitude' => -73.7949,
            ],
            [
                'category_id' => $hvac->id,
                'name' => 'Arctic Air Systems',
                'description' => 'Fast HVAC repairs and new system installations. Serving all NYC boroughs with 24-hour emergency service for heating and cooling.',
                'price_range' => 4,
                'badge' => 'EMERGENCY SERVICE',
                'badge_color' => 'blue',
                'is_available' => true,
                'available_at' => 'Available today at 5:00 PM',
                'rating' => 4.5,
                'reviews_count' => 389,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '200 Park Ave, New York, NY 10166',
                'opening_hours' => 'Open 24 Hours, 7 Days a Week',
                'latitude' => 40.7580,
                'longitude' => -73.9855,
            ],
            // Cleaning
            [
                'category_id' => $cleaning->id,
                'name' => 'Sparkle Clean NYC',
                'description' => 'Professional home and office cleaning services. Eco-friendly products, reliable staff, and flexible scheduling to fit your lifestyle.',
                'price_range' => 2,
                'badge' => 'ECO-FRIENDLY',
                'badge_color' => 'green',
                'is_available' => true,
                'available_at' => 'Available today at 11:00 AM',
                'rating' => 4.9,
                'reviews_count' => 1580,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '30 Rockefeller Plaza, New York, NY 10112',
                'opening_hours' => 'Mon - Sat: 7:00 AM - 8:00 PM | Sun: 9:00 AM - 5:00 PM',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            [
                'category_id' => $cleaning->id,
                'name' => 'Deep Clean Pros',
                'description' => 'Move-in/move-out cleaning, deep cleaning, and regular maintenance. Satisfaction guaranteed or we come back for free.',
                'price_range' => 3,
                'badge' => 'CERTIFIED PRO',
                'badge_color' => 'gray',
                'is_available' => true,
                'available_at' => 'Available tomorrow, 8:00 AM',
                'rating' => 4.6,
                'reviews_count' => 742,
                'city' => 'New York',
                'state' => 'NY',
                'address' => '11 Madison Ave, New York, NY 10010',
                'opening_hours' => 'Mon - Fri: 7:00 AM - 7:00 PM | Sat: 8:00 AM - 4:00 PM',
                'latitude' => 40.6892,
                'longitude' => -74.0445,
            ],
        ];

        foreach ($services as $data) {
            $slug = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $slug;
            while (Service::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$counter++;
            }
            Service::create(array_merge($data, ['slug' => $slug]));
        }
    }
}
