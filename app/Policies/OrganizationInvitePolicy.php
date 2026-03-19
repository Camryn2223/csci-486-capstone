<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Models\User;

/**
 * Authorization policy for OrganizationInvite records. Creating and viewing
 * invites requires the create_invites permission.
 */
class OrganizationInvitePolicy
{
    /**
     * Determine whether the user can view all invites for an organization.
     * Requires the create_invites permission.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'create_invites');
    }

    /**
     * Determine whether the user can create an invite for an organization.
     * Requires the create_invites permission.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'create_invites');
    }

    /**
     * Determine whether the user can delete (revoke) an invite. Requires the
     * create_invites permission in the invite's organization.
     */
    public function delete(User $user, OrganizationInvite $invite): bool
    {
        return $user->hasPermissionIn($invite->organization, 'create_invites');
    }
}