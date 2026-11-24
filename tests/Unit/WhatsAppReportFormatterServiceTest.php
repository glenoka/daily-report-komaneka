<?php

namespace Tests\Unit;

use App\Actions\CreateDailyReport;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\User;
use App\Services\WhatsAppReportFormatterService;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppReportFormatterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected User $user;
    protected DailyReport $report;
    protected WhatsAppReportFormatterService $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB', 'is_active' => true]);
        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);

        $this->user = User::create([
            'name' => 'Ariawan',
            'email' => 'ariawan@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotel->id,
        ]);

        $this->report = app(CreateDailyReport::class)->execute($this->hotel, $this->user, '2026-11-24');
        $this->formatter = app(WhatsAppReportFormatterService::class);
    }

    public function test_formats_whatsapp_markdown_with_title_and_real_data(): void
    {
        // 1. Fill Today's Overview items
        $overviewSection = $this->report->sections()->where('section_key', 'todays_overview')->first();
        ReportItem::create(['report_section_id' => $overviewSection->id, 'content' => 'Occupancy: 20.00%', 'item_order' => 1]);
        ReportItem::create(['report_section_id' => $overviewSection->id, 'content' => 'Rooms Occupied: 15 Rooms', 'item_order' => 2]);
        ReportItem::create(['report_section_id' => $overviewSection->id, 'content' => 'Out of Order: 3 Rooms — #2310, #2321 & #3311 (Preventive Maintenance Program)', 'item_order' => 3]);

        // 2. Fill Guest Experience paragraph
        $guestSec = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $guestSec->update(['content' => "Guest in Suite 101 requested early check-in.\nGuest enjoyed pool villa amenities."]);

        // 3. Fill Engineering and Housekeeping
        $engSec = $this->report->sections()->where('section_key', 'engineering')->first();
        ReportItem::create(['report_section_id' => $engSec->id, 'content' => 'Replaced AC filter in room 2310.', 'item_order' => 1]);
        ReportItem::create(['report_section_id' => $engSec->id, 'content' => 'Checked hot water heater circulation.', 'item_order' => 2]);

        $hkSec = $this->report->sections()->where('section_key', 'housekeeping')->first();
        ReportItem::create(['report_section_id' => $hkSec->id, 'content' => 'Deep cleaning completed for Suite 202.', 'item_order' => 1]);

        $markdown = $this->formatter->format($this->report);

        // Asserts WhatsApp title
        $this->assertStringContainsString('*DAILY REPORT — 24 NOVEMBER 2026*', $markdown);

        // Asserts excluded header/footer
        $this->assertStringNotContainsString('Dear Bapak and Ibu', $markdown);
        $this->assertStringNotContainsString('We are pleased to share', $markdown);
        $this->assertStringNotContainsString('Prepared by', $markdown);

        // Asserts section formatting and ordering
        $this->assertStringContainsString('*Today\'s Overview*', $markdown);
        $this->assertStringContainsString('- Occupancy: 20.00%', $markdown);
        $this->assertStringContainsString('- Rooms Occupied: 15 Rooms', $markdown);

        $this->assertStringContainsString('*Guest Experience & Concern*', $markdown);
        $this->assertStringContainsString("Guest in Suite 101 requested early check-in.", $markdown);

        $this->assertStringContainsString('*Departmental Activities*', $markdown);
        $this->assertStringContainsString('*Engineering*', $markdown);
        $this->assertStringContainsString('- Replaced AC filter in room 2310.', $markdown);
        $this->assertStringContainsString('*Housekeeping*', $markdown);
        $this->assertStringContainsString('- Deep cleaning completed for Suite 202.', $markdown);

        // Verify section order: Overview appears before Guest Experience, which appears before Departmental Activities
        $overviewPos = strpos($markdown, "*Today's Overview*");
        $guestPos = strpos($markdown, "*Guest Experience & Concern*");
        $deptPos = strpos($markdown, "*Departmental Activities*");
        $this->assertTrue($overviewPos < $guestPos);
        $this->assertTrue($guestPos < $deptPos);
    }

    public function test_handles_empty_sections_without_crashing_or_silent_omission(): void
    {
        // Newly created report with empty sections
        $markdown = $this->formatter->format($this->report);

        $this->assertStringContainsString('*DAILY REPORT — 24 NOVEMBER 2026*', $markdown);
        $this->assertStringContainsString('*Today\'s Overview*', $markdown);
        $this->assertStringContainsString('- (No items reported)', $markdown);
        $this->assertStringContainsString('*Guest Experience & Concern*', $markdown);
        $this->assertStringContainsString('(No guest experience or concern reported)', $markdown);
        $this->assertStringContainsString('*Departmental Activities*', $markdown);
        $this->assertStringContainsString('*Engineering*', $markdown);
        $this->assertStringContainsString('*Housekeeping*', $markdown);
    }
}
