<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Interview;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization policy for Interview records. Feedback submission is
 * restricted to the assigned interviewer.
 */
class InterviewPolicy
{
    /**
     * Determine whether the user can view all interviews across an
     * organization. Requires the review-applications or schedule-interviews
     * gate. Users without either can only see their own assigned interviews.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return Gate::forUser($user)->allows('review-applications', $organization)
            || Gate::forUser($user)->allows('schedule-interviews', $organization);
    }

    /**
     * Determine whether the user can schedule a new interview for an
     * application. Requires the schedule-interviews gate in the application's
     * organization.
     */
    public function create(User $user, Application $application): bool
    {
        return Gate::forUser($user)->allows('schedule-interviews', $application->jobPosition->organization);
    }

    /**
     * Determine whether the user can view an interview's details. Requires
     * the review-applications or schedule-interviews gate, or the user must
     * be the assigned interviewer.
     */
    public function view(User $user, Interview $interview): bool
    {
        $organization = $interview->application->jobPosition->organization;

        return $user->id === $interview->interviewer_id
            || Gate::forUser($user)->allows('review-applications', $organization)
            || Gate::forUser($user)->allows('schedule-interviews', $organization);
    }

    /**
     * Determine whether the user can update, reschedule, cancel, or complete
     * an interview. Requires the schedule-interviews gate in the interview's
     * organization.
     */
    public function update(User $user, Interview $interview): bool
    {
        return Gate::forUser($user)->allows('schedule-interviews', $interview->application->jobPosition->organization);
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