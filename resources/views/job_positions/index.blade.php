@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">Job Positions - {{ $organization->name }}</h1>
        @can('create', [App\Models\JobPosition::class, $organization])
            <a href="{{ route('organizations.job-positions.create', $organization) }}" class="btn">+ Create Position</a>
        @endcan
    </div>

    @forelse ($positions as $position)
        <div class="card entry-box">
            <div class="entry-top">
                <strong class="fs-18">{{ $position->title }}</strong>
                <div>
                    <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}" class="btn btn-sm">View</a>
                    
                    @can('update', $position)
                        <a href="{{ route('organizations.job-positions.edit', [$organization, $position]) }}" class="btn btn-sm btn-purple-dark ml-5">Edit</a>
                    @endcan
                    
                    @can('delete', $position)
                        <form method="POST" action="{{ route('organizations.job-positions.destroy', [$organization, $position]) }}" class="d-inline ml-5 m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this position?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p class="m-0 mt-5 text-muted">
                @can('update', $position)
                    <span class="status status-{{ $position->status === 'open' ? 'complete' : 'awaiting-feedback' }}">{{ ucfirst($position->status) }}</span>
                @endcan

                @can('viewAny', [App\Models\Application::class, $position])
                    <span class="ml-10">{{ $position->applications_count ?? 0 }} application(s)</span>
                @endcan
            </p>
        </div>
    @empty
        <div class="card"><p>No positions yet.</p></div>
    @endforelse

    @auth
        <div class="mt-20 flex-gap-10">
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
            @can('viewAny', [App\Models\ApplicationTemplate::class, $organization])
                <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">View Application Templates</a>
            @endcan
        </div>
    @endauth
</div>
@endsection