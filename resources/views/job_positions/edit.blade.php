@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card-header-flex mb-20">
        <h1 class="m-0">Edit Job Position: {{ $jobPosition->title }}</h1>
        <a href="{{ route('organizations.job-positions.show', [$organization, $jobPosition]) }}" class="btn btn-outline">Back to Position</a>
    </div>

    <div class="flex-gap-10 mb-20">
        <button id="btn-tab-builder" class="btn btn-tab-active" onclick="switchTab('builder')">Builder</button>
        <button id="btn-tab-preview" class="btn btn-tab-inactive" onclick="switchTab('preview')">Preview Page</button>
    </div>

    <div id="tab-builder">
        <div class="card">
            <form id="job-form" method="POST" action="{{ route('organizations.job-positions.update', [$organization, $jobPosition]) }}">
                @csrf
                @method('PUT')
                
                <label>Title</label>
                <input type="text" name="title" id="input-title" value="{{ old('title', $jobPosition->title) }}" required oninput="updatePreview()">

                <label>Application Template</label>
                <div class="form-inline-start mb-18">
                    <div class="flex-grow-1">
                        <select name="template_id" id="input-template" required onchange="updatePreview(); updateTemplateLink()" class="mb-0">
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}" {{ old('template_id', $jobPosition->template_id) == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ $template->fields_count }} fields)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <a href="#" id="edit-template-btn" class="btn btn-slate white-space-nowrap" style="display: none;">Edit Selected Template</a>
                </div>

                <label>Description</label>
                <textarea name="description" id="input-desc" rows="4" required oninput="updatePreview()">{{ old('description', $jobPosition->description) }}</textarea>

                <label>Requirements</label>
                <textarea name="requirements" id="input-reqs" rows="4" required oninput="updatePreview()">{{ old('requirements', $jobPosition->requirements) }}</textarea>

                <label>Status</label>
                <select name="status">
                    <option value="open" {{ old('status', $jobPosition->status) === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ old('status', $jobPosition->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <div class="mt-15">
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-preview" style="display: none;">
        @include('job_positions.partials.preview', ['jobPosition' => $jobPosition, 'organization' => $organization, 'templates' => $templates, 'isBuilder' => true])
    </div>
</div>

<div id="preview-config" data-org-id="{{ $organization->id }}" class="d-none"></div>
@endsection