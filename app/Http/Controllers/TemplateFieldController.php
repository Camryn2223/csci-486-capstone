<?php

namespace App\Http\Controllers;

use App\Models\ApplicationTemplate;
use App\Models\Organization;
use App\Models\TemplateField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles creation, updating, deletion, and reordering of individual fields
 * within an application template. All actions are nested under an organization
 * and template. Requires the manage_templates permission in the organization.
 *
 * The reorder action returns JSON and is intended to be called via fetch from
 * the template editor's drag-and-drop interface.
 */
class TemplateFieldController extends Controller
{
    /**
     * Store a new field on the given template. The field is appended after all
     * existing fields by assigning the next available sort order.
     */
    public function store(Request $request, Organization $organization, ApplicationTemplate $applicationTemplate): RedirectResponse
    {
        $this->authorize('create', [TemplateField::class, $applicationTemplate]);

        $this->prepareOptions($request);

        $validated = $request->validate($this->validationRules(), $this->validationMessages());

        $nextOrder = $applicationTemplate->fields()->max('order') + 1;

        $applicationTemplate->fields()->create([
            'label'    => $validated['label'],
            'type'     => $validated['type'],
            'options'  => $validated['options'] ?? null,
            'required' => $validated['required'] ?? false,
            'order'    => $nextOrder,
        ]);

        return redirect()
            ->route('organizations.application-templates.edit', [$organization, $applicationTemplate])
            ->with('success', 'Field added.');
    }

    /**
     * Update an existing template field's label, type, options, and required
     * flag. Sort order is managed separately via the reorder action.
     */
    public function update(Request $request, Organization $organization, ApplicationTemplate $applicationTemplate, TemplateField $templateField): RedirectResponse
    {
        $this->authorize('update', $templateField);

        $this->prepareOptions($request);

        $validated = $request->validate($this->validationRules(), $this->validationMessages());

        $templateField->update([
            'label'    => $validated['label'],
            'type'     => $validated['type'],
            'options'  => $validated['options'] ?? null,
            'required' => $validated['required'] ?? false,
        ]);

        return redirect()
            ->route('organizations.application-templates.edit', [$organization, $applicationTemplate])
            ->with('success', 'Field updated.');
    }

    /**
     * Delete a template field. Only permitted if no answers have been
     * submitted for this field, to preserve historical application data.
     */
    public function destroy(Organization $organization, ApplicationTemplate $applicationTemplate, TemplateField $templateField): RedirectResponse
    {
        $this->authorize('delete', $templateField);

        if ($templateField->answers()->exists()) {
            return back()->withErrors([
                'field' => 'This field cannot be deleted because answers have already been submitted for it.',
            ]);
        }

        $templateField->delete();

        $this->resequenceFields($applicationTemplate);

        return redirect()
            ->route('organizations.application-templates.edit', [$organization, $applicationTemplate])
            ->with('success', 'Field deleted.');
    }

    /**
     * Accepts a JSON array of field IDs in the desired order and updates the
     * sort order of each field accordingly. Returns JSON for use by the
     * drag-and-drop editor.
     *
     * Expected request body: { "order": [3, 1, 4, 2] }
     */
    public function reorder(Request $request, Organization $organization, ApplicationTemplate $applicationTemplate): JsonResponse
    {
        $this->authorize('reorder', [TemplateField::class, $applicationTemplate]);

        $validated = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:template_fields,id'],
        ]);

        foreach ($validated['order'] as $position => $fieldId) {
            $applicationTemplate->fields()
                ->where('id', $fieldId)
                ->update(['order' => $position + 1]);
        }

        return response()->json(['message' => 'Field order updated.']);
    }

    /**
     * Re-sequences the sort order of all remaining fields on a template after
     * a deletion so there are no gaps in the order values.
     */
    private function resequenceFields(ApplicationTemplate $applicationTemplate): void
    {
        $applicationTemplate->fields()
            ->orderBy('order')
            ->get()
            ->each(function (TemplateField $field, int $index) {
                $field->update(['order' => $index + 1]);
            });
    }

    /**
     * Parses comma-separated options into an array before validation.
     * Forces options to null if the field type doesn't support them.
     */
    private function prepareOptions(Request $request): void
    {
        if (in_array($request->input('type'), ['select', 'checkbox', 'radio'])) {
            $options = $request->input('options');
            if (is_string($options)) {
                $optionsArray = array_values(array_filter(array_map('trim', explode(',', $options))));
                // If it's empty, set to null so the required_if validation catches it.
                $request->merge(['options' => empty($optionsArray) ? null : $optionsArray]);
            }
        } else {
            $request->merge(['options' => null]);
        }
    }

    /**
     * Reusable validation rules for creating and updating fields.
     */
    private function validationRules(): array
    {
        return [
            'label'     => ['required', 'string', 'max:255'],
            'type'      => ['required', 'in:text,textarea,select,checkbox,radio,file,date'],
            'options'   => ['required_if:type,select,checkbox,radio', 'nullable', 'array', 'min:1'],
            'options.*' => ['string', 'max:255'],
            'required'  => ['boolean'],
        ];
    }

    /**
     * Reusable validation messages for creating and updating fields.
     */
    private function validationMessages(): array
    {
        return [
            'options.required_if' => 'You must provide at least one valid option (comma-separated) for this field type.',
        ];
    }
}