@extends('layouts.app')

@section('content')
    <h1>{{ $organization->name }}</h1>
    <p>Chairman: {{ $organization->chairman->name }}</p>

    <h2>Actions</h2>
    <ul>
        @can('update', $organization)
            <li><a href="{{ route('organizations.edit', $organization) }}">Edit Organization</a></li>
        @endcan
        
        @can('manageMembers', $organization)
            <li><a href="{{ route('organizations.members', $organization) }}">Manage Members</a></li>
            <li><a href="{{ route('organizations.permissions.index', $organization) }}">Manage Permissions</a></li>
        @endcan
        
        @can('create', [App\Models\OrganizationInvite::class, $organization])
            <li><a href="{{ route('organizations.invites.index', $organization) }}">Manage Invites</a></li>
        @endcan
        
        @can('create', [App\Models\JobPosition::class, $organization])
            <li><a href="{{ route('organizations.job-positions.create', $organization) }}">Create Job Position</a></li>
        @endcan
        
        @can('create', [App\Models\ApplicationTemplate::class, $organization])
            <li><a href="{{ route('organizations.application-templates.create', $organization) }}">Create Application Template</a></li>
        @endcan
        
        @can('viewAny', [App\Models\JobPosition::class, $organization])
            <li><a href="{{ route('organizations.job-positions.index', $organization) }}">View Job Positions</a></li>
        @endcan
        
        @can('viewAny', [App\Models\ApplicationTemplate::class, $organization])
            <li><a href="{{ route('organizations.application-templates.index', $organization) }}">View Templates</a></li>
        @endcan
        
        @can('viewAny', [App\Models\Interview::class, $organization])
            <li><a href="{{ route('organizations.interviews.index', $organization) }}">View Interview Calendar</a></li>
        @endcan
    </ul>

    <h2>Open Positions</h2>
    @forelse ($organization->openPositions as $position)
        <p>
            {{ $position->title }} —
            <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}">View</a>
        </p>
    @empty
        <p>No open positions.</p>
    @endforelse

    <h2>Members ({{ $organization->members->count() }})</h2>
    @foreach ($organization->members as $member)
        <p>{{ $member->name }} ({{ $member->role }})</p>
    @endforeach
@endsection