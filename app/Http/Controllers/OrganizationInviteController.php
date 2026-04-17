<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Notifications\OrganizationInviteNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Handles creation and deletion of organization invites.
 * Authorized users can generate single-use invite codes and optionally
 * send them to a specific email address. Both unused (revoked) and used
 * invites can be deleted if no longer needed.
 */
class OrganizationInviteController extends Controller
{
    /**
     * Redirect any old requests targeting the separate invites index page 
     * back to the newly consolidated members view.
     */
    public function index(Organization $organization): RedirectResponse
    {
        return redirect()->route('organizations.members', $organization);
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
     * Delete an invite. Unused invites are "revoked", used invites are purely
     * deleted to clean up records.
     */
    public function destroy(Organization $organization, OrganizationInvite $invite): RedirectResponse
    {
        $this->authorize('delete', $invite);

        $wasUsed = $invite->used;
        $invite->delete();

        return back()->with('success', $wasUsed ? 'Used invite deleted.' : 'Invite revoked.');
    }
}