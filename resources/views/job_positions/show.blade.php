@extends('layouts.app')

@section('content')
<div class="container container-wide">
    <div class="card card-header-flex">
        <div>
            <h1 class="m-0">Job Position: {{ $jobPosition->title }}</h1>
        </div>
        <div class="flex-gap-10 items-center">
            @can('update', $jobPosition)
                <a href="{{ route('organizations.job-positions.edit', [$organization, $jobPosition]) }}" class="btn">Edit Position</a>
            @endcan
            <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-outline">Back to Positions</a>
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
        </div>
    </div>

    <div class="split-layout">
        <div class="split-builder">
            <div class="card">
                <h2 class="mt-0">Position Details</h2>
                <p class="mb-10"><strong>Status:</strong> <span class="status status-{{ $jobPosition->status === 'open' ? 'complete' : 'awaiting-feedback' }}">{{ ucfirst($jobPosition->status) }}</span></p>
                <p class="mb-10"><strong>Created by:</strong> {{ $jobPosition->creator->name }}</p>
                <p class="mb-10"><strong>Created at:</strong> {{ $jobPosition->created_at->format('M j, Y') }}</p>
                <p class="mb-0"><strong>Application Template:</strong> {{ $jobPosition->template->name }}</p>

                @can('viewAny', [\App\Models\Application::class, $jobPosition])
                    <hr class="divider-20">
                    <h3 class="mt-0 mb-10">Applications</h3>
                    <p class="text-muted mb-15">There are {{ $jobPosition->applications()->count() }} application(s) submitted for this position.</p>
                    <a href="{{ route('applications.index', [$organization, $jobPosition]) }}" class="btn w-full">View Applications</a>
                @endcan
            </div>
        </div>

        <div class="split-preview">
            @include('job_positions.partials.preview', [
                'jobPosition' => $jobPosition,
                'organization' => $organization,
                'templates' => [$jobPosition->template],
                'isBuilder' => true
            ])
        </div>
    </div>
</div>
@endsection