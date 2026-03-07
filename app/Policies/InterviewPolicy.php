<?php
namespace App\Policies;

use App\Models\Interview;
use App\Models\User;

class InterviewPolicy
{
    /**
     * Only users with schedule_interviews permission can create interviews.
     */
    public function create(User $user, Interview $interview): bool
    {
        return $user->hasPermissionIn(
            $interview->application->jobPosition->organization,
            'schedule_interviews'
        );
    }

    /**
     * The assigned interviewer, the applicant, and users with review_applications can view.
     */
    public function view(User $user, Interview $interview): bool
    {
        return $interview->interviewer_id === $user->id
            || $interview->application->user_id === $user->id
            || $user->hasPermissionIn(
                $interview->application->jobPosition->organization,
                'review_applications'
            );
    }

    /**
     * Only the assigned interviewer can submit notes and feedback.
     */
    public function submitFeedback(User $user, Interview $interview): bool
    {
        return $interview->interviewer_id === $user->id;
    }

    /**
     * Only users with schedule_interviews permission can update interview details.
     */
    public function update(User $user, Interview $interview): bool
    {
        return $user->hasPermissionIn(
            $interview->application->jobPosition->organization,
            'schedule_interviews'
        );
    }

    /**
     * Only users with schedule_interviews permission can cancel or delete interviews.
     */
    public function delete(User $user, Interview $interview): bool
    {
        return $user->hasPermissionIn(
            $interview->application->jobPosition->organization,
            'schedule_interviews'
        );
    }
}