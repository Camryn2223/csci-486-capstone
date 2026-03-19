<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Interview;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\InterviewScheduledNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Handles scheduling, updating, canceling, and feedback submission for
 * interviews.
 */
class InterviewController extends Controller
{
    /**
     * Display the shared interview calendar for an organization.
     */
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [Interview::class, $organization]);

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
     * Show the form for scheduling a new interview.
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
     * Store a newly scheduled interview and notify the applicant.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('create', [Interview::class, $application]);

        $validated = $request->validate([
            'interviewer_id' => ['required', 'exists:users,id'],
            'scheduled_at'   => ['required', 'date', 'after:now'],
            'email_subject'  => ['required', 'string', 'max:255'],
            'email_body'     => ['required', 'string', 'max:5000'],
        ]);

        $interviewer = User::findOrFail($validated['interviewer_id']);

        if ($this->hasConflict($interviewer, $validated['scheduled_at'])) {
            return back()->withErrors([
                'scheduled_at' => 'The selected interviewer already has an interview scheduled at this time.',
            ])->withInput();
        }

        $interview = Interview::create([
            'application_id'  => $application->id,
            'interviewer_id'  => $validated['interviewer_id'],
            'scheduled_at'    => $validated['scheduled_at'],
            'status'          => 'scheduled',
        ]);

        // Send notification to the applicant
        Notification::route('mail', $application->applicant_email)
            ->notify(new InterviewScheduledNotification(
                $interview, 
                $validated['email_subject'], 
                $validated['email_body']
            ));

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Interview scheduled and email sent to applicant.');
    }

    /**
     * Display a single interview's details.
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
     * Update a scheduled interview's time or assigned interviewer.
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
     * Submit feedback notes for a completed interview.
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
     * within a 1-hour window of the proposed time.
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