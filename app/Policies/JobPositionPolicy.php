<?php

namespace App\Policies;

use App\Models\JobPosition;
use App\Models\Organization;
use App\Models\User;

/**
 * Authorization policy for JobPosition records. Creating, updating, and
 * deleting positions requires the create_positions permission. Viewing open
 * positions is available to any organization member. Closed positions require
 * review_applications or create_positions.
 */
class JobPositionPolicy
{
    /**
     * Determine whether the user can list job positions for an organization.
     * Available to any organization member.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization)
            || $user->isMemberOf($organization);
    }

    /**
     * Determine whether the user can view a specific job position. Open
     * positions are visible to any member. Closed positions require
     * review_applications or create_positions.
     */
    public function view(User $user, JobPosition $jobPosition): bool
    {
        $organization = $jobPosition->organization;

        if (! $user->isMemberOf($organization) && ! $user->isChairmanOf($organization)) {
            return false;
        }

        if ($jobPosition->isOpen()) {
            return true;
        }

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'review_applications')
            || $user->hasPermissionIn($organization, 'create_positions');
    }

    /**
     * Determine whether the user can create a job position in an organization.
     * Requires create_positions.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'create_positions');
    }

    /**
     * Determine whether the user can update a job position. Requires
     * create_positions in the position's organization.
     */
    public function update(User $user, JobPosition $jobPosition): bool
    {
        $organization = $jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'create_positions');
    }

    /**
     * Determine whether the user can delete a job position. Requires
     * create_positions in the position's organization.
     */
    public function delete(User $user, JobPosition $jobPosition): bool
    {
        $organization = $jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'create_positions');
    }
}