@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <div>
            <h1 class="m-0">{{ $organization->name }}</h1>
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
        $showHiringSetup = $canViewPositions || $canCreatePositions || $canViewTemplates || $canCreateTemplates;

        $canReviewApps = Auth::user()->hasPermissionIn($organization, 'review_applications');
        $canViewInterviews = Auth::user()->hasPermissionIn($organization, 'review_applications') || Auth::user()->hasPermissionIn($organization, 'schedule_interviews');
        $showInterviewsApps = $canReviewApps || $canViewInterviews;
        
        $isChairman = Auth::user()->isChairmanOf($organization);
        $needsReviewApps = collect();
        if ($isChairman) {
            $needsReviewApps = \App\Models\Application::where('status', 'needs_chairman_review')
                ->whereHas('jobPosition', fn($q) => $q->where('organization_id', $organization->id))
                ->with('jobPosition')
                ->latest()
                ->get();
        }
    @endphp

    @if($isChairman && $needsReviewApps->isNotEmpty())
        <div class="card border-warning bg-warning-light">
            <h2 class="mt-0 text-warning d-flex items-center flex-gap-10">
                ⚠️ Applications Flagged for Chairman Review
            </h2>
            <div class="flex-col-10">
                @foreach($needsReviewApps as $app)
                    <div class="entry-box card-header-flex m-0 bg-warning-darker">
                        <div>
                            <strong class="fs-16">{{ $app->applicant_name }}</strong>
                            <span class="text-muted ml-5">({{ $app->jobPosition->title }})</span>
                        </div>
                        <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-slate">Review</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($showTeamAccess || $showHiringSetup || $showInterviewsApps)
        <div class="card">
            <h2 class="mt-0 mb-5 border-bottom-none">Quick Actions</h2>
            
            <div class="flex-col-20">
                @if($showTeamAccess)
                    <div>
                        <h2 class="fs-14 text-primary mb-10 mt-10">Team & Access</h2>
                        <div class="flex-wrap-12">
                            @if($canManageMembers || $canCreateInvites)
                                <a href="{{ route('organizations.members', $organization) }}" class="btn btn-sm">Manage Team</a>
                            @endif
                            @if($canManageMembers)
                                <a href="{{ route('organizations.permissions.index', $organization) }}" class="btn btn-sm">Manage Permissions</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if($showHiringSetup)
                    <div>
                        <h2 class="fs-14 text-primary mb-10 mt-0">Hiring Setup</h2>
                        <div class="flex-wrap-12">
                            @if($canViewPositions)
                                <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-sm btn-slate">View Job Positions</a>
                            @endif
                            @if($canCreatePositions)
                                <a href="{{ route('organizations.job-positions.create', $organization) }}" class="btn btn-sm">Create Job Position</a>
                            @endif
                            @if($canViewTemplates)
                                <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-sm btn-slate">View Application Templates</a>
                            @endif
                            @if($canCreateTemplates)
                                <a href="{{ route('organizations.application-templates.create', $organization) }}" class="btn btn-sm">Create Application Template</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if($showInterviewsApps)
                    <div>
                        <h2 class="fs-14 text-primary mb-10 mt-0">Interviews & Applications</h2>
                        <div class="flex-wrap-12">
                            @if($canReviewApps)
                                <a href="{{ route('organizations.applications', $organization) }}" class="btn btn-sm btn-slate">View All Applications</a>
                            @endif
                            @if($canViewInterviews)
                                <a href="{{ route('organizations.interviews.index', $organization) }}" class="btn btn-sm btn-slate">Interview Calendar Page</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(Auth::user()->hasPermissionIn($organization, 'schedule_interviews'))
        <h2 class="mt-0">Unscheduled Applications ({{ $unscheduledApplications->count() }})</h2>
        <div class="card mb-35" style="max-height: 400px; overflow-y: auto;">
            @forelse ($unscheduledApplications as $app)
                <div class="entry-box card-header-flex m-0 mb-10 p-3">
                    <div>
                        <strong class="fs-16">{{ $app->applicant_name }}</strong>
                        <span class="text-muted ml-5">({{ $app->jobPosition->title }})</span>
                        <span class="status status-{{ str_replace('_', '-', $app->status) }} ml-10">{{ str_replace('_', ' ', Str::title($app->status)) }}</span>
                    </div>
                    <div class="flex-gap-10 items-center">
                        <a href="{{ route('applications.show', $app) }}" class="btn btn-sm btn-slate">View App</a>
                        <a href="{{ route('interviews.create', $app) }}" class="btn btn-sm">Schedule Interview</a>
                    </div>
                </div>
            @empty
                <p class="m-0 text-muted">All active applications have been scheduled for an interview.</p>
            @endforelse
        </div>
    @endif

    <h2>Open Positions</h2>
    @forelse ($organization->openPositions as $position)
        <div class="card entry-box card-header-flex mb-10 p-3">
            <strong class="fs-16">{{ $position->title }}</strong>
            <a href="{{ route('organizations.job-positions.show', [$organization, $position]) }}" class="btn btn-sm">View Position</a>
        </div>
    @empty
        <div class="card"><p class="m-0">No open positions.</p></div>
    @endforelse

    <h2>Members ({{ $organization->members->count() }})</h2>
    <div class="card mb-35">
        <div class="grid-members">
            @foreach ($organization->members as $member)
                @if($canManageMembers)
                    <a href="{{ route('organizations.permissions.show', [$organization, $member]) }}" class="member-grid-box text-decoration-none">
                        <strong class="d-block">{{ $member->name }}</strong>
                        <span class="text-muted fs-13">{{ ucfirst($member->role) }}</span>
                    </a>
                @else
                    <div class="member-grid-box">
                        <strong class="d-block">{{ $member->name }}</strong>
                        <span class="text-muted fs-13">{{ ucfirst($member->role) }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @can('viewAny', [App\Models\Interview::class, $organization])
        <h2 class="mt-0">Schedule Overview</h2>
        <div id="calendar-wrapper" class="card position-relative" style="transition: all 0.3s ease;">
            <div class="card-header-flex mb-15">
                <div class="flex-gap-20 items-center">
                    <div class="flex-gap-15 fs-13">
                        <span class="flex-gap-5 items-center"><span class="legend-box legend-purple"></span> My Interviews</span>
                        <span class="flex-gap-5 items-center"><span class="legend-box legend-gray"></span> Other Interviews</span>
                    </div>
                </div>
                
                <div class="flex-gap-15 items-center">
                    <label id="filter-container" class="d-none cursor-pointer text-primary fw-bold fs-14 m-0">
                        <input type="checkbox" id="filter-mine"> My Interviews Only
                    </label>
                    <button id="toggle-expand" class="btn btn-sm">⛶ Expand Calendar</button>
                </div>
            </div>
            
            <div id="mini-calendar-view" style="min-height: 400px;"></div>
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

        <script id="mini-calendar-events-data" type="application/json">
            {!! json_encode($calendarEvents) !!}
        </script>
    @endcan
</div>
@endsection

@push('scripts')
    @can('viewAny', [App\Models\Interview::class, $organization])
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
    @endcan
@endpush