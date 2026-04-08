@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0;">Template Summary: {{ $applicationTemplate->name }}</h1>
            <p style="margin: 5px 0 0 0; color: #bdbdbd;">Created by {{ $applicationTemplate->creator->name }}</p>
        </div>
        <div>
            @can('update', $applicationTemplate)
                <a href="{{ route('organizations.application-templates.edit', [$organization, $applicationTemplate]) }}" class="btn" style="background: #3a245a; margin-right: 5px;">Edit Fields</a>
            @endcan
            <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back</a>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0; border-bottom: 1px solid #3a3f45; padding-bottom: 15px;">Preview: {{ $applicationTemplate->name }}</h2>
        <p style="color: #bdbdbd; font-size: 14px; margin-bottom: 25px;">This is how the application form will appear to applicants. <em>(Form submission is disabled in preview)</em></p>

        @if($applicationTemplate->request_name || $applicationTemplate->request_email || $applicationTemplate->request_phone)
            <h2 style="margin-top: 0; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Your Information</h2>
            <div style="margin-bottom: 25px;">
                @if($applicationTemplate->request_name)
                    <label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">Full Name <span style="color: #ff9d9d;">*</span></label>
                    <input type="text" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">
                @endif

                @if($applicationTemplate->request_email)
                    <label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">Email Address <span style="color: #ff9d9d;">*</span></label>
                    <input type="email" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">
                @endif

                @if($applicationTemplate->request_phone)
                    <label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">Phone Number (optional)</label>
                    <input type="text" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">
                @endif
            </div>
        @endif

        @if ($applicationTemplate->fields->isNotEmpty())
            <h2 style="margin-top: 30px; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Application Questions</h2>

            @foreach ($applicationTemplate->fields as $field)
                <div style="margin-bottom: 25px;">
                    <label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">
                        {{ $field->label }}
                        @if($field->required) <span style="color: #ff9d9d;">*</span> @endif
                    </label>

                    @if ($field->type === 'text')
                        <input type="text" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">
                    @elseif ($field->type === 'textarea')
                        <textarea rows="4" disabled style="opacity: 0.7; cursor: not-allowed; margin-bottom: 0;"></textarea>
                    @elseif ($field->type === 'date')
                        <input type="date" disabled onclick="this.showPicker()" style="opacity: 0.7; cursor: pointer; margin-bottom: 0;">
                    @elseif ($field->type === 'select')
                        <select disabled style="opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">
                            <option value="">-- Select --</option>
                            @foreach ($field->options ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif ($field->type === 'radio')
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                            @foreach ($field->options ?? [] as $option)
                                <label style="color: #bdbdbd; cursor: not-allowed; opacity: 0.7;">
                                    <input type="radio" disabled> {{ $option }}
                                </label>
                            @endforeach
                        </div>
                    @elseif ($field->type === 'checkbox')
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                            @foreach ($field->options ?? [] as $option)
                                <label style="color: #bdbdbd; cursor: not-allowed; opacity: 0.7;">
                                    <input type="checkbox" disabled> {{ $option }}
                                </label>
                            @endforeach
                        </div>
                    @elseif ($field->type === 'file')
                        <input type="file" disabled style="display: block; margin-top: 10px; margin-bottom: 0; opacity: 0.7; cursor: not-allowed;">
                    @endif
                </div>
            @endforeach
        @endif

        @if($applicationTemplate->request_resume)
            <h2 style="margin-top: 30px; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Upload Documents</h2>
            <p style="color: #bdbdbd; margin-bottom: 15px;">You may upload a resume or other supporting documents (PDF, DOC, DOCX, JPG, PNG).</p>
            <input type="file" disabled style="display: block; margin-bottom: 30px; opacity: 0.7; cursor: not-allowed;">
        @endif

        <div style="margin-top: 30px; border-top: 1px solid #3a3f45; padding-top: 20px;">
            <button type="button" class="btn" style="background: #0f3d1e; color: #9dffb0; border: 1px solid #1a5c30; padding: 15px 30px; font-weight: bold; width: 100%; opacity: 0.5; cursor: not-allowed;">Submit Application</button>
        </div>
    </div>
</div>
@endsection