<?php

namespace Tests\Feature;

use App\Actions\CreateDailyReport;
use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportItem;
use App\Models\User;
use App\Services\DocxGeneratorService;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\ReportTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;

class DocxExportTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected User $user;
    protected DailyReport $report;
    protected DocxGeneratorService $docxService;

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
        $this->docxService = app(DocxGeneratorService::class);
    }

    public function test_filename_matches_required_specification_format(): void
    {
        $filename = $this->docxService->getFilename($this->report);
        $this->assertEquals('Daily_Report_Komaneka_at_Bisma_24_November_2026.docx', $filename);
    }

    public function test_docx_service_creates_populated_phpword_document(): void
    {
        // Add content to engineering
        $engSec = $this->report->sections()->where('section_key', 'engineering')->first();
        ReportItem::create([
            'report_section_id' => $engSec->id,
            'content' => 'Generator routine maintenance completed',
            'sort_order' => 1,
        ]);

        // Add paragraph to guest experience
        $guestSec = $this->report->sections()->where('section_key', 'guest_experience')->first();
        $guestSec->update(['content' => "Villa 5 guest praised prompt room service.\nComplimentary fruit basket delivered."]);

        $phpWord = $this->docxService->createPhpWord($this->report);

        $this->assertInstanceOf(PhpWord::class, $phpWord);
        $sections = $phpWord->getSections();
        $this->assertNotEmpty($sections);

        // Verify margins: 1418 twips (~2.5 cm)
        $sectionStyle = $sections[0]->getStyle();
        $this->assertEquals(1418, $sectionStyle->getMarginTop());
        $this->assertEquals(1418, $sectionStyle->getMarginLeft());
        $this->assertEquals(1418, $sectionStyle->getMarginRight());
        $this->assertEquals(1418, $sectionStyle->getMarginBottom());
    }

    public function test_docx_smoke_test_saves_valid_docx_file_to_disk(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_docx_') . '.docx';

        $savedPath = $this->docxService->saveToFile($this->report, $tempFile);

        $this->assertFileExists($savedPath);
        $this->assertGreaterThan(1000, filesize($savedPath));

        // Verify valid zip/docx archive
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($savedPath));
        $this->assertNotFalse($zip->locateName('word/document.xml'));
        
        // Inspect document.xml content to verify report body is present and header/footer are removed
        $docXml = $zip->getFromName('word/document.xml');
        $this->assertStringContainsString('1. GENERAL INFORMATION', $docXml);
        $this->assertStringNotContainsString('DAILY OPERATIONAL REPORT', $docXml);
        $this->assertStringNotContainsString('Dear Bapak and Ibu,', $docXml);

        $zip->close();
        @unlink($savedPath);
    }

    public function test_authorized_user_can_download_docx(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('reports.export.docx', $this->report));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->assertHeader('Content-Disposition', "attachment; filename=Daily_Report_Komaneka_at_Bisma_24_November_2026.docx");
    }

    public function test_unauthorized_cross_hotel_user_is_denied_docx_download(): void
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

        $this->get(route('reports.export.docx', $this->report))
            ->assertForbidden();
    }
}
