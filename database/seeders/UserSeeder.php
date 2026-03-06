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
        // Service Provider
        User::firstOrCreate(
            ['email' => 'vendor@email.com'],
            [
                'name' => 'Mike Johnson',
                'password' => Hash::make('password'),
                'is_service_provider' => true,
                'phone' => '+1 (555) 123-4567',
            ]
        );

        // Regular Customer
        User::firstOrCreate(
            ['email' => 'customer@email.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'is_service_provider' => false,
                'phone' => '+1 (555) 111-1111',
            ]
        );
    }
}
