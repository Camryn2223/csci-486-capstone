<?php

namespace App\Http\Controllers;

use App\Enums\Permission as PermissionEnum;
use App\Models\Organization;
use App\Models\OrganizationUserPermission;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Handles granting and revoking organization-scoped permissions for members.
 * Also handles promoting a member's system-wide role (interviewer <-> chairman)
 * which is a chairman-only action.
 */
class OrganizationUserPermissionController extends Controller
{
    /**
     * Display all permissions currently granted to members of the organization.
     */
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [OrganizationUserPermission::class, $organization]);

        $organization->load(['members']);
        
        // Use the Enum directly so checkboxes ALWAYS show up, even if the database wasn't seeded yet.
        $allPermissions = PermissionEnum::cases();
        
        /** @var User $actingUser */
        $actingUser = Auth::user();
        $actingUserPerms = $actingUser->permissionNamesIn($organization);

        $memberPermissions = $organization->members->map(function (User $member) use ($organization) {
            return [
                'user'    => $member,
                'granted' => $member->permissionNamesIn($organization),
            ];
        });

        return view('organization_permissions.index', compact('organization', 'memberPermissions', 'allPermissions', 'actingUserPerms'));
    }

    /**
     * Sync permissions for a user based on an array of requested permissions.
     * Enforces that the acting user can only grant or revoke permissions they
     * hold themselves.
     */
    public function sync(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('sync', [OrganizationUserPermission::class, $organization]);

        if (! $organization->hasMember($user)) {
            return back()->withErrors(['user' => 'User is not a member of this organization.']);
        }

        if ($user->isChairmanOf($organization)) {
            return back()->withErrors(['user' => 'The chairman inherently possesses all permissions and cannot be modified.']);
        }

        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $submittedPerms = $validated['permissions'] ?? [];
        
        /** @var User $actingUser */
        $actingUser = Auth::user();
        $actingUserPerms = $actingUser->permissionNamesIn($organization);

        $targetUserPerms = $user->permissionNamesIn($organization);

        $toAdd = array_diff($submittedPerms, $targetUserPerms);
        $toRemove = array_diff($targetUserPerms, $submittedPerms);

        // Filter out permissions the acting user is not allowed to grant/revoke
        $toAdd = array_intersect($toAdd, $actingUserPerms);
        $toRemove = array_intersect($toRemove, $actingUserPerms);

        if (!empty($toAdd)) {
            foreach ($toAdd as $permName) {
                // Self-healing: Create the permission in the DB if it doesn't exist
                $permission = Permission::firstOrCreate(['name' => $permName]);
                
                OrganizationUserPermission::firstOrCreate([
                    'organization_id' => $organization->id,
                    'user_id'         => $user->id,
                    'permission_id'   => $permission->id,
                ], [
                    'granted_by'      => $actingUser->id,
                ]);
            }
        }

        if (!empty($toRemove)) {
            $permissionIds = Permission::whereIn('name', $toRemove)->pluck('id');
            OrganizationUserPermission::where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->whereIn('permission_id', $permissionIds)
                ->delete();
        }

        Cache::forget("user.{$user->id}.org.{$organization->id}.permissions");

        return back()->with('success', "Permissions updated for {$user->name}.");
    }

    /**
     * Promote or demote a member's system-wide role. Only the chairman of the
     * organization may change roles. Prevents the chairman from modifying
     * their own role.
     */
    public function updateRole(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('update', $organization);

        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();

        if ($user->id === $authenticatedUser->id) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        if (! $organization->hasMember($user)) {
            return back()->withErrors(['user' => 'This user is not a member of the organization.']);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:interviewer,chairman'],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "{$user->name}'s role has been updated to {$validated['role']}.");
    }
}