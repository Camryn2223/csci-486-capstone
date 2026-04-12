@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex-start">
        <div>
            <h1 class="m-0">Application - {{ $application->applicant_name }}</h1>
            <p class="text-muted m-0 mt-5 mb-15"><strong>Position:</strong> {{ $application->jobPosition->title }} &bull; <strong>Organization:</strong> {{ $application->jobPosition->organization->name }}</p>
            
            <p class="m-0 mt-5"><strong>Email:</strong> @if(!str_contains($application->applicant_email, 'no-email-')) <a href="mailto:{{ $application->applicant_email }}">{{ $application->applicant_email }}</a> @else <em>No Email Provided</em> @endif</p>
            <p class="m-0 mt-5"><strong>Phone:</strong> {{ $application->applicant_phone ?? 'Not provided' }}</p>
            <p class="m-0 mt-5"><strong>Submitted:</strong> {{ $application->created_at->format('M j, Y g:i A') }}</p>
            <p class="m-0 mt-15"><strong>Status:</strong> <span class="status status-needs-review">{{ str_replace('_', ' ', Str::title($application->status)) }}</span></p>
        </div>
        
        <a href="{{ route('applications.index', [$application->jobPosition->organization, $application->jobPosition]) }}" class="btn btn-outline">Back to Applications</a>
    </div>

    @can('updateStatus', $application)
        <div class="card">
            <h2 class="mt-0">Update Status</h2>
            <form method="POST" action="{{ route('applications.status', $application) }}" class="form-inline-start">
                @csrf
                @method('PATCH')
                <select name="status" class="max-w-300 mb-0">
                    @foreach (['submitted', 'under_review', 'no_longer_under_consideration', 'withdrawn'] as $status)
                        <option value="{{ $status }}" {{ $application->status === $status ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', Str::title($status)) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn">Update Status</button>
            </form>
        </div>
    @endcan

    @if ($application->answers->isNotEmpty())
        <div class="card">
            <h2 class="mt-0">Application Answers</h2>
            @foreach ($application->answers as $answer)
                <div class="mb-15 pb-15 border-bottom-divider">
                    <strong class="d-block mb-5 text-primary">{{ $answer->field->label }}:</strong>
                    
                    @if($answer->document)
                        @include('applications.partials.document-card', ['document' => $answer->document])
                    @else
                        <span class="white-space-pre">{{ $answer->value ?? 'No answer provided' }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-header-flex mb-20">
            <h2 class="m-0">Interviews ({{ $application->interviews->count() }})</h2>
            @can('create', [App\Models\Interview::class, $application])
                <a href="{{ route('interviews.create', $application) }}" class="btn btn-sm">+ Schedule Interview(s)</a>
            @endcan
        </div>

        @forelse ($application->interviews as $interview)
            <div class="entry-box entry-top flex-wrap flex-gap-10">
                <div>
                    <strong class="fs-16">{{ $interview->scheduled_at->format('M j, Y g:i A') }}</strong>
                    <span class="text-muted ml-10">Interviewer(s): {{ $interview->interviewers->pluck('name')->implode(', ') }}</span>
                    <span class="status status-{{ $interview->status === 'scheduled' ? 'needs-review' : ($interview->status === 'completed' ? 'complete' : 'danger') }} ml-10">{{ ucfirst($interview->status) }}</span>
                </div>
                
                <div class="flex-wrap-8">
                    <a href="{{ route('interviews.show', $interview) }}" class="btn btn-sm btn-slate">View Details</a>

                    @can('update', $interview)
                        @if ($interview->status === 'scheduled')
                            <a href="{{ route('interviews.edit', $interview) }}" class="btn btn-sm btn-purple-dark">Reschedule</a>

                            <form method="POST" action="{{ route('interviews.complete', $interview) }}" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this interview as completed?')">Mark Complete</button>
                            </form>

                            <form method="POST" action="{{ route('interviews.cancel', $interview) }}" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this interview?')">Cancel</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        @empty
            <p>No interviews scheduled.</p>
        @endforelse
    </div>
</div>
@endsection