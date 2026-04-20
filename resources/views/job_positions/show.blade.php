@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex-start">
        <div>
            <h1 class="m-0">{{ $jobPosition->title }}</h1>
            <p class="text-muted m-0 mt-5 mb-15">Organization: <strong>{{ $jobPosition->organization->name }}</strong></p>
            
            @can('update', $jobPosition)
                <p class="m-0"><span class="status status-{{ $jobPosition->status === 'open' ? 'complete' : 'awaiting-feedback' }}">{{ ucfirst($jobPosition->status) }}</span></p>
            @endcan
        </div>

        <div class="flex-wrap-10 justify-end">
            @auth
                @can('viewAny', [App\Models\Application::class, $jobPosition])
                    <a href="{{ route('applications.index', [$jobPosition->organization, $jobPosition]) }}" class="btn btn-slate">View Applications</a>
                @endcan
                
                @can('update', $jobPosition)
                    <a href="{{ route('organizations.job-positions.edit', [$organization, $jobPosition]) }}" class="btn">Edit Position</a>
                @endcan
            @endauth
        </div>
    </div>

    <div class="card">
        <h2>Description</h2>
        <p class="white-space-pre mt-0">{{ $jobPosition->description }}</p>

        <h2 class="mt-30">Requirements</h2>
        <p class="white-space-pre mt-0">{{ $jobPosition->requirements }}</p>
        
        @auth
            <hr class="divider">
            <p class="text-muted fs-14 m-0">Created by: {{ $jobPosition->creator->name }}</p>
        @endauth
    </div>

    <div class="card card-header-flex">
        <div>
            @if ($jobPosition->isOpen())
                @guest
                    <a href="{{ route('applications.create', [$jobPosition->organization, $jobPosition]) }}" class="btn btn-success-border p-3 fw-bold">Apply for this Position</a>
                @endguest
            @else
                @auth
                    <p class="text-danger m-0"><em>This position is not currently accepting applications.</em></p>
                @endauth
            @endif
        </div>
        <a href="{{ route('organizations.job-positions.index', $jobPosition->organization) }}" class="btn btn-outline">Back to Positions</a>
    </div>
</div>
@endsection