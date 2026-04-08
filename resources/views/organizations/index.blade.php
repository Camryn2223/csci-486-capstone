@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Organizations</h1>
        @can('create', App\Models\Organization::class)
            <a href="{{ route('organizations.create') }}" class="btn">+ Create Organization</a>
        @endcan
    </div>

    @forelse ($organizations as $organization)
        <div class="card entry-box">
            <div class="entry-top">
                <strong style="font-size: 20px;">{{ $organization->name }}</strong>
                <div>
                    <a href="{{ route('organizations.show', $organization) }}" class="btn btn-sm">View Dashboard</a>
                    @can('update', $organization)
                        <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-sm" style="background: #3a245a; margin-left: 5px;">Edit</a>
                    @endcan
                    @can('delete', $organization)
                        <form method="POST" action="{{ route('organizations.destroy', $organization) }}" style="display:inline; margin-left: 5px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this organization?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p style="margin: 5px 0 0 0; color: #bdbdbd;">
                {{ $organization->members_count }} members &bull; {{ $organization->job_positions_count }} positions
            </p>
        </div>
    @empty
        <div class="card"><p>You don't belong to any organizations yet.</p></div>
    @endforelse
</div>
@endsection