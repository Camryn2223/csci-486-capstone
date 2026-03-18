<?php

namespace App\Policies;

use App\Models\ApplicationTemplate;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization policy for ApplicationTemplate records. All write actions
 * require the manage-templates gate. Viewing is also permitted to users who
 * pass the review-applications gate.
 */
class ApplicationTemplatePolicy
{
    /**
     * Determine whether the user can list templates for an organization.
     * Requires manage-templates or review-applications.
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $organization)
            || Gate::forUser($user)->allows('review-applications', $organization);
    }

    /**
     * Determine whether the user can view a specific template. Requires
     * manage-templates or review-applications in the template's organization.
     */
    public function view(User $user, ApplicationTemplate $template): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $template->organization)
            || Gate::forUser($user)->allows('review-applications', $template->organization);
    }

    /**
     * Determine whether the user can create a template in an organization.
     * Requires manage-templates.
     */
    public function create(User $user, Organization $organization): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $organization);
    }

    /**
     * Determine whether the user can update a template. Requires
     * manage-templates in the template's organization.
     */
    public function update(User $user, ApplicationTemplate $template): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $template->organization);
    }

    /**
     * Determine whether the user can delete a template. Requires
     * manage-templates in the template's organization.
     */
    public function delete(User $user, ApplicationTemplate $template): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $template->organization);
    }
}