<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobPosition;
use App\Models\Organization;
use App\Mail\ApplicationShared;
use App\Mail\ApplicationRejected;
use App\Notifications\ApplicationSubmittedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

        $rules = [
            'answers'         => ['nullable', 'array'],
            'answers.*'       => ['nullable'],
        ];

        // Only enforce rules on standard fields if the template requests them
        if ($template->request_name) {
            $rules['applicant_name'] = ['required', 'string', 'max:255'];
        }
        if ($template->request_email) {
            $rules['applicant_email'] = ['required', 'email', 'max:255'];
        }
        if ($template->request_phone) {
            $rules['applicant_phone'] = ['nullable', 'string', 'max:50'];
        }

        $messages = [];

        // Dynamically enforce rules and friendly error messages for custom file upload fields
        foreach ($template->fields as $field) {
            if ($field->isFileField()) {
                $mimes = implode(',', empty($field->options) ? [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg',
                    'image/png'
                ] : $field->options);

                $friendlyMimes = $this->getFriendlyMimes(empty($field->options) ? null : $field->options);
                $maxMB = $field->file_size_max ?? 7;
                $maxKB = $maxMB * 1024;

                if ($field->file_multiple) {
                    $maxFiles = $field->file_max ?? 5;
                    $rules["answers.{$field->id}"] = [$field->required ? 'required' : 'nullable', 'array', "max:{$maxFiles}"];
                    $rules["answers.{$field->id}.*"] = ['file', "max:{$maxKB}", 'mimetypes:' . $mimes];

                    $messages["answers.{$field->id}.*.mimetypes"] = "Each file for {$field->label} must be a file of type: {$friendlyMimes}.";
                    $messages["answers.{$field->id}.*.max"] = "Each file for {$field->label} must not be larger than {$maxMB}MB.";
                    $messages["answers.{$field->id}.max"] = "You cannot upload more than {$maxFiles} files for {$field->label}.";
                } else {
                    $rules["answers.{$field->id}"] = [$field->required ? 'required' : 'nullable', 'file', "max:{$maxKB}", 'mimetypes:' . $mimes];

                    $messages["answers.{$field->id}.mimetypes"] = "The {$field->label} must be a file of type: {$friendlyMimes}.";
                    $messages["answers.{$field->id}.max"] = "The {$field->label} must not be larger than {$maxMB}MB.";
                }
            } elseif (in_array($field->type, ['text', 'textarea'])) {
                $maxChar = $field->char_max ?? 5000;
                $rules["answers.{$field->id}"] = [$field->required ? 'required' : 'nullable', 'string', "max:{$maxChar}"];
                $messages["answers.{$field->id}.max"] = "The {$field->label} must not exceed {$maxChar} characters.";
            } elseif ($field->type === 'rich_text') {
                $maxChar = $field->char_max ?? 5000;

                $rules["answers.{$field->id}"] = [
                    $field->required ? 'required' : 'nullable',
                    'string',
                    function ($attribute, $value, $fail) use ($field, $maxChar) {
                        $plainText = trim(html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                        if (mb_strlen($plainText) > $maxChar) {
                            $fail("The {$field->label} must not exceed {$maxChar} characters.");
                        }
                    },
                ];
            }
        }

        $validated = $request->validate($rules, $messages);

        // Safely determine email (an anonymous fallback if not requested)
        $determinedEmail = $template->request_email ? $validated['applicant_email'] : 'no-email-'.uniqid().'@hireflow.example.com';

        // Check duplicates if the email is actually known
        if ($template->request_email) {
            $alreadyApplied = Application::where('applicant_email', $determinedEmail)
                ->where('job_position_id', $jobPosition->id)
                ->exists();

            if ($alreadyApplied) {
                return back()->withErrors([
                    'applicant_email' => 'An application from this email address has already been submitted for this position.',
                ])->withInput();
            }
        }

        $this->validateRequiredFields($template, $validated['answers'] ?? []);

        $application = Application::create([
            'job_position_id' => $jobPosition->id,
            'template_id'     => $template->id,
            'applicant_name'  => $template->request_name ? $validated['applicant_name'] : 'Anonymous Applicant',
            'applicant_email' => $determinedEmail,
            'applicant_phone' => $template->request_phone ? ($validated['applicant_phone'] ?? null) : null,
            'status'          => 'submitted',
        ]);

        // Process Template Answers (Including custom file fields)
        foreach ($template->fields as $field) {
            if ($request->has("answers.{$field->id}") || $request->hasFile("answers.{$field->id}")) {
                
                // If it is a custom file upload field
                if ($field->isFileField()) {
                    $files = $request->file("answers.{$field->id}");
                    if (!empty($files)) {
                        if (!is_array($files)) {
                            $files = [$files];
                        }
                        
                        foreach ($files as $file) {
                            if ($file && $file->isValid()) {
                                $path = $file->store("documents/{$application->id}", 'local');
                                
                                $doc = $application->documents()->create([
                                    'filename' => $file->getClientOriginalName(),
                                    'filepath' => $path,
                                    'mimetype' => $file->getMimeType(),
                                ]);

                                $application->answers()->create([
                                    'template_field_id' => $field->id,
                                    'value'             => $file->getClientOriginalName(),
                                    'document_id'       => $doc->id,
                                ]);
                            }
                        }
                    }
                } else {
                    $val = $validated['answers'][$field->id] ?? null;
                    if ($val !== null) {
                        if ($field->type === 'rich_text') {
                            $val = clean($val);
                        }
                        $application->answers()->create([
                            'template_field_id' => $field->id,
                            'value'             => is_array($val) ? implode(', ', $val) : $val,
                        ]);
                    }
                }
            }
        }

        // Notify reviewers
        $reviewers = $organization->members()
            ->where('application_email_notifications', true)
            ->get()
            ->filter(function ($member) use ($organization) {
                return $member->hasPermissionIn($organization, 'review_applications');
            });

        foreach ($reviewers as $reviewer) {
            Notification::route('mail', $reviewer->email)
                ->notify(new ApplicationSubmittedNotification($application, $reviewer->name));
        }

        return redirect()
            ->route('organizations.job-positions.index', $organization)
            ->with('success', 'Your application has been submitted successfully.');
    }

    /**
     * Display a single application with all answers.
     */
    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'jobPosition.organization.members',
            'template.fields',
            'answers.field',
            'answers.document.uploader',
            'documents.uploader',
            'interviews.interviewers',
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
            'status' => ['required', 'in:submitted,under_review,needs_chairman_review,no_longer_under_consideration,withdrawn'],
        ]);

        $application->update(['status' => $validated['status']]);

        return back()->with('success', 'Application status updated.');
    }

    /**
     * Mark an application as no longer under consideration and notify the applicant.
     */
    public function reject(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('updateStatus', $application);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:5000'],
        ]);

        $application->update(['status' => 'no_longer_under_consideration']);

        $application->load('jobPosition.organization');

        if (! str_contains($application->applicant_email, 'no-email-')) {
            Mail::to($application->applicant_email)->send(
                new ApplicationRejected($application, $validated['rejection_reason'] ?? null)
            );
        }

        return back()->with('success', 'Application status updated and applicant notified.');
    }

    /**
     * Convert the application to a PDF and email it to internal or external addresses.
     */
    public function share(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);

        $validated = $request->validate([
            'emails'   => ['required', 'array'],
            'emails.*' => ['email'],
            'message'  => ['nullable', 'string', 'max:5000'],
        ]);

        $application->load([
            'jobPosition.organization',
            'answers.field',
            'answers.document.uploader',
            'interviews.interviewers',
            'documents.uploader',
        ]);

        // Generate the PDF
        $pdf = Pdf::loadView('applications.pdf', compact('application'));
        $pdfContent = $pdf->output();

        // Send email with PDF and documents attached
        Mail::to($validated['emails'])->send(new ApplicationShared($application, $validated['message'] ?? null, $pdfContent));

        return back()->with('success', 'Application PDF and documents successfully generated and shared via email.');
    }
    
    /**
     * Stream the application as a PDF directly to the browser.
     */
    public function previewPdf(Application $application)
    {
        $this->authorize('view', $application);

        $application->load([
            'jobPosition.organization',
            'answers.field',
            'answers.document.uploader',
            'interviews.interviewers'
        ]);

        $pdf = Pdf::loadView('applications.pdf', compact('application'));
        
        $filename = preg_replace('/[^A-Za-z0-9\- ]/', '', $application->applicant_name) . ' - Application.pdf';

        return $pdf->stream($filename);
    }
    
    /**
     * Delete an application.
     */
    public function destroy(Application $application): RedirectResponse
    {
        $this->authorize('delete', $application);

        $organization = $application->jobPosition->organization;

        // Delete documents from storage
        foreach ($application->documents as $document) {
            Storage::disk('local')->delete($document->filepath);
        }
        
        // Remove the directory
        Storage::disk('local')->deleteDirectory("documents/{$application->id}");

        $application->delete();

        return redirect()
            ->route('organizations.applications', $organization)
            ->with('success', 'Application deleted successfully.');
    }

    /**
     * Validates that all required template fields have a non-empty answer.
     * (File validation is handled by Laravel's built-in validator above).
     */
    private function validateRequiredFields($template, array $answers): void
    {
        $errors = [];

        foreach ($template->fields->where('required', true) as $field) {
            // Because files upload to the $request differently, ensure we check the pure request specifically for files
            if ($field->isFileField()) {
                $files = request()->file("answers.{$field->id}");
                $hasAnyFile = false;
                
                if (is_array($files)) {
                    foreach ($files as $f) {
                        if ($f && $f->isValid()) { $hasAnyFile = true; break; }
                    }
                } else {
                    if ($files && $files->isValid()) { $hasAnyFile = true; }
                }
                
                if (! $hasAnyFile) {
                    $errors["answers.{$field->id}"] = "The field \"{$field->label}\" is required.";
                }
            } else {
                if (empty($answers[$field->id])) {
                    $errors["answers.{$field->id}"] = "The field \"{$field->label}\" is required.";
                }
            }
        }

        if (! empty($errors)) {
            redirect()->back()->withErrors($errors)->withInput()->throwResponse();
        }
    }
    
    /**
     * Maps standard MIME types to user-friendly file format strings.
     */
    private function getFriendlyMimes(?array $mimes): string
    {
        if (empty($mimes)) {
            return 'PDF, DOC, DOCX, JPEG, PNG';
        }
        
        $map = [
            'application/pdf' => 'PDF',
            'application/msword' => 'DOC',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
            'image/jpeg' => 'JPEG',
            'image/png' => 'PNG',
        ];
        
        $friendly = array_map(fn($mime) => $map[$mime] ?? $mime, $mimes);
        return implode(', ', $friendly);
    }
}