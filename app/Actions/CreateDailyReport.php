<?php

namespace App\Actions;

use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\User;
use App\Services\ReportTemplateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateDailyReport
{
    public function __construct(
        protected ReportTemplateService $templateService
    ) {}

    /**
     * Create or resume today's report draft for a hotel and user.
     */
    public function execute(Hotel $hotel, User $user, ?string $reportDate = null): DailyReport
    {
        $date = $reportDate ? Carbon::parse($reportDate)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        // Check if draft already exists for hotel & date -> resume draft
        $existingDraft = DailyReport::where('hotel_id', $hotel->id)
            ->where('report_date', $date)
            ->where('status', 'draft')
            ->first();

        if ($existingDraft) {
            $this->templateService->initializeReportSections($existingDraft);
            return $existingDraft;
        }

        $template = $this->templateService->getDefaultTemplate($hotel);

        return DB::transaction(function () use ($hotel, $user, $date, $template) {
            $report = DailyReport::create([
                'hotel_id' => $hotel->id,
                'report_template_id' => $template->id,
                'report_date' => $date,
                'created_by' => $user->id,
                'status' => 'draft',
                'guest_experience' => null,
            ]);

            $this->templateService->initializeReportSections($report);

            return $report;
        });
    }
}
