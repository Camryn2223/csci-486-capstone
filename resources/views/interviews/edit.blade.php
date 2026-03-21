@extends('layouts.app')

@section('content')
    <!-- Flatpickr Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <h1>Reschedule Interview</h1>

    <p><strong>Applicant:</strong> {{ $interview->application->applicant_name }}</p>
    <p><strong>Position:</strong> {{ $interview->application->jobPosition->title }}</p>
    <p><strong>Current time:</strong> {{ $interview->scheduled_at->format('M j, Y g:i A') }}</p>

    <form method="POST" action="{{ route('interviews.update', $interview) }}">
        @csrf
        @method('PATCH')

        <div style="margin-bottom: 15px;">
            <label>Interviewer<br>
                <select name="interviewer_id" required>
                    @foreach ($interviewers as $interviewer)
                        <option value="{{ $interviewer->id }}"
                            {{ old('interviewer_id', $interview->interviewer_id) == $interviewer->id ? 'selected' : '' }}>
                            {{ $interviewer->name }} ({{ $interviewer->role }})
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div style="margin-bottom: 15px;">
            <label>New Date and Time<br>
                <input type="text" id="scheduled_at_picker"
                       name="scheduled_at"
                       value="{{ old('scheduled_at', $interview->scheduled_at->format('Y-m-d H:i')) }}"
                       required>
            </label>
        </div>

        <button type="submit">Save Changes</button>
    </form>

    <br><a href="{{ route('interviews.show', $interview) }}">Cancel</a>

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