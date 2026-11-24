<?php

namespace Tests\Unit;

use App\Actions\CreateDailyReport;
use App\Actions\SaveReportStep;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\ReportSection;
use App\Models\User;
use App\Services\Normalizer\ReportNormalizerService;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaveReportStepTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected User $user;
    protected DailyReport $report;
    protected SaveReportStep $saver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB']);
        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);

        $this->user = User::create([
            'name' => 'Staff Member',
            'email' => 'staff@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotel->id,
        ]);

        $createAction = app(CreateDailyReport::class);
        $this->report = $createAction->execute($this->hotel, $this->user, '2026-11-24');
        $this->saver = app(SaveReportStep::class);
    }

    public function test_saves_list_section_into_ordered_report_items_with_cleaned_prefixes(): void
    {
        $rawOverview = "• Room 101 AC serviced\n- Guest requested late checkout\n1. Swimming pool chemical balanced";

        $this->saver->execute(
            $this->report,
            'general_information',
            ['todays_overview' => $rawOverview],
            $this->user
        );

        $overviewSection = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertNotNull($overviewSection);

        $items = $overviewSection->items()->orderBy('sort_order')->get();
        $this->assertCount(3, $items);
        $this->assertEquals('Room 101 AC serviced', $items[0]->content);
        $this->assertEquals(1, $items[0]->sort_order);

        $this->assertEquals('Guest requested late checkout', $items[1]->content);
        $this->assertEquals(2, $items[1]->sort_order);

        $this->assertEquals('Swimming pool chemical balanced', $items[2]->content);
        $this->assertEquals(3, $items[2]->sort_order);

        // Content on section contains clean lines joined by newline
        $this->assertStringNotContainsString('•', $overviewSection->content);
        $this->assertStringNotContainsString('1.', $overviewSection->content);
    }

    public function test_saves_paragraph_text_preserving_line_breaks_without_list_normalization(): void
    {
        $paragraph = "Guest in Villa 5 expressed high satisfaction with spa service.\n\nHowever, breakfast delivery was delayed by 10 minutes.\nDuty manager apologized and provided complimentary fruit basket.";

        $this->saver->execute(
            $this->report,
            'guest_experience',
            ['guest_experience' => $paragraph],
            $this->user
        );

        $section = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $this->assertEquals($paragraph, $section->content);
        $this->assertEquals(0, $section->items()->count());

        $this->report->refresh();
        $this->assertEquals($paragraph, $this->report->guest_experience);
    }

    public function test_saves_only_requested_tab_and_does_not_overwrite_other_tabs(): void
    {
        // 1. Save Tab 1
        $this->saver->execute(
            $this->report,
            'general_information',
            ['todays_overview' => "• Highlight 1\n• Highlight 2"],
            $this->user
        );

        // 2. Save Tab 3 (Engineering)
        $this->saver->execute(
            $this->report,
            'departmental_activities',
            ['engineering' => "• Generator checked"],
            $this->user
        );

        // 3. Save Tab 1 again with updated data
        $this->saver->execute(
            $this->report,
            'general_information',
            ['todays_overview' => "• New Highlight 1"],
            $this->user
        );

        // Verify Tab 3 is completely unaffected
        $engSection = $this->report->sections()->where('section_key', 'engineering')->first();
        $this->assertCount(1, $engSection->items);
        $this->assertEquals('Generator checked', $engSection->items->first()->content);

        // Verify Tab 1 was updated
        $overviewSection = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertCount(1, $overviewSection->items);
        $this->assertEquals('New Highlight 1', $overviewSection->items->first()->content);
    }

    public function test_denies_saving_for_user_from_different_hotel(): void
    {
        $otherHotel = Hotel::create(['name' => 'Other Resort', 'code' => 'OR']);
        $otherUser = User::create([
            'name' => 'Other Staff',
            'email' => 'other@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $otherHotel->id,
        ]);

        $this->expectException(AuthorizationException::class);

        $this->saver->execute(
            $this->report,
            'general_information',
            ['todays_overview' => "• Attempted modification"],
            $otherUser
        );
    }

    public function test_denies_saving_completed_report_for_non_admin(): void
    {
        $this->report->update(['status' => 'completed']);

        $this->expectException(AuthorizationException::class);

        $this->saver->execute(
            $this->report,
            'general_information',
            ['todays_overview' => "• Attempted modification"],
            $this->user
        );
    }

    public function test_allows_admin_to_save_completed_report(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'hotel_id' => $this->hotel->id,
        ]);

        $this->report->update(['status' => 'completed']);

        $this->saver->execute(
            $this->report,
            'general_information',
            ['todays_overview' => "• Admin update on completed report"],
            $admin
        );

        $overviewSection = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertCount(1, $overviewSection->items);
        $this->assertEquals('Admin update on completed report', $overviewSection->items->first()->content);
    }

    public function test_transaction_rolls_back_if_exception_occurs_during_save(): void
    {
        // Pre-populate with existing data
        $overviewSection = $this->report->sections()->where('section_key', 'todays_overview')->first();
        ReportItem::create([
            'report_section_id' => $overviewSection->id,
            'content' => 'Initial permanent item',
            'sort_order' => 1,
        ]);
        $overviewSection->update(['content' => 'Initial permanent item']);

        // Mock normalizer to throw an exception
        $failingNormalizer = \Mockery::mock(ReportNormalizerService::class);
        $failingNormalizer->shouldReceive('normalizeToArray')
            ->andThrow(new \RuntimeException('Database / Normalization failure'));

        $saverWithMock = new SaveReportStep($failingNormalizer, app(\App\Services\OverviewForecastFormatterService::class));

        try {
            $saverWithMock->execute(
                $this->report,
                'general_information',
                ['todays_overview' => "• New item that should fail"],
                $this->user
            );
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Database / Normalization failure', $e->getMessage());
        }

        // Verify database rolled back: initial item remains intact
        $overviewSection->refresh();
        $this->assertEquals('Initial permanent item', $overviewSection->content);
        $this->assertCount(1, $overviewSection->items);
        $this->assertEquals('Initial permanent item', $overviewSection->items->first()->content);
    }
}
