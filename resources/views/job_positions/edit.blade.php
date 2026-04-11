@extends('layouts.app')

@section('content')
<div class="container container-wide">
    <div class="card-header-flex mb-20">
        <h1 class="m-0">Edit Job Position: {{ $jobPosition->title }}</h1>
        <a href="{{ route('organizations.job-positions.show', [$organization, $jobPosition]) }}" class="btn btn-outline">Back to Position</a>
    </div>

    <div class="split-layout">
        <div class="split-builder">
            <div class="card">
                <form id="job-form" method="POST" action="{{ route('organizations.job-positions.update', [$organization, $jobPosition]) }}">
                    @csrf
                    @method('PUT')
                    
                    @include('job_positions.partials.form-fields', ['jobPosition' => $jobPosition, 'templates' => $templates, 'isEdit' => true])
                    
                </form>
            </div>
        </div>
        <div class="split-preview">
            @include('job_positions.partials.preview', ['jobPosition' => $jobPosition, 'organization' => $organization, 'templates' => $templates, 'isBuilder' => true])
        </div>
    </div>
</div>

<div id="preview-config" data-org-id="{{ $organization->id }}" class="d-none"></div>
@endsection