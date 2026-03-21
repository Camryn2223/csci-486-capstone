@extends('layouts.app')

@section('content')
    <h1>Members - {{ $organization->name }}</h1>

    @can('manageMembers', $organization)
        <h2>Add Member</h2>
        <form method="POST" action="{{ route('organizations.members.add', $organization) }}">
            @csrf
            <label>Email address<br>
                <input type="email" name="email" required>
            </label>
            <button type="submit">Add</button>
        </form>
    @endcan

    <h2>Current Members</h2>
    @foreach ($organization->members as $member)
        <p>
            {{ $member->name }} ({{ $member->email }}) - {{ $member->role }}
            
            @if ($member->id !== $organization->chairman_id)
                @can('manageMembers', $organization)
                    <form method="POST" action="{{ route('organizations.members.remove', [$organization, $member]) }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Remove {{ $member->name }}?')">Remove</button>
                    </form>
                @endcan

                @can('update', $organization)
                    <form method="POST" action="{{ route('organizations.members.role', [$organization, $member]) }}" style="display:inline">
                        @csrf
                        @method('PATCH')
                        <select name="role">
                            <option value="interviewer" {{ $member->role === 'interviewer' ? 'selected' : '' }}>Interviewer</option>
                            <option value="chairman" {{ $member->role === 'chairman' ? 'selected' : '' }}>Chairman</option>
                        </select>
                        <button type="submit">Update Role</button>
                    </form>
                @endcan
            @else
                <em>(Chairman)</em>
            @endif
        </p>
    @endforeach

    <br><a href="{{ route('organizations.show', $organization) }}">Back</a>
@endsection