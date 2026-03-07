<?php
namespace App\Policies;

use App\Models\JobPosition;
use App\Models\User;

class JobPositionPolicy
{
    /**
     * Chairmen can always create positions. Organization members with can_create_positions can too.
     */
    public function create(User $user): bool
    {
        return $user->isChairman() || $user->organizations()->wherePivot('role', 'manager')->exists();
    }

    /**
     * Anyone can view active positions. Chairmen and interviewers can view inactive ones.
     */
    public function view(User $user, JobPosition $jobPosition): bool
    {
        if ($jobPosition->status === 'active') {
            return true;
        }

        return $user->isChairman() || $user->isInterviewer();
    }

    /**
     * Only the chairman of the owning organization or the creator can update a position.
     */
    public function update(User $user, JobPosition $jobPosition): bool
    {
        return $user->isChairman() && $jobPosition->organization->chairman_id === $user->id
            || $jobPosition->created_by === $user->id;
    }

    /**
     * Only the chairman of the owning organization or the creator can delete a position.
     */
    public function delete(User $user, JobPosition $jobPosition): bool
    {
        return $user->isChairman() && $jobPosition->organization->chairman_id === $user->id
            || $jobPosition->created_by === $user->id;
    }
}