@extends('layouts.app')

@section('content')
    <h1>Organizations</h1>

    @can('create', App\Models\Organization::class)
        <a href="{{ route('organizations.create') }}">+ Create Organization</a>
    @endcan

    @forelse ($organizations as $organization)
        <hr>
        <p>
            <strong>{{ $organization->name }}</strong>
            ({{ $organization->members_count }} members, {{ $organization->job_positions_count }} positions)
        </p>
        
        <a href="{{ route('organizations.show', $organization) }}">View</a>
        
        @can('update', $organization)
            | <a href="{{ route('organizations.edit', $organization) }}">Edit</a>
        @endcan
        
        @can('delete', $organization)
            |
            <form method="POST" action="{{ route('organizations.destroy', $organization) }}" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this organization?')">Delete</button>
            </form>
        @endcan
    @empty
        <p>You don't belong to any organizations yet.</p>
    @endforelse
@endsection