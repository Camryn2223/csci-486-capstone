@extends('layouts.app')

@push('styles')
    <style>
        .fc-event {
            cursor: pointer;
        }

        /* Tippy tooltip styling overrides */
        .tippy-box {
            background-color: #1a1d21;
            color: #e6e6e6;
            border: 1px solid #3a3f45;
            box-shadow: 0 6px 15px rgba(0,0,0,0.7);
            padding: 8px;
        }
        .tippy-arrow {
            color: #1a1d21;
        }
    </style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px;"> <!-- Wider container for full calendar -->
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Interview Calendar - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.show', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Organization</a>
    </div>

    <div class="card" style="position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; gap: 15px; font-size: 14px;">
                <span style="display: flex; align-items: center; gap: 5px;"><span style="width: 12px; height: 12px; background: #6d3fa9; display: inline-block; border-radius: 3px;"></span> My Interviews</span>
                <span style="display: flex; align-items: center; gap: 5px;"><span style="width: 12px; height: 12px; background: #3a3f45; display: inline-block; border-radius: 3px;"></span> Other Interviews</span>
            </div>
            
            <div>
                <label style="cursor: pointer; color: #a97dff; font-weight: bold; font-size: 15px; margin: 0;">
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
                'start' => $inv->scheduled_at->toIso8601String(),
                'url' => route('interviews.show', $inv),
                'color' => $isMine ? '#6d3fa9' : '#3a3f45',
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

    <!-- FullCalendar & Tippy.js Dependencies -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var eventsData = @json($calendarEvents);
            var showOnlyMine = false;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 700,
                headerToolbar: { 
                    left: 'prev,next today', 
                    center: 'title', 
                    right: 'dayGridMonth,timeGridWeek,listWeek' 
                },
                events: eventsData,
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); // Prevents instant navigation so users can use the tooltip buttons
                },
                eventDidMount: function(info) {
                    let actionButtons = `<div style="margin-top:15px; display:flex; gap:8px; flex-wrap:wrap;">`;
                    actionButtons += `<a href="${info.event.url}" class="btn btn-sm">View Details</a>`;
                    
                    if (info.event.extendedProps.canUpdate && info.event.extendedProps.status === 'scheduled') {
                        actionButtons += `
                            <a href="/interviews/${info.event.id}/edit" class="btn btn-sm" style="background:#3a245a;">Reschedule</a>
                            <form method="POST" action="/interviews/${info.event.id}/complete" style="margin:0;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="PATCH">
                                <button type="submit" class="btn btn-sm" style="background:#0f3d1e; color:#9dffb0;" onclick="return confirm('Mark completed?')">Complete</button>
                            </form>
                            <form method="POST" action="/interviews/${info.event.id}/cancel" style="margin:0;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="PATCH">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel interview?')">Cancel</button>
                            </form>
                        `;
                    }
                    actionButtons += `</div>`;

                    tippy(info.el, {
                        content: `
                            <div style="text-align:left; padding:5px;">
                                <strong style="color:#a97dff; font-size:16px;">Applicant: ${info.event.title.replace('Interview: ', '')}</strong><br>
                                <div style="margin-top:8px;">
                                    <strong>Position:</strong> ${info.event.extendedProps.position}<br>
                                    <strong>Interviewer(s):</strong> ${info.event.extendedProps.interviewer}<br>
                                    <strong>Status:</strong> ${info.event.extendedProps.status.toUpperCase()}
                                </div>
                                ${actionButtons}
                            </div>
                        `,
                        allowHTML: true,
                        interactive: true,
                        placement: 'auto', // Safely flips if near edge
                        appendTo: document.body, // Breaks tooltip out of any clipping container!
                        maxWidth: 550, // Tells JS it has plenty of room
                        theme: 'translucent',
                        delay: [50, 50],
                    });
                }
            });
            
            calendar.render();

            // Filter Logic
            document.getElementById('filter-mine').addEventListener('change', function(e) {
                showOnlyMine = e.target.checked;
                var filtered = eventsData.filter(function(ev) {
                    return showOnlyMine ? ev.extendedProps.isMine : true;
                });
                calendar.removeAllEvents();
                calendar.addEventSource(filtered);
            });
        });
    </script>
</div>
@endsection