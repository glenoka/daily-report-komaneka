<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotel = Hotel::where('code', 'KB')->first();
        $hotelId = $hotel?->id;

        $departments = [
            ['name' => 'Engineering', 'code' => 'ENG', 'sort_order' => 1],
            ['name' => 'Food & Beverage — Production', 'code' => 'FBP', 'sort_order' => 2],
            ['name' => 'Food & Beverage — Service', 'code' => 'FBS', 'sort_order' => 3],
            ['name' => 'Housekeeping', 'code' => 'HK', 'sort_order' => 4],
            ['name' => 'SPA', 'code' => 'SPA', 'sort_order' => 5],
            ['name' => 'Security', 'code' => 'SEC', 'sort_order' => 6],
            ['name' => 'IT', 'code' => 'IT', 'sort_order' => 7],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['hotel_id' => $hotelId, 'code' => $dept['code']],
                [
                    'name' => $dept['name'],
                    'sort_order' => $dept['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
