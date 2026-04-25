@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@section('content')
<div class="container container-wide">
    <div class="card card-header-flex-start">
        <div>
            <h1 class="m-0">Application - {{ $application->applicant_name }}</h1>
            <p class="text-muted m-0 mt-5 mb-15"><strong>Position:</strong> {{ $application->jobPosition->title }} &bull; <strong>Organization:</strong> {{ $application->jobPosition->organization->name }}</p>
            
            <p class="m-0 mt-5"><strong>Email:</strong> @if(!str_contains($application->applicant_email, 'no-email-')) <a href="mailto:{{ $application->applicant_email }}">{{ $application->applicant_email }}</a> @else <em>No Email Provided</em> @endif</p>
            <p class="m-0 mt-5"><strong>Phone:</strong> {{ $application->applicant_phone ?? 'Not provided' }}</p>
            <p class="m-0 mt-5"><strong>Submitted:</strong> {{ $application->created_at->format('M j, Y g:i A') }}</p>
            
            <div class="mt-15 flex-gap-10 items-center">
                <strong class="m-0">Status:</strong>
                @can('updateStatus', $application)
                    <form method="POST" action="{{ route('applications.status', $application) }}" id="status-form" class="m-0 d-flex flex-gap-10 items-center">
                        @csrf
                        @method('PATCH')
                        <select name="status" id="status-select" class="m-0 w-auto" style="min-width: 200px; padding-top: 6px; padding-bottom: 6px;">
                            @foreach (['submitted', 'under_review', 'needs_chairman_review', 'no_longer_under_consideration', 'withdrawn'] as $status)
                                <option value="{{ $status }}" {{ $application->status === $status ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', Str::title($status)) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" id="status-update-btn" class="btn btn-sm m-0">Update</button>
                    </form>

                    <form method="POST" action="{{ route('applications.reject', $application) }}" id="reject-form" class="m-0">
                        @csrf
                        <div id="rejection-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
                            <div class="card" style="width:500px; max-width:90%; margin:0;">
                                <h2 class="mt-0">No Longer Under Consideration</h2>
                                <p class="text-muted">This will email the applicant to inform them. You may include an optional message explaining the reason.</p>
                                <label>Reason (optional)</label>
                                <textarea name="rejection_reason" id="rejection-reason-input" rows="4" placeholder="We appreciate your interest, however..."></textarea>
                                <div class="d-flex flex-gap-10 justify-end mt-15">
                                    <button type="button" id="rejection-cancel" class="btn btn-outline">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Confirm & Notify</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    <span class="status status-{{ str_replace('_', '-', $application->status) }} m-0">{{ str_replace('_', ' ', Str::title($application->status)) }}</span>
                @endcan
            </div>
        </div>
        
        <div class="flex-gap-10 items-center">
            @can('delete', $application)
                <form method="POST" action="{{ route('applications.destroy', $application) }}" class="m-0 d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline text-danger" style="border-color: var(--danger-border);" onclick="return confirm('Are you sure you want to delete this application? This action cannot be undone.')">Delete</button>
                </form>
            @endcan
            <a href="{{ route('applications.index', [$application->jobPosition->organization, $application->jobPosition]) }}" class="btn btn-outline">Back to Applications</a>
        </div>
    </div>

    <div class="split-layout">
        <div class="split-main">
            @if ($application->answers->isNotEmpty())
                <div class="card">
                    <h2 class="mt-0">Application Answers</h2>
                    @foreach ($application->answers->groupBy('template_field_id') as $fieldId => $answers)
                    <div class="mb-15 pb-15 border-bottom-divider">
                        <strong class="d-block mb-5 text-primary">{{ $answers->first()->field->label }}:</strong>

                        @foreach ($answers as $answer)
                            @if($answer->document)
                                @include('applications.partials.document-card', ['document' => $answer->document])
                            @elseif($answer->field->type === 'rich_text')
                                <div class="rich-text-content mt-5">{!! clean($answer->value ?? 'No answer provided') !!}</div>
                            @else
                                <span class="white-space-pre">{{ $answer->value ?? 'No answer provided' }}</span>
                            @endif
                        @endforeach
                    </div>
                @endforeach
                </div>
            @endif

            <div class="card">
                <div class="card-header-flex mb-20">
                    <h2 class="m-0">Interviews ({{ $application->interviews->count() }})</h2>
                    @can('create', [App\Models\Interview::class, $application])
                        <a href="{{ route('interviews.create', $application) }}" class="btn btn-sm">+ Schedule Interview</a>
                    @endcan
                </div>

                @forelse ($application->interviews as $interview)
                    <div class="entry-box entry-top flex-wrap flex-gap-10">
                        <div>
                            <strong class="fs-16">{{ $interview->scheduled_at->format('M j, Y g:i A') }}</strong>
                            <span class="text-muted ml-10">Interviewer(s): {{ $interview->interviewers->pluck('name')->implode(', ') }}</span>
                            <span class="status status-{{ $interview->status === 'scheduled' ? 'needs-review' : ($interview->status === 'completed' ? 'complete' : 'danger') }} ml-10">{{ ucfirst($interview->status) }}</span>
                        </div>
                        
                        <div class="flex-wrap-8">
                            <a href="{{ route('interviews.show', $interview) }}" class="btn btn-sm btn-slate">View Details</a>

                            @can('update', $interview)
                                @if ($interview->status === 'scheduled')
                                    <a href="{{ route('interviews.edit', $interview) }}" class="btn btn-sm">Reschedule</a>

                                    <form method="POST" action="{{ route('interviews.complete', $interview) }}" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this interview as completed?')">Mark Complete</button>
                                    </form>

                                    <form method="POST" action="{{ route('interviews.cancel', $interview) }}" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this interview?')">Cancel</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </div>
                @empty
                    <p>No interviews scheduled.</p>
                @endforelse
            </div>
        </div>

        <div class="split-sidebar split-sidebar-notes">
            <div class="card mb-20" style="padding: 0; overflow: hidden;">
                <div class="card-header-flex border-bottom-divider" style="padding: 15px 20px; background: var(--bg-entry);">
                    <h2 class="m-0 fs-18 flex-gap-10 items-center">
                        <i data-lucide="share-2" class="text-primary" style="width: 20px; height: 20px;"></i> 
                        Share Application
                    </h2>
                    
                    <a href="{{ route('applications.pdf', $application) }}" target="_blank" class="btn btn-sm btn-slate m-0">Preview PDF</a>
                </div>
                
                <div style="padding: 15px 20px;">
                    <p class="text-muted fs-13 mb-15">Email a generated PDF version of this application to internal members or external recipients.</p>
                    <form method="POST" action="{{ route('applications.share', $application) }}">
                        @csrf
                        <div class="mb-15">
                            <label>Recipients</label>
                            <select id="share_emails" name="emails[]" multiple required autocomplete="off">
                                <option value="">Type email or select...</option>
                                @foreach($application->jobPosition->organization->members as $member)
                                    <option value="{{ $member->email }}">{{ $member->name }} ({{ $member->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-15">
                            <label>Optional Message</label>
                            <textarea name="message" rows="3" placeholder="I thought you should review this candidate..."></textarea>
                        </div>

                        <button type="submit" class="btn w-full">Email PDF</button>
                    </form>
                </div>
            </div>

            @include('partials.interview-notes', ['interviews' => $application->interviews])
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('share_emails')) {
                new TomSelect('#share_emails', {
                    plugins: ['remove_button'],
                    create: true,
                    createFilter: function(input) {
                        // Regex to broadly match valid emails
                        var regex = /^[\w\-\.\+]+@([\w\-]+\.)+[\w\-]{2,4}$/;
                        return regex.test(input);
                    },
                    placeholder: "Select members or type external emails..."
                });
            }

            const statusForm = document.getElementById('status-form');
            const statusSelect = document.getElementById('status-select');
            const updateBtn = document.getElementById('status-update-btn');
            const modal = document.getElementById('rejection-modal');
            const cancelBtn = document.getElementById('rejection-cancel');

            if (!statusForm || !statusSelect || !updateBtn) return;

            updateBtn.addEventListener('click', function() {
                if (statusSelect.value === 'no_longer_under_consideration') {
                    modal.style.display = 'flex';
                } else {
                    statusForm.submit();
                }
            });

            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                document.getElementById('rejection-reason-input').value = '';
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    document.getElementById('rejection-reason-input').value = '';
                }
            });
        });
    </script>
@endpush