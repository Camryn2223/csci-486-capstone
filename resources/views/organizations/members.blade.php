@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">Members - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
    </div>

    @can('manageMembers', $organization)
        <div class="card">
            <h2 class="mt-0">Add Member</h2>
            <label>Email address (existing users are added immediately; new emails get an invite)</label>
            <form method="POST" action="{{ route('organizations.members.add', $organization) }}" class="d-flex items-center flex-gap-10" style="margin-top: 6px;">
                @csrf
                <input type="email" name="email" required class="mb-0 flex-grow-1 w-full" style="margin-top: 0;">
                <button type="submit" class="btn" style="white-space: nowrap;">Add Member</button>
            </form>
        </div>
    @endcan

    <h2>Current Members</h2>
    @foreach ($organization->members as $member)
        <div class="card entry-box card-header-flex">
            <div>
                <strong class="fs-16">{{ $member->name }}</strong> 
                <span class="text-muted fs-14">({{ $member->email }})</span>
                <span class="status status-awaiting-interview ml-10">{{ ucfirst($member->role) }}</span>
            </div>
            
            <div class="flex-gap-10 items-center">
                @if ($member->id !== $organization->chairman_id)
                    @can('update', $organization)
                        <form method="POST" action="{{ route('organizations.members.role', [$organization, $member]) }}" class="flex-gap-5 items-center m-0">
                            @csrf
                            @method('PATCH')
                            <select name="role" class="m-0 w-auto">
                                <option value="interviewer" {{ $member->role === 'interviewer' ? 'selected' : '' }}>Interviewer</option>
                                <option value="chairman" {{ $member->role === 'chairman' ? 'selected' : '' }}>Chairman</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-purple-dark">Update Role</button>
                        </form>
                    @endcan

                    @can('manageMembers', $organization)
                        <form method="POST" action="{{ route('organizations.members.remove', [$organization, $member]) }}" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove {{ $member->name }}?')">Remove</button>
                        </form>
                    @endcan
                @else
                    <span class="text-primary text-italic">Chairman</span>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection