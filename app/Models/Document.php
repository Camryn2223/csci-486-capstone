<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Resolves the publicly accessible download URL for this document's file.
     */
    public function getDownloadUrlAttribute(): string
    {
        return Storage::url($this->filepath);
    }
}