<?php

use App\Actions\CreateDailyReport;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Reports\Index;
use App\Livewire\Reports\Preview;
use App\Livewire\Reports\Wizard;
use App\Models\Hotel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/', function () {
        return redirect()->route('login');
    });
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/reports', Index::class)->name('reports.index');
    Route::get('/reports/create', function (CreateDailyReport $action) {
        $user = auth()->user();
        $hotel = $user->hotel ?? Hotel::first();
        if (!$hotel) {
            return redirect()->route('dashboard')->with('error', 'No active hotel available.');
        }
        $report = $action->execute($hotel, $user);
        return redirect()->route('reports.wizard', $report);
    })->name('reports.create');

    Route::get('/reports/{report}/wizard', Wizard::class)->name('reports.wizard');
    Route::get('/reports/{report}/preview', Preview::class)->name('reports.preview');
    Route::get('/reports/{report}/export/docx', [\App\Http\Controllers\ReportExportController::class, 'docx'])->name('reports.export.docx');
    Route::get('/reports/{report}/export/pdf', [\App\Http\Controllers\ReportExportController::class, 'pdf'])->name('reports.export.pdf');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
