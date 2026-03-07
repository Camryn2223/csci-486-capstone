<?php
namespace App\Policies;

use App\Models\ApplicationTemplate;
use App\Models\User;

class ApplicationTemplatePolicy
{
    /**
     * Users with manage_templates permission in the organization can create templates.
     */
    public function create(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }

    /**
     * Users with review_applications or manage_templates permission can view templates.
     */
    public function view(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'review_applications')
            || $user->hasPermissionIn($template->organization, 'manage_templates');
    }

    /**
     * Only users with manage_templates permission can update templates.
     */
    public function update(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }

    /**
     * Only users with manage_templates permission can delete templates.
     */
    public function delete(User $user, ApplicationTemplate $template): bool
    {
        return $user->hasPermissionIn($template->organization, 'manage_templates');
    }
}