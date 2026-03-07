<?php
namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Only applicants can submit applications.
     */
    public function create(User $user): bool
    {
        return $user->isApplicant();
    }

    /**
     * Applicants can view their own. Users with review_applications can view any.
     */
    public function view(User $user, Application $application): bool
    {
        return $application->user_id === $user->id
            || $user->hasPermissionIn($application->jobPosition->organization, 'review_applications');
    }

    /**
     * Applicants can only update their own application while it is still submitted.
     */
    public function update(User $user, Application $application): bool
    {
        return $user->isApplicant()
            && $application->user_id === $user->id
            && $application->status === 'submitted';
    }

    /**
     * Applicants can withdraw their own. Users with review_applications can remove any.
     */
    public function delete(User $user, Application $application): bool
    {
        return ($user->isApplicant() && $application->user_id === $user->id)
            || $user->hasPermissionIn($application->jobPosition->organization, 'review_applications');
    }

    /**
     * Only users with review_applications permission can update application status.
     */
    public function updateStatus(User $user, Application $application): bool
    {
        return $user->hasPermissionIn($application->jobPosition->organization, 'review_applications');
    }
}