<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Represents a file uploaded by an applicant in support of their application.
 * Stores the original filename, the storage path, and the MIME type. Provides
 * helpers to determine the file type and resolve a download URL.
 *
 * @property int    $id
 * @property int    $application_id
 * @property int    $user_id
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
        'user_id',
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
     * The user who uploaded this document.
     *
     * @return BelongsTo<User, Document>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Resolves the publicly accessible download URL for this document's file.
     */
    public function getDownloadUrlAttribute(): string
    {
        return Storage::url($this->filepath);
    }

    /**
     * Returns true if this document is an image based on its MIME type.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mimetype, 'image/');
    }

    /**
     * Returns true if this document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mimetype === 'application/pdf';
    }
}