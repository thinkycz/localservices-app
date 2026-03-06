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
                ['name' => 'Classic Haircut',        'description' => 'Traditional haircut with clippers and scissors. Includes hot towel and scalp massage.',          'duration_minutes' => 30,  'is_popular' => true,  'category_tag' => 'Haircut',  'staff_level' => 'Barber'],
                ['name' => 'Beard Trim & Shape',     'description' => 'Professional beard trimming and shaping with razor line-up.',                                    'duration_minutes' => 20,  'is_popular' => false, 'category_tag' => 'Grooming', 'staff_level' => 'Barber'],
                ['name' => 'Hot Towel Shave',        'description' => 'Relaxing hot towel treatment with straight razor shave.',                                       'duration_minutes' => 45,  'is_popular' => true,  'category_tag' => 'Shave',    'staff_level' => 'Senior Barber'],
                ['name' => 'Hair & Beard Combo',     'description' => 'Full haircut with beard trim and styling.',                                                     'duration_minutes' => 50,  'is_popular' => false, 'category_tag' => 'Combo',    'staff_level' => 'Barber'],
                ['name' => 'Kids Haircut',           'description' => 'Gentle haircut for children under 12. Friendly barbers and fun atmosphere.',                    'duration_minutes' => 20,  'is_popular' => true,  'category_tag' => 'Haircut',  'staff_level' => 'Barber'],
            ],

            // ── Auto Repair ──────────────────────────────────────────────────────
            'quickfix-auto-garage' => [
                ['name' => 'Oil Change',             'description' => 'Full synthetic oil change with filter replacement and 21-point inspection.',                    'duration_minutes' => 30,  'is_popular' => true,  'category_tag' => 'Maintenance', 'staff_level' => 'Mechanic'],
                ['name' => 'Brake Pad Replacement',  'description' => 'Front or rear brake pad replacement with rotor inspection.',                                    'duration_minutes' => 90,  'is_popular' => true,  'category_tag' => 'Repair',      'staff_level' => 'Senior Mechanic'],
                ['name' => 'Tire Rotation & Balance', 'description' => 'Rotate all four tires and balance for even wear. Includes pressure check.',                    'duration_minutes' => 45,  'is_popular' => false, 'category_tag' => 'Maintenance', 'staff_level' => 'Mechanic'],
                ['name' => 'Engine Diagnostics',     'description' => 'Full computer diagnostic scan with detailed report of any issues found.',                       'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Diagnostics', 'staff_level' => 'Senior Mechanic'],
                ['name' => 'A/C Recharge',           'description' => 'Air conditioning system evacuation, recharge, and leak detection.',                             'duration_minutes' => 60,  'is_popular' => true,  'category_tag' => 'Repair',      'staff_level' => 'Mechanic'],
            ],

            // ── Fitness ──────────────────────────────────────────────────────────
            'iron-peak-fitness-studio' => [
                ['name' => 'Personal Training',      'description' => 'One-on-one training session tailored to your fitness goals. Includes body assessment.',         'duration_minutes' => 60,  'is_popular' => true,  'category_tag' => 'Training',  'staff_level' => 'Certified Trainer'],
                ['name' => 'Group HIIT Class',       'description' => 'High intensity interval training in a group setting. All fitness levels welcome.',              'duration_minutes' => 45,  'is_popular' => true,  'category_tag' => 'Class',     'staff_level' => 'Instructor'],
                ['name' => 'Yoga Flow Session',      'description' => 'Vinyasa-style yoga flow focusing on flexibility, strength, and mindfulness.',                   'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Yoga',      'staff_level' => 'Yoga Instructor'],
                ['name' => 'Nutrition Consultation',  'description' => 'Personalized nutrition plan based on your goals, dietary needs, and lifestyle.',                'duration_minutes' => 45,  'is_popular' => false, 'category_tag' => 'Wellness',  'staff_level' => 'Nutritionist'],
                ['name' => 'Boxing Fundamentals',    'description' => 'Learn boxing basics — footwork, combinations, and bag work. Great cardio workout.',             'duration_minutes' => 60,  'is_popular' => true,  'category_tag' => 'Training',  'staff_level' => 'Boxing Coach'],
            ],

            // ── Pet Care ─────────────────────────────────────────────────────────
            'paws-claws-pet-spa' => [
                ['name' => 'Full Grooming Package',  'description' => 'Bath, haircut, nail trim, ear cleaning, and teeth brushing. For dogs of all sizes.',            'duration_minutes' => 90,  'is_popular' => true,  'category_tag' => 'Grooming',  'staff_level' => 'Senior Groomer'],
                ['name' => 'Bath & Brush',           'description' => 'Relaxing bath with premium organic shampoo, blow dry, and thorough brushing.',                  'duration_minutes' => 45,  'is_popular' => true,  'category_tag' => 'Grooming',  'staff_level' => 'Groomer'],
                ['name' => 'Nail Trim & Filing',     'description' => 'Safe nail clipping and smooth filing. Quick and stress-free for your pet.',                     'duration_minutes' => 15,  'is_popular' => false, 'category_tag' => 'Quick Service', 'staff_level' => 'Groomer'],
                ['name' => 'Puppy First Groom',      'description' => 'Gentle introduction to grooming for puppies. Designed to build comfort and trust.',             'duration_minutes' => 45,  'is_popular' => true,  'category_tag' => 'Special',   'staff_level' => 'Senior Groomer'],
                ['name' => 'De-shedding Treatment',  'description' => 'Deep de-shedding treatment to reduce loose fur. Includes special conditioner and blow-out.',   'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Treatment', 'staff_level' => 'Groomer'],
            ],

            // ── Cleaning ─────────────────────────────────────────────────────────
            'sparkle-home-cleaning-co' => [
                ['name' => 'Standard Home Clean',    'description' => 'Full house cleaning including dusting, vacuuming, mopping, and bathroom sanitization.',         'duration_minutes' => 120, 'is_popular' => true,  'category_tag' => 'Residential', 'staff_level' => 'Cleaner'],
                ['name' => 'Deep Clean',             'description' => 'Intensive deep cleaning covering baseboards, inside appliances, windows, and more.',            'duration_minutes' => 240, 'is_popular' => true,  'category_tag' => 'Residential', 'staff_level' => 'Senior Cleaner'],
                ['name' => 'Move-In/Move-Out Clean', 'description' => 'Thorough cleaning for empty properties. Covers every surface, cabinet, and fixture.',          'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Special',     'staff_level' => 'Senior Cleaner'],
                ['name' => 'Office Cleaning',        'description' => 'Professional office and commercial space cleaning. Desks, floors, restrooms, and break rooms.', 'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Commercial',  'staff_level' => 'Cleaner'],
                ['name' => 'Post-Construction Clean', 'description' => 'Heavy-duty cleaning after renovation or construction. Dust, debris, and residue removal.',     'duration_minutes' => 300, 'is_popular' => true,  'category_tag' => 'Special',     'staff_level' => 'Senior Cleaner'],
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
