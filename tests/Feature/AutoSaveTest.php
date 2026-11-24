<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Livewire\Reports\Wizard;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AutoSaveTest extends TestCase
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

    public function test_auto_save_triggers_on_field_update_and_persists_to_database(): void
    {
        $this->actingAs($this->user);

        // Edit Tab 1 structured field
        Livewire::test(Wizard::class, ['report' => $this->report])
            ->assertSet('saveStatus', 'idle')
            ->set('overviewData.occupancy', 25.50)
            ->assertSet('saveStatus', 'saved')
            ->assertSee('Auto-saved at', false);

        // Verify database has persisted the auto-saved data immediately
        $sec = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertNotNull($sec->data);
        $this->assertEquals(25.50, $sec->data['occupancy']);
        $this->assertStringContainsString('Occupancy: 25.50%', $sec->content);
    }

    public function test_auto_save_triggers_on_list_field_update(): void
    {
        $this->actingAs($this->user);

        // Navigate to departmental activities and update engineering
        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'departmental_activities'])
            ->set('formData.engineering', "• Generator tested under load\n• Fuel level 95%")
            ->assertSet('saveStatus', 'saved');

        // Check that report items were persisted in background
        $engSec = $this->report->sections()->where('section_key', 'engineering')->first();
        $this->assertCount(2, $engSec->items);
        $this->assertEquals('Generator tested under load', $engSec->items[0]->content);
    }

    public function test_auto_save_error_state_and_retry_mechanism(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(Wizard::class, ['report' => $this->report]);

        // Complete report behind the scenes to trigger an authorization/editable error
        $this->report->update(['status' => 'completed']);

        // Attempting to auto-save should transition to error status
        $component->set('overviewData.occupancy', 50.00)
            ->assertSet('saveStatus', 'error')
            ->assertSee('Save failed', false);

        // Now revert report back to draft and call retrySave
        $this->report->update(['status' => 'draft']);
        $component->call('retrySave')
            ->assertSet('saveStatus', 'saved')
            ->assertSee('Auto-saved at', false);

        $sec = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertEquals(50.00, $sec->data['occupancy']);
    }

    public function test_restore_after_refresh_retains_auto_saved_data(): void
    {
        $this->actingAs($this->user);

        // Component 1 auto-saves changes
        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'guest_experience'])
            ->set('formData.guest_experience', 'Guest praised the villa garden landscaping.')
            ->assertSet('saveStatus', 'saved');

        // Component 2 mounts as a fresh instance (simulating browser reload / refresh)
        $refreshed = Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'guest_experience']);
        $this->assertEquals(
            'Guest praised the villa garden landscaping.',
            $refreshed->get('formData.guest_experience')
        );
    }

    public function test_tab_isolation_during_auto_save(): void
    {
        $this->actingAs($this->user);

        // Pre-seed SPA section in Tab 3
        $spaSection = $this->report->sections()->where('section_key', 'spa')->first();
        ReportItem::create([
            'report_section_id' => $spaSection->id,
            'content' => '10 massage packages booked',
            'sort_order' => 1,
        ]);
        $spaSection->update(['content' => '10 massage packages booked']);

        // Auto-save in Tab 1
        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'general_information'])
            ->set('overviewData.rooms_occupied', 22)
            ->assertSet('saveStatus', 'saved');

        // Verify Tab 3 is completely untouched
        $spaSection->refresh();
        $this->assertEquals('10 massage packages booked', $spaSection->content);
        $this->assertCount(1, $spaSection->items);
    }
}
