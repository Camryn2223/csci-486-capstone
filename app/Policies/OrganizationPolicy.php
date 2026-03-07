<?php
namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Only chairmen can create organizations.
     */
    public function create(User $user): bool
    {
        return $user->isChairman();
    }

    /**
     * Any member of the organization or its chairman can view it.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->isChairman() && $organization->chairman_id === $user->id
            || $user->organizations->contains($organization);
    }

    /**
     * Only the chairman who owns the organization can update it.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->isChairman() && $organization->chairman_id === $user->id;
    }

    /**
     * Only the chairman who owns the organization can delete it.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->isChairman() && $organization->chairman_id === $user->id;
    }

    /**
     * Only the chairman who owns the organization can transfer it.
     */
    public function transfer(User $user, Organization $organization): bool
    {
        return $user->isChairman() && $organization->chairman_id === $user->id;
    }
}