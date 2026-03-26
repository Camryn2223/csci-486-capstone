@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <div class="card">
            <h2>Welcome, {{ auth()->user()->name }}</h2>
            <p style="color: #bdbdbd;">
                You are logged in as <strong style="color: #a97dff;">{{ auth()->user()->role }}</strong>.
            </p>
        </div>

        <div class="card">
            <h2>Quick Links</h2>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('organizations.index') }}" class="btn btn-sm">My Organizations</a>
                @can('create', App\Models\Organization::class)
                    <a href="{{ route('organizations.create') }}" class="btn btn-sm">Create Organization</a>
                @endcan
                <a href="{{ route('two-factor.show') }}" class="btn btn-sm">Two-Factor Settings</a>
            </div>
        </div>
    </div>
@endsection