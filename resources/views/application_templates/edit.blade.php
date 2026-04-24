@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@section('content')
<div class="container container-wide">
    <div class="card card-header-flex">
        <h1 class="m-0">Edit Application Template</h1>
        <div class="flex-gap-10">
            <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">Back to Templates</a>
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
        </div>
    </div>

    <div class="split-layout">
        <div class="split-builder">
            <div class="card">
                <form id="template-settings-form" method="POST" action="{{ route('organizations.application-templates.update', [$organization, $applicationTemplate]) }}">
                    @csrf
                    @method('PUT')
                    
                    <h2 class="mt-0 border-bottom-none">Template Settings</h2>
                    @include('application_templates.partials.settings-fields', ['template' => $applicationTemplate])

                    <div class="card-header-flex mt-30 mb-10">
                        <h2 class="m-0 border-bottom-none pb-0">Custom Fields</h2>
                        <span class="text-muted fs-13">Drag the ☰ icon to reorder fields automatically.</span>
                    </div>
                    
                    <div id="fields-list">
                        @forelse ($applicationTemplate->fields as $field)
                            @include('application_templates.partials.builder-field-item', ['field' => $field])
                        @empty
                        @endforelse
                    </div>

                    <div class="card card-dashed-purple" id="add-field-card">
                        <h2 class="mt-0 border-bottom-none">Add New Field</h2>
                        
                        @include('application_templates.partials.field-form-inputs', [
                            'idPrefix' => 'add',
                            'labelName' => 'add_label',
                            'typeName' => 'add_type',
                            'reqName' => 'add_required',
                            'fileMultName' => 'add_file_multiple',
                            'fileMaxName' => 'add_file_max',
                            'charMaxName' => 'add_char_max',
                            'fileSizeMaxName' => 'add_file_size_max',
                            'optionsName' => 'add_options',
                            'optionsNameArray' => 'add_options[]',
                            'fileOptionsNameArray' => 'add_file_options[]',
                            'field' => null,
                            'isAdd' => true
                        ])
                        
                        <div class="mt-15">
                            <button type="button" class="btn btn-outline" onclick="addFieldToCreateForm()">Add Field</button>
                        </div>
                    </div>

                    <div class="mt-30">
                        <button type="submit" class="btn">Update Application Template</button>
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