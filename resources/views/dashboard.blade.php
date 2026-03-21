@extends('layouts.app')

@section('content')
    <h1>Dashboard</h1>
    <p>Logged in as: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role }})</p>
    <ul>
        <li><a href="{{ route('organizations.index') }}">My Organizations</a></li>
        @can('create', App\Models\Organization::class)
            <li><a href="{{ route('organizations.create') }}">Create Organization</a></li>
        @endcan
        <li><a href="{{ route('two-factor.show') }}">Two-Factor Authentication Settings</a></li>
    </ul>
@endsection