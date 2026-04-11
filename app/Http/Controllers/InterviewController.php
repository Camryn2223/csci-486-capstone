<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Interview;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\InterviewScheduledNotification;
use App\Notifications\InterviewRescheduledNotification;
use App\Notifications\InterviewCanceledNotification;
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
                ->with(['application', 'application.jobPosition', 'interviewers'])
                ->upcoming()
                ->get()
            : $user->upcomingInterviews()
                ->whereHas('application.jobPosition', fn ($q) => $q->where('organization_id', $organization->id))
                ->with(['application', 'application.jobPosition', 'interviewers'])
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
            ->with(['interviews' => function($q) {
                $q->scheduled()->with('application.jobPosition', 'interviewers');
            }])
            ->get();

        $schedules = $this->buildSchedulesJson($interviewers);

        return view('interviews.create', compact('application', 'organization', 'interviewers', 'schedules'));
    }

    /**
     * Store a newly scheduled interview and assign all selected interviewers to
     * it. Notifies the applicant and every assigned interviewer.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('create', [Interview::class, $application]);

        $validated = $request->validate([
            'interviewer_ids'   => ['required', 'array'],
            'interviewer_ids.*' => ['exists:users,id'],
            'scheduled_at'      => ['required', 'date', 'after:now'],
            'email_subject'     => ['required', 'string', 'max:255'],
            'email_body'        => ['required', 'string', 'max:5000'],
        ]);

        // Verify conflicts for all selected interviewers before creating anything
        foreach ($validated['interviewer_ids'] as $intId) {
            $interviewer = User::findOrFail($intId);
            if ($this->hasConflict($interviewer, $validated['scheduled_at'])) {
                return back()->withErrors([
                    'scheduled_at' => "Interviewer {$interviewer->name} already has an interview scheduled at this time.",
                ])->withInput();
            }
        }

        $interview = Interview::create([
            'application_id'  => $application->id,
            'scheduled_at'    => $validated['scheduled_at'],
            'status'          => 'scheduled',
        ]);
        
        $interview->interviewers()->attach($validated['interviewer_ids']);

        // Load interviewers to iterate over for emails
        $interview->load('interviewers');

        $applicantEmail = $application->applicant_email;
        
        if (!str_contains($applicantEmail, 'no-email-')) {
            Notification::route('mail', $applicantEmail)
                ->notify(new InterviewScheduledNotification(
                    $interview, 
                    $validated['email_subject'], 
                    $validated['email_body'],
                    $application->applicant_name
                ));
        }

        foreach ($interview->interviewers as $interviewer) {
            Notification::route('mail', $interviewer->email)
                ->notify(new InterviewScheduledNotification(
                    $interview, 
                    $validated['email_subject'], 
                    $validated['email_body'],
                    $interviewer->name
                ));
        }

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Interview scheduled successfully.');
    }

    /**
     * Display a single interview's details.
     */
    public function show(Interview $interview): View
    {
        $this->authorize('view', $interview);

        $interview->load([
            'application.jobPosition.organization',
            'interviewers',
        ]);

        return view('interviews.show', compact('interview'));
    }

    /**
     * Show the form for rescheduling or reassigning an existing interview.
     */
    public function edit(Interview $interview): View
    {
        $this->authorize('update', $interview);

        $interview->load('application.jobPosition.organization', 'interviewers');

        $organization = $interview->application->jobPosition->organization;

        $interviewers = $organization->members()
            ->whereIn('role', ['interviewer', 'chairman'])
            ->with(['interviews' => function($q) use ($interview) {
                $q->scheduled()
                  ->where('interviews.id', '!=', $interview->id)
                  ->with('application.jobPosition', 'interviewers');
            }])
            ->get();
            
        $schedules = $this->buildSchedulesJson($interviewers);

        return view('interviews.edit', compact('interview', 'organization', 'interviewers', 'schedules'));
    }

    /**
     * Update a scheduled interview's time or assigned interviewers.
     * Automatically notifies everyone involved.
     */
    public function update(Request $request, Interview $interview): RedirectResponse
    {
        $this->authorize('update', $interview);

        $validated = $request->validate([
            'interviewer_ids'   => ['required', 'array'],
            'interviewer_ids.*' => ['exists:users,id'],
            'scheduled_at'      => ['required', 'date', 'after:now'],
        ]);

        // Check conflicts for everyone
        foreach ($validated['interviewer_ids'] as $intId) {
            $interviewer = User::findOrFail($intId);
            // Allow the interviewer to exclude the CURRENT interview ID to avoid self-conflict if they are already on it
            $excludeId = $interview->interviewers->contains('id', $intId) ? $interview->id : null;
            if ($this->hasConflict($interviewer, $validated['scheduled_at'], $excludeId)) {
                return back()->withErrors([
                    'scheduled_at' => "Interviewer {$interviewer->name} already has an interview scheduled at this time.",
                ]);
            }
        }

        $oldScheduledAt = $interview->scheduled_at;

        $interview->update([
            'scheduled_at'   => $validated['scheduled_at'],
        ]);

        $interview->interviewers()->sync($validated['interviewer_ids']);

        // Reload to get fresh relations
        $interview->load('interviewers', 'application.jobPosition.organization');

        $applicantEmail = $interview->application->applicant_email;
        if (!str_contains($applicantEmail, 'no-email-')) {
            Notification::route('mail', $applicantEmail)
                ->notify(new InterviewRescheduledNotification($interview, $oldScheduledAt, $interview->application->applicant_name));
        }
        
        foreach ($interview->interviewers as $interviewer) {
            Notification::route('mail', $interviewer->email)
                ->notify(new InterviewRescheduledNotification($interview, $oldScheduledAt, $interviewer->name));
        }

        return redirect()
            ->route('interviews.show', $interview)
            ->with('success', 'Interview updated and notifications sent.');
    }

    /**
     * Cancel a scheduled interview. Notifies the applicant and interviewers.
     */
    public function cancel(Interview $interview): RedirectResponse
    {
        $this->authorize('update', $interview);

        if (! $interview->isScheduled()) {
            return back()->withErrors(['interview' => 'Only scheduled interviews can be canceled.']);
        }

        $interview->update(['status' => 'canceled']);
        
        $interview->load('interviewers', 'application.jobPosition.organization');
        
        $applicantEmail = $interview->application->applicant_email;
        if (!str_contains($applicantEmail, 'no-email-')) {
            Notification::route('mail', $applicantEmail)
                ->notify(new InterviewCanceledNotification($interview, $interview->application->applicant_name));
        }
        
        foreach ($interview->interviewers as $interviewer) {
            Notification::route('mail', $interviewer->email)
                ->notify(new InterviewCanceledNotification($interview, $interviewer->name));
        }

        return back()->with('success', 'Interview canceled and notifications sent.');
    }

    /**
     * Submit feedback notes for a completed interview. Evaluated and stored 
     * purely on the specific acting interviewer's pivot row.
     */
    public function submitFeedback(Request $request, Interview $interview): RedirectResponse
    {
        $this->authorize('submitFeedback', $interview);

        if (! $interview->isCompleted()) {
            return back()->withErrors(['interview' => 'Feedback can only be submitted for completed interviews.']);
        }

        if ($interview->hasFeedbackFrom(Auth::user())) {
            return back()->withErrors(['interview' => 'Feedback has already been submitted by you for this interview.']);
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:5000'],
        ]);

        $interview->interviewers()->updateExistingPivot(Auth::id(), [
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
            ->when($excludeId, fn ($q) => $q->where('interviews.id', '!=', $excludeId))
            ->whereBetween('scheduled_at', [
                $proposedTime->copy()->subHour(),
                $proposedTime->copy()->addHour(),
            ])
            ->exists();
    }
    
    /**
     * Helper to map out all scheduled interviews of the given users into an array
     * structured cleanly for the FullCalendar JS integration.
     */
    private function buildSchedulesJson($interviewers): array
    {
        $schedules = [];
        foreach ($interviewers as $interviewer) {
            foreach ($interviewer->interviews as $inv) {
                if (!isset($schedules[$inv->id])) {
                    $interviewerNames = $inv->interviewers->pluck('name')->implode(', ');
                    
                    $schedules[$inv->id] = [
                        'id' => 'inv_'.$inv->id,
                        'title' => "{$inv->application->applicant_name} (with: {$interviewerNames})",
                        'start' => $inv->scheduled_at->toIso8601String(),
                        'end' => $inv->scheduled_at->copy()->addHour()->toIso8601String(),
                        'interviewer_ids' => [],
                        'color' => '#3a3f45',
                        'url' => route('interviews.show', $inv->id),
                        'extendedProps' => [
                            'position' => $inv->application->jobPosition->title,
                            'interviewer' => $interviewerNames,
                            'status' => $inv->status,
                            'canUpdate' => Auth::user()->can('update', $inv)
                        ]
                    ];
                }
                $schedules[$inv->id]['interviewer_ids'][] = $interviewer->id;
            }
        }
        
        return array_values($schedules);
    }
}