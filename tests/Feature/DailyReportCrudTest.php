<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Actions\DuplicateDailyReport;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB']);
        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);
    }

    public function test_create_daily_report_creates_draft_and_resumes_existing_draft(): void
    {
        $hotel = Hotel::where('code', 'KB')->first();
        $user = User::create(['name' => 'Officer', 'email' => 'officer@hotel.com', 'password' => 'secret', 'role' => 'user', 'hotel_id' => $hotel->id]);

        $action = app(CreateDailyReport::class);

        // 1. First execution: creates draft
        $report1 = $action->execute($hotel, $user, '2026-11-24');
        $this->assertInstanceOf(DailyReport::class, $report1);
        $this->assertEquals('draft', $report1->status);
        $this->assertCount(11, $report1->sections);

        // 2. Second execution for same date: resumes draft
        $report2 = $action->execute($hotel, $user, '2026-11-24');
        $this->assertEquals($report1->id, $report2->id);
    }

    public function test_duplicate_daily_report_copies_structure_and_items(): void
    {
        $hotel = Hotel::where('code', 'KB')->first();
        $user = User::create(['name' => 'Officer', 'email' => 'officer@hotel.com', 'password' => 'secret', 'role' => 'user', 'hotel_id' => $hotel->id]);

        $createAction = app(CreateDailyReport::class);
        $sourceReport = $createAction->execute($hotel, $user, '2026-11-24');

        // Add item to engineering section
        $engSection = $sourceReport->sections()->where('section_key', 'engineering')->first();
        ReportItem::create([
            'report_section_id' => $engSection->id,
            'content' => 'Generator routine maintenance completed',
            'sort_order' => 1,
        ]);

        $duplicateAction = app(DuplicateDailyReport::class);
        $duplicatedReport = $duplicateAction->execute($sourceReport, $user, '2026-11-25');

        $this->assertNotEquals($sourceReport->id, $duplicatedReport->id);
        $this->assertEquals('2026-11-25', $duplicatedReport->report_date->format('Y-m-d'));
        $this->assertCount(11, $duplicatedReport->sections);

        $duplicatedEng = $duplicatedReport->sections()->where('section_key', 'engineering')->first();
        $this->assertCount(1, $duplicatedEng->items);
        $this->assertEquals('Generator routine maintenance completed', $duplicatedEng->items->first()->content);
    }

    public function test_policy_restricts_cross_hotel_and_completed_report_modifications(): void
    {
        $hotel1 = Hotel::where('code', 'KB')->first();
        $hotel2 = Hotel::create(['name' => 'Second Resort', 'code' => 'SR']);

        $user1 = User::create(['name' => 'User1', 'email' => 'u1@hotel.com', 'password' => 'secret', 'role' => 'user', 'hotel_id' => $hotel1->id]);
        $user2 = User::create(['name' => 'User2', 'email' => 'u2@hotel.com', 'password' => 'secret', 'role' => 'user', 'hotel_id' => $hotel2->id]);
        $admin = User::create(['name' => 'Admin', 'email' => 'adm@hotel.com', 'password' => 'secret', 'role' => 'admin', 'hotel_id' => $hotel1->id]);

        $createAction = app(CreateDailyReport::class);
        $report1 = $createAction->execute($hotel1, $user1, '2026-11-24');

        // user1 can view and update their own hotel's report
        $this->assertTrue($user1->can('view', $report1));
        $this->assertTrue($user1->can('update', $report1));

        // user2 cannot view or update hotel1 report
        $this->assertFalse($user2->can('view', $report1));
        $this->assertFalse($user2->can('update', $report1));

        // admin can view and update
        $this->assertTrue($admin->can('view', $report1));
        $this->assertTrue($admin->can('update', $report1));

        // When report is completed, regular user cannot update, but admin can
        $report1->update(['status' => 'completed']);
        $this->assertFalse($user1->can('update', $report1));
        $this->assertTrue($admin->can('update', $report1));
    }
}
