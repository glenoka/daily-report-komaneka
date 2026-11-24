<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Services\DocxGeneratorService;
use App\Services\PdfGeneratorService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportExportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Download the report as a Word (.docx) document.
     */
    public function docx(DailyReport $report, DocxGeneratorService $docxService)
    {
        $this->authorize('view', $report);

        return $docxService->downloadResponse($report);
    }

    /**
     * Download the report as a PDF document.
     */
    public function pdf(DailyReport $report, PdfGeneratorService $pdfService)
    {
        $this->authorize('view', $report);

        return $pdfService->downloadResponse($report);
    }
}
