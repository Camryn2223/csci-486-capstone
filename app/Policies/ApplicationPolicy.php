<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\JobPosition;
use App\Models\User;

/**
 * Authorization policy for Application records. Public submission is handled outside the
 * policy layer as an unauthenticated route.
 */
class ApplicationPolicy
{
    /**
     * Determine whether the user can view a list of applications for a job
     * position. Requires review_applications in the position's organization.
     */
    public function viewAny(User $user, JobPosition $jobPosition): bool
    {
        return $user->isChairmanOf($jobPosition->organization)
            || $user->hasPermissionIn($jobPosition->organization, 'review_applications');
    }

    /**
     * Determine whether the user can view a specific application. Requires
     * review_applications in the application's organization.
     */
    public function view(User $user, Application $application): bool
    {
        $organization = $application->jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'review_applications');
    }

    /**
     * Determine whether the user can update the status of an application.
     * Requires review_applications in the application's organization.
     * Valid status transitions are enforced in the controller.
     */
    public function updateStatus(User $user, Application $application): bool
    {
        $organization = $application->jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'review_applications');
    }

    /**
     * Determine whether the user can delete (hard-delete) an application
     * record. Requires review_applications in the application's organization.
     * Soft status changes (withdraw, no longer under consideration) are handled
     * via updateStatus.
     */
    public function delete(User $user, Application $application): bool
    {
        $organization = $application->jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'review_applications');
    }
}