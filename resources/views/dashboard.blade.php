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
            <h2 class="mb-5">Welcome, {{ auth()->user()->name }}</h2>
            <p class="text-muted mt-0">
                You are logged in as <strong class="text-primary">{{ auth()->user()->role }}</strong>.
            </p>
        </div>

        <div class="card">
            <h2>Quick Links</h2>
            <div class="flex-wrap-12">
                <a href="{{ route('organizations.index') }}" class="btn">My Organizations</a>
                @can('create', App\Models\Organization::class)
                    <a href="{{ route('organizations.create') }}" class="btn btn-purple-dark">Create Organization</a>
                @endcan
                <a href="{{ route('two-factor.show') }}" class="btn btn-slate">Two-Factor Settings</a>
            </div>
        </div>
    </div>
@endsection