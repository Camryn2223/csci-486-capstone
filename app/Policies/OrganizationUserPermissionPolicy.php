<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationUserPermission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization policy for OrganizationUserPermission records. Controls who
 * can view, grant, and revoke permissions for users within an organization.
 * All checks use the manage-members gate plus the delegation rule that the
 * acting user must also hold the target permission themselves.
 */
class OrganizationUserPermissionPolicy
{
    /**
     * Determine whether the user can view all permissions granted to members
     * of a given organization. Requires the manage-members gate.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return Gate::forUser($user)->allows('manage-members', $organization);
    }

    /**
     * Determine whether the user can view a specific permission grant record.
     * Requires the manage-members gate in the record's organization.
     */
    public function view(User $user, OrganizationUserPermission $permission): bool
    {
        return Gate::forUser($user)->allows('manage-members', $permission->organization);
    }

    /**
     * Determine whether the user can grant a named permission to another user
     * within a given organization. Requires the manage-members gate plus the
     * user must hold the target permission themselves.
     */
    public function grant(User $user, Organization $organization, string $permissionName): bool
    {
        return Gate::forUser($user)->allows('manage-members', $organization)
            && $user->hasPermissionIn($organization, $permissionName);
    }

    /**
     * Determine whether the user can revoke an existing permission grant.
     * Applies the same rule as granting - manage-members gate plus holding
     * the permission being revoked.
     */
    public function revoke(User $user, OrganizationUserPermission $permission): bool
    {
        return Gate::forUser($user)->allows('manage-members', $permission->organization)
            && $user->hasPermissionIn($permission->organization, $permission->permission->name);
    }
}