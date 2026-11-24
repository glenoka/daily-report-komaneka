<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $hotel = Hotel::firstOrCreate(
            ['code' => 'KB'],
            [
                'name' => 'Komaneka at Bisma',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@hotel.com'],
            [
                'name' => 'Admin Manager',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'hotel_id' => $hotel->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@hotel.com'],
            [
                'name' => 'Report Officer',
                'password' => Hash::make('password'),
                'role' => 'user',
                'hotel_id' => $hotel->id,
            ]
        );
    }
}
