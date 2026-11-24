<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Livewire\Reports\Index;
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

class WhatsAppMarkdownTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel1;
    protected Hotel $hotel2;
    protected User $user1;
    protected User $user2;
    protected DailyReport $report1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel1 = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB', 'is_active' => true]);
        $this->hotel2 = Hotel::create(['name' => 'Komaneka at Tanggayuda', 'code' => 'KT', 'is_active' => true]);

        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);

        $this->user1 = User::create([
            'name' => 'Staff Bisma',
            'email' => 'bisma@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotel1->id,
        ]);

        $this->user2 = User::create([
            'name' => 'Staff Tanggayuda',
            'email' => 'tanggayuda@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotel2->id,
        ]);

        $this->report1 = app(CreateDailyReport::class)->execute($this->hotel1, $this->user1, '2026-11-24');

        // Add some test items
        $overviewSection = $this->report1->sections()->where('section_key', 'todays_overview')->first();
        ReportItem::create(['report_section_id' => $overviewSection->id, 'content' => 'Occupancy: 85.00%', 'item_order' => 1]);
    }

    public function test_preview_screen_renders_whatsapp_section_and_copy_button(): void
    {
        $this->actingAs($this->user1);

        $response = $this->get(route('reports.preview', $this->report1));

        $response->assertStatus(200);
        $response->assertSee('WhatsApp Report / Markdown Preview');
        $response->assertSee('Copy WhatsApp Report');
        $response->assertSee('*DAILY REPORT — 24 NOVEMBER 2026*', false);
        $response->assertSee('- Occupancy: 85.00%', false);
        $response->assertDontSee('Dear Bapak and Ibu', false);
    }

    public function test_reports_table_index_has_whatsapp_button_and_opens_modal(): void
    {
        $this->actingAs($this->user1);

        $component = Livewire::test(Index::class);

        // Assert WhatsApp action button is rendered in the table
        $component->assertSee('WhatsApp');

        // Call openWhatsAppModal
        $component->call('openWhatsAppModal', $this->report1->id)
            ->assertSet('showWhatsAppModal', true)
            ->assertSet('selectedReportId', $this->report1->id)
            ->assertSee('*DAILY REPORT — 24 NOVEMBER 2026*', false)
            ->assertSee('- Occupancy: 85.00%', false)
            ->assertSee('Copy Report');

        // Call closeWhatsAppModal
        $component->call('closeWhatsAppModal')
            ->assertSet('showWhatsAppModal', false)
            ->assertSet('selectedReportId', null);
    }

    public function test_cross_hotel_user_cannot_open_whatsapp_modal_for_other_hotel(): void
    {
        $this->actingAs($this->user2);

        Livewire::test(Index::class)
            ->call('openWhatsAppModal', $this->report1->id)
            ->assertForbidden();
    }
}
