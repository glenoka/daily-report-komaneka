<?php

namespace App\Actions;

use App\Models\DailyReport;
use App\Models\ReportItem;
use App\Models\ReportSection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DuplicateDailyReport
{
    /**
     * Duplicate a report to a new date as a draft.
     */
    public function execute(DailyReport $sourceReport, User $user, ?string $newDate = null): DailyReport
    {
        $targetDate = $newDate ? Carbon::parse($newDate)->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');

        return DB::transaction(function () use ($sourceReport, $user, $targetDate) {
            $newReport = DailyReport::create([
                'hotel_id' => $sourceReport->hotel_id,
                'report_template_id' => $sourceReport->report_template_id,
                'report_date' => $targetDate,
                'created_by' => $user->id,
                'status' => 'draft',
                'guest_experience' => $sourceReport->guest_experience,
            ]);

            foreach ($sourceReport->sections()->with('items')->get() as $sourceSection) {
                $newSection = ReportSection::create([
                    'daily_report_id' => $newReport->id,
                    'tab_key' => $sourceSection->tab_key,
                    'section_key' => $sourceSection->section_key,
                    'title' => $sourceSection->title,
                    'field_type' => $sourceSection->field_type,
                    'sort_order' => $sourceSection->sort_order,
                    'content' => $sourceSection->content,
                ]);

                foreach ($sourceSection->items as $item) {
                    ReportItem::create([
                        'report_section_id' => $newSection->id,
                        'content' => $item->content,
                        'sort_order' => $item->sort_order,
                    ]);
                }
            }

            return $newReport;
        });
    }
}
