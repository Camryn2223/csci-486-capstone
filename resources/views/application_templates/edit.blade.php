@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card-header-flex mb-20">
        <h1 class="m-0">Edit Template: {{ $applicationTemplate->name }}</h1>
        <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">Back to Templates</a>
    </div>

    <div class="flex-gap-10 mb-20">
        <button id="btn-tab-builder" class="btn btn-tab-active" onclick="switchTab('builder')">Builder</button>
        <button id="btn-tab-preview" class="btn btn-tab-inactive" onclick="switchTab('preview')">Preview Form</button>
    </div>

    <div id="tab-builder">
        <div class="card">
            <h2 class="mt-0 border-bottom-none">Template Settings</h2>
            <form method="POST" action="{{ route('organizations.application-templates.update', [$organization, $applicationTemplate]) }}">
                @csrf
                @method('PUT')
                
                <div class="mb-25">
                    <label>Template Name</label>
                    <input type="text" name="name" value="{{ old('name', $applicationTemplate->name) }}" class="mb-0" required>
                </div>

                <h3 class="h3-primary">Standard Sections</h3>
                <p class="text-muted mt-0 fs-14">Choose which standard fields to request from the applicant.</p>
                <div class="flex-col-8 mb-20">
                    <label class="text-light cursor-pointer">
                        <input type="checkbox" name="request_name" value="1" {{ old('request_name', $applicationTemplate->request_name) ? 'checked' : '' }}> Request Full Name
                    </label>
                    <label class="text-light cursor-pointer">
                        <input type="checkbox" name="request_email" value="1" {{ old('request_email', $applicationTemplate->request_email) ? 'checked' : '' }}> Request Email Address
                    </label>
                    <label class="text-light cursor-pointer">
                        <input type="checkbox" name="request_phone" value="1" {{ old('request_phone', $applicationTemplate->request_phone) ? 'checked' : '' }}> Request Phone Number
                    </label>
                    <label class="text-light cursor-pointer">
                        <input type="checkbox" name="request_resume" value="1" {{ old('request_resume', $applicationTemplate->request_resume) ? 'checked' : '' }}> Request Resume / Documents
                    </label>
                </div>

                <button type="submit" class="btn btn-sm">Save Settings</button>
            </form>
        </div>

        <div class="card-header-flex mt-30 mb-10">
            <h2 class="m-0 border-bottom-none pb-0">Custom Fields</h2>
            <span class="text-muted fs-13">Drag the ☰ icon to reorder fields automatically.</span>
        </div>
        
        <div id="fields-list" data-reorder-url="{{ route('organizations.application-templates.fields.reorder', [$organization, $applicationTemplate]) }}">
            @forelse ($applicationTemplate->fields as $field)
                <div class="card field-item-box" data-id="{{ $field->id }}" data-options="{{ json_encode($field->options ?? []) }}">
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
                            <button type="button" class="btn btn-sm btn-purple-dark" onclick="toggleEdit('{{ $field->id }}')">Edit</button>
                            <form method="POST" action="{{ route('organizations.application-templates.fields.destroy', [$organization, $applicationTemplate, $field]) }}" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this field?')">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div id="edit-form-{{ $field->id }}" style="display: {{ $errors->has('label') && old('type') !== null ? 'block' : 'none' }}; margin-top: 20px; padding-top: 20px; border-top: 1px solid #2f343a;">
                        <form method="POST" action="{{ route('organizations.application-templates.fields.update', [$organization, $applicationTemplate, $field]) }}">
                            @csrf
                            @method('PATCH')

                            @php
                                $currentType = old('type') !== null ? old('type') : $field->type;
                                $showOptions = in_array($currentType, ['select', 'checkbox', 'radio']);
                                $oldOptions = old('options');
                                $opts = is_array($oldOptions) ? $oldOptions : ($field->options ?? []);
                            @endphp

                            <label>Question Label</label>
                            <input type="text" name="label" value="{{ old('label', $field->label) }}" class="w-full" required>
                            
                            <div class="flex-wrap-15">
                                <div class="flex-1 min-w-200">
                                    <label>Type</label>
                                    <select name="type" onchange="toggleOptions(this, 'edit-options-container-{{ $field->id }}', 'edit-options-list-{{ $field->id }}')">
                                        @foreach (['text','textarea','select','checkbox','radio','file','date'] as $type)
                                            <option value="{{ $type }}" {{ $currentType === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-1 d-flex items-center min-w-150 pt-2">
                                    <label class="text-light cursor-pointer">
                                        <input type="checkbox" name="required" value="1" {{ old('required', $field->required) ? 'checked' : '' }}> Required
                                    </label>
                                </div>
                            </div>
                            
                            <div id="edit-options-container-{{ $field->id }}" class="options-container-edit" style="display: {{ $showOptions ? 'block' : 'none' }};">
                                <label class="text-primary mb-10">Multiple Choice Options</label>
                                <div id="edit-options-list-{{ $field->id }}">
                                    @foreach($opts as $opt)
                                        <div class="option-item">
                                            <span class="text-muted">&bull;</span>
                                            <input type="text" name="options[]" value="{{ $opt }}" class="m-0 flex-grow-1" required placeholder="Option value">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-add-opt" onclick="addOption('edit-options-list-{{ $field->id }}')">+ Add New Option</button>
                            </div>
                            
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

        <div class="card card-dashed-purple">
            <h2 class="mt-0 border-bottom-none">Add New Field</h2>
            <form method="POST" action="{{ route('organizations.application-templates.fields.store', [$organization, $applicationTemplate]) }}">
                @csrf

                @php
                    $addType = old('type', 'text');
                    $addShowOptions = in_array($addType, ['select', 'checkbox', 'radio']);
                    $addOptions = is_array(old('options')) ? old('options') : [];
                @endphp

                <label>Question Label</label>
                <input type="text" name="label" value="{{ old('label') }}" class="w-full" required>
                
                <div class="flex-wrap-15">
                    <div class="flex-1 min-w-200">
                        <label>Type</label>
                        <select name="type" onchange="toggleOptions(this, 'add-options-container', 'add-options-list')">
                            @foreach (['text','textarea','select','checkbox','radio','file','date'] as $type)
                                <option value="{{ $type }}" {{ $addType === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 d-flex items-center min-w-150 pt-2">
                        <label class="text-light cursor-pointer">
                            <input type="checkbox" name="required" value="1" {{ old('required') ? 'checked' : '' }}> Required
                        </label>
                    </div>
                </div>

                <div id="add-options-container" class="options-container-add" style="display: {{ $addShowOptions ? 'block' : 'none' }};">
                    <label class="text-primary mb-10">Multiple Choice Options</label>
                    <div id="add-options-list">
                        @foreach($addOptions as $opt)
                            <div class="option-item">
                                <span class="text-muted">&bull;</span>
                                <input type="text" name="options[]" value="{{ $opt }}" class="m-0 flex-grow-1" required placeholder="Option value">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-add-opt-alt" onclick="addOption('add-options-list')">+ Add New Option</button>
                </div>
                
                <div class="mt-15">
                    <button type="submit" class="btn">Add Field</button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-preview" style="display: none;">
        <div class="card">
            <h2 class="mt-0 pb-15 border-bottom-divider">Preview: <span id="preview-template-name">{{ $applicationTemplate->name }}</span></h2>
            <p class="text-muted fs-14 mb-25">This is how the application form will appear to applicants. <em>(Form submission is disabled in preview)</em></p>

            <div id="preview-content">
                @include('applications.partials.form-fields', ['template' => $applicationTemplate, 'isPreview' => true, 'isBuilder' => true])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
@endpush