<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Represents a named capability that can be granted to a user within an
 * organization. Permissions are seeded at boot and never created at runtime.
 * The five system permissions are: create_positions, manage_templates,
 * review_applications, schedule_interviews, and manage_members.
 *
 * @property int    $id
 * @property string $name
 */
class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    /**
     * All users who have been granted this permission in at least one
     * organization, with organization_id and granted_by available on the pivot.
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user_permissions')
            ->withPivot(['organization_id', 'granted_by']);
    }
}