@extends('layouts.app')

@section('content')
    <h1>Interview Details</h1>

    <p><strong>Applicant:</strong> {{ $interview->application->applicant_name }}</p>
    <p><strong>Applicant Email:</strong> {{ $interview->application->applicant_email }}</p>
    <p><strong>Position:</strong> {{ $interview->application->jobPosition->title }}</p>
    <p><strong>Organization:</strong> {{ $interview->application->jobPosition->organization->name }}</p>
    <p><strong>Interviewer:</strong> {{ $interview->interviewer->name }}</p>
    <p><strong>Scheduled:</strong> {{ $interview->scheduled_at->format('M j, Y g:i A') }}</p>
    <p><strong>Status:</strong> {{ $interview->status }}</p>

    @if ($interview->notes)
        <h2>Feedback Notes</h2>
        <p>{{ $interview->notes }}</p>
        <p><em>Submitted: {{ $interview->feedback_submitted_at->format('M j, Y g:i A') }}</em></p>
    @endif

    <h2>Actions</h2>

    @can('update', $interview)
        @if ($interview->isScheduled())
            <a href="{{ route('interviews.edit', $interview) }}">Reschedule</a> |

            <form method="POST" action="{{ route('interviews.complete', $interview) }}" style="display:inline">
                @csrf
                @method('PATCH')
                <button type="submit" onclick="return confirm('Mark this interview as completed?')">
                    Mark Completed
                </button>
            </form>
            |
            <form method="POST" action="{{ route('interviews.cancel', $interview) }}" style="display:inline">
                @csrf
                @method('PATCH')
                <button type="submit" onclick="return confirm('Cancel this interview?')">Cancel Interview</button>
            </form>
        @endif
    @endcan

    @can('submitFeedback', $interview)
        @if ($interview->isCompleted() && ! $interview->hasFeedback())
            <h2>Submit Feedback</h2>
            <form method="POST" action="{{ route('interviews.feedback', $interview) }}">
                @csrf
                @method('PATCH')
                <label>Notes<br>
                    <textarea name="notes" rows="6" cols="60" required>{{ old('notes') }}</textarea>
                </label>
                <br><br>
                <button type="submit">Submit Feedback</button>
            </form>
        @endif
    @endcan

    <br><br>
    <a href="{{ route('applications.show', $interview->application) }}">Back to Application</a>
@endsection