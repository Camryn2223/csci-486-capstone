<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\JobPosition;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles the full lifecycle of job applications. Public users (anonymous
 * applicants) submit applications via unauthenticated routes. Authenticated
 * staff (interviewers and the chairman) review applications, update statuses,
 * and manage records. Document uploads during submission are handled inline
 * here rather than in DocumentController since they form part of the atomic
 * submission flow.
 */
class ApplicationController extends Controller
{
    /**
     * Display all applications for a given job position. Requires
     * review_applications in the organization.
     */
    public function index(Organization $organization, JobPosition $jobPosition): View
    {
        $this->authorize('viewAny', [Application::class, $jobPosition]);

        $applications = $jobPosition->applications()
            ->with(['documents', 'interviews'])
            ->latest()
            ->get();

        return view('applications.index', compact('organization', 'jobPosition', 'applications'));
    }

    /**
     * Show the public application form for a job position. No authentication
     * required. Aborts with 403 if the position is not open.
     */
    public function create(Organization $organization, JobPosition $jobPosition): View
    {
        if (! $jobPosition->isOpen()) {
            abort(403, 'This position is not currently accepting applications.');
        }

        $jobPosition->load('organization');

        $templates = $organization->templates()->with('fields')->get();

        return view('applications.create', compact('organization', 'jobPosition', 'templates'));
    }

    /**
     * Store a new application submitted by an anonymous applicant. No
     * authentication required. Validates contact details, prevents duplicate
     * submissions from the same email address for the same position, validates
     * required template fields, and stores all answers.
     */
    public function store(Request $request, Organization $organization, JobPosition $jobPosition): RedirectResponse
    {
        if (! $jobPosition->isOpen()) {
            abort(403, 'This position is not currently accepting applications.');
        }

        $validated = $request->validate([
            'applicant_name'  => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:50'],
            'template_id'     => ['required', 'exists:application_templates,id'],
            'answers'         => ['nullable', 'array'],
            'answers.*'       => ['nullable', 'string'],
        ]);

        $alreadyApplied = Application::where('applicant_email', $validated['applicant_email'])
            ->where('job_position_id', $jobPosition->id)
            ->exists();

        if ($alreadyApplied) {
            return back()->withErrors([
                'applicant_email' => 'An application from this email address has already been submitted for this position.',
            ])->withInput();
        }

        $template = ApplicationTemplate::with('fields')->findOrFail($validated['template_id']);

        $this->validateRequiredFields($template, $validated['answers'] ?? []);

        $application = Application::create([
            'job_position_id' => $jobPosition->id,
            'template_id'     => $template->id,
            'applicant_name'  => $validated['applicant_name'],
            'applicant_email' => $validated['applicant_email'],
            'applicant_phone' => $validated['applicant_phone'] ?? null,
            'status'          => 'submitted',
        ]);

        foreach ($template->fields as $field) {
            if (isset($validated['answers'][$field->id])) {
                $application->answers()->create([
                    'template_field_id' => $field->id,
                    'value'             => $validated['answers'][$field->id],
                ]);
            }
        }

        return redirect()
            ->route('applications.confirmation', $application)
            ->with('success', 'Your application has been submitted successfully.');
    }

    /**
     * Display a submission confirmation page for the applicant after they
     * submit. Shows their name and email back to them. No authentication
     * required.
     */
    public function confirmation(Application $application): View
    {
        return view('applications.confirmation', compact('application'));
    }

    /**
     * Display a single application with all answers, documents, and
     * interviews. Requires review_applications in the organization.
     */
    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'jobPosition.organization',
            'template.fields',
            'answers.field',
            'documents',
            'interviews.interviewer',
        ]);

        return view('applications.show', compact('application'));
    }

    /**
     * Update the status of an application. Requires review_applications in
     * the organization.
     *
     * Valid transitions: submitted → under_review → no_longer_under_consideration | withdrawn
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('updateStatus', $application);

        $validated = $request->validate([
            'status' => ['required', 'in:submitted,under_review,no_longer_under_consideration,withdrawn'],
        ]);

        $application->update(['status' => $validated['status']]);

        return back()->with('success', 'Application status updated.');
    }

    /**
     * Validates that all required template fields have a non-empty answer in
     * the submitted answers array. Redirects back with errors if any are
     * missing.
     *
     * @param  array<int, string> $answers  Keyed by template_field_id
     */
    private function validateRequiredFields(ApplicationTemplate $template, array $answers): void
    {
        $errors = [];

        foreach ($template->fields->where('required', true) as $field) {
            if (empty($answers[$field->id])) {
                $errors["answers.{$field->id}"] = "The field \"{$field->label}\" is required.";
            }
        }

        if (! empty($errors)) {
            redirect()->back()->withErrors($errors)->withInput()->throwResponse();
        }
    }
}