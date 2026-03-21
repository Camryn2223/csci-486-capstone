@extends('layouts.app')

@section('content')
    <h1>Job Positions - {{ $organization->name }}</h1>

    @can('create', [App\Models\JobPosition::class, $organization])
        <a href="{{ route('organizations.job-positions.create', $organization) }}">+ Create Position</a>
    @endcan

    @forelse ($positions as $position)
        <hr>
        <p>
            <strong>{{ $position->title }}</strong>
            
            @can('update', $position)
                [{{ $position->status }}]
            @endcan

            @can('viewAny', [App\Models\Application::class, $position])
                - {{ $position->applications_count ?? 0 }} application(s)
            @endcan
        </p>
        <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}">View</a>
        
        @can('update', $position)
            | <a href="{{ route('organizations.job-positions.edit', [$organization, $position]) }}">Edit</a>
        @endcan
        
        @can('delete', $position)
            |
            <form method="POST" action="{{ route('organizations.job-positions.destroy', [$organization, $position]) }}" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this position?')">Delete</button>
            </form>
        @endcan
    @empty
        <p>No positions yet.</p>
    @endforelse

    <br>
    @auth
        <a href="{{ route('organizations.show', $organization) }}">Back</a>
    @else
        <a href="{{ url('/') }}">Back</a>
    @endauth
@endsection