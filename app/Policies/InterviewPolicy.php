<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Interview;
use App\Models\User;

/**
 * Authorization policy for Interview records.
 * Scheduling requires the schedule_interviews permission. Feedback submission
 * is restricted to the assigned interviewer.
 */
class InterviewPolicy
{
    /**
     * Determine whether the user can schedule a new interview for an
     * application. Requires schedule_interviews in the application's
     * organization.
     */
    public function create(User $user, Application $application): bool
    {
        $organization = $application->jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'schedule_interviews');
    }

    /**
     * Determine whether the user can view an interview's details. Requires
     * review_applications or schedule_interviews in the interview's
     * organization, or the user must be the assigned interviewer.
     */
    public function view(User $user, Interview $interview): bool
    {
        $organization = $interview->application->jobPosition->organization;

        return $user->id === $interview->interviewer_id
            || $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'review_applications')
            || $user->hasPermissionIn($organization, 'schedule_interviews');
    }

    /**
     * Determine whether the user can update, reschedule, cancel, or complete
     * an interview. Requires schedule_interviews in the interview's
     * organization.
     */
    public function update(User $user, Interview $interview): bool
    {
        $organization = $interview->application->jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'schedule_interviews');
    }

    /**
     * Determine whether the user can submit feedback for an interview. Only
     * the assigned interviewer may submit feedback.
     */
    public function submitFeedback(User $user, Interview $interview): bool
    {
        return $user->id === $interview->interviewer_id;
    }
}
