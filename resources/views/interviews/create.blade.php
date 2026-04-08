@extends('layouts.app')

@push('styles')
    <!-- Flatpickr Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- TomSelect Styles (styled for dark mode) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-control { background-color: #1f2327 !important; border: 1px solid #3a3f45 !important; border-radius: 6px !important; color: #e6e6e6 !important; padding: 10px !important; min-height: 40px; }
        .ts-control input { color: #e6e6e6 !important; }
        .ts-dropdown { background-color: #1f2327 !important; border: 1px solid #3a3f45 !important; color: #e6e6e6 !important; }
        .ts-dropdown .option.active, .ts-dropdown .option:hover { background-color: #3a245a !important; color: white !important; }
        .ts-control .item { background-color: #6d3fa9 !important; color: white !important; border: none !important; border-radius: 4px !important; padding: 2px 8px !important; }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="card">
        <h1 style="margin-top: 0;">Schedule Interview</h1>
        <p><strong>Applicant:</strong> {{ $application->applicant_name }}</p>
        <p><strong>Position:</strong> {{ $application->jobPosition->title }}</p>
        <p><strong>Organization:</strong> {{ $organization->name }}</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('interviews.store', $application) }}">
            @csrf

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <label><strong>1. Select Interviewer(s)</strong></label>
                    <select id="interviewers-select" name="interviewer_ids[]" multiple required autocomplete="off">
                        <option value="">Search or select interviewers...</option>
                        @foreach ($interviewers as $interviewer)
                            <option value="{{ $interviewer->id }}" {{ is_array(old('interviewer_ids')) && in_array($interviewer->id, old('interviewer_ids')) ? 'selected' : '' }}>
                                {{ $interviewer->name }} ({{ $interviewer->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="flex: 1; min-width: 250px;">
                    <label><strong>2. Date and Time</strong></label>
                    <input type="text" id="scheduled_at_picker" name="scheduled_at" value="{{ old('scheduled_at') }}" placeholder="Select Date & Time.." required>
                </div>
            </div>

            <hr style="border-color: #3a3f45; margin: 20px 0;">

            <h3>Email Notification</h3>
            <p style="color: #bdbdbd; margin-bottom: 15px;">The applicant will receive exactly one email regardless of how many interviewers are assigned to this block.</p>

            <label><strong>Email Subject</strong></label>
            <input type="text" name="email_subject" value="{{ old('email_subject', 'Interview Scheduled: ' . $application->jobPosition->title) }}" required>

            <label><strong>Email Body</strong></label>
            <textarea name="email_body" rows="6" required>{{ old('email_body', "We are pleased to invite you to an interview for the {$application->jobPosition->title} position. Please find the details below.") }}</textarea>

            <div style="margin-top: 15px;">
                <button type="submit" class="btn">Schedule Interview(s) & Send Email</button>
                <a href="{{ route('applications.show', $application) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45; margin-left: 10px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        flatpickr("#scheduled_at_picker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: false
        });

        new TomSelect("#interviewers-select", {
            plugins: ['remove_button'],
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    </script>
@endsection