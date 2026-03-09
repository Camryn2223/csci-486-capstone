<?php

namespace App\Models\Concerns;

use App\Models\ApplicationTemplate;
use App\Models\JobPosition;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Provides chairman-specific relationships for the User model. A chairman owns
 * organizations and may also directly create templates and job positions.
 */
trait HasChairmanFeatures
{
    /**
     * Organizations where this user is the chairman (owner).
     *
     * @return HasMany<Organization>
     */
    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'chairman_id');
    }

    /**
     * Application templates this user created directly.
     *
     * @return HasMany<ApplicationTemplate>
     */
    public function createdTemplates(): HasMany
    {
        return $this->hasMany(ApplicationTemplate::class, 'created_by');
    }

    /**
     * Job positions this user created directly.
     *
     * @return HasMany<JobPosition>
     */
    public function createdPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class, 'created_by');
    }
}