<?php
namespace App\Models\Concerns;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasApplicantFeatures
{
    /**
     * All applications this user has submitted.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * All documents this user has uploaded.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}