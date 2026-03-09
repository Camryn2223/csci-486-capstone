<?php

namespace App\Policies;

use App\Models\ApplicationTemplate;
use App\Models\TemplateField;
use App\Models\User;

/**
 * Authorization policy for TemplateField records. Controls who can add, view,
 * update, reorder, and delete individual fields on an application template.
 *
 * All checks resolve the field's parent template to determine the organization,
 * then verify the user holds manage_templates in that organization. This mirrors
 * ApplicationTemplatePolicy but operates at the field level so controllers
 * managing individual fields have a clean authorize() call target.
 */
class TemplateFieldPolicy
{
    /**
     * Determine whether the user can add fields to a given template. Requires
     * manage_templates in the template's organization.
     */
    public function create(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }

    /**
     * Determine whether the user can view a specific template field. Requires
     * manage_templates or review_applications in the field's organization.
     */
    public function view(User $user, TemplateField $field): bool
    {
        $organization = $field->template->organization;

        return $user->hasPermissionIn($organization, 'manage_templates')
            || $user->hasPermissionIn($organization, 'review_applications');
    }

    /**
     * Determine whether the user can update a template field (label, type,
     * options, required flag, or sort order). Requires manage_templates in
     * the field's organization.
     */
    public function update(User $user, TemplateField $field): bool
    {
        return $user->hasPermissionIn($field->template->organization, 'manage_templates');
    }

    /**
     * Determine whether the user can delete a template field. Requires
     * manage_templates in the field's organization.
     */
    public function delete(User $user, TemplateField $field): bool
    {
        return $user->hasPermissionIn($field->template->organization, 'manage_templates');
    }

    /**
     * Determine whether the user can reorder fields on a template. Treated as
     * an update-level action — requires manage_templates in the template's
     * organization.
     */
    public function reorder(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }
}