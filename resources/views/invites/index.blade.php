@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">Invites - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
    </div>

    <div class="card">
        <h2>Create New Invite</h2>
        <label>Email address (optional - leave blank to get a code only)</label>
        <form method="POST" action="{{ route('organizations.invites.store', $organization) }}" class="d-flex items-center flex-gap-10 mt-10">
            @csrf
            <input type="email" name="email" value="{{ old('email') }}" placeholder="interviewer@example.com" class="m-0 flex-grow-1 w-full">
            <button type="submit" class="btn m-0 white-space-nowrap">Generate Invite</button>
        </form>
    </div>

    <h2>Existing Invites</h2>

    @forelse ($invites as $invite)
        <div class="card entry-box">
            <div class="entry-top">
                <strong class="invite-code-strong">{{ $invite->code }}</strong>
                <div class="items-center d-flex">
                    @if ($invite->used)
                        <span class="status status-awaiting-interview">Used</span>
                    @else
                        <span class="status status-complete">Available</span>
                        
                        <button type="button" class="btn btn-sm btn-slate ml-10" onclick="navigator.clipboard.writeText('{{ route('register', ['invite' => $invite->code]) }}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Link', 2000);">Copy Link</button>
                        
                        <a href="{{ route('register', ['invite' => $invite->code]) }}" target="_blank" class="btn btn-sm ml-5">Test Link</a>
                        
                        <form method="POST" action="{{ route('organizations.invites.destroy', [$organization, $invite]) }}" class="d-inline ml-5 m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Revoke this invite?')">Revoke</button>
                        </form>
                    @endif
                </div>
            </div>
            <p class="m-0 mt-5 text-muted">
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