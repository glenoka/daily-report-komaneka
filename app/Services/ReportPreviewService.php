<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\ReportTemplate;
use Carbon\Carbon;

class ReportPreviewService
{
    public function __construct(
        protected ReportTemplateService $templateService
    ) {}

    /**
     * Build a consolidated preview DTO / array structure for a daily report.
     *
     * @param DailyReport $report
     * @return array<string, mixed>
     */
    public function buildPreviewData(DailyReport $report): array
    {
        $report->loadMissing(['hotel', 'template', 'creator', 'sections.items']);

        $hotel = $report->hotel;
        $hotelName = $hotel?->name ?? 'Hotel';

        $carbonDate = Carbon::parse($report->report_date);
        $formattedDate = $carbonDate->format('d F Y');
        $dayName = $carbonDate->format('l');

        $template = $report->template ?? $this->templateService->getDefaultTemplate($hotel);
        $config = $template->config ?? [];

        $tabsConfig = $this->templateService->getTabs($template);
        $persistedSections = $report->sections->keyBy('section_key');

        $tabsData = [];
        $totalSections = 0;
        $filledSections = 0;
        $emptySections = [];

        foreach ($tabsConfig as $tab) {
            $tabKey = $tab['key'];
            $tabTitle = $tab['title'];
            $sectionsInTab = [];

            foreach ($tab['sections'] ?? [] as $secDef) {
                $totalSections++;
                $secKey = $secDef['key'];
                $secTitle = $secDef['title'];
                $fieldType = $secDef['field_type'] ?? 'list';

                $persistedSec = $persistedSections->get($secKey);
                $content = $persistedSec?->content;
                $items = $persistedSec ? $persistedSec->items->pluck('content')->all() : [];

                // If items exist in ReportItem, use them; if not, split content lines for list fields
                if ($fieldType === 'list' && empty($items) && !empty($content)) {
                    $items = array_values(array_filter(array_map('trim', explode("\n", $content))));
                }

                $hasContent = ($fieldType === 'text')
                    ? (!empty($content) && trim($content) !== '')
                    : (!empty($items));

                if ($hasContent) {
                    $filledSections++;
                } else {
                    $emptySections[] = $secTitle;
                }

                $sectionsInTab[] = [
                    'key' => $secKey,
                    'title' => $secTitle,
                    'field_type' => $fieldType,
                    'content' => $content,
                    'items' => $items,
                    'is_empty' => !$hasContent,
                    'department_code' => $secDef['department_code'] ?? null,
                ];
            }

            $tabsData[] = [
                'key' => $tabKey,
                'title' => $tabTitle,
                'sections' => $sectionsInTab,
                'edit_url' => route('reports.wizard', ['report' => $report, 'tab' => $tabKey]),
            ];
        }

        return [
            'hotel_name' => $hotelName,
            'report_date' => $formattedDate,
            'day_name' => $dayName,
            'status' => $report->status,
            'creator_name' => $report->creator?->name ?? 'Staff',
            'tabs' => $tabsData,
            'total_sections' => $totalSections,
            'filled_sections' => $filledSections,
            'empty_sections' => $emptySections,
            'is_complete' => count($emptySections) === 0,
        ];
    }
}
