<?php
namespace App\Policies;

use App\Models\JobPosition;
use App\Models\User;

class JobPositionPolicy
{
    /**
     * Users with create_positions permission in the relevant organization can create positions.
     */
    public function create(User $user, JobPosition $jobPosition): bool
    {
        return $user->hasPermissionIn($jobPosition->organization, 'create_positions');
    }

    /**
     * Anyone can view active positions. Only users with review_applications can view inactive ones.
     */
    public function view(User $user, JobPosition $jobPosition): bool
    {
        if ($jobPosition->status === 'active') {
            return true;
        }

        return $user->hasPermissionIn($jobPosition->organization, 'review_applications');
    }

    /**
     * Only users with create_positions permission in the organization can update positions.
     */
    public function update(User $user, JobPosition $jobPosition): bool
    {
        return $user->hasPermissionIn($jobPosition->organization, 'create_positions');
    }

    /**
     * Only users with create_positions permission in the organization can delete positions.
     */
    public function delete(User $user, JobPosition $jobPosition): bool
    {
        return $user->hasPermissionIn($jobPosition->organization, 'create_positions');
    }
}