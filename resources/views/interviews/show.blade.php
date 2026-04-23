@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">Interview Details</h1>
        <a href="{{ route('applications.show', $interview->application) }}" class="btn btn-outline">Back to Application</a>
    </div>

    <div class="card">
        <p><strong>Applicant:</strong> {{ $interview->application->applicant_name }}</p>
        <p><strong>Applicant Email:</strong> {{ $interview->application->applicant_email }}</p>
        <p><strong>Position:</strong> {{ $interview->application->jobPosition->title }}</p>
        <p><strong>Organization:</strong> {{ $interview->application->jobPosition->organization->name }}</p>
        <p><strong>Interviewer(s):</strong> {{ $interview->interviewers->pluck('name')->implode(', ') }}</p>
        <p><strong>Scheduled:</strong> {{ $interview->scheduled_at->format('M j, Y g:i A') }}</p>
        <p><strong>Status:</strong> <span class="status status-{{ $interview->status === 'scheduled' ? 'needs-review' : ($interview->status === 'completed' ? 'complete' : 'danger') }}">{{ ucfirst($interview->status) }}</span></p>

        @foreach ($interview->interviewers as $interviewer)
            @if ($interviewer->pivot->notes)
                <hr class="divider-20">
                <h2>Feedback from {{ $interviewer->name }}</h2>
                <div class="feedback-box rich-text-content">
                    {!! clean($interviewer->pivot->notes) !!}
                </div>
                <p class="text-muted fs-13"><em>Submitted: {{ \Carbon\Carbon::parse($interviewer->pivot->feedback_submitted_at)->format('M j, Y g:i A') }}</em></p>
            @endif
        @endforeach
    </div>

    @if((Auth::user()->can('update', $interview) && $interview->isScheduled()) || (Auth::user()->can('submitFeedback', $interview) && $interview->isCompleted() && ! $interview->hasFeedbackFrom(Auth::user())))
        <div class="card">
            <h2>Actions</h2>

            @can('update', $interview)
                @if ($interview->isScheduled())
                    <div class="flex-gap-10">
                        <a href="{{ route('interviews.edit', $interview) }}" class="btn">Reschedule</a>
                        
                        <form method="POST" action="{{ route('interviews.complete', $interview) }}" class="m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Mark this interview as completed?')">Mark Completed</button>
                        </form>
                        
                        <form method="POST" action="{{ route('interviews.cancel', $interview) }}" class="m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this interview?')">Cancel Interview</button>
                        </form>
                    </div>
                @endif
            @endcan

            @can('submitFeedback', $interview)
                @if ($interview->isCompleted() && ! $interview->hasFeedbackFrom(Auth::user()))
                    <h3 class="mt-20">Submit Feedback</h3>
                    <form method="POST" action="{{ route('interviews.feedback', $interview) }}">
                        @csrf
                        @method('PATCH')
                        <label>Notes</label>
                        <textarea name="notes" rows="6" required>{{ old('notes') }}</textarea>
                        <button type="submit" class="btn">Submit Feedback</button>
                    </form>
                @endif
            @endcan
        </div>
    @endif
</div>
@endsection