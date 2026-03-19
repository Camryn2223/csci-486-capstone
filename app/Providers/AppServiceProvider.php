<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers application-level gate definitions for all organization-scoped
 * permissions. Gates are generated automatically from the Permission enum.
 *
 * Each gate is named by replacing underscores with hyphens in the permission
 * value, e.g. create_positions -> create-positions. Every gate follows the
 * same rule: pass if the user holds the permission in that organization
 * (the hasPermissionIn method naturally exempts the organization's chairman).
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        foreach (Permission::cases() as $permission) {
            $gateName = str_replace('_', '-', $permission->value);

            Gate::define($gateName, function (User $user, Organization $organization) use ($permission): bool {
                return $user->hasPermissionIn($organization, $permission->value);
            });
        }
    }
}