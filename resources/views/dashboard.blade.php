@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $orgs = $user->isChairman() ? $user->ownedOrganizations() : $user->organizations();
        if ($orgs->count() === 1) {
            header("Location: " . route('organizations.show', $orgs->first()));
            exit;
        }
    @endphp

    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 5px;">Welcome, {{ auth()->user()->name }}</h2>
            <p style="color: #bdbdbd; margin-top: 0;">
                You are logged in as <strong style="color: #a97dff;">{{ auth()->user()->role }}</strong>.
            </p>
        </div>

        <div class="card">
            <h2>Quick Links</h2>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('organizations.index') }}" class="btn">My Organizations</a>
                @can('create', App\Models\Organization::class)
                    <a href="{{ route('organizations.create') }}" class="btn" style="background: #3a245a;">Create Organization</a>
                @endcan
                <a href="{{ route('two-factor.show') }}" class="btn" style="background: #2f3a4a;">Two-Factor Settings</a>
            </div>
        </div>
    </div>
@endsection