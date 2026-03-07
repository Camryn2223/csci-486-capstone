<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateField extends Model
{
    protected $fillable = [
        'template_id',
        'label',
        'type',
        'options',
        'required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    /**
     * The template this field belongs to.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class);
    }

    /**
     * All answers submitted for this field across all applications.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class);
    }
}