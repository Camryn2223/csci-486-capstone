@extends('layouts.app')

@section('content')
    <h1>Interview Calendar - {{ $organization->name }}</h1>

    @forelse ($interviews as $interview)
        <hr>
        <p>
            <strong>{{ $interview->scheduled_at->format('M j, Y g:i A') }}</strong><br>
            Applicant: {{ $interview->application->applicant_name }}<br>
            Position: {{ $interview->application->jobPosition->title }}<br>
            Interviewer: {{ $interview->interviewer->name }}<br>
            Status: {{ $interview->status }}
        </p>
        <a href="{{ route('interviews.show', $interview) }}">View</a>
    @empty
        <p>No upcoming interviews.</p>
    @endforelse

    <br><a href="{{ route('organizations.show', $organization) }}">Back</a>
@endsection