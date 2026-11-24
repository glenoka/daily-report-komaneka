<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\Hotel;
use App\Models\ReportSection;
use App\Models\ReportTemplate;
use Illuminate\Support\Facades\DB;

class ReportTemplateService
{
    /**
     * Get the default report template for a hotel or global.
     */
    public function getDefaultTemplate(?Hotel $hotel = null): ReportTemplate
    {
        $hotelId = $hotel?->id;

        $template = null;
        if ($hotelId) {
            $template = ReportTemplate::where('hotel_id', $hotelId)
                ->where('is_default', true)
                ->where('is_active', true)
                ->first();
        }

        if (!$template) {
            $template = ReportTemplate::where('is_default', true)
                ->where('is_active', true)
                ->first();
        }

        if (!$template) {
            $template = ReportTemplate::first();
        }

        if (!$template) {
            throw new \RuntimeException('No active report template found. Please run seeders.');
        }

        return $template;
    }

    /**
     * Get tabs definition from template config.
     */
    public function getTabs(ReportTemplate $template): array
    {
        return $template->config['tabs'] ?? [];
    }

    /**
     * Ensure all sections defined in template exist for this daily report.
     */
    public function initializeReportSections(DailyReport $report): void
    {
        $template = $report->template ?? $this->getDefaultTemplate($report->hotel);
        $tabs = $this->getTabs($template);

        DB::transaction(function () use ($report, $tabs) {
            $globalSortOrder = 1;

            foreach ($tabs as $tab) {
                $tabKey = $tab['key'];
                $sections = $tab['sections'] ?? [];

                foreach ($sections as $sec) {
                    $sectionKey = $sec['key'];
                    $title = $sec['title'];
                    $fieldType = $sec['field_type'] ?? 'list';

                    ReportSection::firstOrCreate(
                        [
                            'daily_report_id' => $report->id,
                            'section_key' => $sectionKey,
                        ],
                        [
                            'tab_key' => $tabKey,
                            'title' => $title,
                            'field_type' => $fieldType,
                            'sort_order' => $globalSortOrder++,
                            'content' => null,
                        ]
                    );
                }
            }
        });
    }

    /**
     * Get report sections grouped by tab key.
     */
    public function getGroupedSections(DailyReport $report): array
    {
        $sections = $report->sections()->with('items')->get();
        return $sections->groupBy('tab_key')->toArray();
    }
}
