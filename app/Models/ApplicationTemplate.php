<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a reusable form template that defines what fields an applicant
 * must fill out when applying for a job. Templates belong to an organization
 * and are composed of ordered TemplateField records.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $created_by
 * @property string $name
 * @property bool $request_name
 * @property bool $request_email
 * @property bool $request_phone
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ApplicationTemplate extends Model
{
    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'request_name',
        'request_email',
        'request_phone',
    ];

    protected function casts(): array
    {
        return [
            'request_name' => 'boolean',
            'request_email' => 'boolean',
            'request_phone' => 'boolean',
        ];
    }

    /**
     * The organization this template belongs to.
     *
     * @return BelongsTo<Organization, ApplicationTemplate>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who created this template.
     *
     * @return BelongsTo<User, ApplicationTemplate>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The fields that make up this template, ordered by sort order ascending.
     *
     * @return HasMany<TemplateField>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(TemplateField::class, 'template_id')->orderBy('order');
    }

    /**
     * All applications that were submitted using this template.
     *
     * @return HasMany<Application>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'template_id');
    }

    /**
     * Returns true if this template contains at least one required field.
     */
    public function hasRequiredFields(): bool
    {
        return $this->fields()->where('required', true)->exists();
    }
}