<?php

namespace App\Models\Concerns;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Provides applicant-specific relationships and convenience methods for the
 * User model. Mixed in when the user's role is applicant, though the methods
 * are available on all User instances regardless of role.
 */
trait HasApplicantFeatures
{
    /**
     * All applications this user has submitted.
     *
     * @return HasMany<Application>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'user_id');
    }

    /**
     * All documents this user has uploaded across all of their applications.
     *
     * @return HasMany<Document>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'user_id');
    }

    /**
     * Applications this user has submitted that are still in an active review
     * state — excludes withdrawn and no longer under consideration.
     *
     * @return HasMany<Application>
     */
    public function activeApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'user_id')
            ->whereNotIn('status', ['withdrawn', 'no_longer_under_consideration']);
    }

    /**
     * Returns true if this user has already submitted an application for the
     * given job position ID.
     */
    public function hasAppliedTo(int $jobPositionId): bool
    {
        return $this->applications()->where('job_position_id', $jobPositionId)->exists();
    }
}