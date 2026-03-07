<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

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
     * All organizations this user chairs.
     */
    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'chairman_id');
    }

    /**
     * All applications this user has submitted.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * All interviews this user has been assigned to conduct.
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'interviewer_id');
    }

    /**
     * All documents this user has uploaded.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * All application templates this user has created.
     */
    public function createdTemplates(): HasMany
    {
        return $this->hasMany(ApplicationTemplate::class, 'created_by');
    }

    /**
     * All job positions this user has created.
     */
    public function createdPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class, 'created_by');
    }
}