<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Actions\SaveReportStep;
use App\Livewire\Reports\Preview;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportPreviewTest extends TestCase
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

    public function test_authorized_user_can_view_document_preview(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('reports.preview', $this->report));
        $response->assertStatus(200);
        $response->assertSee('Consolidated Document Preview');
        $response->assertSee('Komaneka at Bisma');
        $response->assertSee('24 November 2026');
        $response->assertSee('Tuesday');
    }

    public function test_preview_removes_header_and_footer_and_renders_whatsapp_markdown_preview(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Preview::class, ['report' => $this->report])
            ->assertDontSee('Dear Bapak and Ibu,')
            ->assertDontSee('We are pleased to share daily report')
            ->assertSee('WhatsApp Report / Markdown Preview')
            ->assertSee('Copy WhatsApp Report')
            ->assertSee('*DAILY REPORT — 24 NOVEMBER 2026*', false);
    }

    public function test_preview_renders_all_five_sections_in_official_template_order(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(Preview::class, ['report' => $this->report]);

        $component->assertSee('General Information')
            ->assertSee('Guest Experience')
            ->assertSee('Departmental Activities')
            ->assertSee('Operational Updates')
            ->assertSee('Activities and Works Plan');

        $tabs = $component->get('previewData.tabs');
        $this->assertCount(5, $tabs);
        $this->assertEquals('general_information', $tabs[0]['key']);
        $this->assertEquals('guest_experience', $tabs[1]['key']);
        $this->assertEquals('departmental_activities', $tabs[2]['key']);
        $this->assertEquals('operational_updates', $tabs[3]['key']);
        $this->assertEquals('activities_works_plan', $tabs[4]['key']);
    }

    public function test_preview_renders_paragraph_with_preserved_line_breaks(): void
    {
        $this->actingAs($this->user);

        $narrative = "VIP guest in Villa 1 requested special dinner setup.\nButler service arranged private poolside dining.\nGuest left positive handwritten feedback.";
        
        $guestSec = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $guestSec->update(['content' => $narrative]);

        Livewire::test(Preview::class, ['report' => $this->report])
            ->assertSee('VIP guest in Villa 1 requested special dinner setup.')
            ->assertSee('Butler service arranged private poolside dining.')
            ->assertSee('Guest left positive handwritten feedback.');
    }

    public function test_preview_renders_clean_bullets_and_departmental_activities(): void
    {
        $this->actingAs($this->user);

        $engSec = $this->report->sections()->where('section_key', 'engineering')->first();
        ReportItem::create([
            'report_section_id' => $engSec->id,
            'content' => 'Generator routine service finished',
            'sort_order' => 1,
        ]);
        ReportItem::create([
            'report_section_id' => $engSec->id,
            'content' => 'Pool chemical balance verified at 7.2 pH',
            'sort_order' => 2,
        ]);

        Livewire::test(Preview::class, ['report' => $this->report])
            ->assertSee('Engineering')
            ->assertSee('Generator routine service finished')
            ->assertSee('Pool chemical balance verified at 7.2 pH');
    }

    public function test_preview_handles_empty_sections_cleanly(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Preview::class, ['report' => $this->report])
            ->assertSee('(No items reported)')
            ->assertSee('(No guest experience or concern reported)');
    }

    public function test_preview_edit_links_target_correct_wizard_tabs(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(Preview::class, ['report' => $this->report]);
        $tabs = $component->get('previewData.tabs');

        $this->assertEquals(
            route('reports.wizard', ['report' => $this->report, 'tab' => 'general_information']),
            $tabs[0]['edit_url']
        );
        $this->assertEquals(
            route('reports.wizard', ['report' => $this->report, 'tab' => 'departmental_activities']),
            $tabs[2]['edit_url']
        );
    }

    public function test_preview_denies_cross_hotel_user(): void
    {
        $otherHotel = Hotel::create(['name' => 'Other Hotel', 'code' => 'OH']);
        $otherUser = User::create([
            'name' => 'Other Staff',
            'email' => 'other@oh.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $otherHotel->id,
        ]);

        $this->actingAs($otherUser);

        $this->get(route('reports.preview', $this->report))
            ->assertForbidden();
    }
}
