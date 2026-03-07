<?php
namespace App\Models;

use App\Models\Concerns\HasApplicantFeatures;
use App\Models\Concerns\HasChairmanFeatures;
use App\Models\Concerns\HasInterviewerFeatures;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    use HasApplicantFeatures;
    use HasInterviewerFeatures;
    use HasChairmanFeatures;

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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * All organizations this user is a member of.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Whether this user is an applicant.
     */
    public function isApplicant(): bool
    {
        return $this->role === 'applicant';
    }

    /**
     * Whether this user is an interviewer.
     */
    public function isInterviewer(): bool
    {
        return $this->role === 'interviewer';
    }

    /**
     * Whether this user is a chairman.
     */
    public function isChairman(): bool
    {
        return $this->role === 'chairman';
    }
}