<?php

namespace App\Models;

use App\Models\Concerns\HasApplicantFeatures;
use App\Models\Concerns\HasChairmanFeatures;
use App\Models\Concerns\HasInterviewerFeatures;
use App\Models\Concerns\HasPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    use HasApplicantFeatures;
    use HasInterviewerFeatures;
    use HasChairmanFeatures;
    use HasPermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
     * Returns true if this user's role is applicant.
     */
    public function isApplicant(): bool
    {
        return $this->role === 'applicant';
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