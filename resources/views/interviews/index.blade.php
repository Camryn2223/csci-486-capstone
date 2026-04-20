@extends('layouts.app')

@section('content')
<div class="container container-wide">
    <div class="card card-header-flex">
        <h1 class="m-0">Interview Calendar - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
    </div>

    <div class="card position-relative">
        <div class="card-header-flex mb-20">
            <div class="flex-gap-15 fs-14">
                <span class="flex-gap-5 items-center"><span class="legend-box legend-purple"></span> My Interviews</span>
                <span class="flex-gap-5 items-center"><span class="legend-box legend-gray"></span> Other Interviews</span>
            </div>
            
            <div>
                <label class="cursor-pointer text-primary fw-bold fs-15 m-0">
                    <input type="checkbox" id="filter-mine"> Show My Interviews Only
                </label>
            </div>
        </div>

        <div id="calendar"></div>
    </div>

    @php
        $calendarEvents = $interviews->map(function($inv) {
            $isMine = $inv->interviewers->contains('id', auth()->id());
            $interviewerNames = $inv->interviewers->pluck('name')->implode(', ');
            return [
                'id' => $inv->id,
                'title' => 'Interview: ' . $inv->application->applicant_name,
                'start' => $inv->scheduled_at->format('Y-m-d\TH:i:s'),
                'url' => route('interviews.show', $inv),
                'color' => $isMine ? 'var(--brand-base)' : 'var(--text-muted)',
                'extendedProps' => [
                    'isMine' => $isMine,
                    'position' => $inv->application->jobPosition->title,
                    'interviewer' => $interviewerNames,
                    'status' => $inv->status,
                    'canUpdate' => auth()->user()->can('update', $inv)
                ]
            ];
        });
    @endphp

    <script id="calendar-events-data" type="application/json">
        {!! json_encode($calendarEvents) !!}
    </script>
</div>
@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
@endpush