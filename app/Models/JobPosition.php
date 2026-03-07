<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a job opening within an organization. A position can be open or
 * closed and may receive many applications from applicants. Positions are
 * created by authorized interviewers or the chairman.
 *
 * @property int    $id
 * @property int    $organization_id
 * @property int    $created_by
 * @property string $title
 * @property string $description
 * @property string $requirements
 * @property string $status          open | closed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class JobPosition extends Model
{
    protected $fillable = [
        'organization_id',
        'created_by',
        'title',
        'description',
        'requirements',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * The organization this position belongs to.
     *
     * @return BelongsTo<Organization, JobPosition>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who created this position.
     *
     * @return BelongsTo<User, JobPosition>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All applications submitted for this position.
     *
     * @return HasMany<Application>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Scope to positions with status = open.
     *
     * @param  Builder<JobPosition> $query
     * @return Builder<JobPosition>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope to positions with status = closed.
     *
     * @param  Builder<JobPosition> $query
     * @return Builder<JobPosition>
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    /**
     * Returns true if this position is currently open for applications.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Returns true if the given user has already submitted an application for
     * this position.
     */
    public function hasApplicationFrom(User $user): bool
    {
        return $this->applications()->where('user_id', $user->id)->exists();
    }
}