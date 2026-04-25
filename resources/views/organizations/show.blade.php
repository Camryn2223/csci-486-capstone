@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/gridstack@10.1.2/dist/gridstack.min.css" rel="stylesheet"/>
@endpush

@section('content')
<div class="container container-wide">
    <div class="card card-header-flex">
        <div>
            <h1 class="m-0"><span class="text-muted fs-18 fw-normal">Organization Name:</span> {{ $organization->name }}</h1>
            <p class="text-muted m-0 mt-5">Chairman: {{ $organization->chairman->name }}</p>
        </div>
        @can('update', $organization)
            <a href="{{ route('organizations.edit', $organization) }}" class="btn">Edit Organization</a>
        @endcan
    </div>

    @php
        $canManageMembers = Auth::user()->hasPermissionIn($organization, 'manage_members');
        $canCreateInvites = Auth::user()->hasPermissionIn($organization, 'create_invites');
        $showTeamAccess = $canManageMembers || $canCreateInvites;

        $canViewPositions = Auth::user()->hasPermissionIn($organization, 'review_applications') || Auth::user()->hasPermissionIn($organization, 'create_positions');
        $canCreatePositions = Auth::user()->hasPermissionIn($organization, 'create_positions');

        $canViewTemplates = Auth::user()->hasPermissionIn($organization, 'manage_templates') || Auth::user()->hasPermissionIn($organization, 'review_applications');
        $canCreateTemplates = Auth::user()->hasPermissionIn($organization, 'manage_templates');

        $canReviewApps = Auth::user()->hasPermissionIn($organization, 'review_applications');
        $canViewInterviews = Auth::user()->hasPermissionIn($organization, 'review_applications') || Auth::user()->hasPermissionIn($organization, 'schedule_interviews');

        $isChairman = Auth::user()->isChairmanOf($organization);

        $needsReviewApps = collect();
        if ($isChairman) {
            $needsReviewApps = \App\Models\Application::where('status', 'needs_chairman_review')
                ->whereHas('jobPosition', fn ($q) => $q->where('organization_id', $organization->id))
                ->with('jobPosition')
                ->latest()
                ->get();
        }

        $layout = Auth::user()->dashboard_layout ?? [];
        $getGsAttrs = function($id, $w, $h, $minW = 3, $minH = 2) use ($layout) {
            $widget = collect($layout)->firstWhere('id', $id);
            if ($widget) {
                return "gs-id=\"{$id}\" gs-x=\"{$widget['x']}\" gs-y=\"{$widget['y']}\" gs-w=\"{$widget['w']}\" gs-h=\"{$widget['h']}\" gs-min-w=\"{$minW}\" gs-min-h=\"{$minH}\"";
            }
            return "gs-id=\"{$id}\" gs-w=\"{$w}\" gs-h=\"{$h}\" gs-min-w=\"{$minW}\" gs-min-h=\"{$minH}\"";
        };
    @endphp

    <div style="overflow-x: hidden; width: 100%; padding-bottom: 20px;">
        <div class="grid-stack" id="org-dashboard-grid">
            
            <div class="grid-stack-item" {!! $getGsAttrs('actions', 12, 3, 4, 2) !!}>
                <div class="grid-stack-item-content card org-dashboard-panel org-dashboard-panel-actions">
                    <div class="org-dashboard-panel-header dash-drag-handle" title="Drag to move">
                        <div class="flex-gap-10 items-center">
                            <h2 class="m-0">Quick Actions</h2>
                        </div>
                    </div>

                    <div class="org-dashboard-scroll">
                        <div class="org-action-grid">
                            @if($showTeamAccess)
                                <a href="{{ route('organizations.members', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="users-round" class="action-icon"></i>
                                        Manage team access
                                    </strong>
                                    <span class="org-action-description">View members, manage roles, and handle organization invites.</span>
                                </a>
                            @endif

                            @if($canManageMembers)
                                <a href="{{ route('organizations.permissions.index', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="shield-check" class="action-icon"></i>
                                        Manage permissions
                                    </strong>
                                    <span class="org-action-description">Grant or revoke specific feature access for your teammates.</span>
                                </a>
                            @endif

                            @if($canReviewApps)
                                <a href="{{ route('organizations.applications', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="files" class="action-icon"></i>
                                        Review submitted applications
                                    </strong>
                                    <span class="org-action-description">See every applicant in one place and keep hiring moving.</span>
                                </a>
                            @endif

                            @if($canViewInterviews)
                                <a href="{{ route('organizations.interviews.index', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="calendar-days" class="action-icon"></i>
                                        Open the interview calendar
                                    </strong>
                                    <span class="org-action-description">Check scheduled interviews and manage upcoming sessions.</span>
                                </a>
                            @endif

                            @if($canViewPositions)
                                <a href="{{ route('organizations.job-positions.index', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="briefcase-business" class="action-icon"></i>
                                        Browse job postings
                                    </strong>
                                    <span class="org-action-description">Review open roles and see what candidates can apply for.</span>
                                </a>
                            @endif

                            @if($canCreatePositions)
                                <a href="{{ route('organizations.job-positions.create', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="circle-plus" class="action-icon"></i>
                                        Create a new job posting
                                    </strong>
                                    <span class="org-action-description">Add a new role and start collecting applications.</span>
                                </a>
                            @endif

                            @if($canViewTemplates)
                                <a href="{{ route('organizations.application-templates.index', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="clipboard-list" class="action-icon"></i>
                                        Browse application forms
                                    </strong>
                                    <span class="org-action-description">Review the templates applicants fill out before they apply.</span>
                                </a>
                            @endif

                            @if($canCreateTemplates)
                                <a href="{{ route('organizations.application-templates.create', $organization) }}" class="org-action-card">
                                    <strong class="org-action-title">
                                        <i data-lucide="file-plus-2" class="action-icon"></i>
                                        Create an application form
                                    </strong>
                                    <span class="org-action-description">Build a new template for a role or hiring workflow.</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($isChairman)
                <div class="grid-stack-item" {!! $getGsAttrs('chairman_review', 6, 3) !!}>
                    <div class="grid-stack-item-content card org-dashboard-panel">
                        <div class="org-dashboard-panel-header dash-drag-handle" title="Drag to move">
                            <div class="flex-gap-10 items-center">
                                <div>
                                    <h2 class="m-0">Needs Chairman Review</h2>
                                    <p class="text-muted m-0 mt-5">{{ $needsReviewApps->count() }} application(s) waiting on you.</p>
                                </div>
                            </div>
                        </div>

                        <div class="org-dashboard-scroll">
                            <div class="org-dashboard-list">
                                @forelse ($needsReviewApps as $application)
                                    <div class="org-dashboard-entry">
                                        <div class="card-header-flex">
                                            <div>
                                                <strong class="fs-16">{{ $application->applicant_name }}</strong>
                                                <span class="text-muted ml-5">({{ $application->jobPosition->title }})</span>
                                            </div>

                                            <a href="{{ route('applications.show', $application) }}" class="btn btn-sm">Review</a>
                                        </div>

                                        <p class="m-0 mt-5 text-muted">
                                            Status:
                                            <strong class="text-light">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($application->status)) }}</strong>
                                            &bull;
                                            Submitted: {{ $application->created_at->format('M j, Y') }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="m-0 text-muted">Nothing is waiting on chairman review right now.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(Auth::user()->hasPermissionIn($organization, 'schedule_interviews'))
                <div class="grid-stack-item" {!! $getGsAttrs('ready_schedule', 6, 3) !!}>
                    <div class="grid-stack-item-content card org-dashboard-panel">
                        <div class="org-dashboard-panel-header dash-drag-handle" title="Drag to move">
                            <div class="flex-gap-10 items-center">
                                <div>
                                    <h2 class="m-0">Ready to Schedule</h2>
                                    <p class="text-muted m-0 mt-5">{{ $unscheduledApplications->count() }} active application(s) without interviews.</p>
                                </div>
                            </div>
                        </div>

                        <div class="org-dashboard-scroll">
                            <div class="org-dashboard-list">
                                @forelse ($unscheduledApplications as $app)
                                    <div class="org-dashboard-entry">
                                        <div class="card-header-flex">
                                            <div>
                                                <strong class="fs-16">{{ $app->applicant_name }}</strong>
                                                <span class="text-muted ml-5">({{ $app->jobPosition->title }})</span>
                                                <span class="status status-{{ str_replace('_', '-', $app->status) }} ml-10">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($app->status)) }}</span>
                                            </div>

                                            <div class="flex-gap-10 items-center">
                                                <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-slate">View</a>
                                                <a href="{{ route('interviews.create', $app) }}" class="btn btn-sm">Schedule</a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="m-0 text-muted">All active applications already have interviews scheduled.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid-stack-item" {!! $getGsAttrs('open_positions', 6, 3) !!}>
                <div class="grid-stack-item-content card org-dashboard-panel">
                    <div class="org-dashboard-panel-header dash-drag-handle" title="Drag to move">
                        <div class="card-header-flex w-full">
                            <div class="flex-gap-10 items-center">
                                <div>
                                    <h2 class="m-0">Open Positions</h2>
                                    <p class="text-muted m-0 mt-5">{{ $organization->openPositions->count() }} role(s) currently open.</p>
                                </div>
                            </div>

                            @can('create', [App\Models\JobPosition::class, $organization])
                                <a href="{{ route('organizations.job-positions.create', $organization) }}" class="btn btn-sm white-space-nowrap">+ Create Position</a>
                            @endcan
                        </div>
                    </div>

                    <div class="org-dashboard-scroll">
                        <div class="org-dashboard-list">
                            @forelse ($organization->openPositions as $position)
                                <div class="org-dashboard-entry">
                                    <div class="card-header-flex">
                                        <div>
                                            <strong class="fs-16">{{ $position->title }}</strong>
                                        </div>

                                        <div class="flex-gap-5 items-center">
                                            <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}" class="btn btn-sm">View</a>
                                            @can('update', $position)
                                                <a href="{{ route('organizations.job-positions.edit', [$organization, $position]) }}" class="btn btn-sm btn-slate">Edit</a>
                                            @endcan
                                            @can('delete', $position)
                                                <form method="POST" action="{{ route('organizations.job-positions.destroy', [$organization, $position]) }}" class="d-inline m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this position?')">Delete</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="m-0 text-muted">No open positions right now.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid-stack-item" {!! $getGsAttrs('members', 6, 3) !!}>
                <div class="grid-stack-item-content card org-dashboard-panel">
                    <div class="org-dashboard-panel-header dash-drag-handle" title="Drag to move">
                        <div class="card-header-flex w-full">
                            <div class="flex-gap-10 items-center">
                                <div>
                                    <h2 class="m-0">Members</h2>
                                    <p class="text-muted m-0 mt-5">{{ $organization->members->count() }} teammate(s) in this organization.</p>
                                </div>
                            </div>

                            @if($showTeamAccess)
                                <a href="{{ route('organizations.members', $organization) }}" class="btn btn-sm white-space-nowrap">Manage Team</a>
                            @endif
                        </div>
                    </div>

                    <div class="org-dashboard-scroll">
                        <div class="grid-members">
                            @foreach ($organization->members as $member)
                                @php
                                    $memberInitial = strtoupper(mb_substr($member->name, 0, 1));
                                @endphp

                                @if($canManageMembers)
                                    <a href="{{ route('organizations.permissions.show', [$organization, $member]) }}" class="member-grid-box member-grid-link text-decoration-none">
                                        <div class="member-grid-person">
                                            <div class="avatar-circle member-avatar bg-slate text-slate-dark">{{ $memberInitial }}</div>

                                            <div class="member-grid-meta">
                                                <strong class="d-block fs-16">{{ $member->name }}</strong>
                                                <span class="text-muted fs-13">{{ ucfirst($member->role) }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    <div class="member-grid-box">
                                        <div class="member-grid-person">
                                            <div class="avatar-circle member-avatar bg-slate text-slate-dark">{{ $memberInitial }}</div>

                                            <div class="member-grid-meta">
                                                <strong class="d-block fs-16">{{ $member->name }}</strong>
                                                <span class="text-muted fs-13">{{ ucfirst($member->role) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @if($canViewTemplates)
                <div class="grid-stack-item" {!! $getGsAttrs('templates', 6, 3) !!}>
                    <div class="grid-stack-item-content card org-dashboard-panel">
                        <div class="org-dashboard-panel-header dash-drag-handle" title="Drag to move">
                            <div class="card-header-flex w-full">
                                <div class="flex-gap-10 items-center">
                                    <div>
                                        <h2 class="m-0">Application Forms</h2>
                                        <p class="text-muted m-0 mt-5">{{ $organization->templates->count() }} template(s) available.</p>
                                    </div>
                                </div>

                                @if($canCreateTemplates)
                                    <a href="{{ route('organizations.application-templates.create', $organization) }}" class="btn btn-sm white-space-nowrap">+ Create Template</a>
                                @endif
                            </div>
                        </div>

                        <div class="org-dashboard-scroll">
                            <div class="org-dashboard-list">
                                @forelse ($organization->templates as $template)
                                    <div class="org-dashboard-entry">
                                        <div class="card-header-flex">
                                            <div>
                                                <strong class="fs-16">{{ $template->name }}</strong>
                                            </div>

                                            <div class="flex-gap-5 items-center">
                                                <a href="{{ route('organizations.application-templates.show', [$organization, $template]) }}" class="btn btn-sm">View</a>

                                                @can('update', $template)
                                                    <a href="{{ route('organizations.application-templates.edit', [$organization, $template]) }}" class="btn btn-sm btn-slate">Edit</a>
                                                @endcan
                                                @can('delete', $template)
                                                    <form method="POST" action="{{ route('organizations.application-templates.destroy', [$organization, $template]) }}" class="d-inline m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this template?')">Delete</button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="m-0 text-muted">No application forms have been created yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @can('viewAny', [App\Models\Interview::class, $organization])
                <div class="grid-stack-item" {!! $getGsAttrs('calendar', 12, 5, 6) !!}>
                    <div id="calendar-wrapper" class="grid-stack-item-content card org-dashboard-panel org-dashboard-calendar-panel">
                        <div class="org-dashboard-panel-header org-dashboard-panel-header-tight dash-drag-handle" title="Drag to move">
                            <div class="flex-gap-20 items-center flex-wrap w-full card-header-flex">
                                <div class="flex-gap-10 items-center">
                                    <h2 class="m-0">Schedule Overview</h2>
                                </div>

                                <div class="flex-gap-15 items-center flex-wrap">
                                    <div class="fs-14 text-muted flex-gap-15 items-center">
                                        <span class="flex-gap-5 items-center"><span class="legend-box legend-purple"></span> My Interviews</span>
                                        <span class="flex-gap-5 items-center"><span class="legend-box legend-gray"></span> Other Interviews</span>
                                    </div>

                                    @if($canViewInterviews)
                                        <a href="{{ route('organizations.interviews.index', $organization) }}" class="btn btn-sm btn-slate">Open full calendar</a>
                                    @endif

                                    <label id="filter-container" class="d-none cursor-pointer text-primary fw-bold fs-14 m-0">
                                        <input type="checkbox" id="filter-mine"> My Interviews Only
                                    </label>

                                    <button id="toggle-expand" class="btn btn-sm" type="button">⛶ Expand Calendar</button>
                                </div>
                            </div>
                        </div>

                        <div class="org-dashboard-calendar-body">
                            <div id="mini-calendar-view"></div>
                        </div>
                    </div>
                </div>

                @php
                    $calendarEvents = $interviews->map(function ($inv) {
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
                                'canUpdate' => auth()->user()->can('update', $inv),
                            ],
                        ];
                    });
                @endphp

                <script id="mini-calendar-events-data" type="application/json">
                    {!! json_encode($calendarEvents) !!}
                </script>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @can('viewAny', [App\Models\Interview::class, $organization])
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
    @endcan
    <script src="https://cdn.jsdelivr.net/npm/gridstack@10.1.2/dist/gridstack-all.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof GridStack !== 'undefined') {
                var grid = GridStack.init({
                    column: 12,
                    cellHeight: 110,
                    margin: 10,
                    handle: '.dash-drag-handle',
                    float: true,
                    animate: true,
                    acceptWidgets: true,
                    alwaysShowResizeHandle: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
                }, '#org-dashboard-grid');

                grid.on('change', function(e, items) {
                    if (!items || items.length === 0) return;
                    
                    var layout = grid.engine.nodes.map(function(n) {
                        return {
                            id: n.id,
                            x: n.x,
                            y: n.y,
                            w: n.w,
                            h: n.h
                        };
                    });
                    
                    axios.patch('{{ route('dashboard.layout') }}', {
                        layout: layout
                    }).catch(err => console.error('Failed to save layout:', err));
                });

                grid.on('resizestop', function(event, el) {
                    if (!el || el.getAttribute('gs-id') !== 'calendar') return;

                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            if (window.miniCalendar) {
                                window.miniCalendar.updateSize();
                            }
                        });
                    });
                });
            }
        });
    </script>
@endpush