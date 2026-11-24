<?php

namespace App\Livewire\Reports;

use App\Models\DailyReport;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Daily Reports History')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?string $hotelFilter = null;

    #[Url(history: true)]
    public string $statusFilter = 'all';

    #[Url(history: true)]
    public ?string $dateFrom = null;

    #[Url(history: true)]
    public ?string $dateTo = null;

    public ?int $selectedReportId = null;
    public string $whatsAppMarkdown = '';
    public bool $showWhatsAppModal = false;

    public function openWhatsAppModal(int $reportId, \App\Services\WhatsAppReportFormatterService $formatter): void
    {
        $report = DailyReport::with(['hotel', 'template', 'creator', 'sections.items'])->findOrFail($reportId);
        $this->authorize('view', $report);
        $this->selectedReportId = $reportId;
        $this->whatsAppMarkdown = $formatter->format($report);
        $this->showWhatsAppModal = true;
    }

    public function closeWhatsAppModal(): void
    {
        $this->showWhatsAppModal = false;
        $this->selectedReportId = null;
        $this->whatsAppMarkdown = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingHotelFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'hotelFilter', 'statusFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function deleteReport(DailyReport $report): void
    {
        $this->authorize('delete', $report);

        $report->delete();

        session()->flash('success', 'Report draft deleted successfully.');
    }

    public function render()
    {
        $user = auth()->user();
        $userHotelId = $user->hotel_id;

        $query = DailyReport::with(['hotel', 'creator'])->latest('report_date');

        // Hotel isolation: non-admin can only see reports for their assigned hotel
        if (!$user->isAdmin()) {
            if ($userHotelId) {
                $query->where('hotel_id', $userHotelId);
            }
        } elseif (!empty($this->hotelFilter)) {
            $query->where('hotel_id', $this->hotelFilter);
        }

        // Status filter
        if ($this->statusFilter === 'draft') {
            $query->where('status', 'draft');
        } elseif ($this->statusFilter === 'completed') {
            $query->where('status', 'completed');
        }

        // Date range filter
        if (!empty($this->dateFrom)) {
            $query->whereDate('report_date', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('report_date', '<=', $this->dateTo);
        }

        // Keyword search
        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('report_date', 'like', $term)
                    ->orWhere('guest_experience', 'like', $term)
                    ->orWhereHas('creator', function ($userQuery) use ($term) {
                        $userQuery->where('name', 'like', $term);
                    })
                    ->orWhereHas('hotel', function ($hotelQuery) use ($term) {
                        $hotelQuery->where('name', 'like', $term);
                    })
                    ->orWhereHas('sections', function ($secQuery) use ($term) {
                        $secQuery->where('content', 'like', $term)
                            ->orWhereHas('items', function ($itemQuery) use ($term) {
                                $itemQuery->where('content', 'like', $term);
                            });
                    });
            });
        }

        $reports = $query->paginate(10);
        $hotels = $user->isAdmin() ? Hotel::where('is_active', true)->orderBy('name')->get() : collect();

        // Count metrics for quick header info
        $metricsQuery = DailyReport::query();
        if (!$user->isAdmin() && $userHotelId) {
            $metricsQuery->where('hotel_id', $userHotelId);
        }

        $totalReports = (clone $metricsQuery)->count();
        $draftsCount = (clone $metricsQuery)->where('status', 'draft')->count();
        $completedCount = (clone $metricsQuery)->where('status', 'completed')->count();

        return view('livewire.reports.index', [
            'reports' => $reports,
            'hotels' => $hotels,
            'totalReports' => $totalReports,
            'draftsCount' => $draftsCount,
            'completedCount' => $completedCount,
        ]);
    }
}
