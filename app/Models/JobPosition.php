<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosition extends Model
{
    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'requirements',
        'status',
        'created_by',
    ];

    /**
     * The organization this position belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who created this position.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All applications submitted for this position.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}