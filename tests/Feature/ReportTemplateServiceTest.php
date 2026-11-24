<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\User;
use App\Services\ReportTemplateService;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $hotel = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB']);
        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);
    }

    public function test_can_retrieve_default_template_and_tabs(): void
    {
        $hotel = Hotel::where('code', 'KB')->first();
        $service = app(ReportTemplateService::class);

        $template = $service->getDefaultTemplate($hotel);
        $this->assertNotNull($template);
        $this->assertTrue($template->is_default);

        $tabs = $service->getTabs($template);
        $this->assertCount(5, $tabs);

        $tabKeys = array_column($tabs, 'key');
        $this->assertEquals([
            'general_information',
            'guest_experience',
            'departmental_activities',
            'operational_updates',
            'activities_works_plan',
        ], $tabKeys);
    }

    public function test_initialize_report_sections_populates_all_expected_sections(): void
    {
        $hotel = Hotel::where('code', 'KB')->first();
        $user = User::create(['name' => 'Staff', 'email' => 'staff@hotel.com', 'password' => 'secret', 'role' => 'user', 'hotel_id' => $hotel->id]);
        $service = app(ReportTemplateService::class);
        $template = $service->getDefaultTemplate($hotel);

        $report = DailyReport::create([
            'hotel_id' => $hotel->id,
            'report_template_id' => $template->id,
            'report_date' => '2026-11-24',
            'created_by' => $user->id,
            'status' => 'draft',
        ]);

        $service->initializeReportSections($report);

        // Sections count: Tab1 (2) + Tab2 (1) + Tab3 (6) + Tab4 (1) + Tab5 (1) = 11 sections
        $this->assertCount(11, $report->sections()->get());

        // Check specific section keys
        $sectionKeys = $report->sections()->pluck('section_key')->toArray();
        $this->assertContains('todays_overview', $sectionKeys);
        $this->assertContains('forecast_tomorrow', $sectionKeys);
        $this->assertContains('guest_experience', $sectionKeys);
        $this->assertContains('engineering', $sectionKeys);
        $this->assertContains('fb_production', $sectionKeys);
        $this->assertContains('fb_service', $sectionKeys);
        $this->assertContains('spa', $sectionKeys);
        $this->assertContains('security', $sectionKeys);
        $this->assertContains('it', $sectionKeys);
        $this->assertContains('operational_updates', $sectionKeys);
        $this->assertContains('activities_works_plan', $sectionKeys);

        // Verify field types
        $guestExp = $report->sections()->where('section_key', 'guest_experience')->first();
        $this->assertEquals('text', $guestExp->field_type);

        $it = $report->sections()->where('section_key', 'it')->first();
        $this->assertEquals('list', $it->field_type);
    }
}
