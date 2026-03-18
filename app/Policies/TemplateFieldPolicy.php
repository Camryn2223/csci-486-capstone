<?php

namespace App\Policies;

use App\Models\ApplicationTemplate;
use App\Models\TemplateField;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization policy for TemplateField records. All checks resolve the
 * field's parent template to determine the organization and then defer to the
 * manage-templates or review-applications gate.
 */
class TemplateFieldPolicy
{
    /**
     * Determine whether the user can add fields to a given template. Requires
     * the manage-templates gate in the template's organization.
     */
    public function create(User $user, ApplicationTemplate $template): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $template->organization);
    }

    /**
     * Determine whether the user can view a specific template field. Requires
     * the manage-templates or review-applications gate in the field's
     * organization.
     */
    public function view(User $user, TemplateField $field): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $field->template->organization)
            || Gate::forUser($user)->allows('review-applications', $field->template->organization);
    }

    /**
     * Determine whether the user can update a template field. Requires the
     * manage-templates gate in the field's organization.
     */
    public function update(User $user, TemplateField $field): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $field->template->organization);
    }

    /**
     * Determine whether the user can delete a template field. Requires the
     * manage-templates gate in the field's organization.
     */
    public function delete(User $user, TemplateField $field): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $field->template->organization);
    }

    /**
     * Determine whether the user can reorder fields on a template. Requires
     * the manage-templates gate in the template's organization.
     */
    public function reorder(User $user, ApplicationTemplate $template): bool
    {
        return Gate::forUser($user)->allows('manage-templates', $template->organization);
    }
}