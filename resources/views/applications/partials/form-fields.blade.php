@php
    $hasUserInfo = $template && ($template->request_name || $template->request_email || $template->request_phone);
    $hasFields = $template && $template->fields->isNotEmpty();
@endphp

@if($hasUserInfo)
    <h2 class="mt-0 mb-15">Your Information</h2>

    @if($template->request_name)
        @php $errName = $errors->has('applicant_name'); @endphp
        <div class="mb-25">
            <label for="applicant_name" class="{{ $isPreview ? 'preview-label' : '' }} {{ $errName ? 'text-danger fw-bold' : '' }}">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="applicant_name" name="applicant_name" value="{{ old('applicant_name') }}" class="{{ $isPreview ? 'preview-input' : 'w-full' }} {{ $errName ? 'input-error' : '' }}" {{ $isPreview ? 'disabled' : 'required' }}>
            @if($errName) <span class="text-danger fs-13 d-block mt-5">{{ $errors->first('applicant_name') }}</span> @endif
        </div>
    @endif
    
    @if($template->request_email)
        @php $errEmail = $errors->has('applicant_email'); @endphp
        <div class="mb-25">
            <label for="applicant_email" class="{{ $isPreview ? 'preview-label' : '' }} {{ $errEmail ? 'text-danger fw-bold' : '' }}">Email Address <span class="text-danger">*</span></label>
            <input type="email" id="applicant_email" name="applicant_email" value="{{ old('applicant_email') }}" class="{{ $isPreview ? 'preview-input' : 'w-full' }} {{ $errEmail ? 'input-error' : '' }}" {{ $isPreview ? 'disabled' : 'required' }}>
            @if($errEmail) <span class="text-danger fs-13 d-block mt-5">{{ $errors->first('applicant_email') }}</span> @endif
        </div>
    @endif

    @if($template->request_phone)
        @php $errPhone = $errors->has('applicant_phone'); @endphp
        <div class="mb-25">
            <label for="applicant_phone" class="{{ $isPreview ? 'preview-label' : '' }} {{ $errPhone ? 'text-danger fw-bold' : '' }}">Phone Number (optional)</label>
            <input type="text" id="applicant_phone" name="applicant_phone" value="{{ old('applicant_phone') }}" class="{{ $isPreview ? 'preview-input' : 'w-full' }} {{ $errPhone ? 'input-error' : '' }}" {{ $isPreview ? 'disabled' : '' }}>
            @if($errPhone) <span class="text-danger fs-13 d-block mt-5">{{ $errors->first('applicant_phone') }}</span> @endif
        </div>
    @endif
@endif

@if ($template)
    @if (!$isPreview)
        <input type="hidden" name="template_id" value="{{ $template->id }}">
    @endif

    @if ($hasFields)
        @if($hasUserInfo)
            <hr class="divider-30">
        @endif
        
        <h2 class="mt-0 mb-15">Application Questions</h2>
        
        @foreach ($template->fields as $field)
            @php
                $fieldErrKey = "answers.{$field->id}";
                // Also check for multiple files validation errors (like answers.1.0)
                $hasFieldErr = $errors->has($fieldErrKey) || $errors->has($fieldErrKey . '.*');
                $errClass = $hasFieldErr ? 'input-error' : '';
            @endphp
            <div class="mb-25">
                <label class="{{ $isPreview ? 'preview-label' : 'text-light fs-16 mb-8' }} {{ $hasFieldErr ? 'text-danger fw-bold' : '' }}" 
                       @if(!in_array($field->type, ['radio', 'checkbox'])) for="field_{{ $field->id }}" @endif>
                    {{ $field->label }}
                    @if($field->required) <span class="text-danger">*</span> @endif
                </label>

                @if ($field->type === 'text')
                    @php $charMax = $field->char_max ?? 128; @endphp
                    <input type="text" id="field_{{ $field->id }}" name="answers[{{ $field->id }}]"
                           value="{{ old("answers.{$field->id}") }}"
                           data-char-max="{{ $charMax }}"
                           maxlength="{{ $charMax }}"
                           class="char-counted {{ $isPreview ? 'preview-input' : 'w-full' }} {{ $errClass }}"
                           {{ ($field->required && !$isPreview) ? 'required' : '' }}
                           {{ $isPreview ? 'disabled' : '' }}>
                    <small class="char-counter text-muted d-block mt-5" style="text-align: right;">0 / {{ $charMax }}</small>

                @elseif ($field->type === 'textarea')
                    @php $charMax = $field->char_max ?? 1024; @endphp
                    <textarea id="field_{{ $field->id }}" name="answers[{{ $field->id }}]" rows="4"
                              data-char-max="{{ $charMax }}"
                              maxlength="{{ $charMax }}"
                              class="char-counted {{ $isPreview ? 'preview-input' : '' }} {{ $errClass }}"
                              {{ ($field->required && !$isPreview) ? 'required' : '' }}
                              {{ $isPreview ? 'disabled' : '' }}>{{ old("answers.{$field->id}") }}</textarea>
                    <small class="char-counter text-muted d-block mt-5" style="text-align: right;">0 / {{ $charMax }}</small>

                @elseif ($field->type === 'date')
                    <input type="date" id="field_{{ $field->id }}" name="answers[{{ $field->id }}]"
                           value="{{ old("answers.{$field->id}") }}"
                           class="{{ $isPreview ? 'preview-input cursor-pointer' : '' }} {{ $errClass }}"
                           @if(!$isPreview) onclick="this.showPicker()" @endif
                           {{ ($field->required && !$isPreview) ? 'required' : '' }}
                           {{ $isPreview ? 'disabled' : '' }}>

                @elseif ($field->type === 'select')
                    <select id="field_{{ $field->id }}" name="answers[{{ $field->id }}]" 
                            class="{{ $isPreview ? 'preview-input' : '' }} {{ $errClass }}"
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
                    <div class="flex-col-8 mt-10 {{ $hasFieldErr ? 'border-left-danger pl-10' : '' }}">
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
                    <div class="flex-col-8 mt-10 {{ $hasFieldErr ? 'border-left-danger pl-10' : '' }}">
                        @foreach ($field->options ?? [] as $index => $option)
                            <label for="field_{{ $field->id }}_opt_{{ $index }}" class="text-muted {{ $isPreview ? 'cursor-not-allowed opacity-70' : 'cursor-pointer' }}">
                                <input type="checkbox" id="field_{{ $field->id }}_opt_{{ $index }}" name="answers[{{ $field->id }}][]" value="{{ $option }}"
                                       {{ is_array(old("answers.{$field->id}")) && in_array($option, old("answers.{$field->id}")) ? 'checked' : '' }}
                                       {{ $isPreview ? 'disabled' : '' }}>
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>

                @elseif ($field->type === 'file')
                    @php
                        $friendlyMimes = empty($field->options) ? 'PDF, DOC, DOCX, JPEG, PNG' : implode(', ', array_map(function($mime) {
                            $map = ['application/pdf' => 'PDF', 'application/msword' => 'DOC', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX', 'image/jpeg' => 'JPEG', 'image/png' => 'PNG'];
                            return $map[$mime] ?? $mime;
                        }, $field->options));
                        $maxFiles = $field->file_multiple ? ($field->file_max ?? 5) : 1;
                        $maxMB = $field->file_size_max ?? 7;
                    @endphp
                    <div class="mb-10 text-muted fs-13">
                        Allowed file types: {{ $friendlyMimes }}.<br>
                        Maximum file size: {{ $maxMB }} MB.
                        @if($field->file_multiple)
                            <br>Maximum allowed files: {{ $maxFiles }}.
                        @endif
                    </div>
                    
                    @if ($field->file_multiple)
                        <div class="multi-file-wrapper" data-max="{{ $maxFiles }}" data-max-mb="{{ $maxMB }}">
                            <div class="file-inputs-container">
                                <input type="file" name="answers[{{ $field->id }}][]" 
                                       class="{{ $isPreview ? 'preview-input-file' : 'tracked-upload d-block mt-10' }} {{ $errClass }}"
                                       {{ ($field->required && !$isPreview) ? 'required' : '' }}
                                       {{ $isPreview ? 'disabled' : '' }}
                                       @if(!empty($field->options)) accept="{{ implode(',', $field->options) }}" @endif>
                            </div>
                            @if(!$isPreview)
                                <button type="button" class="btn btn-sm btn-outline btn-add-file mt-10">+ Add another file</button>
                            @endif
                        </div>
                    @else
                        <input type="file" id="field_{{ $field->id }}" name="answers[{{ $field->id }}]" 
                               data-max-mb="{{ $maxMB }}"
                               class="{{ $isPreview ? 'preview-input-file' : 'tracked-upload d-block mt-10' }} {{ $errClass }}"
                               {{ ($field->required && !$isPreview) ? 'required' : '' }}
                               {{ $isPreview ? 'disabled' : '' }}
                               @if(!empty($field->options)) accept="{{ implode(',', $field->options) }}" @endif>
                    @endif
                @endif
                
                @if($hasFieldErr)
                    @if($errors->has($fieldErrKey))
                        <span class="text-danger fs-13 d-block mt-5">{{ $errors->first($fieldErrKey) }}</span>
                    @endif
                    @foreach($errors->get($fieldErrKey . '.*') as $msgList)
                        <span class="text-danger fs-13 d-block mt-5">{{ $msgList[0] }}</span>
                    @endforeach
                @endif
            </div>
        @endforeach
    @endif
@endif

@if ($isPreview)
    <div class="mt-30 border-divider pt-20">
        <button type="button" class="btn btn-submit-large-disabled">Submit Application</button>
    </div>
@else
    <button type="submit" class="btn btn-submit-large">Submit Application</button>
@endif
