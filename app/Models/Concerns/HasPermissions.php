<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Models\OrganizationUserPermission;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Provides organization-scoped permission checking for the User model. A user
 * can hold different permissions in different organizations. Granting a
 * permission to another user requires the granter to hold both manage_members
 * and the target permission themselves.
 */
trait HasPermissions
{
    /**
     * All permission grants recorded for this user across all organizations.
     *
     * @return HasMany<OrganizationUserPermission>
     */
    public function organizationPermissions(): HasMany
    {
        return $this->hasMany(OrganizationUserPermission::class, 'user_id');
    }

    /**
     * Returns true if this user holds the named permission within the given
     * organization.
     */
    public function hasPermissionIn(Organization $organization, string $permissionName): bool
    {
        return $this->organizationPermissions()
            ->where('organization_id', $organization->id)
            ->whereHas('permission', fn ($q) => $q->where('name', $permissionName))
            ->exists();
    }

    /**
     * Returns true if this user holds the manage_members permission in the
     * given organization, which is the prerequisite for granting permissions
     * to others.
     */
    public function canGrantPermissionsIn(Organization $organization): bool
    {
        return $this->hasPermissionIn($organization, 'manage_members');
    }

    /**
     * Returns true if this user is allowed to grant a specific permission to
     * another user within the given organization. Requires manage_members AND
     * that this user already holds the target permission themselves.
     */
    public function canGrantPermissionIn(Organization $organization, string $permissionName): bool
    {
        return $this->canGrantPermissionsIn($organization)
            && $this->hasPermissionIn($organization, $permissionName);
    }

    /**
     * Returns an array of all permission names this user holds within the
     * given organization. Useful for loading all permissions in a single query
     * when a controller needs to check multiple capabilities.
     *
     * @return string[]
     */
    public function permissionNamesIn(Organization $organization): array
    {
        return Cache::remember(
            "user.{$this->id}.org.{$organization->id}.permissions",
            now()->addMinutes(15),
            fn () => $this->organizationPermissions()
                ->where('organization_id', $organization->id)
                ->with('permission')
                ->get()
                ->pluck('permission.name')
                ->toArray()
        );
    }
}