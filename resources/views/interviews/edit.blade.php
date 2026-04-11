@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@section('content')
<div class="container container-wide">
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

            @include('interviews.partials.form-fields', [
                'step1Label' => 'Interviewer(s)',
                'step2Label' => 'New Date and Time (Click an open slot)',
                'interview' => $interview,
                'application' => $interview->application,
                'schedules' => $schedules
            ])

            <div class="mt-15">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('interviews.show', $interview) }}" class="btn btn-outline ml-10">Cancel</a>
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