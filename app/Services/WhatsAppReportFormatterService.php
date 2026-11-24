<?php

namespace App\Services;

use App\Models\DailyReport;
use Carbon\Carbon;

class WhatsAppReportFormatterService
{
    public function __construct(
        protected ReportPreviewService $previewService
    ) {}

    /**
     * Format a DailyReport into clean, WhatsApp-friendly Markdown text.
     *
     * Rules:
     * - WhatsApp single asterisk (*text*) for bold headings/labels
     * - Hyphen bullets (- item) for list items
     * - Preserves official section and department ordering
     * - Excludes letterhead, greeting, intro, and sign-off footer
     * - Formats line breaks and empty sections cleanly
     *
     * @param DailyReport $report
     * @return string
     */
    public function format(DailyReport $report): string
    {
        $previewData = $this->previewService->buildPreviewData($report);
        $dateFormatted = Carbon::parse($report->report_date)->format('d F Y');

        $lines = [];
        $lines[] = "*DAILY REPORT — " . strtoupper($dateFormatted) . "*";

        foreach ($previewData['tabs'] as $tab) {
            $tabKey = $tab['key'];
            $sections = $tab['sections'];

            if ($tabKey === 'departmental_activities') {
                $lines[] = "";
                $lines[] = "*Departmental Activities*";

                foreach ($sections as $sec) {
                    $lines[] = "*{$sec['title']}*";

                    if (!empty($sec['items'])) {
                        foreach ($sec['items'] as $item) {
                            $clean = preg_replace('/^[•●○◦▪▫‣⁃\-\*]\s*/u', '', trim($item));
                            if ($clean !== '') {
                                $lines[] = "- {$clean}";
                            }
                        }
                    } else {
                        $lines[] = "- (No items reported)";
                    }

                    $lines[] = "";
                }

                // Remove trailing blank line if present
                if (end($lines) === "") {
                    array_pop($lines);
                }
                continue;
            }

            foreach ($sections as $sec) {
                $lines[] = "";
                $lines[] = "*{$sec['title']}*";

                if ($sec['field_type'] === 'text') {
                    $content = trim($sec['content'] ?? '');
                    if ($content !== '') {
                        $lines[] = $content;
                    } else {
                        $lines[] = "(No guest experience or concern reported)";
                    }
                } else {
                    if (!empty($sec['items'])) {
                        foreach ($sec['items'] as $item) {
                            $clean = preg_replace('/^[•●○◦▪▫‣⁃\-\*]\s*/u', '', trim($item));
                            if ($clean !== '') {
                                $lines[] = "- {$clean}";
                            }
                        }
                    } else {
                        $lines[] = "- (No items reported)";
                    }
                }
            }
        }

        return implode("\n", $lines);
    }
}
