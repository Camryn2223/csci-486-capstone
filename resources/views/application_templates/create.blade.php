@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h1 style="margin-top: 0;">Create Application Template - {{ $organization->name }}</h1>

        <form method="POST" action="{{ route('organizations.application-templates.store', $organization) }}">
            @csrf
            
            <label>Template Name</label>
            <input type="text" name="name" value="{{ old('name') }}" style="max-width: 100%;" required>
            
            <hr style="border-color: #3a3f45; margin: 25px 0;">
            <h2 style="font-size: 18px; margin-bottom: 15px;">Standard Sections</h2>
            <p style="color: #bdbdbd; font-size: 14px; margin-bottom: 20px;">Choose which standard fields to request from the applicant.</p>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="color: #e6e6e6; cursor: pointer; font-size: 16px;">
                    <input type="checkbox" name="request_name" value="1" {{ old('request_name', true) ? 'checked' : '' }}> Request Full Name
                </label>
                <label style="color: #e6e6e6; cursor: pointer; font-size: 16px;">
                    <input type="checkbox" name="request_email" value="1" {{ old('request_email', true) ? 'checked' : '' }}> Request Email Address
                </label>
                <label style="color: #e6e6e6; cursor: pointer; font-size: 16px;">
                    <input type="checkbox" name="request_phone" value="1" {{ old('request_phone', true) ? 'checked' : '' }}> Request Phone Number
                </label>
                <label style="color: #e6e6e6; cursor: pointer; font-size: 16px;">
                    <input type="checkbox" name="request_resume" value="1" {{ old('request_resume', true) ? 'checked' : '' }}> Request Resume / Documents
                </label>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn">Create Template</button>
                <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45; margin-left: 10px;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection