<?php

namespace App\Livewire\Reports;

use App\Actions\CompleteDailyReport;
use App\Models\DailyReport;
use App\Services\DocxGeneratorService;
use App\Services\PdfGeneratorService;
use App\Services\ReportPreviewService;
use App\Services\WhatsAppReportFormatterService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Daily Report Document Preview')]
class Preview extends Component
{
    use AuthorizesRequests;

    public DailyReport $report;
    public array $previewData = [];
    public ?string $exportError = null;
    public ?string $failedFormat = null;

    public function mount(DailyReport $report, ReportPreviewService $previewService): void
    {
        $this->report = $report;

        // Authorize viewing the report
        $this->authorize('view', $this->report);

        $this->previewData = $previewService->buildPreviewData($this->report);
    }

    /**
     * Finalize the report and mark as completed.
     */
    public function finalizeReport(CompleteDailyReport $action, ReportPreviewService $previewService): void
    {
        $this->authorize('finalize', $this->report);

        $action->execute($this->report);
        $this->report->refresh();
        $this->previewData = $previewService->buildPreviewData($this->report);

        session()->flash('success', 'Daily Report finalized successfully. You can now download the DOCX and PDF documents.');
    }

    /**
     * Reopen a completed report back to draft for revisions.
     */
    public function reopenReport(CompleteDailyReport $action, ReportPreviewService $previewService): void
    {
        $this->authorize('reopen', $this->report);

        $action->reopen($this->report);
        $this->report->refresh();
        $this->previewData = $previewService->buildPreviewData($this->report);

        session()->flash('info', 'Report status changed back to Draft for revisions.');
    }

    /**
     * Retry an export format if generation encountered an error.
     */
    public function retryExport(string $format, DocxGeneratorService $docxService, PdfGeneratorService $pdfService)
    {
        $this->exportError = null;
        $this->failedFormat = null;

        try {
            if ($format === 'docx') {
                return redirect()->route('reports.export.docx', $this->report);
            } elseif ($format === 'pdf') {
                return redirect()->route('reports.export.pdf', $this->report);
            }
        } catch (\Throwable $e) {
            $this->exportError = "Export generation failed: " . $e->getMessage();
            $this->failedFormat = $format;
        }
    }

    public function render(WhatsAppReportFormatterService $whatsAppFormatter)
    {
        return view('livewire.reports.preview', [
            'whatsAppMarkdown' => $whatsAppFormatter->format($this->report),
        ]);
    }
}
