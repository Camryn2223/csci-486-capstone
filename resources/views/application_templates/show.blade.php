@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <div>
            <h1 class="m-0">Template Summary: {{ $applicationTemplate->name }}</h1>
            <p class="m-0 mt-5 text-muted">Created by {{ $applicationTemplate->creator->name }}</p>
        </div>
        <div>
            @can('update', $applicationTemplate)
                <a href="{{ route('organizations.application-templates.edit', [$organization, $applicationTemplate]) }}" class="btn mr-5">Edit Fields</a>
            @endcan
            <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">Back</a>
        </div>
    </div>

    <div class="card">
        <h2 class="mt-0 border-bottom-divider pb-15">Preview: {{ $applicationTemplate->name }}</h2>
        <p class="text-muted fs-14 mb-25">This is how the application form will appear to applicants. <em>(Form submission is disabled in preview)</em></p>

        @include('applications.partials.form-fields', ['template' => $applicationTemplate, 'isPreview' => true])
    </div>
</div>
@endsection