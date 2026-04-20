@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@section('content')
<div class="container container-wide">
    <div class="card-header-flex mb-20">
        <h1 class="m-0">Edit Template: {{ $applicationTemplate->name }}</h1>
        <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">Back to Templates</a>
    </div>

    <div class="split-layout">
        <div class="split-builder">
            <div class="card">
                <h2 class="mt-0 border-bottom-none">Template Settings</h2>
                <form id="template-settings-form" method="POST" action="{{ route('organizations.application-templates.update', [$organization, $applicationTemplate]) }}">
                    @csrf
                    @method('PUT')
                    
                    @include('application_templates.partials.settings-fields', ['template' => $applicationTemplate])

                    <button type="submit" class="btn btn-sm mt-10">Save Settings</button>
                </form>
            </div>

            <div class="card-header-flex mt-30 mb-10">
                <h2 class="m-0 border-bottom-none pb-0">Custom Fields</h2>
                <span class="text-muted fs-13">Drag the ☰ icon to reorder fields automatically.</span>
            </div>
            
            <div id="fields-list" data-reorder-url="{{ route('organizations.application-templates.fields.reorder', [$organization, $applicationTemplate]) }}">
                @forelse ($applicationTemplate->fields as $field)
                    <div class="card field-item-box" data-id="{{ $field->id }}">
                        <div class="card-header-flex">
                            <div class="flex-gap-15 items-center">
                                <span class="drag-handle-icon" title="Drag to reorder">☰</span>
                                <div>
                                    <strong class="fs-16">{{ $field->label }}</strong>
                                    <span class="status status-awaiting-interview ml-10">{{ ucfirst($field->type) }}</span>
                                    @if($field->required) <span class="status status-needs-review ml-5">Required</span> @endif
                                </div>
                            </div>
                            <div class="flex-gap-10">
                                <button type="button" class="btn btn-sm" onclick="toggleEdit('{{ $field->id }}')">Edit</button>
                                <form method="POST" action="{{ route('organizations.application-templates.fields.destroy', [$organization, $applicationTemplate, $field]) }}" class="m-0 d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this field?')">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-form-{{ $field->id }}" class="field-edit-panel" style="display: {{ $errors->has('label') && old('type') !== null ? 'block' : 'none' }};">
                            <form method="POST" action="{{ route('organizations.application-templates.fields.update', [$organization, $applicationTemplate, $field]) }}">
                                @csrf
                                @method('PATCH')

                                @include('application_templates.partials.field-form-inputs', [
                                    'idPrefix' => 'edit-' . $field->id,
                                    'field' => $field
                                ])
                                
                                <div class="mt-15">
                                    <button type="submit" class="btn btn-sm">Update Field</button>
                                    <button type="button" class="btn btn-sm btn-outline ml-10" onclick="toggleEdit('{{ $field->id }}')">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="card"><p>No custom fields added yet.</p></div>
                @endforelse
            </div>

            <div class="card card-dashed-purple" id="add-field-card">
                <h2 class="mt-0 border-bottom-none">Add New Field</h2>
                <form method="POST" action="{{ route('organizations.application-templates.fields.store', [$organization, $applicationTemplate]) }}">
                    @csrf

                    @include('application_templates.partials.field-form-inputs', [
                        'idPrefix' => 'add',
                        'field' => null,
                        'isAdd' => true
                    ])
                    
                    <div class="mt-15">
                        <button type="submit" class="btn">Add Field</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="split-preview">
            <div class="card">
                <h2 class="mt-0 pb-15 border-bottom-divider">Preview: <span id="preview-template-name">{{ $applicationTemplate->name }}</span></h2>
                <p class="text-muted fs-14 mb-25">This is how the application form will appear to applicants. <em>(Form submission is disabled in preview)</em></p>

                <div id="preview-content">
                    @include('applications.partials.form-fields', ['template' => $applicationTemplate, 'isPreview' => true, 'isBuilder' => true])
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.previewUrl = "{{ route('organizations.application-templates.preview', $organization) }}";
</script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
@endpush