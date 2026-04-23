<?php

namespace App\Models\Concerns;

use App\Models\Interview;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Provides interviewer-specific relationships and convenience methods for the
 * User model. Mixed in for users whose role is interviewer or chairman.
 */
trait HasInterviewerFeatures
{
    /**
     * All interviews assigned to this user as an interviewer.
     *
     * @return BelongsToMany<Interview>
     */
    public function interviews(): BelongsToMany
    {
        return $this->belongsToMany(Interview::class, 'interview_user')
            ->withPivot(['notes', 'feedback_submitted_at'])
            ->withTimestamps();
    }

    /**
     * Scheduled interviews assigned to this user that have not yet occurred,
     * ordered by soonest first.
     *
     * @return BelongsToMany<Interview>
     */
    public function upcomingInterviews(): BelongsToMany
    {
        return $this->interviews()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at');
    }
}