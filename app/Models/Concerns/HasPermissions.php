<?php
namespace App\Models\Concerns;

use App\Models\Organization;
use App\Models\OrganizationUserPermission;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPermissions
{
    /**
     * All permissions this user has been granted across all organizations.
     */
    public function organizationPermissions(): HasMany
    {
        return $this->hasMany(OrganizationUserPermission::class);
    }

    /**
     * Check if this user has a specific permission within a given organization.
     */
    public function hasPermissionIn(Organization $organization, string $permission): bool
    {
        return $this->organizationPermissions()
            ->where('organization_id', $organization->id)
            ->whereHas('permission', fn($q) => $q->where('name', $permission))
            ->exists();
    }

    /**
     * Check if this user can grant permissions within a given organization.
     * A user can grant if they have manage_members permission in that organization.
     */
    public function canGrantPermissionsIn(Organization $organization): bool
    {
        return $this->hasPermissionIn($organization, 'manage_members');
    }

    /**
     * Check if this user can grant a specific permission within a given organization.
     * A user can only grant permissions they themselves have.
     */
    public function canGrantPermissionIn(Organization $organization, string $permission): bool
    {
        return $this->canGrantPermissionsIn($organization)
            && $this->hasPermissionIn($organization, $permission);
    }
}