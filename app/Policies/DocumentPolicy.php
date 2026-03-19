<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Authorization policy for Document records.
 */
class DocumentPolicy
{
    /**
     * Determine whether the user can view and download a document. Requires
     * the review_applications permission in the document's organization.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->hasPermissionIn($document->application->jobPosition->organization, 'review_applications');
    }

    /**
     * Determine whether the user can delete a document. Requires the
     * review_applications permission in the document's organization.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->hasPermissionIn($document->application->jobPosition->organization, 'review_applications');
    }
}