@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Members - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Organization</a>
    </div>

    @can('manageMembers', $organization)
        <div class="card">
            <h2 style="margin-top: 0;">Add Member</h2>
            <form method="POST" action="{{ route('organizations.members.add', $organization) }}" style="display: flex; gap: 10px; align-items: flex-end;">
                @csrf
                <div style="flex-grow: 1;">
                    <label>Email address (existing users are added immediately; new emails get an invite)</label>
                    <input type="email" name="email" required style="margin-bottom: 0;">
                </div>
                <button type="submit" class="btn">Add Member</button>
            </form>
        </div>
    @endcan

    <h2>Current Members</h2>
    @foreach ($organization->members as $member)
        <div class="card entry-box" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong style="font-size: 16px;">{{ $member->name }}</strong> 
                <span style="color: #bdbdbd; font-size: 14px;">({{ $member->email }})</span>
                <span class="status status-awaiting-interview" style="margin-left: 10px;">{{ ucfirst($member->role) }}</span>
            </div>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                @if ($member->id !== $organization->chairman_id)
                    @can('update', $organization)
                        <form method="POST" action="{{ route('organizations.members.role', [$organization, $member]) }}" style="display: flex; gap: 5px; align-items: center;">
                            @csrf
                            @method('PATCH')
                            <select name="role" style="margin: 0; padding: 6px; width: auto;">
                                <option value="interviewer" {{ $member->role === 'interviewer' ? 'selected' : '' }}>Interviewer</option>
                                <option value="chairman" {{ $member->role === 'chairman' ? 'selected' : '' }}>Chairman</option>
                            </select>
                            <button type="submit" class="btn btn-sm" style="background: #3a245a;">Update Role</button>
                        </form>
                    @endcan

                    @can('manageMembers', $organization)
                        <form method="POST" action="{{ route('organizations.members.remove', [$organization, $member]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove {{ $member->name }}?')">Remove</button>
                        </form>
                    @endcan
                @else
                    <span style="color: #a97dff; font-style: italic;">Chairman</span>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection