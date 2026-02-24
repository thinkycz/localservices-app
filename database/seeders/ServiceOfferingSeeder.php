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

            // ── Plumbing ──────────────────────────────────────────────────────
            'precision-plumbing-drain' => [
                ['name' => 'Drain Cleaning',          'description' => 'Professional hydro-jet drain cleaning for kitchen, bathroom, and main lines.',          'price' => 89,   'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Drain',    'staff_level' => 'Licensed Plumber'],
                ['name' => 'Leak Detection & Repair', 'description' => 'Advanced leak detection using thermal imaging. Includes minor pipe repair.',             'price' => 149,  'duration_minutes' => 90,  'is_popular' => true,  'category_tag' => 'Repair',   'staff_level' => 'Senior Plumber'],
                ['name' => 'Water Heater Installation','description' => 'Full water heater replacement including disposal of old unit and all fittings.',        'price' => 599,  'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Install',  'staff_level' => 'Master Plumber'],
                ['name' => 'Emergency Call-Out',       'description' => '24/7 emergency plumbing response. Covers assessment and up to 1 hour of labor.',        'price' => 199,  'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Emergency','staff_level' => 'Licensed Plumber'],
            ],

            'hudson-valley-pipe-masters' => [
                ['name' => 'Sewer Line Inspection',   'description' => 'Full CCTV camera inspection of your sewer line with detailed report.',                  'price' => 249,  'duration_minutes' => 90,  'is_popular' => false, 'category_tag' => 'Inspection','staff_level' => 'Senior Plumber'],
                ['name' => 'High-End Fixture Install', 'description' => 'Installation of premium faucets, sinks, and shower systems. Parts not included.',       'price' => 299,  'duration_minutes' => 120, 'is_popular' => true,  'category_tag' => 'Install',  'staff_level' => 'Master Plumber'],
                ['name' => 'Pipe Repiping (Per Room)', 'description' => 'Full copper or PEX repiping for a single room. Includes drywall patching.',             'price' => 799,  'duration_minutes' => 300, 'is_popular' => false, 'category_tag' => 'Repair',   'staff_level' => 'Master Plumber'],
                ['name' => 'Backflow Prevention',      'description' => 'Installation and certification of backflow prevention device.',                          'price' => 349,  'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Install',  'staff_level' => 'Certified Plumber'],
            ],

            'quick-fix-plumbers' => [
                ['name' => 'Faucet Repair or Replace', 'description' => 'Fix dripping faucets or install a new one. Parts extra if needed.',                     'price' => 65,   'duration_minutes' => 45,  'is_popular' => true,  'category_tag' => 'Repair',   'staff_level' => 'Plumber'],
                ['name' => 'Toilet Repair',            'description' => 'Fix running toilets, replace flappers, fill valves, or wax rings.',                     'price' => 75,   'duration_minutes' => 45,  'is_popular' => false, 'category_tag' => 'Repair',   'staff_level' => 'Plumber'],
                ['name' => 'Garbage Disposal Install', 'description' => 'Supply and install a standard garbage disposal unit.',                                   'price' => 149,  'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Install',  'staff_level' => 'Plumber'],
                ['name' => 'Drain Snake Service',      'description' => 'Manual drain snaking for slow or clogged drains.',                                       'price' => 55,   'duration_minutes' => 30,  'is_popular' => false, 'category_tag' => 'Drain',    'staff_level' => 'Plumber'],
            ],

            'nyc-pro-plumbing' => [
                ['name' => 'Full Bathroom Rough-In',  'description' => 'Complete rough-in plumbing for new bathroom construction or remodel.',                   'price' => 1200, 'duration_minutes' => 480, 'is_popular' => false, 'category_tag' => 'Install',  'staff_level' => 'Master Plumber'],
                ['name' => 'Water Pressure Fix',      'description' => 'Diagnose and resolve low water pressure issues throughout your home.',                   'price' => 129,  'duration_minutes' => 60,  'is_popular' => true,  'category_tag' => 'Repair',   'staff_level' => 'Licensed Plumber'],
                ['name' => 'Emergency Burst Pipe',    'description' => '24/7 emergency burst pipe repair. Includes up to 2 ft of pipe replacement.',             'price' => 249,  'duration_minutes' => 90,  'is_popular' => false, 'category_tag' => 'Emergency','staff_level' => 'Senior Plumber'],
                ['name' => 'Sump Pump Installation',  'description' => 'Install a new sump pump with battery backup system.',                                    'price' => 499,  'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Install',  'staff_level' => 'Licensed Plumber'],
            ],

            'brooklyn-pipe-drain' => [
                ['name' => 'Basic Drain Cleaning',    'description' => 'Standard drain cleaning for kitchen or bathroom sink.',                                  'price' => 69,   'duration_minutes' => 45,  'is_popular' => true,  'category_tag' => 'Drain',    'staff_level' => 'Plumber'],
                ['name' => 'Toilet Installation',     'description' => 'Remove old toilet and install new one. Toilet not included.',                            'price' => 119,  'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Install',  'staff_level' => 'Plumber'],
                ['name' => 'Pipe Insulation',         'description' => 'Insulate exposed pipes to prevent freezing in winter months.',                           'price' => 99,   'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Repair',   'staff_level' => 'Plumber'],
            ],

            // ── Electrical ────────────────────────────────────────────────────
            'bright-spark-electricians' => [
                ['name' => 'Panel Upgrade (200A)',    'description' => 'Upgrade your electrical panel to 200-amp service. Includes permit filing.',              'price' => 1800, 'duration_minutes' => 480, 'is_popular' => false, 'category_tag' => 'Upgrade',  'staff_level' => 'Master Electrician'],
                ['name' => 'Outlet Installation',     'description' => 'Install up to 3 new outlets including GFCI where required.',                            'price' => 199,  'duration_minutes' => 90,  'is_popular' => true,  'category_tag' => 'Install',  'staff_level' => 'Licensed Electrician'],
                ['name' => 'Smart Home Wiring',       'description' => 'Wire and configure smart switches, dimmers, and home automation hubs.',                  'price' => 349,  'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Smart',    'staff_level' => 'Certified Electrician'],
                ['name' => 'Ceiling Fan Install',     'description' => 'Install ceiling fan with light kit. Includes wiring and mounting.',                      'price' => 149,  'duration_minutes' => 90,  'is_popular' => false, 'category_tag' => 'Install',  'staff_level' => 'Licensed Electrician'],
            ],

            'powerup-electric-nyc' => [
                ['name' => 'EV Charger Installation', 'description' => 'Level 2 EV charger installation (240V). Includes dedicated circuit and permit.',         'price' => 599,  'duration_minutes' => 240, 'is_popular' => true,  'category_tag' => 'EV',       'staff_level' => 'Certified Electrician'],
                ['name' => 'Solar Panel Wiring',      'description' => 'Electrical wiring and connection for residential solar panel systems.',                   'price' => 899,  'duration_minutes' => 360, 'is_popular' => false, 'category_tag' => 'Solar',    'staff_level' => 'Master Electrician'],
                ['name' => 'Electrical Inspection',   'description' => 'Full home electrical safety inspection with written report.',                             'price' => 149,  'duration_minutes' => 90,  'is_popular' => false, 'category_tag' => 'Inspection','staff_level' => 'Licensed Electrician'],
                ['name' => 'Breaker Replacement',     'description' => 'Replace faulty circuit breakers. Up to 3 breakers included.',                            'price' => 179,  'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Repair',   'staff_level' => 'Licensed Electrician'],
            ],

            // ── HVAC ──────────────────────────────────────────────────────────
            'coolbreeze-hvac-solutions' => [
                ['name' => 'AC Tune-Up',              'description' => 'Full AC system tune-up including coil cleaning, refrigerant check, and filter change.',  'price' => 129,  'duration_minutes' => 90,  'is_popular' => true,  'category_tag' => 'Maintenance','staff_level' => 'HVAC Technician'],
                ['name' => 'AC Installation',         'description' => 'Supply and install a new central air conditioning unit. Up to 3-ton system.',            'price' => 3500, 'duration_minutes' => 480, 'is_popular' => false, 'category_tag' => 'Install',   'staff_level' => 'Senior HVAC Tech'],
                ['name' => 'Duct Cleaning',           'description' => 'Professional duct cleaning for up to 10 vents. Improves air quality and efficiency.',    'price' => 299,  'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Maintenance','staff_level' => 'HVAC Technician'],
                ['name' => 'Thermostat Upgrade',      'description' => 'Install a smart thermostat (Nest or Ecobee). Device not included.',                      'price' => 149,  'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Upgrade',   'staff_level' => 'HVAC Technician'],
            ],

            'arctic-air-systems' => [
                ['name' => 'Emergency AC Repair',     'description' => '24/7 emergency AC repair. Covers diagnosis and up to 1 hour of labor.',                  'price' => 249,  'duration_minutes' => 60,  'is_popular' => false, 'category_tag' => 'Emergency', 'staff_level' => 'Senior HVAC Tech'],
                ['name' => 'Furnace Installation',    'description' => 'Install a new gas or electric furnace. Includes all connections and testing.',            'price' => 2800, 'duration_minutes' => 480, 'is_popular' => false, 'category_tag' => 'Install',   'staff_level' => 'Master HVAC Tech'],
                ['name' => 'Refrigerant Recharge',    'description' => 'Recharge AC refrigerant (R-410A). Includes leak check.',                                 'price' => 199,  'duration_minutes' => 60,  'is_popular' => true,  'category_tag' => 'Repair',    'staff_level' => 'HVAC Technician'],
                ['name' => 'Heat Pump Service',       'description' => 'Full heat pump inspection, cleaning, and performance test.',                              'price' => 169,  'duration_minutes' => 90,  'is_popular' => false, 'category_tag' => 'Maintenance','staff_level' => 'HVAC Technician'],
            ],

            // ── Cleaning ──────────────────────────────────────────────────────
            'sparkle-clean-nyc' => [
                ['name' => 'Standard Home Cleaning',  'description' => 'Full home clean including all rooms, bathrooms, and kitchen. Up to 3 bedrooms.',         'price' => 149,  'duration_minutes' => 180, 'is_popular' => true,  'category_tag' => 'Home',     'staff_level' => 'Cleaning Pro'],
                ['name' => 'Deep Clean Package',      'description' => 'Intensive deep clean including inside appliances, baseboards, and window sills.',         'price' => 249,  'duration_minutes' => 300, 'is_popular' => false, 'category_tag' => 'Deep',     'staff_level' => 'Senior Cleaner'],
                ['name' => 'Office Cleaning',         'description' => 'Professional office cleaning for spaces up to 1,000 sq ft.',                             'price' => 199,  'duration_minutes' => 180, 'is_popular' => false, 'category_tag' => 'Office',   'staff_level' => 'Cleaning Pro'],
                ['name' => 'Post-Construction Clean', 'description' => 'Remove construction dust, debris, and residue. Includes window cleaning.',               'price' => 399,  'duration_minutes' => 360, 'is_popular' => false, 'category_tag' => 'Specialty','staff_level' => 'Senior Cleaner'],
            ],

            'deep-clean-pros' => [
                ['name' => 'Move-In Cleaning',        'description' => 'Thorough clean of your new home before moving in. Every surface scrubbed.',              'price' => 299,  'duration_minutes' => 300, 'is_popular' => true,  'category_tag' => 'Move',     'staff_level' => 'Senior Cleaner'],
                ['name' => 'Move-Out Cleaning',       'description' => 'Leave your old place spotless. Includes inside cabinets, oven, and fridge.',             'price' => 279,  'duration_minutes' => 300, 'is_popular' => false, 'category_tag' => 'Move',     'staff_level' => 'Senior Cleaner'],
                ['name' => 'Carpet Shampooing',       'description' => 'Deep carpet shampoo and extraction for up to 3 rooms.',                                  'price' => 179,  'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Specialty','staff_level' => 'Cleaning Pro'],
                ['name' => 'Regular Maintenance',     'description' => 'Weekly or bi-weekly home maintenance cleaning. Quoted per visit.',                       'price' => 119,  'duration_minutes' => 120, 'is_popular' => false, 'category_tag' => 'Home',     'staff_level' => 'Cleaning Pro'],
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
