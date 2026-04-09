@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card-header-flex mb-20">
        <h1 class="m-0">Create Job Position - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-outline">Back to Positions</a>
    </div>

    <div class="flex-gap-10 mb-20">
        <button id="btn-tab-builder" class="btn btn-tab-active" onclick="switchTab('builder')">Builder</button>
        <button id="btn-tab-preview" class="btn btn-tab-inactive" onclick="switchTab('preview')">Preview Page</button>
    </div>

    <div id="tab-builder">
        <div class="card">
            <form id="job-form" method="POST" action="{{ route('organizations.job-positions.store', $organization) }}">
                @csrf
                
                <label>Title</label>
                <input type="text" name="title" id="input-title" value="{{ old('title') }}" required oninput="updatePreview()">

                <label>Application Template</label>
                <select name="template_id" id="input-template" required onchange="updatePreview()">
                    <option value="">-- Select Template --</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                            {{ $template->name }} ({{ $template->fields_count }} fields)
                        </option>
                    @endforeach
                </select>

                <label>Description</label>
                <textarea name="description" id="input-desc" rows="4" required oninput="updatePreview()">{{ old('description') }}</textarea>

                <label>Requirements</label>
                <textarea name="requirements" id="input-reqs" rows="4" required oninput="updatePreview()">{{ old('requirements') }}</textarea>

                <label>Status</label>
                <select name="status">
                    <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <div class="mt-15">
                    <button type="submit" class="btn">Create Position</button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-preview" style="display: none;">
        @include('job_positions.partials.preview', ['jobPosition' => null, 'organization' => $organization, 'templates' => $templates, 'isBuilder' => true])
    </div>
</div>

<script id="templates-data" type="application/json">
    {!! json_encode($templates) !!}
</script>
<div id="preview-config" data-org-id="{{ $organization->id }}" class="d-none"></div>
@endsection