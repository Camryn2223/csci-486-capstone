@extends('layouts.app')

@section('content')
    <h1>Permissions - {{ $organization->name }}</h1>

    @foreach ($memberPermissions as $entry)
        <hr>
        <h3>{{ $entry['user']->name }} ({{ $entry['user']->email }})</h3>

        @if ($entry['user']->isChairmanOf($organization))
            <p><span style="color:green">All (Organization Chairman)</span></p>
        @else
            <form method="POST" action="{{ route('organizations.permissions.sync', [$organization, $entry['user']]) }}">
                @csrf
                @method('PUT')
                
                @foreach ($allPermissions as $permission)
                    @php
                        $hasPermission = in_array($permission->value, $entry['granted']);
                        $canManageThis = in_array($permission->value, $actingUserPerms);
                    @endphp
                    <label style="{{ ! $canManageThis ? 'color: gray;' : '' }}">
                        <input 
                            type="checkbox" 
                            name="permissions[]" 
                            value="{{ $permission->value }}"
                            {{ $hasPermission ? 'checked' : '' }}
                            {{ ! $canManageThis ? 'disabled' : '' }}
                        >
                        {{ str_replace('_', ' ', Str::title($permission->value)) }}
                    </label><br>
                @endforeach
                <br>
                <button type="submit">Save Permissions</button>
            </form>
        @endif
    @endforeach

    <br><a href="{{ route('organizations.show', $organization) }}">Back</a>
@endsection