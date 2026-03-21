@extends('layouts.app')

@section('content')
    <!-- Flatpickr Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <h1>Schedule Interview</h1>

    <p><strong>Applicant:</strong> {{ $application->applicant_name }}</p>
    <p><strong>Position:</strong> {{ $application->jobPosition->title }}</p>
    <p><strong>Organization:</strong> {{ $organization->name }}</p>

    <form method="POST" action="{{ route('interviews.store', $application) }}">
        @csrf

        <div style="margin-bottom: 15px;">
            <label><strong>1. Select Interviewer</strong><br>
                <select name="interviewer_id" required>
                    <option value="">-- Select Interviewer --</option>
                    @foreach ($interviewers as $interviewer)
                        <option value="{{ $interviewer->id }}" {{ old('interviewer_id') == $interviewer->id ? 'selected' : '' }}>
                            {{ $interviewer->name }} ({{ $interviewer->role }})
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>2. Date and Time</strong><br>
                <input type="text" id="scheduled_at_picker" name="scheduled_at" value="{{ old('scheduled_at') }}" placeholder="Select Date & Time.." required>
            </label>
        </div>

        <hr>

        <h3>Email Notification</h3>
        <p>The applicant will be sent an email with the following details once scheduled.</p>

        <div style="margin-bottom: 15px;">
            <label><strong>Email Subject</strong><br>
                <input type="text" name="email_subject" value="{{ old('email_subject', 'Interview Scheduled: ' . $application->jobPosition->title) }}" style="width: 100%; max-width: 500px;" required>
            </label>
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Email Body</strong><br>
                <textarea name="email_body" rows="6" style="width: 100%; max-width: 500px;" required>{{ old('email_body', "We are pleased to invite you to an interview for the {$application->jobPosition->title} position. Please find the details below.") }}</textarea>
            </label>
        </div>

        <button type="submit">Schedule Interview & Send Email</button>
    </form>

    <br><a href="{{ route('applications.show', $application) }}">Cancel</a>

    <!-- Flatpickr Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#scheduled_at_picker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: false
        });
    </script>
@endsection