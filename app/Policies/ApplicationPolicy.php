<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization policy for Application records. Public submission is handled outside the
 * policy layer as an unauthenticated route.
 */
class ApplicationPolicy
{
    /**
     * Determine whether the user can view a list of applications for a job
     * position. Requires the review-applications gate in the position's
     * organization.
     */
    public function viewAny(User $user, JobPosition $jobPosition): bool
    {
        return Gate::forUser($user)->allows('review-applications', $jobPosition->organization);
    }

    /**
     * Determine whether the user can view a specific application. Requires
     * the review-applications gate in the application's organization.
     */
    public function view(User $user, Application $application): bool
    {
        return Gate::forUser($user)->allows('review-applications', $application->jobPosition->organization);
    }

    /**
     * Determine whether the user can update the status of an application.
     * Requires the review-applications gate in the application's organization.
     * Valid status transitions are enforced in the controller.
     */
    public function updateStatus(User $user, Application $application): bool
    {
        return Gate::forUser($user)->allows('review-applications', $application->jobPosition->organization);
    }

    /**
     * Determine whether the user can delete an application record. Requires
     * the review-applications gate in the application's organization.
     */
    public function delete(User $user, Application $application): bool
    {
        return Gate::forUser($user)->allows('review-applications', $application->jobPosition->organization);
    }
}