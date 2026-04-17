@extends('layouts.app')

@section('content')
<div class="container container-wide">
    <div class="card card-header-flex">
        <h1 class="m-0">Team{{ Auth::user()->hasPermissionIn($organization, 'create_invites') ? ' & Invites' : '' }} - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
    </div>

    @php
        $canCreateInvites = Auth::user()->hasPermissionIn($organization, 'create_invites');
    @endphp

    <div class="{{ $canCreateInvites ? 'split-layout' : '' }}">
        <div class="{{ $canCreateInvites ? 'split-builder' : '' }}">
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

        @if($canCreateInvites)
            <div class="split-preview">
                <div class="card">
                    <h2 class="mt-0">Create New Invite</h2>
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
                                    <form method="POST" action="{{ route('organizations.invites.destroy', [$organization, $invite]) }}" class="d-inline ml-10 m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this used invite?')">Delete</button>
                                    </form>
                                @else
                                    <span class="status status-complete">Available</span>
                                    
                                    <button type="button" class="btn btn-sm btn-slate ml-10" onclick="navigator.clipboard.writeText('{{ route('register', ['invite' => $invite->code]) }}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Link', 2000);">Copy Link</button>
                                    
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
        @endif
    </div>
</div>
@endsection