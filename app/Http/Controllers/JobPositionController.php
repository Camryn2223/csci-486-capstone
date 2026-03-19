<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
/**
 * Handles creation, management, and display of job positions within an
 * organization. Creating, editing, and deleting require the create-positions
 * gate. Viewing open positions is available to any organization member.
 */
class JobPositionController extends Controller
{
    /**
     * Display all job positions for an organization. Members who pass the
     * review-applications or create-positions gate see all statuses. Other
     * members see only open positions.
     */
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [JobPosition::class, $organization]);

        $positions = Gate::allows('review-applications', $organization)
            || Gate::allows('create-positions', $organization)
                ? $organization->jobPositions()->withCount('applications')->latest()->get()
                : $organization->openPositions()->withCount('applications')->latest()->get();

        return view('job_positions.index', compact('organization', 'positions'));
    }

    /**
     * Show the form for creating a new job position within the organization.
     */
    public function create(Organization $organization): View
    {
        $this->authorize('create', [JobPosition::class, $organization]);

        return view('job_positions.create', compact('organization'));
    }

    /**
     * Store a newly created job position in the database.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('create', [JobPosition::class, $organization]);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'requirements' => ['required', 'string'],
            'status'       => ['required', 'in:open,closed'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $organization->jobPositions()->create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('organizations.job-positions.index', $organization)
            ->with('success', 'Job position created successfully.');
    }

    /**
     * Display a job position and its application form to prospective
     * applicants, or its full detail view to interviewers and the chairman.
     */
    public function show(Organization $organization, JobPosition $jobPosition): View
    {
        $this->authorize('view', $jobPosition);

        $jobPosition->load('organization', 'creator');

        return view('job_positions.show', compact('organization', 'jobPosition'));
    }

    /**
     * Show the form for editing an existing job position.
     */
    public function edit(Organization $organization, JobPosition $jobPosition): View
    {
        $this->authorize('update', $jobPosition);

        return view('job_positions.edit', compact('organization', 'jobPosition'));
    }

    /**
     * Update an existing job position in the database.
     */
    public function update(Request $request, Organization $organization, JobPosition $jobPosition): RedirectResponse
    {
        $this->authorize('update', $jobPosition);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'requirements' => ['required', 'string'],
            'status'       => ['required', 'in:open,closed'],
        ]);

        $jobPosition->update($validated);

        return redirect()
            ->route('organizations.job-positions.show', [$organization, $jobPosition])
            ->with('success', 'Job position updated successfully.');
    }

    /**
     * Delete a job position. This will cascade-delete all associated
     * applications and interviews.
     */
    public function destroy(Organization $organization, JobPosition $jobPosition): RedirectResponse
    {
        $this->authorize('delete', $jobPosition);

        $jobPosition->delete();

        return redirect()
            ->route('organizations.job-positions.index', $organization)
            ->with('success', 'Job position deleted.');
    }
}