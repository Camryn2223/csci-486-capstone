<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Represents a company or hiring organization owned by a chairman. An
 * organization has members (users), open job positions, application templates,
 * and a granular permission system that controls what each member can do.
 *
 * @property int    $id
 * @property int    $chairman_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Organization extends Model
{
    protected $fillable = [
        'name',
        'chairman_id',
    ];

    /**
     * The user who owns and chairs this organization.
     *
     * @return BelongsTo<User, Organization>
     */
    public function chairman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    /**
     * All users who are members of this organization.
     *
     * @return BelongsToMany<User>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user');
    }

    /**
     * All granular permissions granted to users within this organization.
     *
     * @return HasMany<OrganizationUserPermission>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(OrganizationUserPermission::class);
    }

    /**
     * All job positions that belong to this organization.
     *
     * @return HasMany<JobPosition>
     */
    public function jobPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }

    /**
     * All applications submitted across all job positions in this organization.
     *
     * @return HasManyThrough<Application>
     */
    public function applications(): HasManyThrough
    {
        return $this->hasManyThrough(Application::class, JobPosition::class);
    }

    /**
     * All application templates created under this organization.
     *
     * @return HasMany<ApplicationTemplate>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(ApplicationTemplate::class);
    }

    /**
     * Job positions that are currently open for applications.
     *
     * @return HasMany<JobPosition>
     */
    public function openPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class)->where('status', 'open');
    }

    /**
     * Returns true if the given user is a member of this organization.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Returns all permission records granted to a specific user in this
     * organization, eager-loading the related Permission model.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, OrganizationUserPermission>
     */
    public function userPermissions(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->permissions()
            ->where('user_id', $user->id)
            ->with('permission')
            ->get();
    }
}