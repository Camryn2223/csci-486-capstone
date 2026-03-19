<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Interview;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Handles scheduling, updating, canceling, and feedback submission for
 * interviews. Creating and updating interviews requires the
 * schedule_interviews permission in the relevant organization. Since
 * applicants have no system accounts, viewing is restricted to authenticated
 * staff — the assigned interviewer or users with review_applications or
 * schedule_interviews.
 */
class InterviewController extends Controller
{
    /**
     * Display the shared interview calendar for an organization, showing all
     * scheduled interviews across all interviewers. Available to any member
     * with review_applications or schedule_interviews.
     */
    public function index(Organization $organization): View
    {
        $this->authorize('view', $organization);

        /** @var User $user */
        $user = Auth::user();

        $interviews = Gate::allows('viewAny', [Interview::class, $organization])
            ? Interview::whereHas('application.jobPosition', fn ($q) => $q->where('organization_id', $organization->id))
                ->with(['application.jobPosition', 'interviewer'])
                ->upcoming()
                ->get()
            : $user->upcomingInterviews()
                ->whereHas('application.jobPosition', fn ($q) => $q->where('organization_id', $organization->id))
                ->with(['application.jobPosition'])
                ->get();

        return view('interviews.index', compact('organization', 'interviews'));
    }

    /**
     * Show the form for scheduling a new interview for an application.
     */
    public function create(Application $application): View
    {
        $this->authorize('create', [Interview::class, $application]);

        $application->load('jobPosition.organization');

        $organization = $application->jobPosition->organization;

        $interviewers = $organization->members()
            ->whereIn('role', ['interviewer', 'chairman'])
            ->get();

        return view('interviews.create', compact('application', 'organization', 'interviewers'));
    }

    /**
     * Store a newly scheduled interview. Checks for scheduling conflicts with
     * the chosen interviewer before saving.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('create', [Interview::class, $application]);

        $validated = $request->validate([
            'interviewer_id' => ['required', 'exists:users,id'],
            'scheduled_at'   => ['required', 'date', 'after:now'],
        ]);

        $interviewer = User::findOrFail($validated['interviewer_id']);

        if ($this->hasConflict($interviewer, $validated['scheduled_at'])) {
            return back()->withErrors([
                'scheduled_at' => 'The selected interviewer already has an interview scheduled at this time.',
            ]);
        }

        Interview::create([
            'application_id'  => $application->id,
            'interviewer_id'  => $validated['interviewer_id'],
            'scheduled_at'    => $validated['scheduled_at'],
            'status'          => 'scheduled',
        ]);

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Interview scheduled successfully.');
    }

    /**
     * Display a single interview's details. Visible to the assigned
     * interviewer, the applicant on the linked application, and users with
     * review_applications.
     */
    public function show(Interview $interview): View
    {
        $this->authorize('view', $interview);

        $interview->load([
            'application.jobPosition.organization',
            'interviewer',
        ]);

        return view('interviews.show', compact('interview'));
    }

    /**
     * Show the form for rescheduling or reassigning an existing interview.
     */
    public function edit(Interview $interview): View
    {
        $this->authorize('update', $interview);

        $interview->load('application.jobPosition.organization');

        $organization = $interview->application->jobPosition->organization;

        $interviewers = $organization->members()
            ->whereIn('role', ['interviewer', 'chairman'])
            ->get();

        return view('interviews.edit', compact('interview', 'organization', 'interviewers'));
    }

    /**
     * Update a scheduled interview's time or assigned interviewer. Checks for
     * conflicts with the (potentially new) interviewer before saving.
     */
    public function update(Request $request, Interview $interview): RedirectResponse
    {
        $this->authorize('update', $interview);

        $validated = $request->validate([
            'interviewer_id' => ['required', 'exists:users,id'],
            'scheduled_at'   => ['required', 'date', 'after:now'],
        ]);

        $interviewer = User::findOrFail($validated['interviewer_id']);

        if ($this->hasConflict($interviewer, $validated['scheduled_at'], $interview->id)) {
            return back()->withErrors([
                'scheduled_at' => 'The selected interviewer already has an interview scheduled at this time.',
            ]);
        }

        $interview->update([
            'interviewer_id' => $validated['interviewer_id'],
            'scheduled_at'   => $validated['scheduled_at'],
        ]);

        return redirect()
            ->route('interviews.show', $interview)
            ->with('success', 'Interview updated successfully.');
    }

    /**
     * Cancel a scheduled interview.
     */
    public function cancel(Interview $interview): RedirectResponse
    {
        $this->authorize('update', $interview);

        if (! $interview->isScheduled()) {
            return back()->withErrors(['interview' => 'Only scheduled interviews can be canceled.']);
        }

        $interview->update(['status' => 'canceled']);

        return back()->with('success', 'Interview canceled.');
    }

    /**
     * Submit feedback notes for a completed interview. Only the assigned
     * interviewer may submit feedback, and only once.
     */
    public function submitFeedback(Request $request, Interview $interview): RedirectResponse
    {
        $this->authorize('submitFeedback', $interview);

        if (! $interview->isCompleted()) {
            return back()->withErrors(['interview' => 'Feedback can only be submitted for completed interviews.']);
        }

        if ($interview->hasFeedback()) {
            return back()->withErrors(['interview' => 'Feedback has already been submitted for this interview.']);
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:5000'],
        ]);

        $interview->update([
            'notes'                 => $validated['notes'],
            'feedback_submitted_at' => now(),
        ]);

        return back()->with('success', 'Feedback submitted.');
    }

    /**
     * Mark a scheduled interview as completed.
     */
    public function complete(Interview $interview): RedirectResponse
    {
        $this->authorize('update', $interview);

        if (! $interview->isScheduled()) {
            return back()->withErrors(['interview' => 'Only scheduled interviews can be marked as completed.']);
        }

        $interview->update(['status' => 'completed']);

        return back()->with('success', 'Interview marked as completed.');
    }

    /**
     * Checks whether the given interviewer already has a scheduled interview
     * within a 1-hour window of the proposed time. Optionally excludes a
     * specific interview ID to allow updating an existing record.
     */
    private function hasConflict(User $interviewer, string $scheduledAt, ?int $excludeId = null): bool
    {
        $proposedTime = \Carbon\Carbon::parse($scheduledAt);

        return $interviewer->interviews()
            ->where('status', 'scheduled')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereBetween('scheduled_at', [
                $proposedTime->copy()->subHour(),
                $proposedTime->copy()->addHour(),
            ])
            ->exists();
    }
}