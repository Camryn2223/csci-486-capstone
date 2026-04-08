@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h1 style="margin-top: 0;">Apply for: {{ $jobPosition->title }}</h1>
        <p style="color: #a97dff; font-size: 18px; margin-bottom: 20px;"><strong>Organization:</strong> {{ $organization->name }}</p>
        
        <p style="white-space: pre-wrap; line-height: 1.5; color: #e6e6e6;">{{ $jobPosition->description }}</p>
        
        <h3 style="margin-top: 25px;">Requirements:</h3>
        <p style="white-space: pre-wrap; line-height: 1.5; color: #e6e6e6;">{{ $jobPosition->requirements }}</p>
    </div>

    <div class="card">
        <form id="application-form" method="POST" action="{{ route('applications.store', [$organization, $jobPosition]) }}" enctype="multipart/form-data">
            @csrf

            @if($jobPosition->template && ($jobPosition->template->request_name || $jobPosition->template->request_email || $jobPosition->template->request_phone))
                <h2 style="margin-top: 0; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Your Information</h2>

                @if($jobPosition->template->request_name)
                    <label>Full Name <span style="color: #ff9d9d;">*</span></label>
                    <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" required>
                @endif
                
                @if($jobPosition->template->request_email)
                    <label>Email Address <span style="color: #ff9d9d;">*</span></label>
                    <input type="email" name="applicant_email" value="{{ old('applicant_email') }}" required>
                @endif

                @if($jobPosition->template->request_phone)
                    <label>Phone Number (optional)</label>
                    <input type="text" name="applicant_phone" value="{{ old('applicant_phone') }}">
                @endif
            @endif

            @if ($jobPosition->template)
                <input type="hidden" name="template_id" value="{{ $jobPosition->template->id }}">

                @if ($jobPosition->template->fields->isNotEmpty())
                    <h2 style="margin-top: 30px; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Application Questions</h2>
                    
                    @foreach ($jobPosition->template->fields as $field)
                        <div style="margin-bottom: 25px;">
                            <label style="color: #e6e6e6; font-size: 16px; margin-bottom: 8px;">
                                {{ $field->label }}
                                @if($field->required) <span style="color: #ff9d9d;">*</span> @endif
                            </label>

                            @if ($field->type === 'text')
                                <input type="text" name="answers[{{ $field->id }}]"
                                       value="{{ old("answers.{$field->id}") }}"
                                       style="max-width: 100%;"
                                       {{ $field->required ? 'required' : '' }}>

                            @elseif ($field->type === 'textarea')
                                <textarea name="answers[{{ $field->id }}]" rows="4"
                                          {{ $field->required ? 'required' : '' }}>{{ old("answers.{$field->id}") }}</textarea>

                            @elseif ($field->type === 'date')
                                <input type="date" name="answers[{{ $field->id }}]"
                                       value="{{ old("answers.{$field->id}") }}"
                                       onclick="this.showPicker()"
                                       {{ $field->required ? 'required' : '' }}>

                            @elseif ($field->type === 'select')
                                <select name="answers[{{ $field->id }}]" {{ $field->required ? 'required' : '' }}>
                                    <option value="">-- Select --</option>
                                    @foreach ($field->options ?? [] as $option)
                                        <option value="{{ $option }}"
                                            {{ old("answers.{$field->id}") === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>

                            @elseif ($field->type === 'radio')
                                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                    @foreach ($field->options ?? [] as $option)
                                        <label style="color: #bdbdbd; cursor: pointer;">
                                            <input type="radio" name="answers[{{ $field->id }}]" value="{{ $option }}"
                                                {{ old("answers.{$field->id}") === $option ? 'checked' : '' }}
                                                {{ $field->required ? 'required' : '' }}>
                                            {{ $option }}
                                        </label>
                                    @endforeach
                                </div>

                            @elseif ($field->type === 'checkbox')
                                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                    @foreach ($field->options ?? [] as $option)
                                        <label style="color: #bdbdbd; cursor: pointer;">
                                            <input type="checkbox" name="answers[{{ $field->id }}][]" value="{{ $option }}">
                                            {{ $option }}
                                        </label>
                                    @endforeach
                                </div>

                            @elseif ($field->type === 'file')
                                <input type="file" name="answers[{{ $field->id }}]" class="tracked-upload"
                                       {{ $field->required ? 'required' : '' }} style="display: block; margin-top: 10px;">
                            @endif
                        </div>
                    @endforeach
                @endif
            @endif

            @if($jobPosition->template && $jobPosition->template->request_resume)
                <h2 style="margin-top: 30px; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Upload Documents</h2>
                <p style="color: #bdbdbd; margin-bottom: 15px;">You may upload a resume or other supporting documents (PDF, DOC, DOCX, JPG, PNG).</p>
                <input type="file" name="document" class="tracked-upload" style="display: block; margin-bottom: 30px;" required>
            @endif

            <button type="submit" class="btn" style="background: #0f3d1e; color: #9dffb0; border: 1px solid #1a5c30; padding: 15px 30px; font-weight: bold; width: 100%;">Submit Application</button>
        </form>
    </div>
</div>

<script>
    // Intercept form submission to prevent the 413 Request Entity Too Large error gracefully
    document.getElementById('application-form').addEventListener('submit', function(e) {
        const fileInputs = document.querySelectorAll('.tracked-upload');
        let tooLarge = false;
        
        // Setting an 7.5MB client-side threshold to safely stay below PHP's default post_max_size (usually 8MB)
        const SAFE_MB_LIMIT = 7.5; 

        fileInputs.forEach(input => {
            if (input.files && input.files.length > 0) {
                const fileSizeMB = input.files[0].size / 1024 / 1024;
                if (fileSizeMB > SAFE_MB_LIMIT) {
                    tooLarge = true;
                }
            }
        });

        if (tooLarge) {
            e.preventDefault();
            alert('One or more selected files are too large! Please ensure each file is strictly under ' + SAFE_MB_LIMIT + 'MB to prevent the server from rejecting your application.');
        }
    });
</script>
@endsection