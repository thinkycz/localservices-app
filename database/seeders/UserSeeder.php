<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@localservices.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_service_provider' => false,
                'is_admin' => true,
                'phone' => '+1 (555) 000-0000',
            ]
        );

        // Service Provider 1 - Plumbing
        $plumber = User::firstOrCreate(
            ['email' => 'mike@precisionplumbing.com'],
            [
                'name' => 'Mike Johnson',
                'password' => Hash::make('password'),
                'is_service_provider' => true,
                'is_admin' => false,
                'phone' => '+1 (555) 123-4567',
            ]
        );

        // Service Provider 2 - Electrical
        $electrician = User::firstOrCreate(
            ['email' => 'sarah@brightsparkelectric.com'],
            [
                'name' => 'Sarah Williams',
                'password' => Hash::make('password'),
                'is_service_provider' => true,
                'is_admin' => false,
                'phone' => '+1 (555) 234-5678',
            ]
        );

        // Service Provider 3 - HVAC
        $hvac = User::firstOrCreate(
            ['email' => 'david@coolbreezehvac.com'],
            [
                'name' => 'David Chen',
                'password' => Hash::make('password'),
                'is_service_provider' => true,
                'is_admin' => false,
                'phone' => '+1 (555) 345-6789',
            ]
        );

        // Service Provider 4 - Cleaning
        $cleaner = User::firstOrCreate(
            ['email' => 'maria@sparkleclean.com'],
            [
                'name' => 'Maria Garcia',
                'password' => Hash::make('password'),
                'is_service_provider' => true,
                'is_admin' => false,
                'phone' => '+1 (555) 456-7890',
            ]
        );

        // Regular Customers
        $customers = [
            [
                'email' => 'john.doe@email.com',
                'name' => 'John Doe',
                'phone' => '+1 (555) 111-1111',
            ],
            [
                'email' => 'jane.smith@email.com',
                'name' => 'Jane Smith',
                'phone' => '+1 (555) 222-2222',
            ],
            [
                'email' => 'robert.brown@email.com',
                'name' => 'Robert Brown',
                'phone' => '+1 (555) 333-3333',
            ],
            [
                'email' => 'emily.davis@email.com',
                'name' => 'Emily Davis',
                'phone' => '+1 (555) 444-4444',
            ],
            [
                'email' => 'michael.wilson@email.com',
                'name' => 'Michael Wilson',
                'phone' => '+1 (555) 555-5555',
            ],
            [
                'email' => 'lisa.martinez@email.com',
                'name' => 'Lisa Martinez',
                'phone' => '+1 (555) 666-6666',
            ],
            [
                'email' => 'james.taylor@email.com',
                'name' => 'James Taylor',
                'phone' => '+1 (555) 777-7777',
            ],
            [
                'email' => 'amanda.anderson@email.com',
                'name' => 'Amanda Anderson',
                'phone' => '+1 (555) 888-8888',
            ],
        ];

        foreach ($customers as $customer) {
            User::firstOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => Hash::make('password'),
                    'is_service_provider' => false,
                    'is_admin' => false,
                    'phone' => $customer['phone'],
                ]
            );
        }
    }
}
