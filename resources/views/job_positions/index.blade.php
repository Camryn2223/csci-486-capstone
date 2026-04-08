@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Job Positions - {{ $organization->name }}</h1>
        @can('create', [App\Models\JobPosition::class, $organization])
            <a href="{{ route('organizations.job-positions.create', $organization) }}" class="btn">+ Create Position</a>
        @endcan
    </div>

    @forelse ($positions as $position)
        <div class="card entry-box">
            <div class="entry-top">
                <strong style="font-size: 18px;">{{ $position->title }}</strong>
                <div>
                    <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}" class="btn btn-sm">View</a>
                    
                    @can('update', $position)
                        <a href="{{ route('organizations.job-positions.edit', [$organization, $position]) }}" class="btn btn-sm" style="background: #3a245a; margin-left: 5px;">Edit</a>
                    @endcan
                    
                    @can('delete', $position)
                        <form method="POST" action="{{ route('organizations.job-positions.destroy', [$organization, $position]) }}" style="display:inline; margin-left: 5px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this position?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p style="margin: 5px 0 0 0; color: #bdbdbd;">
                @can('update', $position)
                    <span class="status status-{{ $position->status === 'open' ? 'complete' : 'awaiting-feedback' }}">{{ ucfirst($position->status) }}</span>
                @endcan

                @can('viewAny', [App\Models\Application::class, $position])
                    <span style="margin-left: 10px;">{{ $position->applications_count ?? 0 }} application(s)</span>
                @endcan
            </p>
        </div>
    @empty
        <div class="card"><p>No positions yet.</p></div>
    @endforelse

    @auth
        <div style="margin-top: 20px;">
            <a href="{{ route('organizations.show', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Organization</a>
        </div>
    @endauth
</div>
@endsection