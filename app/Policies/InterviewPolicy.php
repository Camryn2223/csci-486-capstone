<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Interview;
use App\Models\Organization;
use App\Models\User;

/**
 * Authorization policy for Interview records. Feedback submission is
 * restricted to the assigned interviewer.
 */
class InterviewPolicy
{
    /**
     * Determine whether the user can view all interviews across an
     * organization. Requires the review_applications or schedule_interviews
     * permission. Users without either can only see their own assigned interviews.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'review_applications')
            || $user->hasPermissionIn($organization, 'schedule_interviews');
    }

    /**
     * Determine whether the user can schedule a new interview for an
     * application. Requires the schedule_interviews permission in the application's
     * organization.
     */
    public function create(User $user, Application $application): bool
    {
        return $user->hasPermissionIn($application->jobPosition->organization, 'schedule_interviews');
    }

    /**
     * Determine whether the user can view an interview's details. Requires
     * the review_applications or schedule_interviews permission, or the user must
     * be an assigned interviewer.
     */
    public function view(User $user, Interview $interview): bool
    {
        $organization = $interview->application->jobPosition->organization;

        return $interview->interviewers->contains('id', $user->id)
            || $user->hasPermissionIn($organization, 'review_applications')
            || $user->hasPermissionIn($organization, 'schedule_interviews');
    }

    /**
     * Determine whether the user can update, reschedule, cancel, or complete
     * an interview. Requires the schedule_interviews permission in the interview's
     * organization.
     */
    public function update(User $user, Interview $interview): bool
    {
        return $user->hasPermissionIn($interview->application->jobPosition->organization, 'schedule_interviews');
    }

    /**
     * Determine whether the user can submit feedback for an interview. Only
     * the assigned interviewers may submit feedback.
     */
    public function submitFeedback(User $user, Interview $interview): bool
    {
        return $interview->interviewers->contains('id', $user->id);
    }
}