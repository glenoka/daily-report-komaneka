<?php

namespace App\Actions;

use App\Models\DailyReport;
use App\Models\ReportItem;
use App\Models\ReportSection;
use App\Models\User;
use App\Services\Normalizer\ReportNormalizerService;
use App\Services\OverviewForecastFormatterService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class SaveReportStep
{
    public function __construct(
        protected ReportNormalizerService $normalizer,
        protected OverviewForecastFormatterService $formatter
    ) {}

    /**
     * Persist the data for a specific wizard tab in a report.
     *
     * @param DailyReport $report
     * @param string $tabKey
     * @param array<string, mixed> $data Key-value pair of section_key => raw text or structured array
     * @param User|null $user User attempting the update (optional, falls back to auth()->user())
     * @throws AuthorizationException
     */
    public function execute(DailyReport $report, string $tabKey, array $data, ?User $user = null): void
    {
        $actingUser = $user ?? auth()->user();

        // Check report authorization
        if ($actingUser && $actingUser->cannot('update', $report)) {
            throw new AuthorizationException('You are not authorized to update this report.');
        }

        // Verify report is editable (completed reports cannot be edited unless admin)
        if ($report->isCompleted() && (!$actingUser || !$actingUser->isAdmin())) {
            throw new AuthorizationException('Completed reports cannot be edited.');
        }

        DB::transaction(function () use ($report, $tabKey, $data) {
            $sections = $report->sections()->where('tab_key', $tabKey)->get();

            foreach ($sections as $section) {
                $rawContent = $data[$section->section_key] ?? null;

                if ($section->section_key === 'todays_overview' && is_array($rawContent)) {
                    // Structured Today's Overview
                    $lines = $this->formatter->formatTodaysOverview($rawContent);
                    $cleanContent = count($lines) > 0 ? implode("\n", $lines) : null;

                    $section->update([
                        'data' => $rawContent,
                        'content' => $cleanContent,
                    ]);

                    $section->items()->delete();
                    foreach ($lines as $index => $itemContent) {
                        ReportItem::create([
                            'report_section_id' => $section->id,
                            'content' => $itemContent,
                            'sort_order' => $index + 1,
                        ]);
                    }
                } elseif ($section->section_key === 'forecast_tomorrow' && is_array($rawContent)) {
                    // Structured Forecast for Tomorrow
                    $lines = $this->formatter->formatForecastTomorrow($rawContent);
                    $cleanContent = count($lines) > 0 ? implode("\n", $lines) : null;

                    $section->update([
                        'data' => $rawContent,
                        'content' => $cleanContent,
                    ]);

                    $section->items()->delete();
                    foreach ($lines as $index => $itemContent) {
                        ReportItem::create([
                            'report_section_id' => $section->id,
                            'content' => $itemContent,
                            'sort_order' => $index + 1,
                        ]);
                    }
                } elseif ($section->isText()) {
                    // Paragraph text: preserve as-is with line breaks, without list normalization
                    $cleanText = ($rawContent !== null && trim($rawContent) !== '') ? trim($rawContent) : null;
                    $section->update([
                        'content' => $cleanText,
                    ]);

                    if ($section->section_key === 'guest_experience') {
                        $report->update(['guest_experience' => $cleanText]);
                    }
                } else {
                    // Normal list field: normalize lines into discrete items
                    $items = $this->normalizer->normalizeToArray(is_string($rawContent) ? $rawContent : null);

                    // If saving departmental_activities, apply sentence capitalization
                    if ($tabKey === 'departmental_activities') {
                        $items = array_map([$this->normalizer, 'capitalizeSentences'], $items);
                    }

                    $cleanContent = count($items) > 0 ? implode("\n", $items) : null;

                    $section->update([
                        'content' => $cleanContent,
                    ]);

                    // Replace existing items for this section only
                    $section->items()->delete();

                    foreach ($items as $index => $itemContent) {
                        ReportItem::create([
                            'report_section_id' => $section->id,
                            'content' => $itemContent,
                            'sort_order' => $index + 1,
                        ]);
                    }
                }
            }

            $report->touch();
        });
    }
}
