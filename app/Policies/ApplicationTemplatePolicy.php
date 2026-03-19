<?php

namespace App\Policies;

use App\Models\ApplicationTemplate;
use App\Models\Organization;
use App\Models\User;

/**
 * Authorization policy for ApplicationTemplate records.
 */
class ApplicationTemplatePolicy
{
    /**
     * Determine whether the user can list templates for an organization.
     * Guests are allowed to resolve templates for applying.
     */
    public function viewAny(?User $user, Organization $organization): bool
    {
        if (! $user) {
            return true;
        }

        return $user->hasPermissionIn($organization, 'manage_templates')
            || $user->hasPermissionIn($organization, 'review_applications');
    }

    /**
     * Determine whether the user can view a specific template.
     */
    public function view(?User $user, ApplicationTemplate $template): bool
    {
        if (! $user) {
            return true;
        }

        return $user->hasPermissionIn($template->organization, 'manage_templates')
            || $user->hasPermissionIn($template->organization, 'review_applications');
    }

    /**
     * Determine whether the user can create a template.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $user->hasPermissionIn($organization, 'manage_templates');
    }

    /**
     * Determine whether the user can update a template.
     */
    public function update(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }

    /**
     * Determine whether the user can delete a template.
     */
    public function delete(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }
}