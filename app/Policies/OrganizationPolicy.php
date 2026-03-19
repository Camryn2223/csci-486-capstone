<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

/**
 * Authorization policy for Organization records. Creating organizations is
 * restricted to users with the chairman role. Viewing requires membership or
 * ownership. Updating and deleting are restricted to the organization's
 * chairman.
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
     * Determine whether the user can update an organization's details.
     * Restricted to the organization's chairman.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization);
    }

    /**
     * Determine whether the user can manage the organization's members.
     * Requires the manage_members permission.
     */
    public function manageMembers(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'manage_members');
    }

    /**
     * Determine whether the user can delete an organization. Restricted to
     * the organization's chairman.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization);
    }
}