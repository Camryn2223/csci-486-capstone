@extends('layouts.app')

@push('styles')
    <style>
        /* Expandable calendar styling */
        .calendar-expanded {
            position: fixed !important;
            top: 5vh;
            left: 5vw;
            width: 90vw;
            height: 90vh;
            z-index: 9999;
            overflow: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            margin: 0 !important;
            border: 1px solid #6d3fa9 !important;
        }
        
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
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0;">{{ $organization->name }}</h1>
            <p style="color: #bdbdbd; margin: 5px 0 0 0;">Chairman: {{ $organization->chairman->name }}</p>
        </div>
        @can('update', $organization)
            <a href="{{ route('organizations.edit', $organization) }}" class="btn" style="background: #3a245a;">Edit Organization</a>
        @endcan
    </div>

    <div class="card">
        <h2 style="margin-top: 0; margin-bottom: 5px;">Quick Actions</h2>
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <h3 style="font-size: 14px; color: #a97dff; margin-bottom: 10px; margin-top: 10px;">Team & Access</h3>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @can('manageMembers', $organization)
                        <a href="{{ route('organizations.members', $organization) }}" class="btn btn-sm">Manage Members</a>
                        <a href="{{ route('organizations.permissions.index', $organization) }}" class="btn btn-sm">Manage Permissions</a>
                    @endcan
                    @can('create', [App\Models\OrganizationInvite::class, $organization])
                        <a href="{{ route('organizations.invites.index', $organization) }}" class="btn btn-sm">Manage Invites</a>
                    @endcan
                </div>
            </div>

            <div>
                <h3 style="font-size: 14px; color: #a97dff; margin-bottom: 10px; margin-top: 0;">Hiring Setup</h3>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @can('viewAny', [App\Models\JobPosition::class, $organization])
                        <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-sm" style="background: #2f3a4a;">View Job Positions</a>
                    @endcan
                    @can('create', [App\Models\JobPosition::class, $organization])
                        <a href="{{ route('organizations.job-positions.create', $organization) }}" class="btn btn-sm">Create Job Position</a>
                    @endcan
                    @can('viewAny', [App\Models\ApplicationTemplate::class, $organization])
                        <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-sm" style="background: #2f3a4a;">View Templates</a>
                    @endcan
                    @can('create', [App\Models\ApplicationTemplate::class, $organization])
                        <a href="{{ route('organizations.application-templates.create', $organization) }}" class="btn btn-sm">Create Application Template</a>
                    @endcan
                </div>
            </div>

            <div>
                <h3 style="font-size: 14px; color: #a97dff; margin-bottom: 10px; margin-top: 0;">Interviews</h3>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @can('viewAny', [App\Models\Interview::class, $organization])
                        <a href="{{ route('organizations.interviews.index', $organization) }}" class="btn btn-sm" style="background: #2f3a4a;">Interview Calendar Page</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <h2>Open Positions</h2>
    @forelse ($organization->openPositions as $position)
        <div class="card entry-box" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 15px;">
            <strong style="font-size: 16px;">{{ $position->title }}</strong>
            <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}" class="btn btn-sm">View Position</a>
        </div>
    @empty
        <div class="card"><p style="margin: 0;">No open positions.</p></div>
    @endforelse

    <h2>Members ({{ $organization->members->count() }})</h2>
    <div class="card" style="margin-bottom: 35px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px;">
            @foreach ($organization->members as $member)
                <div style="background: #1f2327; padding: 10px; border-radius: 6px; border: 1px solid #3a3f45;">
                    <strong style="display: block;">{{ $member->name }}</strong>
                    <span style="color: #bdbdbd; font-size: 13px;">{{ ucfirst($member->role) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Interactive Expandable Mini-Calendar -->
    @can('viewAny', [App\Models\Interview::class, $organization])
        <h2 style="margin-top: 0;">Schedule Overview</h2>
        <div id="calendar-wrapper" class="card" style="transition: all 0.3s ease; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- Legend -->
                    <div style="display: flex; gap: 15px; font-size: 13px;">
                        <span style="display: flex; align-items: center; gap: 5px;"><span style="width: 12px; height: 12px; background: #6d3fa9; display: inline-block; border-radius: 3px;"></span> My Interviews</span>
                        <span style="display: flex; align-items: center; gap: 5px;"><span style="width: 12px; height: 12px; background: #3a3f45; display: inline-block; border-radius: 3px;"></span> Other Interviews</span>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 15px;">
                    <label id="filter-container" style="display: none; cursor: pointer; color: #a97dff; font-weight: bold; font-size: 14px; margin: 0;">
                        <input type="checkbox" id="filter-mine"> My Interviews Only
                    </label>
                    <button id="toggle-expand" class="btn btn-sm" style="background: #3a245a;">⛶ Expand Calendar</button>
                </div>
            </div>
            
            <div id="calendar" style="min-height: 400px;"></div>
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
                    height: 500, // Compact height for mini-view
                    headerToolbar: { 
                        left: 'title', 
                        center: '', 
                        right: 'prev,next' 
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

                // Expanding / Collapsing Logic
                document.getElementById('toggle-expand').addEventListener('click', function(e) {
                    e.preventDefault();
                    const wrapper = document.getElementById('calendar-wrapper');
                    const isExpanded = wrapper.classList.toggle('calendar-expanded');
                    
                    this.textContent = isExpanded ? 'Collapse Calendar' : '⛶ Expand Calendar';
                    document.getElementById('filter-container').style.display = isExpanded ? 'inline-block' : 'none';

                    if (isExpanded) {
                        // Dark overlay background
                        let overlay = document.createElement('div');
                        overlay.id = 'calendar-overlay';
                        overlay.style = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9998; cursor:pointer;';
                        overlay.onclick = () => document.getElementById('toggle-expand').click();
                        document.body.appendChild(overlay);
                        
                        // Enlarge calendar and add tools
                        calendar.setOption('height', 'calc(90vh - 100px)');
                        calendar.setOption('headerToolbar', { 
                            left: 'prev,next today', 
                            center: 'title', 
                            right: 'dayGridMonth,timeGridWeek,listWeek' 
                        });
                    } else {
                        // Remove overlay and revert tools
                        document.getElementById('calendar-overlay')?.remove();
                        calendar.setOption('height', 500);
                        calendar.setOption('headerToolbar', { 
                            left: 'title', 
                            center: '', 
                            right: 'prev,next' 
                        });
                    }
                    
                    setTimeout(() => calendar.updateSize(), 200);
                });

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
    @endcan
</div>
@endsection