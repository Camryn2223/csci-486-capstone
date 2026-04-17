<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Models\User;
use App\Models\Interview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Handles creation and management of organizations. Only users with the
 * chairman role may create organizations. Viewing and updating are restricted
 * to the organization's chairman or members based on capabilities.
 */
class OrganizationController extends Controller
{
    /**
     * Display a listing of all organizations the authenticated user belongs to
     * or chairs. If a chairman has no organizations yet, redirect them to the
     * creation page.
     */
    public function index(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isChairman() && $user->ownedOrganizations()->count() === 0) {
            return redirect()->route('organizations.create');
        }

        $organizations = $user->isChairman()
            ? $user->ownedOrganizations()->withCount(['members', 'jobPositions'])->get()
            : $user->organizations()->withCount(['members', 'jobPositions'])->get();

        return view('organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new organization.
     */
    public function create(): View
    {
        $this->authorize('create', Organization::class);

        return view('organizations.create');
    }

    /**
     * Store a newly created organization in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Organization::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $organization = Organization::create([
            'name'        => $validated['name'],
            'chairman_id' => $user->id,
        ]);

        $organization->members()->attach($user->id);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Organization created successfully.');
    }

    /**
     * Display the organization dashboard, including open positions, members,
     * templates visible to the authenticated user, and their upcoming interviews.
     */
    public function show(Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load([
            'chairman',
            'openPositions',
            'templates',
            'members',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Fetch interviews for the mini calendar
        $interviews = Gate::allows('viewAny', [Interview::class, $organization])
            ? Interview::whereHas('application.jobPosition', fn ($q) => $q->where('organization_id', $organization->id))
                ->with(['application', 'application.jobPosition', 'interviewers'])
                ->upcoming()
                ->get()
            : $user->upcomingInterviews()
                ->whereHas('application.jobPosition', fn ($q) => $q->where('organization_id', $organization->id))
                ->with(['application', 'application.jobPosition', 'interviewers'])
                ->get();

        // Fetch applications that have not been scheduled yet
        $unscheduledApplications = collect();
        if ($user->hasPermissionIn($organization, 'schedule_interviews')) {
            $unscheduledApplications = $organization->applications()
                ->active()
                ->doesntHave('interviews')
                ->with('jobPosition')
                ->orderBy('applications.created_at', 'desc')
                ->get();
        }

        return view('organizations.show', compact('organization', 'interviews', 'unscheduledApplications'));
    }

    /**
     * Show the form for editing the organization's details. Only the chairman
     * may edit.
     */
    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        return view('organizations.edit', compact('organization'));
    }

    /**
     * Update the organization's details in the database.
     */
    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $organization->update($validated);

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Organization updated successfully.');
    }

    /**
     * Delete the organization and all associated data.
     */
    public function destroy(Organization $organization): RedirectResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organization deleted.');
    }

    /**
     * Show the team management page listing all members and existing invites.
     */
    public function members(Organization $organization): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if the user has permission to manage members OR create invites
        if (!$user->hasPermissionIn($organization, 'manage_members') && !$user->hasPermissionIn($organization, 'create_invites')) {
            abort(403, 'This action is unauthorized.');
        }

        $organization->load(['members']);
        
        $invites = [];
        if ($user->hasPermissionIn($organization, 'create_invites')) {
            $invites = OrganizationInvite::where('organization_id', $organization->id)
                ->with('creator')
                ->latest()
                ->get();
        }

        return view('organizations.members', compact('organization', 'invites'));
    }

    /**
     * Remove a member from the organization and delete their account.
     * This will also force-logout the user by deleting their sessions.
     */
    public function removeMember(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageMembers', $organization);

        if ($organization->chairman_id === $user->id) {
            return back()->withErrors(['user' => 'The chairman cannot be removed from the organization.']);
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();
        Cache::forget("user.{$user->id}.org.{$organization->id}.permissions");
        $userName = $user->name;
        $user->delete();

        return back()->with('success', "{$userName} has been removed, their account has been deleted, and they have been logged out.");
    }

    /**
     * Show a paginated list of all applications across all jobs for the organization.
     */
    public function applications(Organization $organization): View
    {
        $this->authorize('review-applications', $organization);

        $applications = $organization->applications()
            ->with(['jobPosition', 'interviews'])
            ->orderBy('applications.created_at', 'desc')
            ->paginate(15);

        return view('organizations.applications', compact('organization', 'applications'));
    }
}