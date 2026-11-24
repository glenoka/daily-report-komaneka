<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\User;
use App\Services\PdfGeneratorService;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected User $user;
    protected DailyReport $report;
    protected PdfGeneratorService $pdfService;

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
        $this->pdfService = app(PdfGeneratorService::class);
    }

    public function test_filename_matches_required_specification_format(): void
    {
        $filename = $this->pdfService->getFilename($this->report);
        $this->assertEquals('Daily_Report_Komaneka_at_Bisma_24_November_2026.pdf', $filename);
    }

    public function test_pdf_service_creates_populated_dompdf_document(): void
    {
        // Add content to engineering
        $engSec = $this->report->sections()->where('section_key', 'engineering')->first();
        ReportItem::create([
            'report_section_id' => $engSec->id,
            'content' => 'Chiller pump routine inspection completed',
            'sort_order' => 1,
        ]);

        // Add paragraph to guest experience
        $guestSec = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $guestSec->update(['content' => "Suite 10 guest praised breakfast presentation.\nSpecial honeymoon setup prepared."]);

        $pdf = $this->pdfService->createPdf($this->report);

        $this->assertNotNull($pdf);
        $output = $this->pdfService->output($this->report);

        // Standard PDF header signature
        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertGreaterThan(1000, strlen($output));
    }

    public function test_pdf_smoke_test_saves_valid_pdf_file_to_disk(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_pdf_') . '.pdf';

        $savedPath = $this->pdfService->saveToFile($this->report, $tempFile);

        $this->assertFileExists($savedPath);
        $this->assertGreaterThan(1000, filesize($savedPath));

        $content = file_get_contents($savedPath);
        $this->assertStringStartsWith('%PDF-', $content);

        @unlink($savedPath);
    }

    public function test_authorized_user_can_download_pdf(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('reports.export.pdf', $this->report));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Daily_Report_Komaneka_at_Bisma_24_November_2026.pdf"');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_unauthorized_cross_hotel_user_is_denied_pdf_download(): void
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

        $this->get(route('reports.export.pdf', $this->report))
            ->assertForbidden();
    }

    public function test_pdf_handles_empty_and_filled_sections_cleanly(): void
    {
        // Add content only to some sections
        $itSec = $this->report->sections()->where('section_key', 'it')->first();
        ReportItem::create([
            'report_section_id' => $itSec->id,
            'content' => 'High speed Wi-Fi access point firmware upgraded',
            'sort_order' => 1,
        ]);

        $output = $this->pdfService->output($this->report);

        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertGreaterThan(2000, strlen($output));
    }
}
