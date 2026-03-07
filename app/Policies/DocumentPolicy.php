<?php
namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Only applicants can upload documents.
     */
    public function create(User $user): bool
    {
        return $user->isApplicant();
    }

    /**
     * Uploading user can view their own. Users with review_applications can view any.
     */
    public function view(User $user, Document $document): bool
    {
        return $document->user_id === $user->id
            || $user->hasPermissionIn(
                $document->application->jobPosition->organization,
                'review_applications'
            );
    }

    /**
     * Only the uploading user can delete their own documents.
     */
    public function delete(User $user, Document $document): bool
    {
        return $document->user_id === $user->id;
    }
}