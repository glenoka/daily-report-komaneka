<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Livewire\Reports\Index;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportIndexTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotelA;
    protected Hotel $hotelB;
    protected User $userA;
    protected User $userB;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotelA = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB', 'is_active' => true]);
        $this->hotelB = Hotel::create(['name' => 'Komaneka at Monkey Forest', 'code' => 'KMF', 'is_active' => true]);

        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);

        $this->userA = User::create([
            'name' => 'Staff Bisma',
            'email' => 'bisma@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotelA->id,
        ]);

        $this->userB = User::create([
            'name' => 'Staff Monkey Forest',
            'email' => 'monkeyforest@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotelB->id,
        ]);

        $this->adminUser = User::create([
            'name' => 'Corporate General Manager',
            'email' => 'gm@corporate.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'hotel_id' => null,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_reports_index(): void
    {
        $this->get(route('reports.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_reports_history_page(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Daily Reports History');
        $response->assertSee('Komaneka at Bisma');
        $response->assertSee("Create Today's Report", false);
    }

    public function test_reports_are_isolated_by_hotel_for_regular_user(): void
    {
        $action = app(CreateDailyReport::class);
        $reportA = $action->execute($this->hotelA, $this->userA, '2026-11-20');
        $reportB = $action->execute($this->hotelB, $this->userB, '2026-11-20');

        $this->actingAs($this->userA);

        Livewire::test(Index::class)
            ->assertSee($this->hotelA->name)
            ->assertDontSee($this->hotelB->name);
    }

    public function test_admin_can_view_all_properties_and_filter_by_hotel(): void
    {
        $action = app(CreateDailyReport::class);
        $reportA = $action->execute($this->hotelA, $this->userA, '2026-11-20');
        $reportB = $action->execute($this->hotelB, $this->userB, '2026-11-21');

        $this->actingAs($this->adminUser);

        // Admin sees both hotels initially
        Livewire::test(Index::class)
            ->assertSee($this->hotelA->name)
            ->assertSee($this->hotelB->name)
            // Filter by Hotel B
            ->set('hotelFilter', (string) $this->hotelB->id)
            ->assertSee($this->hotelB->name)
            ->assertDontSee('2026-11-20');
    }

    public function test_filter_by_status_draft_and_completed(): void
    {
        $action = app(CreateDailyReport::class);
        $draft = $action->execute($this->hotelA, $this->userA, '2026-11-20');

        $completed = $action->execute($this->hotelA, $this->userA, '2026-11-21');
        $completed->update(['status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($this->userA);

        // Filter: Draft only
        Livewire::test(Index::class)
            ->set('statusFilter', 'draft')
            ->assertSee('20 November 2026')
            ->assertDontSee('21 November 2026');

        // Filter: Completed only
        Livewire::test(Index::class)
            ->set('statusFilter', 'completed')
            ->assertSee('21 November 2026')
            ->assertDontSee('20 November 2026');
    }

    public function test_filter_by_date_range(): void
    {
        $action = app(CreateDailyReport::class);
        $action->execute($this->hotelA, $this->userA, '2026-11-10');
        $action->execute($this->hotelA, $this->userA, '2026-11-15');
        $action->execute($this->hotelA, $this->userA, '2026-11-20');

        $this->actingAs($this->userA);

        Livewire::test(Index::class)
            ->set('dateFrom', '2026-11-12')
            ->set('dateTo', '2026-11-18')
            ->assertSee('15 November 2026')
            ->assertDontSee('10 November 2026')
            ->assertDontSee('20 November 2026');
    }

    public function test_search_filter_matches_keyword_in_guest_experience_or_section_content(): void
    {
        $action = app(CreateDailyReport::class);
        $report1 = $action->execute($this->hotelA, $this->userA, '2026-11-20');
        $report1->update(['guest_experience' => 'VIP Ambassador praised private villa dinner']);

        $report2 = $action->execute($this->hotelA, $this->userA, '2026-11-21');
        $engSec = $report2->sections()->where('section_key', 'engineering')->first();
        ReportItem::create([
            'report_section_id' => $engSec->id,
            'content' => 'Emergency generator test load successful',
            'sort_order' => 1,
        ]);

        $this->actingAs($this->userA);

        // Search VIP note
        Livewire::test(Index::class)
            ->set('search', 'Ambassador')
            ->assertSee('20 November 2026')
            ->assertDontSee('21 November 2026');

        // Search generator task
        Livewire::test(Index::class)
            ->set('search', 'generator')
            ->assertSee('21 November 2026')
            ->assertDontSee('20 November 2026');
    }

    public function test_reset_filters_restores_default_criteria(): void
    {
        $this->actingAs($this->userA);

        Livewire::test(Index::class)
            ->set('search', 'random query')
            ->set('statusFilter', 'completed')
            ->set('dateFrom', '2026-11-01')
            ->set('dateTo', '2026-11-05')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', 'all')
            ->assertSet('dateFrom', null)
            ->assertSet('dateTo', null);
    }

    public function test_creator_can_delete_draft_report(): void
    {
        $action = app(CreateDailyReport::class);
        $draft = $action->execute($this->hotelA, $this->userA, '2026-11-20');

        $this->actingAs($this->userA);

        Livewire::test(Index::class)
            ->call('deleteReport', $draft->id)
            ->assertSee('Report draft deleted successfully.');

        $this->assertDatabaseMissing('daily_reports', ['id' => $draft->id]);
    }

    public function test_completed_report_displays_direct_download_actions(): void
    {
        $action = app(CreateDailyReport::class);
        $completed = $action->execute($this->hotelA, $this->userA, '2026-11-20');
        $completed->update(['status' => 'completed', 'completed_at' => now()]);

        $this->actingAs($this->userA);

        Livewire::test(Index::class)
            ->assertSee(route('reports.export.docx', $completed), false)
            ->assertSee(route('reports.export.pdf', $completed), false);
    }
}
