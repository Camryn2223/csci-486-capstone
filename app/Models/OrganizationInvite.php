<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Represents a single-use invite that grants one person the ability to
 * register and join an organization. Invites can optionally target a specific
 * email address, in which case an invite link is emailed to the recipient.
 * Once used, the invite cannot be reused, preventing removed members from
 * rejoining with an old code.
 *
 * @property int         $id
 * @property int         $organization_id
 * @property int         $created_by
 * @property string      $code
 * @property string|null $email
 * @property bool        $used
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrganizationInvite extends Model
{
    protected $fillable = [
        'organization_id',
        'created_by',
        'code',
        'email',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
        ];
    }

    /**
     * The organization this invite belongs to.
     *
     * @return BelongsTo<Organization, OrganizationInvite>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who created this invite.
     *
     * @return BelongsTo<User, OrganizationInvite>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Find an unused invite by its code. Returns null if not found or already
     * used.
     */
    public static function findUnused(string $code): ?self
    {
        return self::where('code', $code)->where('used', false)->first();
    }

    /**
     * Generate a unique 8-character uppercase invite code.
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Mark this invite as used so it cannot be reused.
     */
    public function markUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Returns true if this invite was targeted at a specific email address.
     */
    public function hasEmailTarget(): bool
    {
        return ! is_null($this->email);
    }
}