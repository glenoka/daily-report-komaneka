<?php

namespace Tests\Unit;

use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\ReportSection;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_report_relations_and_cascade_deletion(): void
    {
        $hotel = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB']);
        $user = User::create(['name' => 'John', 'email' => 'john@hotel.com', 'password' => 'pass', 'role' => 'user', 'hotel_id' => $hotel->id]);
        $template = ReportTemplate::create(['name' => 'Standard', 'config' => [], 'hotel_id' => $hotel->id]);

        $report = DailyReport::create([
            'hotel_id' => $hotel->id,
            'report_template_id' => $template->id,
            'report_date' => '2026-11-24',
            'created_by' => $user->id,
            'status' => 'draft',
        ]);

        $section = ReportSection::create([
            'daily_report_id' => $report->id,
            'tab_key' => 'general_information',
            'section_key' => 'todays_overview',
            'title' => "Today's Overview",
            'field_type' => 'list',
            'sort_order' => 1,
        ]);

        $item = ReportItem::create([
            'report_section_id' => $section->id,
            'content' => 'High occupancy today',
            'sort_order' => 1,
        ]);

        $this->assertEquals('Komaneka at Bisma', $report->hotel->name);
        $this->assertEquals('John', $report->creator->name);
        $this->assertEquals('24 November 2026', $report->formatted_date);
        $this->assertEquals('Tuesday', $report->day_name);
        $this->assertCount(1, $report->sections);
        $this->assertCount(1, $section->items);

        // Test cascade deletion
        $report->delete();
        $this->assertDatabaseMissing('report_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('report_items', ['id' => $item->id]);
    }
}
