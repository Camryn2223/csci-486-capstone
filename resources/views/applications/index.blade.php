@extends('layouts.app')

@section('content')
    <h1>Applications - {{ $jobPosition->title }}</h1>
    <p>Organization: {{ $organization->name }}</p>

    @forelse ($applications as $application)
        <hr>
        <p>
            <strong>{{ $application->applicant_name }}</strong>
            ({{ $application->applicant_email }})
            - Status: <strong>{{ $application->status }}</strong>
            - Submitted: {{ $application->created_at->format('M j, Y') }}
        </p>
        <a href="{{ route('applications.show', $application) }}">Review</a>
    @empty
        <p>No applications yet.</p>
    @endforelse

    <br><a href="{{ route('organizations.job-positions.show', [$organization, $jobPosition]) }}">Back</a>
@endsection