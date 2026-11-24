<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Livewire\Reports\Index;
use App\Livewire\Reports\Preview;
use App\Livewire\Reports\Wizard;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\User;
use App\Services\PdfGeneratorService;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityAndValidationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel1;
    protected Hotel $hotel2;
    protected User $user1;
    protected User $user2;
    protected User $admin;
    protected DailyReport $report1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel1 = Hotel::create(['name' => 'Komaneka at Bisma', 'code' => 'KB', 'is_active' => true]);
        $this->hotel2 = Hotel::create(['name' => 'Komaneka at Tanggayuda', 'code' => 'KT', 'is_active' => true]);

        $this->seed(DepartmentSeeder::class);
        $this->seed(ReportTemplateSeeder::class);

        $this->user1 = User::create([
            'name' => 'Officer Bisma',
            'email' => 'bisma_sec@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotel1->id,
        ]);

        $this->user2 = User::create([
            'name' => 'Officer Tanggayuda',
            'email' => 'tanggayuda_sec@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'hotel_id' => $this->hotel2->id,
        ]);

        $this->admin = User::create([
            'name' => 'Corporate IT Auditor',
            'email' => 'auditor@corporate.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'hotel_id' => null,
        ]);

        $this->report1 = app(CreateDailyReport::class)->execute($this->hotel1, $this->user1, '2026-11-24');
    }

    public function test_cross_hotel_user_cannot_access_wizard(): void
    {
        $this->actingAs($this->user2);

        $this->get(route('reports.wizard', $this->report1))
            ->assertForbidden();
    }

    public function test_cross_hotel_user_cannot_access_preview(): void
    {
        $this->actingAs($this->user2);

        $this->get(route('reports.preview', $this->report1))
            ->assertForbidden();
    }

    public function test_cross_hotel_user_cannot_download_docx(): void
    {
        $this->actingAs($this->user2);

        $this->get(route('reports.export.docx', $this->report1))
            ->assertForbidden();
    }

    public function test_cross_hotel_user_cannot_download_pdf(): void
    {
        $this->actingAs($this->user2);

        $this->get(route('reports.export.pdf', $this->report1))
            ->assertForbidden();
    }

    public function test_cross_hotel_user_cannot_delete_report_via_component(): void
    {
        $this->actingAs($this->user2);

        Livewire::test(Index::class)
            ->call('deleteReport', $this->report1->id)
            ->assertForbidden();

        $this->assertDatabaseHas('daily_reports', ['id' => $this->report1->id]);
    }

    public function test_admin_has_full_audit_access_across_hotels(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('reports.wizard', $this->report1))->assertOk();
        $this->get(route('reports.preview', $this->report1))->assertOk();
        $this->get(route('reports.export.docx', $this->report1))->assertOk();
        $this->get(route('reports.export.pdf', $this->report1))->assertOk();
    }

    public function test_xss_inputs_are_sanitized_and_escaped_safely(): void
    {
        $this->actingAs($this->user1);

        $maliciousPayload = "<script>alert('xss-exploit')</script><img src=x onerror=alert(1)>";

        // Save malicious script into Guest Experience
        Livewire::test(Wizard::class, ['report' => $this->report1, 'tab' => 'guest_experience'])
            ->set('formData.guest_experience', $maliciousPayload)
            ->call('save')
            ->assertHasNoErrors();

        // Save malicious script into list item
        Livewire::test(Wizard::class, ['report' => $this->report1, 'tab' => 'departmental_activities'])
            ->set('formData.engineering', "• " . $maliciousPayload)
            ->call('save')
            ->assertHasNoErrors();

        // 1. Verify in preview view that raw script tag is escaped (not executed as raw HTML)
        $previewResponse = $this->get(route('reports.preview', $this->report1));
        $previewResponse->assertStatus(200);
        $previewResponse->assertDontSee("<script>alert('xss-exploit')</script>", false);
        $previewResponse->assertSee(e($maliciousPayload), false);

        // 2. Verify PDF generation compiles safely without error
        $pdfService = app(PdfGeneratorService::class);
        $pdfOutput = $pdfService->output($this->report1);
        $this->assertStringStartsWith('%PDF-', $pdfOutput);
    }

    public function test_sql_injection_attempts_in_search_fail_safely(): void
    {
        $this->actingAs($this->user1);

        $sqliPayload = "' OR 1=1; -- DROP TABLE daily_reports; ";

        Livewire::test(Index::class)
            ->set('search', $sqliPayload)
            ->assertHasNoErrors()
            ->assertSee('No reports found');

        // Confirm database table still intact
        $this->assertDatabaseHas('daily_reports', ['id' => $this->report1->id]);
    }
}
