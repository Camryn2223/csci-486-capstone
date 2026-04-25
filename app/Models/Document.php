<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

/**
 * Represents a file uploaded alongside an application. Documents are not
 * associated with a system user since applicants do not have accounts.
 * Deletion is an interviewer-level action managed through the review
 * interface. Stores the original filename, the storage path, and the MIME
 * type.
 *
 * @property int    $id
 * @property int    $application_id
 * @property int|null $uploaded_by
 * @property string $filename
 * @property string $filepath
 * @property string $mimetype
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Document extends Model
{
    protected $fillable = [
        'application_id',
        'uploaded_by',
        'filename',
        'filepath',
        'mimetype',
    ];

    /**
     * The application this document was uploaded for.
     *
     * @return BelongsTo<Application, Document>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * The user who uploaded this document (null if uploaded by applicant).
     *
     * @return BelongsTo<User, Document>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * If this document belongs to a specific custom field, this relationship resolves it.
     * Used to prevent duplicating custom field files into the generic documents pile.
     *
     * @return HasOne<ApplicationAnswer>
     */
    public function answer(): HasOne
    {
        return $this->hasOne(ApplicationAnswer::class, 'document_id');
    }

    /**
     * Resolves the publicly accessible download URL for this document's file.
     */
    public function getDownloadUrlAttribute(): string
    {
        return Storage::url($this->filepath);
    }
}