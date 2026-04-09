@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card-header-flex mb-20">
        <h1 class="m-0">Create Application Template - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn btn-outline">Back to Templates</a>
    </div>

    <div class="flex-gap-10 mb-20">
        <button id="btn-tab-builder" class="btn btn-tab-active" onclick="switchTab('builder')">Builder</button>
        <button id="btn-tab-preview" class="btn btn-tab-inactive" onclick="switchTab('preview')">Preview Form</button>
    </div>

    <div id="tab-builder">
        <div class="card">
            <form method="POST" action="{{ route('organizations.application-templates.store', $organization) }}">
                @csrf
                
                <div class="mb-25">
                    <label>Template Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full mb-0" required>
                </div>
                
                <h2 class="fs-18 mb-15 mt-30">Standard Sections</h2>
                <p class="text-muted fs-14 mb-20">Choose which standard fields to request from the applicant. Custom fields can be added after creating.</p>

                <div class="flex-col-10">
                    <label class="text-light cursor-pointer fs-16">
                        <input type="checkbox" name="request_name" value="1" {{ old('request_name', true) ? 'checked' : '' }}> Request Full Name
                    </label>
                    <label class="text-light cursor-pointer fs-16">
                        <input type="checkbox" name="request_email" value="1" {{ old('request_email', true) ? 'checked' : '' }}> Request Email Address
                    </label>
                    <label class="text-light cursor-pointer fs-16">
                        <input type="checkbox" name="request_phone" value="1" {{ old('request_phone', true) ? 'checked' : '' }}> Request Phone Number
                    </label>
                    <label class="text-light cursor-pointer fs-16">
                        <input type="checkbox" name="request_resume" value="1" {{ old('request_resume', true) ? 'checked' : '' }}> Request Resume / Documents
                    </label>
                </div>

                <div class="mt-30">
                    <button type="submit" class="btn">Create & Continue to Custom Fields</button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-preview" style="display: none;">
        <div class="card">
            <h2 class="mt-0 pb-15">Preview: <span id="preview-template-name">Untitled Template</span></h2>
            <p class="text-muted fs-14 mb-25">This is how the application form will appear to applicants. <em>(Form submission is disabled in preview)</em></p>

            <div id="preview-content">
                @include('applications.partials.form-fields', ['template' => null, 'isPreview' => true, 'isBuilder' => true])
            </div>
        </div>
    </div>
</div>
@endsection