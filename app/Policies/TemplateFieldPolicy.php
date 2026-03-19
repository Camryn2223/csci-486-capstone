<?php

namespace App\Policies;

use App\Models\ApplicationTemplate;
use App\Models\TemplateField;
use App\Models\User;

/**
 * Authorization policy for TemplateField records. All checks resolve the
 * field's parent template to determine the organization.
 */
class TemplateFieldPolicy
{
    /**
     * Determine whether the user can add fields to a given template. Requires
     * the manage_templates permission in the template's organization.
     */
    public function create(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }

    /**
     * Determine whether the user can view a specific template field. Requires
     * the manage_templates or review_applications permission in the field's
     * organization.
     */
    public function view(User $user, TemplateField $field): bool
    {
        return $user->hasPermissionIn($field->template->organization, 'manage_templates')
            || $user->hasPermissionIn($field->template->organization, 'review_applications');
    }

    /**
     * Determine whether the user can update a template field. Requires the
     * manage_templates permission in the field's organization.
     */
    public function update(User $user, TemplateField $field): bool
    {
        return $user->hasPermissionIn($field->template->organization, 'manage_templates');
    }

    /**
     * Determine whether the user can delete a template field. Requires the
     * manage_templates permission in the field's organization.
     */
    public function delete(User $user, TemplateField $field): bool
    {
        return $user->hasPermissionIn($field->template->organization, 'manage_templates');
    }

    /**
     * Determine whether the user can reorder fields on a template. Requires
     * the manage_templates permission in the template's organization.
     */
    public function reorder(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }
}