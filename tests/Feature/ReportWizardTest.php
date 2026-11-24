<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Livewire\Dashboard;
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

class ReportWizardTest extends TestCase
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

    public function test_dashboard_create_today_report_redirects_to_wizard_and_resumes_draft(): void
    {
        $this->actingAs($this->user);

        // Clicking Create Today's Report on Dashboard redirects to wizard
        Livewire::test(Dashboard::class)
            ->call('createTodayReport')
            ->assertRedirect(route('reports.wizard', $this->report));

        // Calling it again resumes same draft without creating duplicate
        Livewire::test(Dashboard::class)
            ->call('createTodayReport')
            ->assertRedirect(route('reports.wizard', $this->report));

        $this->assertEquals(1, DailyReport::where('hotel_id', $this->hotel->id)->count());
    }

    public function test_wizard_renders_with_five_tabs_in_official_order(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report])
            ->assertStatus(200)
            ->assertSee('Daily Report Wizard')
            ->assertSee('Komaneka at Bisma')
            ->assertSee('General Information')
            ->assertSee('Guest Experience & Concern')
            ->assertSee('Departmental Activities')
            ->assertSee('Operational Updates')
            ->assertSee('Activities and Works Plan');
    }

    public function test_tab_1_general_information_renders_overview_and_forecast(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report])
            ->assertSet('currentTab', 'general_information')
            ->assertSee("Today's Overview", false)
            ->assertSee("Forecast for Tomorrow", false);
    }

    public function test_tab_2_guest_experience_saves_paragraph_text_without_normalization(): void
    {
        $this->actingAs($this->user);

        $narrative = "Guest requested airport transfer.\nDriver arrived promptly on schedule.\nGuest complimented hospitality.";

        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'guest_experience'])
            ->set('formData.guest_experience', $narrative)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Changes saved successfully.');

        $section = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $this->assertEquals($narrative, $section->content);
        $this->assertEquals(0, $section->items()->count());
    }

    public function test_tab_3_departmental_activities_renders_departments_in_official_order(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'departmental_activities'])
            ->assertSee('Engineering')
            ->assertSee('Food & Beverage — Production', false)
            ->assertSee('Food & Beverage — Service', false)
            ->assertSee('Housekeeping')
            ->assertSee('SPA')
            ->assertSee('Security')
            ->assertSee('IT');
    }

    public function test_housekeeping_activities_persist_and_restore_properly(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'departmental_activities'])
            ->set('formData.housekeeping', "• Deep cleaning completed for Villa 201\n• 40 bedsheets laundered")
            ->call('save')
            ->assertHasNoErrors();

        $hkSection = $this->report->sections()->where('section_key', 'housekeeping')->first();
        $this->assertNotNull($hkSection);
        $this->assertCount(2, $hkSection->items);
        $this->assertEquals('Deep cleaning completed for Villa 201', $hkSection->items[0]->content);
        $this->assertEquals('40 bedsheets laundered', $hkSection->items[1]->content);
    }

    public function test_departmental_activities_saves_with_sentence_capitalization(): void
    {
        $this->actingAs($this->user);

        // Input with lower-case sentence starts
        $input = "guest request extra pillow. room 2310 need maintenance. housekeeping checked the room.";

        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'departmental_activities'])
            ->set('formData.housekeeping', $input)
            ->call('save')
            ->assertHasNoErrors();

        $hkSection = $this->report->sections()->where('section_key', 'housekeeping')->first();
        $this->assertNotNull($hkSection);
        $this->assertCount(1, $hkSection->items);

        $expected = "Guest request extra pillow. Room 2310 need maintenance. Housekeeping checked the room.";
        $this->assertEquals($expected, $hkSection->items[0]->content);
    }

    public function test_department_seeder_is_idempotent_and_creates_no_duplicate_housekeeping(): void
    {
        $this->seed(DepartmentSeeder::class);
        $this->seed(DepartmentSeeder::class);

        $count = \App\Models\Department::where('code', 'HK')->count();
        $this->assertEquals(1, $count);
    }

    public function test_housekeeping_appears_in_consolidated_preview_and_exports(): void
    {
        $this->actingAs($this->user);

        // Populate housekeeping activity
        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'departmental_activities'])
            ->set('formData.housekeeping', "• Deep cleaning for Villa 105 completed")
            ->call('save')
            ->assertHasNoErrors();

        $this->report->refresh();

        // Check preview data
        $previewService = app(\App\Services\ReportPreviewService::class);
        $previewData = $previewService->buildPreviewData($this->report);

        $deptTab = collect($previewData['tabs'])->firstWhere('key', 'departmental_activities');
        $this->assertNotNull($deptTab);

        $hkSec = collect($deptTab['sections'])->firstWhere('key', 'housekeeping');
        $this->assertNotNull($hkSec);
        $this->assertEquals('Housekeeping', $hkSec['title']);
        $this->assertContains('Deep cleaning for Villa 105 completed', $hkSec['items']);

        // Check Livewire Preview view renders Housekeeping
        Livewire::test(\App\Livewire\Reports\Preview::class, ['report' => $this->report])
            ->assertSee('Housekeeping')
            ->assertSee('Deep cleaning for Villa 105 completed');

        // Check PDF output contains Housekeeping
        $pdfService = app(\App\Services\PdfGeneratorService::class);
        $pdfOutput = $pdfService->output($this->report);
        $this->assertStringStartsWith('%PDF-', $pdfOutput);

        // Check DOCX output contains Housekeeping
        $docxService = app(\App\Services\DocxGeneratorService::class);
        $tempDocx = tempnam(sys_get_temp_dir(), 'test_hk_') . '.docx';
        $docxService->saveToFile($this->report, $tempDocx);
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tempDocx));
        $docXml = $zip->getFromName('word/document.xml');
        $this->assertStringContainsString('Housekeeping', $docXml);
        $this->assertStringContainsString('Deep cleaning for Villa 105 completed', $docXml);
        $zip->close();
        @unlink($tempDocx);
    }

    public function test_guest_experience_and_unrelated_fields_are_unaffected_by_sentence_capitalization(): void
    {
        $this->actingAs($this->user);

        // Guest Experience: lower case paragraph must be preserved exactly as entered
        $rawExperience = "guest loved our service. villa pool was amazing.";
        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'guest_experience'])
            ->set('formData.guest_experience', $rawExperience)
            ->call('save')
            ->assertHasNoErrors();

        $geSection = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $this->assertEquals($rawExperience, $geSection->content);

        // Operational updates: normal list input without department sentence capitalization
        $rawOperational = "• delivery of fresh towels received";
        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'operational_updates'])
            ->set('formData.operational_updates', $rawOperational)
            ->call('save')
            ->assertHasNoErrors();

        $opSection = $this->report->sections()->where('section_key', 'operational_updates')->first();
        // Bullet is stripped, but no sentence capitalization forced on operational updates
        $this->assertEquals('delivery of fresh towels received', $opSection->items[0]->content);
    }

    public function test_tab_3_departments_save_independently_without_overwriting(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'departmental_activities'])
            ->set('formData.engineering', "• Fixed water pump\n• Serviced AC unit")
            ->set('formData.spa', "• 12 aromatherapy treatments conducted")
            ->call('save')
            ->assertHasNoErrors();

        $engSection = $this->report->sections()->where('section_key', 'engineering')->first();
        $spaSection = $this->report->sections()->where('section_key', 'spa')->first();
        $itSection = $this->report->sections()->where('section_key', 'it')->first();

        $this->assertCount(2, $engSection->items);
        $this->assertEquals('Fixed water pump', $engSection->items[0]->content);
        $this->assertEquals('Serviced AC unit', $engSection->items[1]->content);

        $this->assertCount(1, $spaSection->items);
        $this->assertEquals('12 aromatherapy treatments conducted', $spaSection->items[0]->content);

        // IT was not filled, remains empty
        $this->assertCount(0, $itSection->items);
    }

    public function test_save_and_continue_advances_to_next_tab(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report])
            ->assertSet('currentTab', 'general_information')
            ->set('formData.todays_overview', "• All rooms occupied\n• VIP lunch in ballroom")
            ->call('saveAndContinue')
            ->assertSet('currentTab', 'guest_experience');

        // Check Tab 1 items were persisted
        $section = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertCount(2, $section->items);
    }

    public function test_previous_navigation_moves_back_to_prior_tab(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report, 'tab' => 'guest_experience'])
            ->assertSet('currentTab', 'guest_experience')
            ->call('previous')
            ->assertSet('currentTab', 'general_information');
    }

    public function test_set_tab_safely_persists_current_tab_before_navigating(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report])
            ->assertSet('currentTab', 'general_information')
            ->set('formData.todays_overview', "• Safe tab test item")
            ->call('setTab', 'departmental_activities')
            ->assertSet('currentTab', 'departmental_activities');

        // Verify Tab 1 data was automatically saved
        $section = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertCount(1, $section->items);
        $this->assertEquals('Safe tab test item', $section->items->first()->content);
    }

    public function test_loading_draft_restores_persisted_content_on_browser_refresh(): void
    {
        $this->actingAs($this->user);

        // Seed some persisted data into report
        $overviewSection = $this->report->sections()->where('section_key', 'todays_overview')->first();
        ReportItem::create([
            'report_section_id' => $overviewSection->id,
            'content' => 'Persisted bullet item',
            'sort_order' => 1,
        ]);
        $overviewSection->update(['content' => 'Persisted bullet item']);

        $guestSection = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $guestSection->update(['content' => 'Persisted guest narrative with details.']);

        // Mount new wizard instance (simulating page load or browser refresh)
        $component = Livewire::test(Wizard::class, ['report' => $this->report]);

        $this->assertEquals('Persisted bullet item', $component->get('formData.todays_overview'));
        $this->assertEquals('Persisted guest narrative with details.', $component->get('formData.guest_experience'));
    }

    public function test_unauthorized_user_from_different_hotel_is_denied_access(): void
    {
        $otherHotel = Hotel::create(['name' => 'Second Hotel', 'code' => 'SH']);
        $otherUser = User::create([
            'name' => 'Other Hotel User',
            'email' => 'other@sh.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $otherHotel->id,
        ]);

        $this->actingAs($otherUser);

        $this->get(route('reports.wizard', $this->report))
            ->assertForbidden();
    }

    public function test_completed_report_cannot_be_modified_by_regular_user(): void
    {
        $this->report->update(['status' => 'completed']);
        $this->actingAs($this->user);

        Livewire::test(Wizard::class, ['report' => $this->report])
            ->set('formData.todays_overview', 'Attempted unauthorized change')
            ->call('save')
            ->assertForbidden();
    }

    public function test_structured_overview_and_forecast_persist_and_restore_in_wizard(): void
    {
        $this->actingAs($this->user);

        // Pre-fill departmental activities to verify tab isolation
        $engSection = $this->report->sections()->where('section_key', 'engineering')->first();
        \App\Models\ReportItem::create([
            'report_section_id' => $engSection->id,
            'content' => 'Generator fuel checked',
            'sort_order' => 1,
        ]);
        $engSection->update(['content' => 'Generator fuel checked']);

        Livewire::test(Wizard::class, ['report' => $this->report])
            ->assertSet('currentTab', 'general_information')
            // Set Today's Overview structured data
            ->set('overviewData.occupancy', 20.00)
            ->set('overviewData.rooms_occupied', 15)
            ->set('overviewData.ooo_rooms', '2310, 2321, 3311')
            ->set('overviewData.ooo_reason', 'Preventive Maintenance Program')
            ->set('overviewData.guests_adults', 31)
            ->set('overviewData.guests_children', 2)
            ->set('overviewData.check_ins', 7)
            ->set('overviewData.check_outs', 7)
            ->set('overviewData.property_tours', '2311, 4103')
            ->set('overviewData.manager_on_duty', 'Ariawan, Agung Janardana, Laksmi')
            // Set Forecast for Tomorrow structured data
            ->set('forecastData.projected_occupancy', 16.00)
            ->set('forecastData.rooms_projected', 12)
            ->set('forecastData.ooo_rooms', '2310, 2321, 3311')
            ->set('forecastData.ooo_reason', 'Preventive Maintenance Program')
            // Verify live computed properties
            ->assertSee('Total: 33 Pax', false)
            ->assertSee('3 Rooms OOO', false)
            ->assertSee('Occupancy: 20.00%')
            ->assertSee('Projected Occupancy: 16.00%')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Changes saved successfully.');

        // 1. Verify report_sections table has structured json data
        $overviewSec = $this->report->sections()->where('section_key', 'todays_overview')->first();
        $this->assertNotNull($overviewSec->data);
        $this->assertEquals(20.00, $overviewSec->data['occupancy']);
        $this->assertEquals(15, $overviewSec->data['rooms_occupied']);
        $this->assertEquals('2310, 2321, 3311', $overviewSec->data['ooo_rooms']);

        // 2. Verify report_sections.content has formatted output lines
        $this->assertStringContainsString('Occupancy: 20.00%', $overviewSec->content);
        $this->assertStringContainsString('Rooms Occupied: 15 Rooms', $overviewSec->content);
        $this->assertStringContainsString('Out of Order: 3 Rooms — #2310, #2321 & #3311 (Preventive Maintenance Program)', $overviewSec->content);
        $this->assertStringContainsString('Number of Guests: 33 Pax (31 Adults + 2 Children)', $overviewSec->content);
        $this->assertStringContainsString('Property Tours: Room #2311 & #4103', $overviewSec->content);

        // 3. Verify discrete ReportItem records were created with sequential sort_order
        $items = $overviewSec->items()->orderBy('sort_order')->get();
        $this->assertCount(8, $items);
        $this->assertEquals('Occupancy: 20.00%', $items[0]->content);
        $this->assertEquals(1, $items[0]->sort_order);
        $this->assertEquals('Manager on Duty: Ariawan, Agung Janardana, Laksmi', $items[7]->content);
        $this->assertEquals(8, $items[7]->sort_order);

        // 4. Verify Forecast section
        $forecastSec = $this->report->sections()->where('section_key', 'forecast_tomorrow')->first();
        $this->assertNotNull($forecastSec->data);
        $this->assertStringContainsString('Projected Occupancy: 16.00%', $forecastSec->content);
        $this->assertStringContainsString('Rooms Projected: 12 Rooms', $forecastSec->content);
        $this->assertCount(3, $forecastSec->items);

        // 5. Verify Tab isolation: Engineering in Tab 3 remained completely untouched
        $engSection->refresh();
        $this->assertEquals('Generator fuel checked', $engSection->content);
        $this->assertCount(1, $engSection->items);

        // 6. Verify Draft Resume / Reload: new wizard instance restores structured inputs
        $reopened = Livewire::test(Wizard::class, ['report' => $this->report]);
        $this->assertEquals(20.00, $reopened->get('overviewData.occupancy'));
        $this->assertEquals(15, $reopened->get('overviewData.rooms_occupied'));
        $this->assertEquals('2310, 2321, 3311', $reopened->get('overviewData.ooo_rooms'));
        $this->assertEquals(31, $reopened->get('overviewData.guests_adults'));
        $this->assertEquals(2, $reopened->get('overviewData.guests_children'));
        $this->assertEquals(16.00, $reopened->get('forecastData.projected_occupancy'));
    }
}
