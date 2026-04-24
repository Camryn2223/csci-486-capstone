@extends('layouts.app')

@section('content')
<div class="container container-wide">
    <div class="card card-header-flex">
        <h1 class="m-0">Create Job Position</h1>
        <div class="flex-gap-10">
            <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-outline">Back to Positions</a>
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
        </div>
    </div>

    <div class="split-layout">
        <div class="split-builder">
            <div class="card">
                <form id="job-form" method="POST" action="{{ route('organizations.job-positions.store', $organization) }}">
                    @csrf
                    
                    @include('job_positions.partials.form-fields', ['jobPosition' => null, 'templates' => $templates, 'isEdit' => false])
                    
                </form>
            </div>
        </div>
        <div class="split-preview">
            @include('job_positions.partials.preview', ['jobPosition' => null, 'organization' => $organization, 'templates' => $templates, 'isBuilder' => true])
        </div>
    </div>
</div>

<script id="templates-data" type="application/json">
    {!! json_encode($templates) !!}
</script>
<div id="preview-config" data-org-id="{{ $organization->id }}" class="d-none"></div>
@endsection