<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles creation, management, and display of job positions within an
 * organization. index and show methods are accessible to guests to allow
 * for public job browsing.
 */
class JobPositionController extends Controller
{
    /**
     * Display all job positions for an organization.
     */
    public function index(Request $request, Organization $organization): View
    {
        $this->authorize('viewAny', [JobPosition::class, $organization]);

        /** @var User|null $user */
        $user = Auth::user();

        // A guest is always viewing the public board.
        $isPublicView = $request->boolean('public') || !$user;
        $query = $organization->jobPositions();

        $isStaff = $user && ($user->isChairmanOf($organization) || $user->hasPermissionIn($organization, 'review_applications')) && !$isPublicView;

        if ($isStaff) {
            $query->withCount('applications');
        } else {
            $query->where('status', 'open');
        }

        $positions = $query->latest()->get();

        return view('job_positions.index', compact('organization', 'positions', 'isPublicView'));
    }

    /**
     * Show the form for creating a new job position.
     */
    public function create(Organization $organization): View
    {
        $this->authorize('create', [JobPosition::class, $organization]);

        $templates = $organization->templates()->with('fields')->withCount('fields')->get();

        return view('job_positions.create', compact('organization', 'templates'));
    }

    /**
     * Store a newly created job position.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('create', [JobPosition::class, $organization]);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'template_id'  => ['required', 'exists:application_templates,id'],
            'description'  => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'status'       => ['required', 'in:open,closed'],
        ]);

        $validated['description'] = clean($validated['description'] ?? '');
        $validated['requirements'] = clean($validated['requirements'] ?? '');

        $organization->jobPositions()->create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('organizations.job-positions.index', $organization)
            ->with('success', 'Job position created successfully.');
    }

    /**
     * Display a single job position.
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

        $templates = $organization->templates()->with('fields')->withCount('fields')->get();

        return view('job_positions.edit', compact('organization', 'jobPosition', 'templates'));
    }

    /**
     * Update an existing job position.
     */
    public function update(Request $request, Organization $organization, JobPosition $jobPosition): RedirectResponse
    {
        $this->authorize('update', $jobPosition);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'template_id'  => ['required', 'exists:application_templates,id'],
            'description'  => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'status'       => ['required', 'in:open,closed'],
        ]);

        $validated['description'] = clean($validated['description'] ?? '');
        $validated['requirements'] = clean($validated['requirements'] ?? '');

        $jobPosition->update($validated);

        return redirect()
            ->route('organizations.job-positions.show', [$organization, $jobPosition])
            ->with('success', 'Job position updated successfully.');
    }

    /**
     * Delete a job position.
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