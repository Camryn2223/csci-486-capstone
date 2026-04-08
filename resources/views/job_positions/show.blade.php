@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 style="margin: 0;">{{ $jobPosition->title }}</h1>
            <p style="color: #bdbdbd; margin: 5px 0 15px 0;">Organization: <strong>{{ $jobPosition->organization->name }}</strong></p>
            
            @can('update', $jobPosition)
                <p style="margin: 0;"><span class="status status-{{ $jobPosition->status === 'open' ? 'complete' : 'awaiting-feedback' }}">{{ ucfirst($jobPosition->status) }}</span></p>
            @endcan
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
            @auth
                @can('viewAny', [App\Models\Application::class, $jobPosition])
                    <a href="{{ route('applications.index', [$jobPosition->organization, $jobPosition]) }}" class="btn" style="background: #2f3a4a;">View Applications</a>
                @endcan
                
                @can('update', $jobPosition)
                    <a href="{{ route('organizations.job-positions.edit', [$organization, $jobPosition]) }}" class="btn" style="background: #3a245a;">Edit Position</a>
                @endcan
            @endauth
        </div>
    </div>

    <div class="card">
        <h2>Description</h2>
        <p style="white-space: pre-wrap; line-height: 1.5; margin-top: 0;">{{ $jobPosition->description }}</p>

        <h2 style="margin-top: 30px;">Requirements</h2>
        <p style="white-space: pre-wrap; line-height: 1.5; margin-top: 0;">{{ $jobPosition->requirements }}</p>
        
        @auth
            <hr style="border-color: #3a3f45; margin: 25px 0;">
            <p style="color: #bdbdbd; font-size: 14px; margin: 0;">Created by: {{ $jobPosition->creator->name }}</p>
        @endauth
    </div>

    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            @if ($jobPosition->isOpen())
                @guest
                    <a href="{{ route('applications.create', [$jobPosition->organization, $jobPosition]) }}" class="btn" style="background: #0f3d1e; color: #9dffb0; border: 1px solid #1a5c30; padding: 15px 30px; font-weight: bold;">Apply for this Position</a>
                @endguest
            @else
                @auth
                    <p style="color: #ff9d9d; margin: 0;"><em>This position is not currently accepting applications.</em></p>
                @endauth
            @endif
        </div>
        <a href="{{ route('organizations.job-positions.index', $jobPosition->organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Positions</a>
    </div>
</div>
@endsection