<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Authorization policy for Document records. All authenticated actions (viewing and deleting) require
 * review_applications in the document's organization.
 */
class DocumentPolicy
{
    /**
     * Determine whether the user can view and download a document. Requires
     * review_applications in the document's organization.
     */
    public function view(User $user, Document $document): bool
    {
        $organization = $document->application->jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'review_applications');
    }

    /**
     * Determine whether the user can delete a document. Requires
     * review_applications in the document's organization.
     */
    public function delete(User $user, Document $document): bool
    {
        $organization = $document->application->jobPosition->organization;

        return $user->isChairmanOf($organization)
            || $user->hasPermissionIn($organization, 'review_applications');
    }
}