<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Authorization policy for Document records. All authenticated actions require the review-applications
 * gate in the document's organization.
 */
class DocumentPolicy
{
    /**
     * Determine whether the user can view and download a document. Requires
     * the review-applications gate in the document's organization.
     */
    public function view(User $user, Document $document): bool
    {
        return Gate::forUser($user)->allows('review-applications', $document->application->jobPosition->organization);
    }

    /**
     * Determine whether the user can delete a document. Requires the
     * review-applications gate in the document's organization.
     */
    public function delete(User $user, Document $document): bool
    {
        return Gate::forUser($user)->allows('review-applications', $document->application->jobPosition->organization);
    }
}