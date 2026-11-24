<?php

namespace App\Livewire\Reports;

use App\Actions\SaveReportStep;
use App\Models\DailyReport;
use App\Services\OverviewForecastFormatterService;
use App\Services\ReportTemplateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Daily Report Wizard')]
class Wizard extends Component
{
    use AuthorizesRequests;

    public DailyReport $report;
    public array $tabs = [];
    public string $currentTab = 'general_information';
    public array $formData = [];
    public array $overviewData = [];
    public array $forecastData = [];
    public ?string $lastSavedAt = null;
    public array $tabSavedStates = [];
    public string $saveStatus = 'idle'; // 'idle', 'saving', 'saved', 'error'
    public ?string $saveErrorMessage = null;

    public function mount(DailyReport $report, ReportTemplateService $templateService, ?string $tab = null): void
    {
        $this->report = $report;

        // Authorize viewing the report
        $this->authorize('view', $this->report);

        // Ensure all sections defined in template exist
        $templateService->initializeReportSections($this->report);

        // Load tabs from report template or default template
        $template = $this->report->template ?? $templateService->getDefaultTemplate($this->report->hotel);
        $this->tabs = $templateService->getTabs($template);

        // Determine current tab
        $tabKeys = array_column($this->tabs, 'key');
        if ($tab && in_array($tab, $tabKeys, true)) {
            $this->currentTab = $tab;
        } elseif (!empty($this->tabs)) {
            $this->currentTab = $this->tabs[0]['key'];
        }

        // Restore persisted content from database
        $this->loadFormData();
    }

    public function defaultOverviewData(): array
    {
        return [
            'occupancy' => null,
            'rooms_occupied' => null,
            'ooo_rooms' => '',
            'ooo_reason' => '',
            'guests_adults' => null,
            'guests_children' => null,
            'check_ins' => null,
            'check_outs' => null,
            'property_tours' => '',
            'manager_on_duty' => '',
        ];
    }

    public function defaultForecastData(): array
    {
        return [
            'projected_occupancy' => null,
            'rooms_projected' => null,
            'ooo_rooms' => '',
            'ooo_reason' => '',
            'expected_check_ins' => null,
            'expected_check_outs' => null,
        ];
    }

    /**
     * Restore form data from persisted report sections and items.
     */
    public function loadFormData(): void
    {
        $this->report->load(['sections.items']);

        $this->overviewData = $this->defaultOverviewData();
        $this->forecastData = $this->defaultForecastData();

        foreach ($this->report->sections as $section) {
            if ($section->section_key === 'todays_overview') {
                if (!empty($section->data) && is_array($section->data)) {
                    $data = $section->data;
                    if (is_array($data['ooo_rooms'] ?? null)) {
                        $data['ooo_rooms'] = implode(', ', $data['ooo_rooms']);
                    }
                    if (is_array($data['property_tours'] ?? null)) {
                        $data['property_tours'] = implode(', ', $data['property_tours']);
                    }
                    $this->overviewData = array_merge($this->defaultOverviewData(), $data);
                }
                $this->formData[$section->section_key] = $section->content ?? '';
            } elseif ($section->section_key === 'forecast_tomorrow') {
                if (!empty($section->data) && is_array($section->data)) {
                    $data = $section->data;
                    if (is_array($data['ooo_rooms'] ?? null)) {
                        $data['ooo_rooms'] = implode(', ', $data['ooo_rooms']);
                    }
                    $this->forecastData = array_merge($this->defaultForecastData(), $data);
                }
                $this->formData[$section->section_key] = $section->content ?? '';
            } elseif ($section->isText()) {
                $this->formData[$section->section_key] = $section->content ?? $this->report->guest_experience ?? '';
            } else {
                if ($section->items->isNotEmpty()) {
                    $this->formData[$section->section_key] = $section->items->pluck('content')->implode("\n");
                } else {
                    $this->formData[$section->section_key] = $section->content ?? '';
                }
            }
        }
    }

    /**
     * Livewire updated hook: triggers debounced auto-save on field edits.
     */
    public function updated($propertyName): void
    {
        if ($propertyName !== 'currentTab') {
            $this->autoSave();
        }
    }

    /**
     * Perform debounced background auto-save for current tab.
     */
    public function autoSave(): bool
    {
        $this->report->refresh();

        if (!$this->report->isDraft() || !auth()->user()?->can('update', $this->report)) {
            $this->saveStatus = 'error';
            $this->saveErrorMessage = 'This report is completed or you are not authorized to edit it.';
            return false;
        }

        $this->saveStatus = 'saving';

        try {
            $payload = $this->formData;
            if ($this->currentTab === 'general_information') {
                $overviewHasData = false;
                foreach ($this->overviewData as $v) {
                    if ($v !== null && trim((string) $v) !== '') {
                        $overviewHasData = true;
                        break;
                    }
                }
                if ($overviewHasData || empty($this->formData['todays_overview'])) {
                    $payload['todays_overview'] = $this->overviewData;
                }

                $forecastHasData = false;
                foreach ($this->forecastData as $v) {
                    if ($v !== null && trim((string) $v) !== '') {
                        $forecastHasData = true;
                        break;
                    }
                }
                if ($forecastHasData || empty($this->formData['forecast_tomorrow'])) {
                    $payload['forecast_tomorrow'] = $this->forecastData;
                }
            }

            app(SaveReportStep::class)->execute($this->report, $this->currentTab, $payload, auth()->user());

            $this->lastSavedAt = now()->format('H:i:s');
            $this->saveStatus = 'saved';
            $this->saveErrorMessage = null;
            $this->tabSavedStates[$this->currentTab] = true;

            return true;
        } catch (\Throwable $e) {
            $this->saveStatus = 'error';
            $this->saveErrorMessage = $e->getMessage() ?: 'Auto-save failed.';
            return false;
        }
    }

    /**
     * Retry auto-save after an error.
     */
    public function retrySave(): void
    {
        $this->autoSave();
    }

    /**
     * Get 0-based index of the current active tab.
     */
    public function getCurrentTabIndexProperty(): int
    {
        $tabKeys = array_column($this->tabs, 'key');
        $index = array_search($this->currentTab, $tabKeys, true);
        return $index !== false ? $index : 0;
    }

    /**
     * Whether there is a tab after current tab.
     */
    public function getHasNextTabProperty(): bool
    {
        return $this->currentTabIndex < count($this->tabs) - 1;
    }

    /**
     * Whether there is a tab before current tab.
     */
    public function getHasPreviousTabProperty(): bool
    {
        return $this->currentTabIndex > 0;
    }

    /**
     * Get the key of the next tab.
     */
    public function getNextTabKeyProperty(): ?string
    {
        if ($this->hasNextTab) {
            return $this->tabs[$this->currentTabIndex + 1]['key'] ?? null;
        }
        return null;
    }

    /**
     * Get the key of the previous tab.
     */
    public function getPreviousTabKeyProperty(): ?string
    {
        if ($this->hasPreviousTab) {
            return $this->tabs[$this->currentTabIndex - 1]['key'] ?? null;
        }
        return null;
    }

    /**
     * Computed total guests (Adults + Children).
     */
    public function getGuestsTotalProperty(): int
    {
        $adults = isset($this->overviewData['guests_adults']) && $this->overviewData['guests_adults'] !== '' ? (int) $this->overviewData['guests_adults'] : 0;
        $children = isset($this->overviewData['guests_children']) && $this->overviewData['guests_children'] !== '' ? (int) $this->overviewData['guests_children'] : 0;
        return $adults + $children;
    }

    /**
     * Computed Out of Order rooms count for Today's Overview.
     */
    public function getOooCountProperty(): int
    {
        return count(app(OverviewForecastFormatterService::class)->parseRoomList($this->overviewData['ooo_rooms'] ?? null));
    }

    /**
     * Computed Out of Order rooms count for Forecast for Tomorrow.
     */
    public function getForecastOooCountProperty(): int
    {
        return count(app(OverviewForecastFormatterService::class)->parseRoomList($this->forecastData['ooo_rooms'] ?? null));
    }

    /**
     * Live formatted lines preview for Today's Overview.
     */
    public function getLiveOverviewLinesProperty(): array
    {
        return app(OverviewForecastFormatterService::class)->formatTodaysOverview($this->overviewData);
    }

    /**
     * Live formatted lines preview for Forecast for Tomorrow.
     */
    public function getLiveForecastLinesProperty(): array
    {
        return app(OverviewForecastFormatterService::class)->formatForecastTomorrow($this->forecastData);
    }

    /**
     * Save current active tab data.
     */
    public function save(?SaveReportStep $saver = null, bool $quiet = false): void
    {
        // Authorize updating report
        $this->authorize('update', $this->report);

        $success = $this->autoSave();

        // Reload data from DB to reflect cleaned state
        if ($success) {
            $this->loadFormData();
            if (!$quiet) {
                session()->flash('success', 'Changes saved successfully.');
            }
        } else {
            if (!$quiet && $this->saveErrorMessage) {
                session()->flash('error', $this->saveErrorMessage);
            }
        }
    }

    /**
     * Save current active tab data and proceed to next tab.
     */
    public function saveAndContinue(?SaveReportStep $saver = null): void
    {
        $this->save($saver, true);

        if ($this->saveStatus !== 'error') {
            if ($this->hasNextTab) {
                $this->currentTab = $this->nextTabKey;
                session()->flash('success', 'Section saved. Navigated to next section.');
            } else {
                session()->flash('success', 'All sections saved successfully.');
            }
        }
    }

    /**
     * Navigate to the previous tab.
     */
    public function previous(): void
    {
        if ($this->hasPreviousTab) {
            $this->currentTab = $this->previousTabKey;
        }
    }

    /**
     * Safely navigate to a clicked tab (auto-saves current tab first).
     */
    public function setTab(string $tabKey, ?SaveReportStep $saver = null): void
    {
        $tabKeys = array_column($this->tabs, 'key');
        if (!in_array($tabKey, $tabKeys, true)) {
            return;
        }

        if ($tabKey === $this->currentTab) {
            return;
        }

        // Auto-save current tab before navigating away so unsaved changes are not lost
        if ($this->report->isDraft() && auth()->user()->can('update', $this->report)) {
            $this->save($saver, true);
        }

        $this->currentTab = $tabKey;
    }

    /**
     * Calculate tab status ('saved', 'in_progress', 'not_started').
     */
    public function getTabStatus(string $tabKey): string
    {
        $tabConfig = collect($this->tabs)->firstWhere('key', $tabKey);
        if (!$tabConfig) {
            return 'not_started';
        }

        $hasAnyContent = false;

        if ($tabKey === 'general_information') {
            foreach ($this->overviewData as $val) {
                if ($val !== null && trim((string) $val) !== '') {
                    $hasAnyContent = true;
                    break;
                }
            }
            if (!$hasAnyContent) {
                foreach ($this->forecastData as $val) {
                    if ($val !== null && trim((string) $val) !== '') {
                        $hasAnyContent = true;
                        break;
                    }
                }
            }
            if (!$hasAnyContent) {
                if (trim((string) ($this->formData['todays_overview'] ?? '')) !== '' || trim((string) ($this->formData['forecast_tomorrow'] ?? '')) !== '') {
                    $hasAnyContent = true;
                }
            }
        } else {
            $sectionKeys = collect($tabConfig['sections'] ?? [])->pluck('key')->all();
            foreach ($sectionKeys as $secKey) {
                $val = $this->formData[$secKey] ?? '';
                if (trim((string) $val) !== '') {
                    $hasAnyContent = true;
                    break;
                }
            }
        }

        if (!$hasAnyContent) {
            return 'not_started';
        }

        // If tab was saved in this session
        if (!empty($this->tabSavedStates[$tabKey])) {
            return 'saved';
        }

        // Check if sections in DB for this tab have content, data, or items
        $persistedSections = $this->report->sections()->where('tab_key', $tabKey)->with('items')->get();
        $dbHasContent = false;
        foreach ($persistedSections as $sec) {
            if ($sec->data !== null || $sec->content !== null || $sec->items->isNotEmpty()) {
                $dbHasContent = true;
                break;
            }
        }

        return $dbHasContent ? 'saved' : 'in_progress';
    }

    public function render()
    {
        return view('livewire.reports.wizard');
    }
}
