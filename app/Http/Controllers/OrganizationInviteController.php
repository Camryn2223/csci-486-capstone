<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Notifications\OrganizationInviteNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Handles creation, listing, and deletion of organization invites.
 * Authorized users can generate single-use invite codes and optionally
 * send them to a specific email address. Invites that have not yet been
 * used can be revoked (deleted).
 */
class OrganizationInviteController extends Controller
{
    /**
     * Display all invites for an organization, showing which have been used
     * and which are still available.
     */
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [OrganizationInvite::class, $organization]);

        $invites = OrganizationInvite::where('organization_id', $organization->id)
            ->with('creator')
            ->latest()
            ->get();

        return view('invites.index', compact('organization', 'invites'));
    }

    /**
     * Generate a new invite code for the organization. If an email address is
     * provided, send the invite link to that address.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('create', [OrganizationInvite::class, $organization]);

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $invite = OrganizationInvite::create([
            'organization_id' => $organization->id,
            'created_by'      => $user->id,
            'code'            => OrganizationInvite::generateCode(),
            'email'           => $validated['email'] ?? null,
            'used'            => false,
        ]);

        if ($invite->hasEmailTarget()) {
            Notification::route('mail', $invite->email)
                ->notify(new OrganizationInviteNotification($invite));

            return back()->with('success', "Invite created and sent to {$invite->email}. Code: {$invite->code}");
        }

        return back()->with('success', "Invite created. Code: {$invite->code}");
    }

    /**
     * Delete (revoke) an unused invite. Already-used invites cannot be
     * deleted since they are a record of who joined.
     */
    public function destroy(Organization $organization, OrganizationInvite $invite): RedirectResponse
    {
        $this->authorize('delete', $invite);

        if ($invite->used) {
            return back()->withErrors(['invite' => 'Used invites cannot be deleted.']);
        }

        $invite->delete();

        return back()->with('success', 'Invite revoked.');
    }
}