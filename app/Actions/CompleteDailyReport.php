<?php

namespace App\Actions;

use App\Models\DailyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;

class CompleteDailyReport
{
    /**
     * Finalize and mark a daily report as completed.
     *
     * @param DailyReport $report
     * @param User|null $user
     * @return DailyReport
     * @throws AuthorizationException
     */
    public function execute(DailyReport $report, ?User $user = null): DailyReport
    {
        $actingUser = $user ?? auth()->user();

        if ($actingUser && $actingUser->cannot('finalize', $report)) {
            throw new AuthorizationException('You are not authorized to finalize this report.');
        }

        $report->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);

        return $report;
    }

    /**
     * Reopen a completed report back to draft for revisions.
     *
     * @param DailyReport $report
     * @param User|null $user
     * @return DailyReport
     * @throws AuthorizationException
     */
    public function reopen(DailyReport $report, ?User $user = null): DailyReport
    {
        $actingUser = $user ?? auth()->user();

        if ($actingUser && $actingUser->cannot('reopen', $report)) {
            throw new AuthorizationException('You are not authorized to revise this report.');
        }

        $report->update([
            'status' => 'draft',
            'completed_at' => null,
        ]);

        return $report;
    }
}
