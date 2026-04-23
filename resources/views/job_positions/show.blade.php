<div class="card notes-sidebar-card" style="padding: 0; overflow: hidden;">
    <div class="card-header-flex border-bottom-divider" style="padding: 15px 20px; background: var(--bg-entry);">
        <h2 class="m-0 fs-18 flex-gap-10 items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            Interview Notes
        </h2>
    </div>
    
    <div style="padding: 15px 20px;">
        <div class="mb-20 pb-15 border-bottom-divider">
            <label class="fs-14 cursor-pointer items-center flex-gap-10 m-0 text-muted transition-hover">
                <input type="checkbox" id="filter-my-notes" onchange="toggleMyNotesOnly(this)"> Show only my notes
            </label>
        </div>

        @forelse($interviews as $interview)
            <details class="note-accordion mb-15" open>
                <summary class="note-accordion-header transition-hover">
                    <div class="flex-col-5">
                        <span class="text-primary fw-bold fs-15">{{ $interview->scheduled_at->format('M j, Y - g:i A') }}</span>
                        <span class="text-muted fs-13">{{ $interview->application->jobPosition->title }}</span>
                    </div>
                    <svg class="note-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </summary>
                
                <div class="note-accordion-body">
                    @php
                        $myPivot = $interview->interviewers->firstWhere('id', Auth::id());
                        $otherInterviewers = $interview->interviewers->where('id', '!=', Auth::id());
                    @endphp

                    @if($myPivot)
                        <div class="note-thread-item my-note-block mb-20">
                            <div class="note-author flex-gap-8 items-center mb-10">
                                <div class="avatar-circle bg-primary text-white">Me</div>
                                <strong class="fs-14">My Notes</strong>
                            </div>
                            <form class="ajax-note-form" data-url="{{ route('interviews.feedback', $interview) }}">
                                <textarea name="notes" class="note-textarea" placeholder="Type your observations and feedback here...">{{ $myPivot->pivot->notes }}</textarea>
                                <div class="card-header-flex mt-10">
                                    <span class="save-status text-muted fs-13"></span>
                                    <button type="submit" class="btn btn-sm note-save-btn">Save Notes</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @foreach($otherInterviewers as $other)
                        <div class="note-thread-item other-note mb-20">
                            <div class="note-author flex-gap-8 items-center mb-10">
                                <div class="avatar-circle bg-slate text-slate-dark">{{ substr($other->name, 0, 1) }}</div>
                                <strong class="fs-14 text-muted">{{ $other->name }}</strong>
                            </div>
                            <div class="note-content white-space-pre fs-14">{{ $other->pivot->notes ?: 'No notes added yet.' }}</div>
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
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-10 opacity-70"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line><line x1="9" y1="17" x2="15" y2="17"></line></svg>
                <p class="text-muted fs-14 m-0">No interviews scheduled yet.</p>
            </div>
        @endforelse
    </div>
</div>