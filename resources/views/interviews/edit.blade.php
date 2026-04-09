@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@section('content')
<div class="container">
    <div class="card">
        <h1 class="mt-0">Reschedule / Update Interview</h1>
        <p><strong>Applicant:</strong> {{ $interview->application->applicant_name }}</p>
        <p><strong>Position:</strong> {{ $interview->application->jobPosition->title }}</p>
        <p><strong>Current time:</strong> {{ $interview->scheduled_at->format('M j, Y g:i A') }}</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('interviews.update', $interview) }}">
            @csrf
            @method('PATCH')

            <label>Interviewer(s)</label>
            <select id="interviewers-select" name="interviewer_ids[]" multiple required autocomplete="off">
                @foreach ($interviewers as $interviewer)
                    <option value="{{ $interviewer->id }}"
                        {{ (is_array(old('interviewer_ids')) && in_array($interviewer->id, old('interviewer_ids'))) || (!old('interviewer_ids') && $interview->interviewers->contains('id', $interviewer->id)) ? 'selected' : '' }}>
                        {{ $interviewer->name }} ({{ $interviewer->role }})
                    </option>
                @endforeach
            </select>

            <label class="mt-15">New Date and Time</label>
            <input type="text" id="scheduled_at_picker" name="scheduled_at" value="{{ old('scheduled_at', $interview->scheduled_at->format('Y-m-d H:i')) }}" required>

            <div class="mt-15">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('interviews.show', $interview) }}" class="btn btn-outline ml-10">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
@endpush