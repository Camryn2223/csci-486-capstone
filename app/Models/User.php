<?php

namespace App\Models;

use App\Models\Concerns\HasChairmanFeatures;
use App\Models\Concerns\HasInterviewerFeatures;
use App\Models\Concerns\HasPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Represents a system user. A user holds one of two roles - interviewer,
 * or chairman - which controls what features they can access.
 * Role-specific relationships and behaviour are split into trait concerns to
 * keep this class focused on identity and shared logic only.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role interviewer | chairman
 * @property \Carbon\Carbon|null $email_verified_at
 * @property array|null $dashboard_layout
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    use HasInterviewerFeatures;
    use HasChairmanFeatures;
    use HasPermissions;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'dashboard_layout',
        'interview_email_notifications',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => 'string',
            'dashboard_layout'  => 'array',
                    'interview_email_notifications'  => 'boolean',
        ];
    }

    /**
     * All organizations this user belongs to as a member.
     *
     * @return BelongsToMany<Organization>
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user');
    }

    /**
     * Returns true if this user's role is interviewer.
     */
    public function isInterviewer(): bool
    {
        return $this->role === 'interviewer';
    }

    /**
     * Returns true if this user's role is chairman.
     */
    public function isChairman(): bool
    {
        return $this->role === 'chairman';
    }

    /**
     * Returns true if this user is a member of the given organization.
     */
    public function isMemberOf(Organization $organization): bool
    {
        return $this->organizations()->where('organizations.id', $organization->id)->exists();
    }

    /**
     * Returns true if this user is the chairman of the given organization.
     */
    public function isChairmanOf(Organization $organization): bool
    {
        return $organization->chairman_id === $this->id;
    }
}