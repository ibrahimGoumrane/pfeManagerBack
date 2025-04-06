<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Check if the user has already create a report as a user is allowed to create only one report
        if($user->reports()->count() > 0){
            return false;
        }
        
        return true;
    }

    /**
     * Determine whether the user can validate reports.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Report  $report
     * @return bool
     */
    public function validate(User $user, Report $report)
    {
        // Only admins can validate reports
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the report.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Report  $report
     * @return bool
     */
    public function update(User $user, Report $report)
    {
        // Users can update their own reports, admins can update any report
        return $user->id === $report->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the report.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Report  $report
     * @return bool
     */
    public function delete(User $user, Report $report)
    {
        // Users can delete their own reports, admins can delete any report
        return $user->id === $report->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return true;
    }
}
