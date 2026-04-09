@php
    $isBuilder = $isBuilder ?? false;
    
    $reqName = old('request_name', $template ? $template->request_name : true);
    $reqEmail = old('request_email', $template ? $template->request_email : true);
    $reqPhone = old('request_phone', $template ? $template->request_phone : true);
    $reqResume = old('request_resume', $template ? $template->request_resume : true);
    
    $hasUserInfo = $reqName || $reqEmail || $reqPhone;
    $hasFields = $template && $template->fields->isNotEmpty();
    $hasResume = $reqResume;
@endphp

<h2 class="mt-0 mb-15" id="preview-user-info-header" style="{{ $hasUserInfo ? '' : 'display:none;' }}">Your Information</h2>

<div id="preview-group-name" class="mb-25" style="{{ $reqName ? '' : 'display:none;' }}">
    <label for="applicant_name" class="{{ $isPreview ? 'preview-label' : '' }}">Full Name <span class="text-danger">*</span></label>
    <input type="text" id="applicant_name" name="applicant_name" value="{{ old('applicant_name') }}" {{ $isPreview ? 'disabled class=preview-input' : 'required' }}>
</div>

<div id="preview-group-email" class="mb-25" style="{{ $reqEmail ? '' : 'display:none;' }}">
    <label for="applicant_email" class="{{ $isPreview ? 'preview-label' : '' }}">Email Address <span class="text-danger">*</span></label>
    <input type="email" id="applicant_email" name="applicant_email" value="{{ old('applicant_email') }}" {{ $isPreview ? 'disabled class=preview-input' : 'required' }}>
</div>

<div id="preview-group-phone" class="mb-25" style="{{ $reqPhone ? '' : 'display:none;' }}">
    <label for="applicant_phone" class="{{ $isPreview ? 'preview-label' : '' }}">Phone Number (optional)</label>
    <input type="text" id="applicant_phone" name="applicant_phone" value="{{ old('applicant_phone') }}" {{ $isPreview ? 'disabled class=preview-input' : '' }}>
</div>

@if ($template)
    @if (!$isPreview)
        <input type="hidden" name="template_id" value="{{ $template->id }}">
    @endif

    @if ($hasFields)
        <hr id="preview-hr-1" class="divider-30" style="{{ $hasUserInfo ? '' : 'display:none;' }}">
        
        <h2 id="preview-questions-header" class="mt-0 mb-15">Application Questions</h2>
        
        @foreach ($template->fields as $field)
            <div class="mb-25">
                <label class="{{ $isPreview ? 'preview-label' : 'text-light fs-16 mb-8' }}" 
                       @if(!in_array($field->type, ['radio', 'checkbox'])) for="field_{{ $field->id }}" @endif>
                    {{ $field->label }}
                    @if($field->required) <span class="text-danger">*</span> @endif
                </label>

                @if ($field->type === 'text')
                    <input type="text" id="field_{{ $field->id }}" name="answers[{{ $field->id }}]"
                           value="{{ old("answers.{$field->id}") }}"
                           class="{{ $isPreview ? 'preview-input' : 'w-full' }}"
                           {{ ($field->required && !$isPreview) ? 'required' : '' }}
                           {{ $isPreview ? 'disabled' : '' }}>

                @elseif ($field->type === 'textarea')
                    <textarea id="field_{{ $field->id }}" name="answers[{{ $field->id }}]" rows="4"
                              class="{{ $isPreview ? 'preview-input' : '' }}"
                              {{ ($field->required && !$isPreview) ? 'required' : '' }}
                              {{ $isPreview ? 'disabled' : '' }}>{{ old("answers.{$field->id}") }}</textarea>

                @elseif ($field->type === 'date')
                    <input type="date" id="field_{{ $field->id }}" name="answers[{{ $field->id }}]"
                           value="{{ old("answers.{$field->id}") }}"
                           class="{{ $isPreview ? 'preview-input cursor-pointer' : '' }}"
                           @if(!$isPreview) onclick="this.showPicker()" @endif
                           {{ ($field->required && !$isPreview) ? 'required' : '' }}
                           {{ $isPreview ? 'disabled' : '' }}>

                @elseif ($field->type === 'select')
                    <select id="field_{{ $field->id }}" name="answers[{{ $field->id }}]" 
                            class="{{ $isPreview ? 'preview-input' : '' }}"
                            {{ ($field->required && !$isPreview) ? 'required' : '' }}
                            {{ $isPreview ? 'disabled' : '' }}>
                        <option value="">-- Select --</option>
                        @foreach ($field->options ?? [] as $option)
                            <option value="{{ $option }}"
                                {{ old("answers.{$field->id}") === $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>

                @elseif ($field->type === 'radio')
                    <div class="flex-col-8 mt-10">
                        @foreach ($field->options ?? [] as $index => $option)
                            <label for="field_{{ $field->id }}_opt_{{ $index }}" class="text-muted {{ $isPreview ? 'cursor-not-allowed opacity-70' : 'cursor-pointer' }}">
                                <input type="radio" id="field_{{ $field->id }}_opt_{{ $index }}" name="answers[{{ $field->id }}]" value="{{ $option }}"
                                    {{ old("answers.{$field->id}") === $option ? 'checked' : '' }}
                                    {{ ($field->required && !$isPreview) ? 'required' : '' }}
                                    {{ $isPreview ? 'disabled' : '' }}>
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>

                @elseif ($field->type === 'checkbox')
                    <div class="flex-col-8 mt-10">
                        @foreach ($field->options ?? [] as $index => $option)
                            <label for="field_{{ $field->id }}_opt_{{ $index }}" class="text-muted {{ $isPreview ? 'cursor-not-allowed opacity-70' : 'cursor-pointer' }}">
                                <input type="checkbox" id="field_{{ $field->id }}_opt_{{ $index }}" name="answers[{{ $field->id }}][]" value="{{ $option }}"
                                       {{ $isPreview ? 'disabled' : '' }}>
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>

                @elseif ($field->type === 'file')
                    <input type="file" id="field_{{ $field->id }}" name="answers[{{ $field->id }}]" 
                           class="{{ $isPreview ? 'preview-input-file' : 'tracked-upload d-block mt-10' }}"
                           {{ ($field->required && !$isPreview) ? 'required' : '' }}
                           {{ $isPreview ? 'disabled' : '' }}>
                @endif
            </div>
        @endforeach
    @endif
@endif

<div id="preview-group-resume" style="{{ $hasResume ? '' : 'display:none;' }}">
    <hr id="preview-hr-2" class="divider-30" style="{{ ($hasUserInfo || $hasFields) ? '' : 'display:none;' }}">
    <h2 class="mt-0 mb-15">Upload Documents</h2>
    <label for="resume_document" class="text-muted mb-15 d-block">You may upload a resume or other supporting documents (PDF, DOC, DOCX, JPG, PNG).</label>
    <input type="file" id="resume_document" name="document" 
           class="{{ $isPreview ? 'preview-input-file-mb30' : 'tracked-upload d-block mb-30' }}" 
           {{ $isPreview ? 'disabled' : 'required' }}>
</div>

@if ($isPreview)
    <div class="mt-30 border-divider pt-20">
        <button type="button" class="btn btn-submit-large-disabled">Submit Application</button>
    </div>
@else
    <button type="submit" class="btn btn-submit-large">Submit Application</button>
@endif