@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">Organizations</h1>
        @can('create', App\Models\Organization::class)
            <a href="{{ route('organizations.create') }}" class="btn">+ Create Organization</a>
        @endcan
    </div>

    @forelse ($organizations as $organization)
        <div class="card entry-box">
            <div class="entry-top">
                <strong class="fs-20">{{ $organization->name }}</strong>
                <div>
                    <a href="{{ route('organizations.show', $organization) }}" class="btn btn-sm">View Dashboard</a>
                    @can('update', $organization)
                        <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-sm ml-5">Edit</a>
                    @endcan
                    @can('delete', $organization)
                        <form method="POST" action="{{ route('organizations.destroy', $organization) }}" class="d-inline ml-5 m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this organization?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p class="m-0 mt-5 text-muted">
                {{ $organization->members_count }} members &bull; {{ $organization->job_positions_count }} positions
            </p>
        </div>
    @empty
        <div class="card"><p>You don't belong to any organizations yet.</p></div>
    @endforelse
</div>
@endsection