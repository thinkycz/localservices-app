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

        // Service Provider 1 - Barber
        User::firstOrCreate(
            ['email' => 'mike@precisionplumbing.com'],
            [
                'name' => 'Mike Johnson',
                'password' => Hash::make('password'),
                'is_service_provider' => true,
                'is_admin' => false,
                'phone' => '+1 (555) 123-4567',
            ]
        );

        // Regular Customer
        User::firstOrCreate(
            ['email' => 'john.doe@email.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'is_service_provider' => false,
                'is_admin' => false,
                'phone' => '+1 (555) 111-1111',
            ]
        );
    }
}
