@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Permissions - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Organization</a>
    </div>

    @foreach ($memberPermissions as $entry)
        <div class="card entry-box">
            <h3 style="margin-top: 0; color: #a97dff;">{{ $entry['user']->name }} <span style="color: #bdbdbd; font-size: 14px;">({{ $entry['user']->email }})</span></h3>

            @if ($entry['user']->isChairmanOf($organization))
                <p><span class="status status-complete">All (Organization Chairman)</span></p>
            @else
                <form method="POST" action="{{ route('organizations.permissions.sync', [$organization, $entry['user']]) }}">
                    @csrf
                    @method('PUT')
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px;">
                        @foreach ($allPermissions as $permission)
                            @php
                                $hasPermission = in_array($permission->value, $entry['granted']);
                                $canManageThis = in_array($permission->value, $actingUserPerms);
                            @endphp
                            <label style="{{ ! $canManageThis ? 'color: #555;' : 'color: #e6e6e6;' }}">
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