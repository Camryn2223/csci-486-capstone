@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0;">Applications - {{ $jobPosition->title }}</h1>
            <p style="color: #bdbdbd; margin: 5px 0 0 0;">Organization: {{ $organization->name }}</p>
        </div>
        <a href="{{ route('organizations.job-positions.show', [$organization, $jobPosition]) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Position</a>
    </div>

    @forelse ($applications as $application)
        <div class="card entry-box">
            <div class="entry-top">
                <strong style="font-size: 18px;">{{ $application->applicant_name }} <span style="color: #bdbdbd; font-size: 14px;">({{ $application->applicant_email }})</span></strong>
                <a href="{{ route('applications.show', $application) }}" class="btn btn-sm">Review Application</a>
            </div>
            <p style="margin: 5px 0 0 0; color: #bdbdbd;">
                Status: <strong style="color: #e6e6e6;">{{ str_replace('_', ' ', Str::title($application->status)) }}</strong> &bull; 
                Submitted: {{ $application->created_at->format('M j, Y') }}
            </p>
        </div>
    @empty
        <div class="card"><p>No applications yet.</p></div>
    @endforelse
</div>
@endsection