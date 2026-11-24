<?php

namespace App\Livewire;

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

    public function mount()
    {
        // Initial dashboard metrics (will be populated dynamically when reports exist)
        $this->totalThisMonth = 0;
        $this->draftsCount = 0;
        $this->completedCount = 0;
    }

    public function createTodayReport()
    {
        // When report creation is wired in Milestone 2/4
        session()->flash('info', 'Report wizard initialization is being linked.');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
