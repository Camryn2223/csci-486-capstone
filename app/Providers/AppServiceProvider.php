<?php

namespace App\Providers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Grants the ability to create, edit, and delete job positions within
         * an organization.
         */
        Gate::define('create-positions', function (User $user, Organization $organization): bool {
            return $user->isChairmanOf($organization)
                || $user->hasPermissionIn($organization, 'create_positions');
        });
 
        /**
         * Grants the ability to create, edit, and delete application templates
         * and their fields within an organization.
         */
        Gate::define('manage-templates', function (User $user, Organization $organization): bool {
            return $user->isChairmanOf($organization)
                || $user->hasPermissionIn($organization, 'manage_templates');
        });
 
        /**
         * Grants the ability to view and manage applications and their
         * associated documents within an organization.
         */
        Gate::define('review-applications', function (User $user, Organization $organization): bool {
            return $user->isChairmanOf($organization)
                || $user->hasPermissionIn($organization, 'review_applications');
        });
 
        /**
         * Grants the ability to schedule, update, and cancel interviews within
         * an organization.
         */
        Gate::define('schedule-interviews', function (User $user, Organization $organization): bool {
            return $user->isChairmanOf($organization)
                || $user->hasPermissionIn($organization, 'schedule_interviews');
        });
 
        /**
         * Grants the ability to grant and revoke permissions for other members
         * within an organization.
         */
        Gate::define('manage-members', function (User $user, Organization $organization): bool {
            return $user->isChairmanOf($organization)
                || $user->hasPermissionIn($organization, 'manage_members');
        });
    }
}
