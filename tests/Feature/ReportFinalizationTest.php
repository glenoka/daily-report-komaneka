<?php

namespace Tests\Feature;

use App\Actions\CompleteDailyReport;
use App\Actions\CreateDailyReport;
use App\Livewire\Reports\Preview;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected User $user;
    protected DailyReport $report;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB']);
        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);

        $this->user = User::create([
            'name' => 'Report Officer',
            'email' => 'officer@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotel->id,
        ]);

        $this->report = app(CreateDailyReport::class)->execute($this->hotel, $this->user, '2026-11-24');
    }

    public function test_draft_report_can_be_finalized_using_action(): void
    {
        $action = app(CompleteDailyReport::class);

        $this->assertTrue($this->report->isDraft());
        $this->assertNull($this->report->completed_at);

        $action->execute($this->report, $this->user);

        $this->report->refresh();
        $this->assertTrue($this->report->isCompleted());
        $this->assertNotNull($this->report->completed_at);
    }

    public function test_draft_report_can_be_finalized_via_preview_component(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Preview::class, ['report' => $this->report])
            ->assertSee('Create Report')
            ->call('finalizeReport')
            ->assertSee('Daily Report finalized successfully')
            ->assertSee('Download DOCX')
            ->assertSee('Download PDF')
            ->assertSee('Revise Report');

        $this->report->refresh();
        $this->assertTrue($this->report->isCompleted());
    }

    public function test_finalized_report_can_be_reopened_to_draft(): void
    {
        $action = app(CompleteDailyReport::class);
        $action->execute($this->report, $this->user);

        $this->assertTrue($this->report->fresh()->isCompleted());

        $this->actingAs($this->user);

        Livewire::test(Preview::class, ['report' => $this->report])
            ->call('reopenReport')
            ->assertSee('Report status changed back to Draft');

        $this->report->refresh();
        $this->assertTrue($this->report->isDraft());
        $this->assertNull($this->report->completed_at);
    }

    public function test_unauthorized_user_cannot_finalize_report(): void
    {
        $otherHotel = Hotel::create(['name' => 'Other Hotel', 'code' => 'OH']);
        $otherUser = User::create([
            'name' => 'Other Staff',
            'email' => 'other@oh.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $otherHotel->id,
        ]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        app(CompleteDailyReport::class)->execute($this->report, $otherUser);
    }
}
