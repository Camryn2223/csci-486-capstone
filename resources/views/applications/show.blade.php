@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 style="margin: 0;">Application - {{ $application->applicant_name }}</h1>
            <p style="color: #bdbdbd; margin: 5px 0 15px 0;"><strong>Position:</strong> {{ $application->jobPosition->title }} &bull; <strong>Organization:</strong> {{ $application->jobPosition->organization->name }}</p>
            
            <p style="margin: 5px 0;"><strong>Email:</strong> @if(!str_contains($application->applicant_email, 'no-email-')) <a href="mailto:{{ $application->applicant_email }}">{{ $application->applicant_email }}</a> @else <em>No Email Provided</em> @endif</p>
            <p style="margin: 5px 0;"><strong>Phone:</strong> {{ $application->applicant_phone ?? 'Not provided' }}</p>
            <p style="margin: 5px 0;"><strong>Submitted:</strong> {{ $application->created_at->format('M j, Y g:i A') }}</p>
            <p style="margin: 15px 0 0 0;"><strong>Status:</strong> <span class="status status-needs-review">{{ str_replace('_', ' ', Str::title($application->status)) }}</span></p>
        </div>
        
        <a href="{{ route('applications.index', [$application->jobPosition->organization, $application->jobPosition]) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Applications</a>
    </div>

    @can('updateStatus', $application)
        <div class="card">
            <h2 style="margin-top: 0;">Update Status</h2>
            <form method="POST" action="{{ route('applications.status', $application) }}" style="display: flex; gap: 10px; align-items: flex-start;">
                @csrf
                @method('PATCH')
                <select name="status" style="max-width: 300px; margin-bottom: 0;">
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
            <h2 style="margin-top: 0;">Questionnaire Answers</h2>
            @foreach ($application->answers as $answer)
                <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #2f343a;">
                    <strong style="display: block; margin-bottom: 5px; color: #a97dff;">{{ $answer->field->label }}:</strong>
                    
                    @if($answer->document)
                        <div style="display: flex; justify-content: space-between; align-items: center; background: #1f2327; padding: 10px; border-radius: 6px; border: 1px solid #3a3f45; margin-top: 10px;">
                            <span style="font-family: monospace; font-size: 14px;">{{ $answer->value }}</span>
                            
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('documents.show', $answer->document) }}" class="btn btn-sm" target="_blank" style="background: #2f3a4a;">View</a>
                                <a href="{{ route('documents.show', ['document' => $answer->document, 'download' => 1]) }}" class="btn btn-sm" style="background: #3a245a;">Download</a>
                                
                                @can('delete', $answer->document)
                                    <form method="POST" action="{{ route('documents.destroy', $answer->document) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this document?')">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @else
                        <span style="white-space: pre-wrap;">{{ $answer->value ?? 'No answer provided' }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <!-- We use whereDoesntHave('answer') below in the controller so this list strictly shows standard resumes/files, avoiding duplicates -->
        <h2 style="margin-top: 0;">Generic Documents ({{ $application->documents()->whereDoesntHave('answer')->count() }})</h2>
        @forelse ($application->documents()->whereDoesntHave('answer')->get() as $document)
            <div class="entry-box" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-family: monospace; font-size: 16px;">{{ $document->filename }} <span style="color: #bdbdbd; font-size: 13px;">({{ $document->mimetype }})</span></span>
                
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('documents.show', $document) }}" class="btn btn-sm" target="_blank" style="background: #2f3a4a;">View</a>
                    <a href="{{ route('documents.show', ['document' => $document, 'download' => 1]) }}" class="btn btn-sm" style="background: #3a245a;">Download</a>
                    
                    @can('delete', $document)
                        <form method="POST" action="{{ route('documents.destroy', $document) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this document?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p>No extra documents uploaded.</p>
        @endforelse
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;">Interviews ({{ $application->interviews->count() }})</h2>
            @can('create', [App\Models\Interview::class, $application])
                <a href="{{ route('interviews.create', $application) }}" class="btn btn-sm">+ Schedule Interview(s)</a>
            @endcan
        </div>

        @forelse ($application->interviews as $interview)
            <div class="entry-box entry-top" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong style="font-size: 16px;">{{ $interview->scheduled_at->format('M j, Y g:i A') }}</strong>
                    <span style="color: #bdbdbd; margin-left: 10px;">Interviewer(s): {{ $interview->interviewers->pluck('name')->implode(', ') }}</span>
                    <span class="status status-{{ $interview->status === 'scheduled' ? 'needs-review' : ($interview->status === 'completed' ? 'complete' : 'danger') }}" style="margin-left: 10px;">{{ ucfirst($interview->status) }}</span>
                </div>
                
                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                    <a href="{{ route('interviews.show', $interview) }}" class="btn btn-sm" style="background: #2f3a4a;">View Details</a>

                    @can('update', $interview)
                        @if ($interview->status === 'scheduled')
                            <a href="{{ route('interviews.edit', $interview) }}" class="btn btn-sm" style="background: #3a245a;">Reschedule</a>

                            <form method="POST" action="{{ route('interviews.complete', $interview) }}" style="margin: 0;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm" style="background: #0f3d1e; color: #9dffb0;" onclick="return confirm('Mark this interview as completed?')">Mark Complete</button>
                            </form>

                            <form method="POST" action="{{ route('interviews.cancel', $interview) }}" style="margin: 0;">
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