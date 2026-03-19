<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationUserPermission;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles granting and revoking organization-scoped permissions for members.
 * Also handles promoting a member's system-wide role (interviewer <-> chairman)
 * which is a chairman-only action.
 *
 * Granting and revoking enforce the delegation rule: the acting user must pass
 * the manage-members gate AND hold the target permission themselves.
 */
class OrganizationUserPermissionController extends Controller
{
    /**
     * Display all permissions currently granted to members of the organization.
     * Grouped by user for the management UI.
     */
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [OrganizationUserPermission::class, $organization]);

        $organization->load([
            'members',
            'permissions.user',
            'permissions.permission',
        ]);

        $allPermissions = Permission::all();

        $memberPermissions = $organization->members->map(function (User $member) use ($organization, $allPermissions) {
            $granted = $organization->permissions
                ->where('user_id', $member->id)
                ->pluck('permission.name')
                ->toArray();

            return [
                'user'        => $member,
                'granted'     => $granted,
                'available'   => $allPermissions->pluck('name')->diff($granted)->values(),
            ];
        });

        return view('organization_permissions.index', compact('organization', 'memberPermissions', 'allPermissions'));
    }

    /**
     * Grant a permission to a member of the organization. The acting user must
     * pass the manage-members gate AND hold the target permission themselves.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'         => ['required', 'exists:users,id'],
            'permission_name' => ['required', 'exists:permissions,name'],
        ]);

        $this->authorize('grant', [OrganizationUserPermission::class, $organization, $validated['permission_name']]);

        $recipient = User::findOrFail($validated['user_id']);

        if (! $organization->hasMember($recipient)) {
            return back()->withErrors(['user_id' => 'This user is not a member of the organization.']);
        }

        $permission = Permission::where('name', $validated['permission_name'])->firstOrFail();

        $alreadyGranted = OrganizationUserPermission::where([
            'organization_id' => $organization->id,
            'user_id'         => $recipient->id,
            'permission_id'   => $permission->id,
        ])->exists();

        if ($alreadyGranted) {
            return back()->withErrors(['permission_name' => 'This user already has this permission.']);
        }

        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();

        OrganizationUserPermission::create([
            'organization_id' => $organization->id,
            'user_id'         => $recipient->id,
            'permission_id'   => $permission->id,
            'granted_by'      => $authenticatedUser->id,
        ]);

        return back()->with('success', "Permission \"{$validated['permission_name']}\" granted to {$recipient->name}.");
    }

    /**
     * Revoke a specific permission grant from a member. The acting user must
     * satisfy the same delegation rule required for granting.
     */    
    public function destroy(Organization $organization, OrganizationUserPermission $permission): RedirectResponse
    {
        $this->authorize('revoke', $permission);

        $userName       = $permission->user->name;
        $permissionName = $permission->permission->name;

        $permission->delete();

        return back()->with('success', "Permission \"{$permissionName}\" revoked from {$userName}.");
    }

    /**
     * Promote or demote a member's system-wide role. Only the chairman of the
     * organization may change roles. Prevents the chairman from modifying
     * their own role.
     *
     * Valid roles: interviewer, chairman
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