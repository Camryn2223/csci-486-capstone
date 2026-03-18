<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization policy for Organization records. Creating organizations is
 * restricted to users with the chairman role. Viewing requires membership or
 * ownership. Updating and deleting are restricted to the organization's
 * chairman. Permission granting uses the manage-members gate.
 */
class OrganizationPolicy
{
    /**
     * Determine whether the user can create an organization. Restricted to
     * users with the chairman role.
     */
    public function create(User $user): bool
    {
        return $user->isChairman();
    }

    /**
     * Determine whether the user can view an organization. Requires membership
     * or ownership.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization)
            || $user->isMemberOf($organization);
    }

    /**
     * Determine whether the user can update an organization's details or
     * manage its members. Restricted to the organization's chairman.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization);
    }

    /**
     * Determine whether the user can delete an organization. Restricted to
     * the organization's chairman.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization);
    }

    /**
     * Determine whether the user can grant a permission to another member.
     * Requires the manage-members gate plus holding the target permission.
     */
    public function grantPermission(User $user, Organization $organization, string $permissionName): bool
    {
        return Gate::forUser($user)->allows('manage-members', $organization)
            && $user->hasPermissionIn($organization, $permissionName);
    }
}