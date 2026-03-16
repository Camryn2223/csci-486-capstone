<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationUserPermission;
use App\Models\User;

/**
 * Authorization policy for OrganizationUserPermission records. Controls who
 * can view, grant, and revoke permissions for users within an organization.
 *
 * Granting and revoking follow the same delegation rule: the acting user must
 * hold manage_members AND the target permission themselves. The chairman of
 * the organization bypasses these checks and can always manage all permissions
 * within their own organization.
 */
class OrganizationUserPermissionPolicy
{
    /**
     * Determine whether the user can view all permissions granted to members
     * of a given organization. Requires manage_members in that organization,
     * or the user must be the organization's chairman.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'manage_members');
    }

    /**
     * Determine whether the user can view a specific permission grant record.
     * Requires manage_members in the organization the record belongs to, or
     * the user must be the organization's chairman.
     */
    public function view(User $user, OrganizationUserPermission $permission): bool
    {
        $organization = $permission->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'manage_members');
    }

    /**
     * Determine whether the user can grant a named permission to another user
     * within a given organization. The acting user must hold manage_members
     * AND the target permission themselves, unless they are the chairman.
     */
    public function grant(User $user, Organization $organization, string $permissionName): bool
    {
        return $user->isChairmanOf($organization)
            || $user->canGrantPermissionIn($organization, $permissionName);
    }

    /**
     * Determine whether the user can revoke an existing permission grant.
     * Applies the same delegation rule as granting - the acting user must hold
     * manage_members AND the permission being revoked, unless they are the
     * chairman.
     */
    public function revoke(User $user, OrganizationUserPermission $permission): bool
    {
        $organization = $permission->organization;
        $permissionName = $permission->permission->name;

        return $user->isChairmanOf($organization)
            || $user->canGrantPermissionIn($organization, $permissionName);
    }
}