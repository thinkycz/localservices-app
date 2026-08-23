<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [
            ['name' => 'Jan Novák', 'email' => 'vendor@email.com', 'phone' => '+420 777 100 100', 'is_vendor' => true],
            ['name' => 'Petra Svobodová', 'email' => 'petra@domluveno.test', 'phone' => '+420 777 200 200', 'is_vendor' => true],
            ['name' => 'Martin Dvořák', 'email' => 'martin@domluveno.test', 'phone' => '+420 777 300 300', 'is_vendor' => true],
            ['name' => 'Eva Nováková', 'email' => 'customer@email.com', 'phone' => '+420 606 111 111', 'is_vendor' => false],
            ['name' => 'Tomáš Veselý', 'email' => 'tomas@domluveno.test', 'phone' => '+420 606 222 222', 'is_vendor' => false],
            ['name' => 'Klára Procházková', 'email' => 'klara@domluveno.test', 'phone' => '+420 606 333 333', 'is_vendor' => false],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    ...$data,
                    'provider_onboarding_pending' => false,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
        }
    }
}
