@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <div>
            <h1 class="m-0">{{ isset($isPublicView) && $isPublicView ? 'Open Positions' : 'Job Positions' }} - {{ $organization->name }}</h1>
            @if(isset($isPublicView) && $isPublicView && Auth::check() && (Auth::user()->isChairmanOf($organization) || Auth::user()->hasPermissionIn($organization, 'review_applications')))
                <p class="text-muted m-0 mt-5">Viewing as public guest.</p>
            @endif
        </div>
        
        <div class="flex-gap-10">
            @if(Auth::check() && (Auth::user()->isChairmanOf($organization) || Auth::user()->hasPermissionIn($organization, 'review_applications')))
                @if(isset($isPublicView) && $isPublicView)
                    <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-slate white-space-nowrap">Management View</a>
                @else
                    <a href="{{ route('organizations.job-positions.index', ['organization' => $organization, 'public' => 1]) }}" class="btn btn-slate white-space-nowrap">Public View</a>
                @endif
            @endif

            @if(!isset($isPublicView) || !$isPublicView)
                @can('create', [App\Models\JobPosition::class, $organization])
                    <a href="{{ route('organizations.job-positions.create', $organization) }}" class="btn white-space-nowrap">+ Create Position</a>
                @endcan
            @endif
        </div>
    </div>

    @forelse ($positions as $position)
        <div class="card entry-box">
            <div class="entry-top">
                <strong class="fs-18">{{ $position->title }}</strong>
                <div class="flex-gap-5">
                    @if(isset($isPublicView) && $isPublicView)
                        <a href="{{ route('applications.create', [$organization, $position]) }}" class="btn btn-sm btn-success-border">Apply</a>
                    @else
                        <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}" class="btn btn-sm">View</a>
                        
                        @can('update', $position)
                            <a href="{{ route('organizations.job-positions.edit', [$organization, $position]) }}" class="btn btn-sm btn-purple-dark">Edit</a>
                        @endcan
                        
                        @can('delete', $position)
                            <form method="POST" action="{{ route('organizations.job-positions.destroy', [$organization, $position]) }}" class="d-inline m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this position?')">Delete</button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>
            <p class="m-0 mt-5 text-muted">
                @if(!isset($isPublicView) || !$isPublicView)
                    @can('update', $position)
                        <span class="status status-{{ $position->status === 'open' ? 'complete' : 'awaiting-feedback' }}">{{ ucfirst($position->status) }}</span>
                    @endcan

                    @can('viewAny', [App\Models\Application::class, $position])
                        <span class="ml-10">{{ $position->applications_count ?? 0 }} application(s)</span>
                    @endcan
                @else
                    <span class="text-muted">{{ Str::limit($position->description, 100) }}</span>
                @endif
            </p>
        </div>
    @empty
        <div class="card"><p class="m-0">No open positions at this time.</p></div>
    @endforelse

    <div class="mt-20 flex-gap-10">
        @if(Auth::check())
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
            @if(!isset($isPublicView) || !$isPublicView)
                @can('viewAny', [App\Models\ApplicationTemplate::class, $organization])
                    <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">View Application Templates</a>
                @endcan
            @endif
        @else
            <a href="{{ url('/') }}" class="btn btn-outline">Back to Home</a>
        @endif
    </div>
</div>
@endsection