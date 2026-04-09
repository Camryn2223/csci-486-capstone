@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
    <div class="centered-content">
        <div class="form-box form-box-welcome">
            <h2>HireFlow</h2>
            <p class="text-muted mb-30">
                Manage your organization's hiring process - from job postings to interviews.
            </p>

            @auth
                @php
                    $user = auth()->user();
                    $orgs = $user->isChairman() ? $user->ownedOrganizations() : $user->organizations();
                    $dashRoute = $orgs->count() === 1 ? route('organizations.show', $orgs->first()) : route('dashboard');
                @endphp
                <a href="{{ $dashRoute }}" class="btn w-full">Go to Dashboard</a>
            @else
                <div class="flex-center-12">
                    <a href="{{ route('login') }}" class="btn">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-outline">Sign Up</a>
                </div>
            @endauth
        </div>
    </div>
@endsection