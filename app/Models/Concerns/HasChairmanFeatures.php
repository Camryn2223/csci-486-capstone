<?php
namespace App\Models\Concerns;

use App\Models\ApplicationTemplate;
use App\Models\JobPosition;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasChairmanFeatures
{
    /**
     * All organizations this user chairs.
     */
    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'chairman_id');
    }

    /**
     * All application templates this user has created.
     */
    public function createdTemplates(): HasMany
    {
        return $this->hasMany(ApplicationTemplate::class, 'created_by');
    }

    /**
     * All job positions this user has created.
     */
    public function createdPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class, 'created_by');
    }
}