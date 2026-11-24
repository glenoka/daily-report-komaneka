<?php

namespace App\Services;

use App\Models\DailyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{
    public function __construct(
        protected ReportPreviewService $previewService
    ) {}

    /**
     * Generate the standardized filename for PDF export.
     * Format: Daily_Report_{HotelSlug}_{DD_Month_YYYY}.pdf
     * Example: Daily_Report_Komaneka_at_Bisma_24_November_2026.pdf
     */
    public function getFilename(DailyReport $report): string
    {
        $hotelName = $report->hotel?->name ?? 'Hotel';
        $cleanHotel = preg_replace('/[^\w\s]/u', '', $hotelName);
        $hotelPart = str_replace(' ', '_', trim(preg_replace('/\s+/', ' ', $cleanHotel)));
        $dateFormatted = Carbon::parse($report->report_date)->format('d_F_Y');

        return "Daily_Report_{$hotelPart}_{$dateFormatted}.pdf";
    }

    /**
     * Generate a DomPdf instance populated with the report template and data.
     */
    public function createPdf(DailyReport $report): DomPdfWrapper
    {
        $filename = $this->getFilename($report);
        $data = $this->previewService->buildPreviewData($report);

        $pdf = Pdf::loadView('reports.export-pdf', [
            'data' => $data,
            'filename' => $filename,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Helvetica',
        ]);

        return $pdf;
    }

    /**
     * Render and return the raw PDF binary string with page numbering.
     */
    public function output(DailyReport $report): string
    {
        $pdf = $this->createPdf($report);
        $this->addPageNumbers($pdf, $report);

        return $pdf->output();
    }

    /**
     * Save the generated PDF directly to a file path.
     */
    public function saveToFile(DailyReport $report, string $filePath): string
    {
        $content = $this->output($report);
        file_put_contents($filePath, $content);

        return $filePath;
    }

    /**
     * Download the PDF directly as an HTTP response attachment.
     */
    public function downloadResponse(DailyReport $report): Response
    {
        $filename = $this->getFilename($report);
        $content = $this->output($report);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($content),
        ]);
    }

    /**
     * Stream the PDF inline directly in the browser.
     */
    public function streamResponse(DailyReport $report): Response
    {
        $filename = $this->getFilename($report);
        $content = $this->output($report);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Content-Length' => strlen($content),
        ]);
    }

    /**
     * Add running footer and page numbering to the rendered PDF canvas.
     */
    protected function addPageNumbers(DomPdfWrapper $pdf, DailyReport $report): void
    {
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $font = $fontMetrics->get_font('Helvetica', 'normal');
        $size = 8.5;

        $creator = $report->creator?->name ?? 'Staff';
        $hotelName = $report->hotel?->name ?? 'Hotel';
        $footerText = "Prepared by {$creator} • {$hotelName} • Page {PAGE_NUM} of {PAGE_COUNT}";

        $width = $fontMetrics->getTextWidth($footerText, $font, $size);
        // A4 page width = 595.28 pt, height = 841.89 pt
        $x = (595.28 - $width) / 2;
        $y = 812; // ~10mm from bottom

        $canvas->page_text($x, $y, $footerText, $font, $size, [0.42, 0.45, 0.50]);
    }
}
