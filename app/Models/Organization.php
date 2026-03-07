<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'chairman_id',
    ];

    /**
     * The user who chairs this organization.
     */
    public function chairman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    /**
     * All users who are members of this organization.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withTimestamps();
    }

    /**
     * All granted permissions within this organization.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(OrganizationUserPermission::class);
    }

    /**
     * All job positions listed under this organization.
     */
    public function jobPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }

    /**
     * All application templates created under this organization.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(ApplicationTemplate::class);
    }
}