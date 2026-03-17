<?php

namespace App\Http\Controllers;

use App\Models\ApplicationTemplate;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles creation and management of application templates within an
 * organization. All actions require the manage_templates permission scoped to
 * the organization, except viewing which also allows review_applications.
 */
class ApplicationTemplateController extends Controller
{
    /**
     * Display all application templates belonging to an organization.
     */
    public function index(Organization $organization): View
    {
        $this->authorize('viewAny', [ApplicationTemplate::class, $organization]);

        $templates = $organization->templates()
            ->withCount('fields', 'applications')
            ->with('creator')
            ->latest()
            ->get();

        return view('application_templates.index', compact('organization', 'templates'));
    }

    /**
     * Show the form for creating a new application template.
     */
    public function create(Organization $organization): View
    {
        $this->authorize('create', [ApplicationTemplate::class, $organization]);

        return view('application_templates.create', compact('organization'));
    }

    /**
     * Store a newly created application template in the database. The template
     * is created with no fields; fields are added via the TemplateFieldController.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('create', [ApplicationTemplate::class, $organization]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('application_templates')->where(
                    fn ($query) => $query->where('organization_id', $organization->id)
                ),
            ],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $template = $organization->templates()->create([
            'name'       => $validated['name'],
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('organizations.application-templates.edit', [$organization, $template])
            ->with('success', 'Template created. Add fields below.');
    }

    /**
     * Display a preview of the template and its fields. Available to users
     * with manage_templates or review_applications.
     */
    public function show(Organization $organization, ApplicationTemplate $applicationTemplate): View
    {
        $this->authorize('view', $applicationTemplate);

        $applicationTemplate->load('fields', 'creator');

        return view('application_templates.show', compact('organization', 'applicationTemplate'));
    }

    /**
     * Show the template editor, including its current fields. This is the
     * primary interface for building out template fields.
     */
    public function edit(Organization $organization, ApplicationTemplate $applicationTemplate): View
    {
        $this->authorize('update', $applicationTemplate);

        $applicationTemplate->load('fields');

        return view('application_templates.edit', compact('organization', 'applicationTemplate'));
    }

    /**
     * Update the template's name.
     */
    public function update(Request $request, Organization $organization, ApplicationTemplate $applicationTemplate): RedirectResponse
    {
        $this->authorize('update', $applicationTemplate);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('application_templates')
                    ->where(fn ($query) => $query->where('organization_id', $organization->id))
                    ->ignore($applicationTemplate->id),
            ],
        ]);

        $applicationTemplate->update($validated);

        return redirect()
            ->route('organizations.application-templates.show', [$organization, $applicationTemplate])
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Delete a template. Only permitted if the template has no applications
     * submitted against it, to preserve historical records.
     */
    public function destroy(Organization $organization, ApplicationTemplate $applicationTemplate): RedirectResponse
    {
        $this->authorize('delete', $applicationTemplate);

        if ($applicationTemplate->applications()->exists()) {
            return back()->withErrors([
                'template' => 'This template cannot be deleted because applications have been submitted using it.',
            ]);
        }

        $applicationTemplate->delete();

        return redirect()
            ->route('organizations.application-templates.index', $organization)
            ->with('success', 'Template deleted.');
    }
}