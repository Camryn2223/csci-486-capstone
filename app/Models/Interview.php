<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a scheduled interview block for an application. An interview
 * is associated with one or more interviewers who each can provide individual
 * feedback via the pivot table.
 *
 * @property int $id
 * @property int $application_id
 * @property \Carbon\Carbon $scheduled_at
 * @property string $status scheduled | completed | canceled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Interview extends Model
{
    protected $fillable = [
        'application_id',
        'scheduled_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status'       => 'string',
        ];
    }

    /**
     * The application this interview is associated with.
     *
     * @return BelongsTo<Application, Interview>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * The interviewers conducting this interview. Feedback and notes
     * are stored individually on the pivot table.
     *
     * @return BelongsToMany<User>
     */
    public function interviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'interview_user')
            ->withPivot(['notes', 'feedback_submitted_at'])
            ->withTimestamps();
    }

    /**
     * Scope to interviews with status = scheduled.
     *
     * @param  Builder<Interview> $query
     * @return Builder<Interview>
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to scheduled interviews that have not yet occurred, ordered by
     * soonest first.
     *
     * @param  Builder<Interview> $query
     * @return Builder<Interview>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at');
    }

    /**
     * Scope to completed interviews where at least one assigned interviewer
     * has not yet submitted feedback.
     *
     * @param  Builder<Interview> $query
     * @return Builder<Interview>
     */
    public function scopePendingFeedback(Builder $query): Builder
    {
        return $query->where('status', 'completed')
            ->whereHas('interviewers', function ($q) {
                $q->whereNull('interview_user.feedback_submitted_at');
            });
    }

    /**
     * Returns true if this interview is in the scheduled state.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Returns true if this interview has been marked as completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Returns true if this interview has been canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Returns true if the specified interviewer has already submitted feedback
     * for this interview.
     */
    public function hasFeedbackFrom(User $user): bool
    {
        $interviewer = $this->interviewers->firstWhere('id', $user->id);

        return $interviewer && ! is_null($interviewer->pivot->feedback_submitted_at);
    }
}