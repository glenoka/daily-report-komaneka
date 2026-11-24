<?php

namespace App\Livewire;

use App\Actions\CreateDailyReport;
use App\Models\DailyReport;
use App\Models\Hotel;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public int $totalThisMonth = 0;
    public int $draftsCount = 0;
    public int $completedCount = 0;

    public function mount(): void
    {
        $user = auth()->user();
        $hotelId = $user->hotel_id;

        $query = DailyReport::query();
        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }

        $this->totalThisMonth = (clone $query)->whereMonth('report_date', Carbon::today()->month)
            ->whereYear('report_date', Carbon::today()->year)
            ->count();
        $this->draftsCount = (clone $query)->where('status', 'draft')->count();
        $this->completedCount = (clone $query)->where('status', 'completed')->count();
    }

    public function createTodayReport(CreateDailyReport $action)
    {
        $user = auth()->user();
        $hotel = $user->hotel ?? Hotel::first();

        if (!$hotel) {
            session()->flash('info', 'No active hotel available.');
            return null;
        }

        $report = $action->execute($hotel, $user);

        return redirect()->route('reports.wizard', $report);
    }

    public function render()
    {
        $user = auth()->user();
        $hotelId = $user->hotel_id;

        $reportsQuery = DailyReport::with(['hotel', 'creator'])->latest('report_date');
        if ($hotelId) {
            $reportsQuery->where('hotel_id', $hotelId);
        }

        $recentReports = $reportsQuery->take(5)->get();

        return view('livewire.dashboard', [
            'recentReports' => $recentReports,
        ]);
    }
}
