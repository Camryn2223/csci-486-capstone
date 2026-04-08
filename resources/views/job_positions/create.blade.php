@extends('layouts.app')

@section('content')
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">Create Job Position - {{ $organization->name }}</h1>
        <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Positions</a>
    </div>

    <!-- Live Preview QoL Tabs -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <button id="btn-tab-builder" class="btn" style="background: #6d3fa9; border: none;" onclick="switchTab('builder')">Builder</button>
        <button id="btn-tab-preview" class="btn" style="background: #24282d; border: 1px solid #3a3f45;" onclick="switchTab('preview')">Preview Page</button>
    </div>

    <div id="tab-builder">
        <div class="card">
            <form id="job-form" method="POST" action="{{ route('organizations.job-positions.store', $organization) }}">
                @csrf
                
                <label>Title</label>
                <input type="text" name="title" id="input-title" value="{{ old('title') }}" required oninput="updatePreview()">

                <label>Application Template</label>
                <select name="template_id" id="input-template" required onchange="updatePreview()">
                    <option value="">-- Select Template --</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                            {{ $template->name }} ({{ $template->fields_count }} fields)
                        </option>
                    @endforeach
                </select>

                <label>Description</label>
                <textarea name="description" id="input-desc" rows="4" required oninput="updatePreview()">{{ old('description') }}</textarea>

                <label>Requirements</label>
                <textarea name="requirements" id="input-reqs" rows="4" required oninput="updatePreview()">{{ old('requirements') }}</textarea>

                <label>Status</label>
                <select name="status">
                    <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>

                <div style="margin-top: 15px;">
                    <button type="submit" class="btn">Create Position</button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-preview" style="display: none;">
        <div class="card">
            <h1 id="preview-title" style="margin-top: 0; color: #a97dff;">Apply for: <span></span></h1>
            <p style="color: #a97dff; font-size: 18px; margin-bottom: 20px;"><strong>Organization:</strong> {{ $organization->name }}</p>
            
            <p id="preview-desc" style="white-space: pre-wrap; line-height: 1.5; color: #e6e6e6;"></p>
            
            <h3 style="margin-top: 25px;">Requirements:</h3>
            <p id="preview-reqs" style="white-space: pre-wrap; line-height: 1.5; color: #e6e6e6;"></p>
            
            <hr style="border-color: #3a3f45; margin: 30px 0;">
            
            <!-- Dynamic Template Forms Rendered Here -->
            <div id="preview-form-container"></div>
        </div>
    </div>
</div>

<script>
    const templatesData = @json($templates);

    function switchTab(tab) {
        document.getElementById('tab-builder').style.display = tab === 'builder' ? 'block' : 'none';
        document.getElementById('tab-preview').style.display = tab === 'preview' ? 'block' : 'none';
        
        document.getElementById('btn-tab-builder').style.background = tab === 'builder' ? '#6d3fa9' : '#24282d';
        document.getElementById('btn-tab-builder').style.border = tab === 'builder' ? 'none' : '1px solid #3a3f45';
        
        document.getElementById('btn-tab-preview').style.background = tab === 'preview' ? '#6d3fa9' : '#24282d';
        document.getElementById('btn-tab-preview').style.border = tab === 'preview' ? 'none' : '1px solid #3a3f45';
        
        if (tab === 'preview') updatePreview();
    }

    function updatePreview() {
        const title = document.getElementById('input-title').value || 'Untitled Position';
        const desc = document.getElementById('input-desc').value || 'No description provided.';
        const reqs = document.getElementById('input-reqs').value || 'No requirements provided.';
        const templateId = document.getElementById('input-template').value;

        document.querySelector('#preview-title span').textContent = title;
        document.getElementById('preview-desc').textContent = desc;
        document.getElementById('preview-reqs').textContent = reqs;

        const template = templatesData.find(t => t.id == templateId);
        let html = '';

        if (template) {
            if (template.request_name || template.request_email || template.request_phone) {
                html += '<h2 style="margin-top: 0; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Your Information</h2><div style="margin-bottom: 25px;">';
                if(template.request_name) html += '<label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">Full Name <span style="color: #ff9d9d;">*</span></label><input type="text" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">';
                if(template.request_email) html += '<label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">Email Address <span style="color: #ff9d9d;">*</span></label><input type="email" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">';
                if(template.request_phone) html += '<label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">Phone Number (optional)</label><input type="text" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">';
                html += '</div>';
            }

            if (template.fields && template.fields.length > 0) {
                html += '<h2 style="margin-top: 30px; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Application Questions</h2>';
                template.fields.forEach(f => {
                    html += `<div style="margin-bottom: 25px;">
                        <label style="color: #e6e6e6; font-size: 16px; margin-top: 15px; margin-bottom: 8px;">${f.label} ${f.required ? '<span style="color: #ff9d9d;">*</span>' : ''}</label>`;
                    
                    // JSON parsing safeguard for dynamic array formatting from the DB
                    let opts = f.options;
                    if (typeof opts === 'string') {
                        try { opts = JSON.parse(opts); } catch(e) { opts = []; }
                    }

                    if(f.type === 'text') html += '<input type="text" disabled style="max-width: 100%; opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">';
                    if(f.type === 'textarea') html += '<textarea rows="4" disabled style="opacity: 0.7; cursor: not-allowed; margin-bottom: 0;"></textarea>';
                    if(f.type === 'date') html += '<input type="date" disabled style="opacity: 0.7; cursor: not-allowed; margin-bottom: 0;">';
                    if(f.type === 'select') {
                        html += '<select disabled style="opacity: 0.7; cursor: not-allowed; margin-bottom: 0;"><option>-- Select --</option>';
                        if(opts) opts.forEach(o => html += `<option>${o}</option>`);
                        html += '</select>';
                    }
                    if(f.type === 'radio' || f.type === 'checkbox') {
                        html += '<div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">';
                        if(opts) opts.forEach(o => {
                            html += `<label style="color: #bdbdbd; cursor: not-allowed; opacity: 0.7;"><input type="${f.type}" disabled> ${o}</label>`;
                        });
                        html += '</div>';
                    }
                    if(f.type === 'file') html += '<input type="file" disabled style="display: block; margin-top: 10px; margin-bottom: 0; opacity: 0.7; cursor: not-allowed;">';
                    html += '</div>';
                });
            }

            if(template.request_resume) {
                html += '<h2 style="margin-top: 30px; border-bottom: 1px solid #3a3f45; padding-bottom: 10px;">Upload Documents</h2><p style="color: #bdbdbd; margin-bottom: 15px;">You may upload a resume or other supporting documents (PDF, DOC, DOCX, JPG, PNG).</p><input type="file" disabled style="display: block; margin-bottom: 30px; opacity: 0.7; cursor: not-allowed;">';
            }
            
            html += '<div style="margin-top: 30px; border-top: 1px solid #3a3f45; padding-top: 20px;"><button type="button" class="btn" style="background: #0f3d1e; color: #9dffb0; border: 1px solid #1a5c30; padding: 15px 30px; font-weight: bold; width: 100%; opacity: 0.5; cursor: not-allowed;">Submit Application</button></div>';
        } else {
            html = '<p style="color: #bdbdbd; font-style: italic;">Select an application template to preview the form.</p>';
        }
        document.getElementById('preview-form-container').innerHTML = html;
    }
</script>
@endsection