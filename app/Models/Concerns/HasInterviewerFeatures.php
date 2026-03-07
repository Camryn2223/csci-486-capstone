<?php
namespace App\Models\Concerns;

use App\Models\Interview;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasInterviewerFeatures
{
    /**
     * All interviews this user has been assigned to conduct.
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'interviewer_id');
    }

    /**
     * Upcoming scheduled interviews assigned to this user.
     */
    public function upcomingInterviews(): HasMany
    {
        return $this->interviews()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at');
    }

    /**
     * Completed interviews this user has not yet submitted feedback for.
     */
    public function pendingFeedbackInterviews(): HasMany
    {
        return $this->interviews()
            ->where('status', 'completed')
            ->whereNull('feedback_submitted_at');
    }
}