<?php

namespace App\Policies;

use App\Models\ApplicationTemplate;
use App\Models\Organization;
use App\Models\User;

/**
 * Authorization policy for ApplicationTemplate records. All write actions
 * require the manage_templates permission in the template's organization.
 * Viewing is also permitted to users with review_applications.
 */
class ApplicationTemplatePolicy
{
    /**
     * Determine whether the user can list templates for an organization.
     * Requires manage_templates or review_applications.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'manage_templates')
            || $user->hasPermissionIn($organization, 'review_applications');
    }

    /**
     * Determine whether the user can view a specific template. Requires
     * manage_templates or review_applications in the template's organization.
     */
    public function view(User $user, ApplicationTemplate $template): bool
    {
        $organization = $template->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'manage_templates')
            || $user->hasPermissionIn($organization, 'review_applications');
    }

    /**
     * Determine whether the user can create a template in an organization.
     * Requires manage_templates.
     */
    public function create(User $user, Organization $organization): bool
    {
        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'manage_templates');
    }

    /**
     * Determine whether the user can update a template. Requires
     * manage_templates in the template's organization.
     */
    public function update(User $user, ApplicationTemplate $template): bool
    {
        $organization = $template->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'manage_templates');
    }

    /**
     * Determine whether the user can delete a template. Requires
     * manage_templates in the template's organization.
     */
    public function delete(User $user, ApplicationTemplate $template): bool
    {
        $organization = $template->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'manage_templates');
    }
}