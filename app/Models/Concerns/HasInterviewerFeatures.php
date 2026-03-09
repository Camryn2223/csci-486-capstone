<?php

namespace App\Models\Concerns;

use App\Models\Interview;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Provides interviewer-specific relationships and convenience methods for the
 * User model. Mixed in for users whose role is interviewer or chairman.
 */
trait HasInterviewerFeatures
{
    /**
     * All interviews assigned to this user as the interviewer.
     *
     * @return HasMany<Interview>
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'interviewer_id');
    }

    /**
     * Scheduled interviews assigned to this user that have not yet occurred,
     * ordered by soonest first.
     *
     * @return HasMany<Interview>
     */
    public function upcomingInterviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'interviewer_id')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at');
    }

    /**
     * Completed interviews assigned to this user for which feedback has not
     * yet been submitted.
     *
     * @return HasMany<Interview>
     */
    public function pendingFeedbackInterviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'interviewer_id')
            ->where('status', 'completed')
            ->whereNull('feedback_submitted_at');
    }
}