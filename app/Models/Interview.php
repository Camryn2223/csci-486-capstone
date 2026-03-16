<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a scheduled interview between an interviewer and an applicant for
 * a given application. Tracks the scheduled time, current status, optional
 * notes, and when feedback was submitted after the interview was completed.
 *
 * @property int $id
 * @property int $application_id
 * @property int $interviewer_id
 * @property \Carbon\Carbon $scheduled_at
 * @property string $status scheduled | completed | canceled
 * @property string|null $notes
 * @property \Carbon\Carbon|null $feedback_submitted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Interview extends Model
{
    protected $fillable = [
        'application_id',
        'interviewer_id',
        'scheduled_at',
        'status',
        'notes',
        'feedback_submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at'          => 'datetime',
            'feedback_submitted_at' => 'datetime',
            'status'                => 'string',
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
     * The interviewer who is conducting this interview.
     *
     * @return BelongsTo<User, Interview>
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
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
     * Scope to completed interviews where the interviewer has not yet
     * submitted feedback.
     *
     * @param  Builder<Interview> $query
     * @return Builder<Interview>
     */
    public function scopePendingFeedback(Builder $query): Builder
    {
        return $query->where('status', 'completed')
            ->whereNull('feedback_submitted_at');
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
     * Returns true if the interviewer has already submitted feedback for this
     * interview.
     */
    public function hasFeedback(): bool
    {
        return ! is_null($this->feedback_submitted_at);
    }
}