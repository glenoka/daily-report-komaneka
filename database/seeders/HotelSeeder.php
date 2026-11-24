<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Hotel::firstOrCreate(
            ['code' => 'KB'],
            [
                'name' => 'Komaneka at Bisma',
                'is_active' => true,
            ]
        );
    }
}
