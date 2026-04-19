@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <div>
            <h1 class="m-0">{{ $user->name }}'s Permissions</h1>
            <p class="text-muted m-0 mt-5">{{ $organization->name }}</p>
        </div>
        <div class="flex-gap-10 items-center">
            <a href="{{ route('organizations.permissions.index', $organization) }}" class="btn btn-outline">Back to Permissions</a>
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
        </div>
    </div>

    <div class="card entry-box">
        <h2 class="mt-0">{{ $user->name }} <span class="text-muted fs-14">({{ $user->email }})</span></h2>

        @if ($user->isChairmanOf($organization))
            <p><span class="status status-complete">All permissions (Organization Chairman)</span></p>
        @else
            <form method="POST" action="{{ route('organizations.permissions.sync', [$organization, $user]) }}">
                @csrf
                @method('PUT')

                <div class="grid-perms">
                    @foreach ($allPermissions as $permission)
                        @php
                            $hasPermission = in_array($permission->value, $granted);
                            $canManageThis = in_array($permission->value, $actingUserPerms);
                        @endphp
                        <label class="{{ ! $canManageThis ? 'text-muted' : 'text-light' }}">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->value }}"
                                {{ $hasPermission ? 'checked' : '' }}
                                {{ ! $canManageThis ? 'disabled' : '' }}
                            >
                            {{ str_replace('_', ' ', Str::title($permission->value)) }}
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-sm">Save Permissions</button>
            </form>
        @endif
    </div>
</div>
@endsection