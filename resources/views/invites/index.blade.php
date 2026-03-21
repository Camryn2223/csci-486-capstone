@extends('layouts.app')

@section('content')
    <h1>Invites - {{ $organization->name }}</h1>

    <h2>Create New Invite</h2>
    <form method="POST" action="{{ route('organizations.invites.store', $organization) }}">
        @csrf
        <label>Email address (optional - leave blank to get a code only)<br>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="interviewer@example.com">
        </label>
        <br><br>
        <button type="submit">Generate Invite</button>
    </form>

    <hr>

    <h2>Existing Invites</h2>

    @forelse ($invites as $invite)
        <p>
            <code>{{ $invite->code }}</code>
            @if ($invite->email)
                - sent to <strong>{{ $invite->email }}</strong>
            @else
                - no email target
            @endif
            - Created by {{ $invite->creator->name }}
            - {{ $invite->created_at->format('M j, Y') }}
            -
            @if ($invite->used)
                <span style="color:gray">Used</span>
            @else
                <span style="color:green">Available</span>
                |
                <a href="{{ route('register', ['invite' => $invite->code]) }}" target="_blank">Invite Link</a>
                |
                <form method="POST" action="{{ route('organizations.invites.destroy', [$organization, $invite]) }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Revoke this invite?')">Revoke</button>
                </form>
            @endif
        </p>
    @empty
        <p>No invites created yet.</p>
    @endforelse

    <br><a href="{{ route('organizations.show', $organization) }}">Back</a>
@endsection