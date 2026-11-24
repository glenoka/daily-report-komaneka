<?php

namespace App\Services;

use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\ListItem;

class DocxGeneratorService
{
    public function __construct(
        protected ReportPreviewService $previewService
    ) {}

    /**
     * Generate the standardized filename for DOCX export.
     * Format: Daily_Report_{HotelSlug}_{DD_Month_YYYY}.docx
     * Example: Daily_Report_Komaneka_at_Bisma_24_November_2026.docx
     */
    public function getFilename(DailyReport $report): string
    {
        $hotelName = $report->hotel?->name ?? 'Hotel';
        $cleanHotel = preg_replace('/[^\w\s]/u', '', $hotelName);
        $hotelPart = str_replace(' ', '_', trim(preg_replace('/\s+/', ' ', $cleanHotel)));
        $dateFormatted = Carbon::parse($report->report_date)->format('d_F_Y');

        return "Daily_Report_{$hotelPart}_{$dateFormatted}.docx";
    }

    /**
     * Clean text for XML 1.0 compatibility by removing non-printable control characters.
     */
    protected function cleanText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        // Remove illegal XML 1.0 control characters (preserve tab \x09, newline \x0A, carriage return \x0D)
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
    }

    /**
     * Generate a PhpWord document object populated with the report content.
     */
    public function createPhpWord(DailyReport $report): PhpWord
    {
        // Ensure XML output escaping is enabled so characters like & < > don't corrupt the document
        Settings::setOutputEscapingEnabled(true);

        $data = $this->previewService->buildPreviewData($report);

        $phpWord = new PhpWord();

        // Document-wide defaults
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10.5);

        // Define styles
        $phpWord->addFontStyle('MainHeadingStyle', ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '0F766E']);
        $phpWord->addFontStyle('SubHeadingStyle', ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '111827']);
        $phpWord->addFontStyle('BodyStyle', ['name' => 'Arial', 'size' => 10.5, 'color' => '1F2937']);
        $phpWord->addFontStyle('MutedStyle', ['name' => 'Arial', 'size' => 10, 'italic' => true, 'color' => '6B7280']);

        // Paragraph styles
        $phpWord->addParagraphStyle('MainHeadingPara', ['spaceBefore' => 180, 'spaceAfter' => 60, 'keepNext' => true]);
        $phpWord->addParagraphStyle('SubHeadingPara', ['spaceBefore' => 120, 'spaceAfter' => 40, 'keepNext' => true]);
        $phpWord->addParagraphStyle('BulletPara', ['spaceAfter' => 40, 'lineSpacing' => 1.15]);
        $phpWord->addParagraphStyle('BodyPara', ['spaceAfter' => 60, 'lineSpacing' => 1.15]);

        // Add section with 2.5cm (1418 twips) margins
        $section = $phpWord->addSection([
            'marginTop' => 1418,
            'marginBottom' => 1418,
            'marginLeft' => 1418,
            'marginRight' => 1418,
        ]);

        // Report body begins directly without header banner or greeting
        // Ordered Sections (1 to 5)
        foreach ($data['tabs'] as $tIndex => $tab) {
            $num = $tIndex + 1;
            $mainTitle = strtoupper("{$num}. {$tab['title']}");
            $section->addText($this->cleanText($mainTitle), 'MainHeadingStyle', 'MainHeadingPara');

            foreach ($tab['sections'] as $sec) {
                // If section has subtitle (e.g. departments or multi-field tabs)
                if (count($tab['sections']) > 1 || $tab['key'] === 'departmental_activities') {
                    $section->addText($this->cleanText($sec['title']), 'SubHeadingStyle', 'SubHeadingPara');
                }

                if ($sec['field_type'] === 'text') {
                    // Paragraph rendering with line breaks
                    if (!$sec['is_empty'] && !empty($sec['content'])) {
                        $lines = explode("\n", $sec['content']);
                        foreach ($lines as $line) {
                            $cleanLine = trim($line);
                            if ($cleanLine !== '') {
                                $section->addText($this->cleanText($cleanLine), 'BodyStyle', 'BodyPara');
                            }
                        }
                    } else {
                        $section->addText('(No guest experience or concern reported)', 'MutedStyle', 'BodyPara');
                    }
                } else {
                    // Bullet list rendering (Native Word Bullets)
                    if (!$sec['is_empty'] && !empty($sec['items'])) {
                        foreach ($sec['items'] as $item) {
                            // Strip any existing bullet marker from text to avoid duplicate bullets
                            $cleanBulletText = preg_replace('/^[•●○◦▪▫‣⁃\-\*]\s*/u', '', trim($item));
                            $section->addListItem(
                                $this->cleanText($cleanBulletText),
                                0,
                                'BodyStyle',
                                ListItem::TYPE_BULLET_FILLED,
                                'BulletPara'
                            );
                        }
                    } else {
                        $section->addText('(No items reported)', 'MutedStyle', 'BodyPara');
                    }
                }
            }
        }

        // Report body ends without trailing footer
        return $phpWord;
    }

    /**
     * Save the generated DOCX to a file path.
     */
    public function saveToFile(DailyReport $report, string $filePath): string
    {
        $phpWord = $this->createPhpWord($report);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Stream or download the DOCX directly as a response.
     */
    public function downloadResponse(DailyReport $report)
    {
        $filename = $this->getFilename($report);
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'docx_' . uniqid('', true) . '.docx';
        $this->saveToFile($report, $tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ])->deleteFileAfterSend(true);
    }
}
