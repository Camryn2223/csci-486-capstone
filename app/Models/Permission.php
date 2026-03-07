<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name'];

    /**
     * All users who have been granted this permission across any organization.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user_permissions')
            ->withPivot('organization_id', 'granted_by')
            ->withTimestamps();
    }
}