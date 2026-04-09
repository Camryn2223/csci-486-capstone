@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">Permissions - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
    </div>

    @foreach ($memberPermissions as $entry)
        <div class="card entry-box">
            <h3 class="h3-primary mt-0">{{ $entry['user']->name }} <span class="text-muted fs-14">({{ $entry['user']->email }})</span></h3>

            @if ($entry['user']->isChairmanOf($organization))
                <p><span class="status status-complete">All (Organization Chairman)</span></p>
            @else
                <form method="POST" action="{{ route('organizations.permissions.sync', [$organization, $entry['user']]) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid-perms">
                        @foreach ($allPermissions as $permission)
                            @php
                                $hasPermission = in_array($permission->value, $entry['granted']);
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
    @endforeach
</div>
@endsection