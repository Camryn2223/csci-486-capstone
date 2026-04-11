@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@section('content')
<div class="container container-wide">
    <div class="card-header-flex mb-20">
        <h1 class="m-0">Create Application Template - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">Back to Templates</a>
    </div>

    <div class="split-layout">
        <div class="split-builder">
            <form id="template-settings-form" method="POST" action="{{ route('organizations.application-templates.store', $organization) }}">
                @csrf
                
                <div class="card">
                    @include('application_templates.partials.settings-fields', ['template' => null])
                </div>

                <div class="card-header-flex mt-30 mb-10">
                    <h2 class="m-0 border-bottom-none pb-0">Custom Fields</h2>
                    <span class="text-muted fs-13">Drag the ☰ icon to reorder fields automatically.</span>
                </div>

                <div id="fields-list">
                    <!-- Custom fields appended here by JS -->
                </div>

                <div class="card card-dashed-purple" id="add-field-card">
                    <h2 class="mt-0 border-bottom-none">Add New Field</h2>
                    
                    @include('application_templates.partials.field-form-inputs', [
                        'idPrefix' => 'add',
                        'labelName' => 'add_label',
                        'typeName' => 'add_type',
                        'reqName' => 'add_required',
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
                    <button type="submit" class="btn">Create Application Template</button>
                </div>
            </form>
        </div>

        <div class="split-preview">
            <div class="card">
                <h2 class="mt-0 pb-15 border-bottom-divider">Preview: <span id="preview-template-name">Untitled Template</span></h2>
                <p class="text-muted fs-14 mb-25">This is how the application form will appear to applicants. <em>(Form submission is disabled in preview)</em></p>

                <div id="preview-content">
                    @include('applications.partials.form-fields', ['template' => null, 'isPreview' => true, 'isBuilder' => true])
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