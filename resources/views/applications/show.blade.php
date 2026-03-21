@extends('layouts.app')

@section('content')
    <h1>Application - {{ $application->applicant_name }}</h1>

    <p><strong>Email:</strong> {{ $application->applicant_email }}</p>
    <p><strong>Phone:</strong> {{ $application->applicant_phone ?? 'Not provided' }}</p>
    <p><strong>Position:</strong> {{ $application->jobPosition->title }}</p>
    <p><strong>Organization:</strong> {{ $application->jobPosition->organization->name }}</p>
    <p><strong>Status:</strong> {{ $application->status }}</p>
    <p><strong>Submitted:</strong> {{ $application->created_at->format('M j, Y g:i A') }}</p>

    {{-- Status update --}}
    @can('updateStatus', $application)
        <h2>Update Status</h2>
        <form method="POST" action="{{ route('applications.status', $application) }}">
            @csrf
            @method('PATCH')
            <select name="status">
                @foreach (['submitted', 'under_review', 'no_longer_under_consideration', 'withdrawn'] as $status)
                    <option value="{{ $status }}" {{ $application->status === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
            <button type="submit">Update</button>
        </form>
    @endcan

    {{-- Answers --}}
    @if ($application->answers->isNotEmpty())
        <h2>Answers</h2>
        @foreach ($application->answers as $answer)
            <p>
                <strong>{{ $answer->field->label }}:</strong>
                {{ $answer->value ?? 'No answer provided' }}
            </p>
        @endforeach
    @endif

    {{-- Documents --}}
    <h2>Documents ({{ $application->documents->count() }})</h2>
    @forelse ($application->documents as $document)
        <p>
            {{ $document->filename }}
            ({{ $document->mimetype }})
            - <a href="{{ route('documents.show', $document) }}">Download</a>
            
            @can('delete', $document)
                |
                <form method="POST" action="{{ route('documents.destroy', $document) }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this document?')">Delete</button>
                </form>
            @endcan
        </p>
    @empty
        <p>No documents uploaded.</p>
    @endforelse

    {{-- Interviews --}}
    <h2>Interviews ({{ $application->interviews->count() }})</h2>
    @forelse ($application->interviews as $interview)
        <p>
            {{ $interview->scheduled_at->format('M j, Y g:i A') }}
            - Interviewer: {{ $interview->interviewer->name }}
            - Status: {{ $interview->status }}
            - <a href="{{ route('interviews.show', $interview) }}">View</a>
        </p>
    @empty
        <p>No interviews scheduled.</p>
    @endforelse

    @can('create', [App\Models\Interview::class, $application])
        <br>
        <a href="{{ route('interviews.create', $application) }}">+ Schedule Interview</a>
    @endcan

    <br><br>
    <a href="{{ route('applications.index', [$application->jobPosition->organization, $application->jobPosition]) }}">Back to Applications</a>
@endsection