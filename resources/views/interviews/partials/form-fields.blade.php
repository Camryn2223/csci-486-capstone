<div class="mb-20">
    <label><strong>{{ $step1Label ?? 'Select Interviewer(s)' }}</strong></label>
    <select id="interviewers-select" name="interviewer_ids[]" multiple required autocomplete="off">
        @if(empty($interview))
            <option value="">Search or select interviewers...</option>
        @endif
        @foreach ($interviewers as $interviewer)
            <option value="{{ $interviewer->id }}" 
                {{ (is_array(old('interviewer_ids')) && in_array($interviewer->id, old('interviewer_ids'))) || (!old('interviewer_ids') && isset($interview) && $interview->interviewers->contains('id', $interviewer->id)) ? 'selected' : '' }}>
                {{ $interviewer->name }} ({{ $interviewer->role }})
            </option>
        @endforeach
    </select>
</div>

<div class="mt-20">
    <div class="card-header-flex items-center mb-10 flex-wrap-15">
        <label class="m-0"><strong>{{ $step2Label ?? 'Select Time (Click an open slot on the calendar below)' }}</strong></label>
        
        <div class="form-inline-center items-center">
            <label class="m-0 white-space-nowrap text-muted">Jump to Date:</label>
            <input type="text" id="jump_date_picker" placeholder="Select Date..." class="m-0 max-w-200">
        </div>
    </div>
    
    <div id="availability-calendar" 
        data-applicant="{{ $application->applicant_name }}" 
        data-position="{{ $application->jobPosition->title }}" 
        class="availability-calendar-shell mt-10 mb-10"></div>
        
    <input type="hidden" id="scheduled_at_hidden" name="scheduled_at" value="{{ old('scheduled_at', isset($interview) ? $interview->scheduled_at->format('Y-m-d H:i') : '') }}" required>
    
    @error('scheduled_at')
        <span class="text-danger d-block mb-10 mt-5">{{ $message }}</span>
    @enderror

    <p id="selected_time_display" class="text-primary fw-bold fs-16 mt-10">Selected Time: <span id="selected_time_text" class="text-light">None</span></p>
</div>

<script id="schedules-data" type="application/json">
    {!! json_encode($schedules ?? []) !!}
</script>