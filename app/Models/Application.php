<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * Represents an anonymous submission for a specific job position. Applications
 * are submitted by external applicants who do not have system accounts.
 * Contact information is stored directly on the record and all further
 * communication with the applicant happens via email. The record aggregates
 * the applicant's form answers, uploaded documents, and scheduled interviews.
 *
 * @property int $id
 * @property int $job_position_id
 * @property int $template_id
 * @property string $applicant_name
 * @property string $applicant_email
 * @property string|null $applicant_phone
 * @property string $status submitted | under_review | needs_chairman_review | no_longer_under_consideration | withdrawn
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Application extends Model
{
    protected $fillable = [
        'job_position_id',
        'template_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * The job position this application was submitted for.
     *
     * @return BelongsTo<JobPosition, Application>
     */
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    /**
     * The application template used when this application was submitted.
     *
     * @return BelongsTo<ApplicationTemplate, Application>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class, 'template_id');
    }

    /**
     * The applicant's answers to each template field.
     *
     * @return HasMany<ApplicationAnswer>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class);
    }

    /**
     * Documents uploaded alongside this application.
     *
     * @return HasMany<Document>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Interviews scheduled for this application.
     *
     * @return HasMany<Interview>
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    /**
     * Scope to applications that are still in an active review state -
     * excludes withdrawn and no longer under consideration.
     * (Prefixed with applications table to prevent ambiguous column errors during HasManyThrough queries)
     *
     * @param  Builder<Application> $query
     * @return Builder<Application>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('applications.status', ['withdrawn', 'no_longer_under_consideration']);
    }

    /**
     * Scope to applications with status = withdrawn.
     *
     * @param  Builder<Application> $query
     * @return Builder<Application>
     */
    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->where('applications.status', 'withdrawn');
    }

    /**
     * Scope to applications with status = under_review.
     *
     * @param  Builder<Application> $query
     * @return Builder<Application>
     */
    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('applications.status', 'under_review');
    }

    /**
     * Returns true if this application has been withdrawn.
     */
    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }

    /**
     * Returns true if this application is in an active review state.
     */
    public function isActive(): bool
    {
        return ! in_array($this->status, ['withdrawn', 'no_longer_under_consideration']);
    }

    /**
     * Returns true if another application from the same email address already
     * exists for this job position. Used to prevent duplicate submissions.
     */
    public function isDuplicateOf(string $email, int $jobPositionId): bool
    {
        return self::where('applicant_email', $email)
            ->where('job_position_id', $jobPositionId)
            ->whereNot('id', $this->id ?? 0)
            ->exists();
    }

    /**
     * Traverses through the job position to resolve the organization this
     * application ultimately belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough<Organization>
     */
    public function organization(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Organization::class,
            JobPosition::class,
            'id',
            'id',
            'job_position_id',
            'organization_id'
        );
    }
}