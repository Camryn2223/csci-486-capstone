@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@section('content')
<div class="container container-wide">
    <div class="card">
        <h1 class="mt-0">Schedule Interview</h1>
        <p><strong>Applicant:</strong> {{ $application->applicant_name }}</p>
        <p><strong>Position:</strong> {{ $application->jobPosition->title }}</p>
        <p><strong>Organization:</strong> {{ $organization->name }}</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('interviews.store', $application) }}">
            @csrf

            @include('interviews.partials.form-fields', [
                'step1Label' => '1. Select Interviewer(s)',
                'step2Label' => '2. Select Time (Click an open slot)',
                'interview' => null,
                'application' => $application,
                'schedules' => $schedules
            ])

            <hr class="divider-20">

            <h3>Email Notification</h3>
            <p class="text-muted mb-15">The applicant will receive exactly one email regardless of how many interviewers are assigned to this block.</p>

            <label><strong>Email Subject</strong></label>
            <input type="text" name="email_subject" value="{{ old('email_subject', 'Interview Scheduled: ' . $application->jobPosition->title) }}" required>

            <label><strong>Email Body</strong></label>
            <textarea name="email_body" rows="6" required>{{ old('email_body', "We are pleased to invite you to an interview for the {$application->jobPosition->title} position. Please find the details below.") }}</textarea>

            <div class="mt-15">
                <button type="submit" class="btn">Schedule Interview(s) & Send Email</button>
                <a href="{{ route('applications.show', $application) }}" class="btn btn-outline ml-10">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
@endpush