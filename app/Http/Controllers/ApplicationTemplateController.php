<?php

namespace App\Http\Controllers;

use App\Models\ApplicationTemplate;
use App\Models\Organization;
use App\Models\TemplateField;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->withCount('fields')
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
     * Generates a live HTML preview of the template fields by instantiating 
     * non-persisted Eloquent models and passing them to the Blade partial.
     */
    public function preview(Request $request, Organization $organization)
    {
        $this->authorize('viewAny', [ApplicationTemplate::class, $organization]);

        $template = new ApplicationTemplate([
            'name' => $request->input('name', 'Untitled Template'),
            'request_name' => filter_var($request->input('request_name', true), FILTER_VALIDATE_BOOLEAN),
            'request_email' => filter_var($request->input('request_email', true), FILTER_VALIDATE_BOOLEAN),
            'request_phone' => filter_var($request->input('request_phone', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $fields = collect();
        if ($request->has('fields')) {
            foreach ($request->input('fields') as $fieldData) {
                $options = $fieldData['options'] ?? [];
                if (is_string($options)) {
                    $options = array_values(array_filter(array_map('trim', explode(',', $options))));
                }
                
                $fields->push(new TemplateField([
                    'id' => $fieldData['id'] ?? rand(10000, 99999),
                    'label' => $fieldData['label'] ?? 'Untitled Field',
                    'type' => $fieldData['type'] ?? 'text',
                    'required' => filter_var($fieldData['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'file_multiple' => filter_var($fieldData['file_multiple'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'file_max' => !empty($fieldData['file_max']) ? (int)$fieldData['file_max'] : null,
                    'char_max' => !empty($fieldData['char_max']) ? (int)$fieldData['char_max'] : null,
                    'file_size_max' => !empty($fieldData['file_size_max']) ? (int)$fieldData['file_size_max'] : null,
                    'options' => $options,
                ]));
            }
        }
        
        $template->setRelation('fields', $fields);

        return view('applications.partials.form-fields', [
            'template' => $template,
            'isPreview' => true,
            'isBuilder' => true,
        ])->render();
    }

    /**
     * Store a newly created application template and any custom fields added
     * during the initial creation process in a single DB transaction.
     */
    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('create', [ApplicationTemplate::class, $organization]);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('application_templates')->where(fn ($query) => $query->where('organization_id', $organization->id))],
            'request_name'   => ['nullable', 'boolean'],
            'request_email'  => ['nullable', 'boolean'],
            'request_phone'  => ['nullable', 'boolean'],
            'fields'         => ['nullable', 'array'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type'  => ['required', 'in:text,textarea,rich_text,select,checkbox,radio,file,date'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.file_multiple' => ['nullable', 'boolean'],
            'fields.*.file_max' => ['nullable', 'integer', 'min:2', 'max:10'],
            'fields.*.char_max' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'fields.*.file_size_max' => ['nullable', 'integer', 'min:1', 'max:100'],
            'fields.*.options'  => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($validated, $organization, $request) {
            $template = $organization->templates()->create([
                'name'           => $validated['name'],
                'created_by'     => Auth::id(),
                'request_name'   => $request->boolean('request_name', false),
                'request_email'  => $request->boolean('request_email', false),
                'request_phone'  => $request->boolean('request_phone', false),
            ]);

            if (isset($validated['fields'])) {
                foreach (array_values($validated['fields']) as $index => $fieldData) {
                    $template->fields()->create([
                        'label'    => $fieldData['label'],
                        'type'     => $fieldData['type'],
                        'required' => filter_var($fieldData['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'file_multiple' => filter_var($fieldData['file_multiple'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'file_max' => $fieldData['file_max'] ?? null,
                        'char_max' => $fieldData['char_max'] ?? null,
                        'file_size_max' => $fieldData['file_size_max'] ?? null,
                        'options'  => $fieldData['options'] ?? null,
                        'order'    => $index + 1, // Sort order implicitly set by DOM position!
                    ]);
                }
            }
        });

        return redirect()
            ->route('organizations.application-templates.index', $organization)
            ->with('success', 'Template created successfully.');
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
     * Update the template's configuration and fields in a single transaction.
     */
    public function update(Request $request, Organization $organization, ApplicationTemplate $applicationTemplate): RedirectResponse
    {
        $this->authorize('update', $applicationTemplate);

        $validated = $request->validate([
            'name'           => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('application_templates')
                    ->where(fn ($query) => $query->where('organization_id', $organization->id))
                    ->ignore($applicationTemplate->id),
            ],
            'request_name'   => ['nullable', 'boolean'],
            'request_email'  => ['nullable', 'boolean'],
            'request_phone'  => ['nullable', 'boolean'],
            'fields'         => ['nullable', 'array'],
            'fields.*.id'    => ['nullable'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type'  => ['required', 'in:text,textarea,rich_text,select,checkbox,radio,file,date'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.file_multiple' => ['nullable', 'boolean'],
            'fields.*.file_max' => ['nullable', 'integer', 'min:2', 'max:10'],
            'fields.*.char_max' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'fields.*.file_size_max' => ['nullable', 'integer', 'min:1', 'max:100'],
            'fields.*.options'  => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($validated, $organization, $request, $applicationTemplate) {
            $applicationTemplate->update([
                'name'           => $validated['name'],
                'request_name'   => $request->boolean('request_name', false),
                'request_email'  => $request->boolean('request_email', false),
                'request_phone'  => $request->boolean('request_phone', false),
            ]);

            $existingFieldIds = $applicationTemplate->fields()->pluck('id')->toArray();
            $submittedFieldIds = [];

            if (isset($validated['fields'])) {
                foreach (array_values($validated['fields']) as $index => $fieldData) {
                    $fieldId = $fieldData['id'] ?? null;
                    
                    // Discard the ID if it's our JS "new_XXXX" unique string marker
                    if (is_string($fieldId) && str_starts_with($fieldId, 'new_')) {
                        $fieldId = null;
                    }

                    if ($fieldId && in_array($fieldId, $existingFieldIds)) {
                        $field = $applicationTemplate->fields()->find($fieldId);
                        $field->update([
                            'label'    => $fieldData['label'],
                            'type'     => $fieldData['type'],
                            'required' => filter_var($fieldData['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'file_multiple' => filter_var($fieldData['file_multiple'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'file_max' => $fieldData['file_max'] ?? null,
                            'char_max' => $fieldData['char_max'] ?? null,
                            'file_size_max' => $fieldData['file_size_max'] ?? null,
                            'options'  => $fieldData['options'] ?? null,
                            'order'    => $index + 1, // Sort order implicitly set by DOM position!
                        ]);
                        $submittedFieldIds[] = $fieldId;
                    } else {
                        $applicationTemplate->fields()->create([
                            'label'    => $fieldData['label'],
                            'type'     => $fieldData['type'],
                            'required' => filter_var($fieldData['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'file_multiple' => filter_var($fieldData['file_multiple'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'file_max' => $fieldData['file_max'] ?? null,
                            'char_max' => $fieldData['char_max'] ?? null,
                            'file_size_max' => $fieldData['file_size_max'] ?? null,
                            'options'  => $fieldData['options'] ?? null,
                            'order'    => $index + 1,
                        ]);
                    }
                }
            }

            // Any ID not submitted means it was removed from the DOM
            $toDelete = array_diff($existingFieldIds, $submittedFieldIds);
            foreach ($toDelete as $delId) {
                $field = $applicationTemplate->fields()->find($delId);
                
                if ($field && $field->answers()->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'template' => "Field '{$field->label}' cannot be deleted because answers have already been submitted for it."
                    ]);
                }
                
                $field?->delete();
            }
        });

        return redirect()
            ->route('organizations.application-templates.edit', [$organization, $applicationTemplate])
            ->with('success', 'Template settings and fields updated successfully.');
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