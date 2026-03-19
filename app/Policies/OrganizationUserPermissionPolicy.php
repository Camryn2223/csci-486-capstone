<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationUserPermission;
use App\Models\User;

/**
 * Authorization policy for OrganizationUserPermission records. Controls who
 * can view and sync permissions for users within an organization.
 */
class OrganizationUserPermissionPolicy
{
    /**
     * Determine whether the user can view all permissions granted to members
     * of a given organization. Requires the manage_members permission.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'manage_members');
    }

    /**
     * Determine whether the user can sync permissions for another user
     * within a given organization. Requires the manage_members permission.
     */
    public function sync(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'manage_members');
    }
}