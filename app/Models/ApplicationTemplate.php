<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationTemplate extends Model
{
    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
    ];

    /**
     * The organization this template belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All fields defined in this template, ordered by sort_order.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(TemplateField::class, 'template_id')->orderBy('sort_order');
    }

    /**
     * All applications submitted using this template.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'template_id');
    }
}