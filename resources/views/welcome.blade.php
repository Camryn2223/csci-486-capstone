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
                <div class="flex-center-12 flex-gap-10 d-flex justify-center">
                    <a href="{{ route('login') }}" class="btn">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-outline">Sign Up</a>
                </div>
            @endauth
        </div>

        @guest
            <div class="mt-40 w-full max-w-400">
                <h3 class="text-center mb-15 text-light border-bottom-divider pb-10">Public Job Boards</h3>
                <div class="flex-col-10">
                    @php
                        $publicOrgs = \App\Models\Organization::whereHas('jobPositions', function($q) {
                            $q->where('status', 'open');
                        })->withCount(['jobPositions' => function($q) {
                            $q->where('status', 'open');
                        }])->get();
                    @endphp
                    @forelse($publicOrgs as $org)
                        <a href="{{ route('organizations.job-positions.index', $org) }}" class="card text-center mb-0 transition-hover py-15">
                            <strong class="fs-18 d-block mb-5 text-primary">{{ $org->name }}</strong>
                            <span class="text-muted">{{ $org->job_positions_count }} open position(s)</span>
                        </a>
                    @empty
                        <p class="text-muted text-center">No public job boards available at this time.</p>
                    @endforelse
                </div>
            </div>
        @endguest
    </div>
@endsection