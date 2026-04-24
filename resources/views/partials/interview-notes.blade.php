<div class="card bg-entry interview-notes-card">
    <div class="card-header-flex interview-notes-header">
        <h2 class="m-0 fs-18 flex-gap-10 items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            Interview Notes
        </h2>
    </div>

    <div class="interview-notes-body">
        <div class="interview-notes-filter border-bottom-divider">
            <label class="fs-14 cursor-pointer items-center flex-gap-10 m-0 text-muted transition-hover">
                <input type="checkbox" id="filter-my-notes" onchange="toggleMyNotesOnly(this)">
                Show only my notes
            </label>
        </div>

        @forelse($interviews as $interview)
            <details class="note-accordion mb-10" open>
                <summary class="note-accordion-header transition-hover">
                    <div class="flex-col-5">
                        <span class="text-primary fw-bold fs-15">{{ $interview->scheduled_at->format('M j, Y - g:i A') }}</span>
                        <span class="text-muted fs-13">{{ $interview->application->jobPosition->title }}</span>
                    </div>
                    <svg class="note-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </summary>

                <div class="note-accordion-body">
                    @php
                        $myPivot = $interview->interviewers->firstWhere('id', Auth::id());
                        $otherInterviewers = $interview->interviewers->where('id', '!=', Auth::id());
                        $noteEditorId = 'interview_notes_' . $interview->id . '_' . Auth::id();
                    @endphp

                    @if($myPivot)
                        <div class="note-thread-item my-note-block mb-15">
                            <div class="note-author">
                                <div class="avatar-circle bg-primary text-white">Me</div>
                                <strong class="fs-14">My Notes</strong>
                            </div>

                            <form class="ajax-note-form" data-url="{{ route('interviews.feedback', $interview) }}">
                                <textarea
                                    id="{{ $noteEditorId }}"
                                    name="notes"
                                    class="note-textarea tinymce-note"
                                    placeholder="Type your observations and feedback here."
                                >{{ $myPivot->pivot->notes }}</textarea>

                                <div class="card-header-flex note-save-row">
                                    <span class="save-status text-muted fs-13"></span>
                                    <button type="submit" class="btn btn-sm note-save-btn">Save Notes</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @foreach($otherInterviewers as $other)
                        <div class="note-thread-item other-note mb-15">
                            <div class="note-author">
                                <div class="avatar-circle bg-slate text-slate-dark">{{ substr($other->name, 0, 1) }}</div>
                                <strong class="fs-14 text-muted">{{ $other->name }}</strong>
                            </div>

                            <div class="note-content fs-14">
                                @if(filled($other->pivot->notes))
                                    {!! clean($other->pivot->notes) !!}
                                @else
                                    <span class="note-empty-state">No notes added yet.</span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if(!$myPivot && $otherInterviewers->isEmpty())
                        <div class="text-center py-15">
                            <p class="text-muted fs-13 m-0 text-italic">No interviewers assigned to take notes.</p>
                        </div>
                    @endif
                </div>
            </details>
        @empty
            <div class="text-center py-20">
                <p class="text-muted fs-14 m-0">No interviews scheduled yet.</p>
            </div>
        @endforelse
    </div>
</div>