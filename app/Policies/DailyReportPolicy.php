<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;

class DailyReportPolicy
{
    /**
     * Determine whether the user can view any reports.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the report.
     */
    public function view(User $user, DailyReport $dailyReport): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->hotel_id === null || $user->hotel_id === $dailyReport->hotel_id;
    }

    /**
     * Determine whether the user can create reports.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the report.
     */
    public function update(User $user, DailyReport $dailyReport): bool
    {
        if ($dailyReport->isCompleted() && !$user->isAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hotel_id === null || $user->hotel_id === $dailyReport->hotel_id;
    }

    /**
     * Determine whether the user can delete the report.
     */
    public function delete(User $user, DailyReport $dailyReport): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can delete draft
        return $dailyReport->isDraft() && $user->id === $dailyReport->created_by;
    }

    /**
     * Determine whether the user can finalize the report.
     */
    public function finalize(User $user, DailyReport $dailyReport): bool
    {
        if (!$dailyReport->isDraft()) {
            return false;
        }

        return $this->update($user, $dailyReport);
    }

    /**
     * Determine whether the user can reopen/revise a completed report.
     */
    public function reopen(User $user, DailyReport $dailyReport): bool
    {
        if (!$dailyReport->isCompleted()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Creator from the same hotel can reopen their own completed report
        return ($user->hotel_id === null || $user->hotel_id === $dailyReport->hotel_id)
            && $user->id === $dailyReport->created_by;
    }
}
