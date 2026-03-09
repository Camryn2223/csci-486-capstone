<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a single field within an ApplicationTemplate. Fields define the
 * label, input type, available options (for select/radio/checkbox types),
 * whether they are required, and their display order on the form.
 *
 * @property int         $id
 * @property int         $template_id
 * @property string      $label
 * @property string      $type       text | textarea | select | checkbox | radio | file | date
 * @property array|null  $options    JSON-decoded list of choices for select/radio/checkbox fields
 * @property bool        $required
 * @property int         $order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TemplateField extends Model
{
    protected $fillable = [
        'template_id',
        'label',
        'type',
        'options',
        'required',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'options'  => 'array',
            'required' => 'boolean',
            'order'    => 'integer',
        ];
    }

    /**
     * The template this field belongs to.
     *
     * @return BelongsTo<ApplicationTemplate, TemplateField>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class, 'template_id');
    }

    /**
     * All applicant answers submitted for this field across all applications.
     *
     * @return HasMany<ApplicationAnswer>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class, 'template_field_id');
    }

    /**
     * Returns true if this field type supports a predefined list of options
     * (select, checkbox, or radio).
     */
    public function hasOptions(): bool
    {
        return in_array($this->type, ['select', 'checkbox', 'radio']);
    }

    /**
     * Returns true if this field expects a file upload.
     */
    public function isFileField(): bool
    {
        return $this->type === 'file';
    }
}