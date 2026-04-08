@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Invites - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Organization</a>
    </div>

    <div class="card">
        <h2>Create New Invite</h2>
        <form method="POST" action="{{ route('organizations.invites.store', $organization) }}" style="display: flex; gap: 10px; align-items: flex-end;">
            @csrf
            <div style="flex-grow: 1;">
                <label>Email address (optional - leave blank to get a code only)</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="interviewer@example.com" style="margin-bottom: 0;">
            </div>
            <button type="submit" class="btn">Generate Invite</button>
        </form>
    </div>

    <h2>Existing Invites</h2>

    @forelse ($invites as $invite)
        <div class="card entry-box">
            <div class="entry-top">
                <strong style="font-size: 18px; font-family: monospace; color: #a97dff;">{{ $invite->code }}</strong>
                <div style="display: flex; align-items: center;">
                    @if ($invite->used)
                        <span class="status status-awaiting-interview">Used</span>
                    @else
                        <span class="status status-complete">Available</span>
                        
                        <button type="button" class="btn btn-sm" style="margin-left: 10px; background: #2f3a4a;" onclick="navigator.clipboard.writeText('{{ route('register', ['invite' => $invite->code]) }}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Link', 2000);">Copy Link</button>
                        
                        <a href="{{ route('register', ['invite' => $invite->code]) }}" target="_blank" class="btn btn-sm" style="margin-left: 5px;">Test Link</a>
                        
                        <form method="POST" action="{{ route('organizations.invites.destroy', [$organization, $invite]) }}" style="display:inline; margin-left: 5px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Revoke this invite?')">Revoke</button>
                        </form>
                    @endif
                </div>
            </div>
            <p style="margin: 5px 0 0 0; color: #bdbdbd;">
                @if ($invite->email)
                    Sent to <strong>{{ $invite->email }}</strong> &bull;
                @else
                    No email target &bull;
                @endif
                Created by {{ $invite->creator->name }} &bull; {{ $invite->created_at->format('M j, Y') }}
            </p>
        </div>
    @empty
        <div class="card"><p>No invites created yet.</p></div>
    @endforelse
</div>
@endsection