@extends('layouts.app')

@section('content')
    <h1>{{ $jobPosition->title }}</h1>
    
    @can('update', $jobPosition)
        <p><strong>Status:</strong> {{ $jobPosition->status }}</p>
    @endcan

    <p><strong>Organization:</strong> {{ $jobPosition->organization->name }}</p>
    <p><strong>Description:</strong> {{ $jobPosition->description }}</p>
    <p><strong>Requirements:</strong> {{ $jobPosition->requirements }}</p>
    
    @auth
        <p><strong>Created by:</strong> {{ $jobPosition->creator->name }}</p>
    @endauth

    @auth
        @can('viewAny', [App\Models\Application::class, $jobPosition])
            <a href="{{ route('applications.index', [$jobPosition->organization, $jobPosition]) }}">
                View Applications
            </a>
        @endcan
    @endauth

    @if ($jobPosition->isOpen())
        @auth
            @can('viewAny', [App\Models\Application::class, $jobPosition])
                |
            @endcan
        @endauth
        <a href="{{ route('applications.create', [$jobPosition->organization, $jobPosition]) }}">
            Apply for this Position
        </a>
    @else
        @auth
            <p><em>This position is not currently accepting applications.</em></p>
        @endauth
    @endif

    @auth
        @can('update', $jobPosition)
            | <a href="{{ route('organizations.job-positions.edit', [$organization, $jobPosition]) }}">Edit</a>
        @endcan
    @endauth

    <br><br>
    <a href="{{ route('organizations.job-positions.index', $jobPosition->organization) }}">Back</a>
@endsection