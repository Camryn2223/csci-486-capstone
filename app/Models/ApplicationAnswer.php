<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single applicant's answer to one TemplateField within an
 * Application. Each answer stores the raw value submitted for that field.
 *
 * @property int         $id
 * @property int         $application_id
 * @property int         $template_field_id
 * @property string|null $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ApplicationAnswer extends Model
{
    protected $fillable = [
        'application_id',
        'template_field_id',
        'value',
    ];

    /**
     * The application this answer belongs to.
     *
     * @return BelongsTo<Application, ApplicationAnswer>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * The template field this answer responds to.
     *
     * @return BelongsTo<TemplateField, ApplicationAnswer>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(TemplateField::class, 'template_field_id');
    }
}