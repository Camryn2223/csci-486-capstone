<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model for the organization_user_permissions table. Records a single
 * permission granted to a user within a specific organization, along with who
 * granted it. Enforces a composite unique constraint so the same permission
 * cannot be granted to the same user in the same organization twice.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property int $permission_id
 * @property int $granted_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrganizationUserPermission extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'permission_id',
        'granted_by',
    ];

    /**
     * The organization this permission grant is scoped to.
     *
     * @return BelongsTo<Organization, OrganizationUserPermission>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who has been granted this permission.
     *
     * @return BelongsTo<User, OrganizationUserPermission>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The permission that was granted.
     *
     * @return BelongsTo<Permission, OrganizationUserPermission>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * The user who granted this permission.
     *
     * @return BelongsTo<User, OrganizationUserPermission>
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}