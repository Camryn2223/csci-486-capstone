<?php

namespace App\Policies;

use App\Models\JobPosition;
use App\Models\Organization;
use App\Models\User;

/**
 * Authorization policy for JobPosition records.
 */
class JobPositionPolicy
{
    /**
     * Determine whether the user can list job positions.
     * Guests (?User) are allowed to hit the index; filtering is handled in the controller.
     */
    public function viewAny(?User $user, Organization $organization): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific job position.
     * The position view is an internal staff-only view.
     */
    public function view(User $user, JobPosition $jobPosition): bool
    {
        return $user->isChairmanOf($jobPosition->organization)
            || $user->hasPermissionIn($jobPosition->organization, 'review_applications')
            || $user->hasPermissionIn($jobPosition->organization, 'create_positions');
    }

    /**
     * Determine whether the user can create a job position.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'create_positions');
    }

    /**
     * Determine whether the user can update a job position.
     */
    public function update(User $user, JobPosition $jobPosition): bool
    {
        return $user->hasPermissionIn($jobPosition->organization, 'create_positions');
    }

    /**
     * Determine whether the user can delete a job position.
     */
    public function delete(User $user, JobPosition $jobPosition): bool
    {
        return $user->hasPermissionIn($jobPosition->organization, 'create_positions');
    }
}