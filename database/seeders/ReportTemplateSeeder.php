<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;

class ReportTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotel = Hotel::where('code', 'KB')->first();
        $hotelId = $hotel?->id;

        $templateConfig = [
            'greeting' => 'Dear Bapak and Ibu,',
            'intro' => 'We are pleased to share daily report from {hotel} on {day}, {date} as below:',
            'tabs' => [
                [
                    'key' => 'general_information',
                    'title' => 'General Information',
                    'sort_order' => 1,
                    'sections' => [
                        [
                            'key' => 'todays_overview',
                            'title' => "Today's Overview",
                            'field_type' => 'list',
                            'required' => false,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'forecast_tomorrow',
                            'title' => 'Forecast for Tomorrow',
                            'field_type' => 'list',
                            'required' => false,
                            'sort_order' => 2,
                        ],
                    ],
                ],
                [
                    'key' => 'guest_experience',
                    'title' => 'Guest Experience & Concern',
                    'sort_order' => 2,
                    'sections' => [
                        [
                            'key' => 'guest_experience',
                            'title' => 'Guest Experience & Concern',
                            'field_type' => 'text',
                            'required' => false,
                            'sort_order' => 1,
                        ],
                    ],
                ],
                [
                    'key' => 'departmental_activities',
                    'title' => 'Departmental Activities',
                    'sort_order' => 3,
                    'sections' => [
                        [
                            'key' => 'engineering',
                            'title' => 'Engineering',
                            'field_type' => 'list',
                            'department_code' => 'ENG',
                            'required' => false,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'fb_production',
                            'title' => 'Food & Beverage — Production',
                            'field_type' => 'list',
                            'department_code' => 'FBP',
                            'required' => false,
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'fb_service',
                            'title' => 'Food & Beverage — Service',
                            'field_type' => 'list',
                            'department_code' => 'FBS',
                            'required' => false,
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'housekeeping',
                            'title' => 'Housekeeping',
                            'field_type' => 'list',
                            'department_code' => 'HK',
                            'required' => false,
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'spa',
                            'title' => 'SPA',
                            'field_type' => 'list',
                            'department_code' => 'SPA',
                            'required' => false,
                            'sort_order' => 5,
                        ],
                        [
                            'key' => 'security',
                            'title' => 'Security',
                            'field_type' => 'list',
                            'department_code' => 'SEC',
                            'required' => false,
                            'sort_order' => 6,
                        ],
                        [
                            'key' => 'it',
                            'title' => 'IT',
                            'field_type' => 'list',
                            'department_code' => 'IT',
                            'required' => false,
                            'sort_order' => 7,
                        ],
                    ],
                ],
                [
                    'key' => 'operational_updates',
                    'title' => 'Operational Updates',
                    'sort_order' => 4,
                    'sections' => [
                        [
                            'key' => 'operational_updates',
                            'title' => 'Operational Updates',
                            'field_type' => 'list',
                            'required' => false,
                            'sort_order' => 1,
                        ],
                    ],
                ],
                [
                    'key' => 'activities_works_plan',
                    'title' => 'Activities and Works Plan',
                    'sort_order' => 5,
                    'sections' => [
                        [
                            'key' => 'activities_works_plan',
                            'title' => 'Activities and Works Plan',
                            'field_type' => 'list',
                            'required' => false,
                            'sort_order' => 1,
                        ],
                    ],
                ],
            ],
        ];

        ReportTemplate::updateOrCreate(
            ['name' => 'Official Hotel Daily Report Template', 'hotel_id' => $hotelId],
            [
                'config' => $templateConfig,
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}
