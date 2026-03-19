<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobPosition;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles the full lifecycle of job applications. Public users submit via
 * unauthenticated routes. Authenticated staff review and manage records.
 */
class ApplicationController extends Controller
{
    /**
     * Display all applications for a given job position.
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
     * Show the public application form for a job position.
     */
    public function create(Organization $organization, JobPosition $jobPosition): View
    {
        if (! $jobPosition->isOpen()) {
            abort(403, 'This position is not currently accepting applications.');
        }

        $jobPosition->load('template.fields', 'organization');

        return view('applications.create', compact('organization', 'jobPosition'));
    }

    /**
     * Store a new application submitted by an anonymous applicant.
     * Includes processing of custom template answers and file uploads.
     */
    public function store(Request $request, Organization $organization, JobPosition $jobPosition): RedirectResponse
    {
        if (! $jobPosition->isOpen()) {
            abort(403, 'This position is not currently accepting applications.');
        }

        $template = $jobPosition->template()->with('fields')->firstOrFail();

        $validated = $request->validate([
            'applicant_name'  => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:50'],
            'answers'         => ['nullable', 'array'],
            'answers.*'       => ['nullable'],
            'document'        => ['nullable', 'file', 'max:10240', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png'],
        ]);

        $alreadyApplied = Application::where('applicant_email', $validated['applicant_email'])
            ->where('job_position_id', $jobPosition->id)
            ->exists();

        if ($alreadyApplied) {
            return back()->withErrors([
                'applicant_email' => 'An application from this email address has already been submitted for this position.',
            ])->withInput();
        }

        $this->validateRequiredFields($template, $validated['answers'] ?? []);

        $application = Application::create([
            'job_position_id' => $jobPosition->id,
            'template_id'     => $template->id,
            'applicant_name'  => $validated['applicant_name'],
            'applicant_email' => $validated['applicant_email'],
            'applicant_phone' => $validated['applicant_phone'] ?? null,
            'status'          => 'submitted',
        ]);

        // Process Template Answers
        foreach ($template->fields as $field) {
            if (isset($validated['answers'][$field->id])) {
                $val = $validated['answers'][$field->id];
                
                $application->answers()->create([
                    'template_field_id' => $field->id,
                    'value'             => is_array($val) ? implode(', ', $val) : $val,
                ]);
            }
        }

        // Process File Upload
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store("documents/{$application->id}", 'local');

            $application->documents()->create([
                'filename' => $file->getClientOriginalName(),
                'filepath' => $path,
                'mimetype' => $file->getMimeType(),
            ]);
        }

        return redirect()
            ->route('organizations.job-positions.show', [$organization, $jobPosition])
            ->with('success', 'Your application has been submitted successfully.');
    }

    /**
     * Display a single application with all answers.
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
     * Update the status of an application.
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
     * Validates that all required template fields have a non-empty answer.
     */
    private function validateRequiredFields($template, array $answers): void
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