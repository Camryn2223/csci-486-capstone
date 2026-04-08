@extends('layouts.app')

@section('content')
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">Edit Template: {{ $applicationTemplate->name }}</h1>
        <a href="{{ route('organizations.application-templates.index', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Templates</a>
    </div>

    <!-- Live Preview QoL feature -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <button id="btn-tab-builder" class="btn" style="background: #6d3fa9; border: none;" onclick="switchTab('builder')">Builder</button>
        <button id="btn-tab-preview" class="btn" style="background: #24282d; border: 1px solid #3a3f45;" onclick="switchTab('preview')">Preview Form</button>
    </div>

    <div id="tab-builder">
        <!-- Configuration and Standard Fields -->
        <div class="card">
            <h2 style="margin-top: 0;">Template Settings</h2>
            <form method="POST" action="{{ route('organizations.application-templates.update', [$organization, $applicationTemplate]) }}">
                @csrf
                @method('PUT')
                
                <div style="margin-bottom: 25px;">
                    <label>Template Name</label>
                    <input type="text" name="name" value="{{ old('name', $applicationTemplate->name) }}" required style="margin-bottom: 0;">
                </div>

                <h3 style="font-size: 16px; margin-bottom: 10px; color: #a97dff;">Standard Sections</h3>
                <p style="color: #bdbdbd; font-size: 14px; margin-top: 0;">Choose which standard fields to request from the applicant.</p>
                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                    <label style="color: #e6e6e6; cursor: pointer;">
                        <input type="checkbox" name="request_name" value="1" {{ old('request_name', $applicationTemplate->request_name) ? 'checked' : '' }}> Request Full Name
                    </label>
                    <label style="color: #e6e6e6; cursor: pointer;">
                        <input type="checkbox" name="request_email" value="1" {{ old('request_email', $applicationTemplate->request_email) ? 'checked' : '' }}> Request Email Address
                    </label>
                    <label style="color: #e6e6e6; cursor: pointer;">
                        <input type="checkbox" name="request_phone" value="1" {{ old('request_phone', $applicationTemplate->request_phone) ? 'checked' : '' }}> Request Phone Number
                    </label>
                    <label style="color: #e6e6e6; cursor: pointer;">
                        <input type="checkbox" name="request_resume" value="1" {{ old('request_resume', $applicationTemplate->request_resume) ? 'checked' : '' }}> Request Resume / Documents
                    </label>
                </div>

                <button type="submit" class="btn btn-sm">Save Settings</button>
            </form>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; margin-bottom: 10px;">
            <h2 style="margin: 0;">Custom Fields</h2>
            <!-- Drag and Drop QoL Hint -->
            <span style="color: #bdbdbd; font-size: 13px;">Drag the ☰ icon to reorder fields automatically.</span>
        </div>
        
        <div id="fields-list">
            @forelse ($applicationTemplate->fields as $field)
                <div class="card field-item" data-id="{{ $field->id }}" style="padding: 15px; margin-bottom: 10px; border: 1px solid #3a3f45;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span class="drag-handle" style="cursor: grab; color: #a97dff; font-size: 24px; line-height: 1;" title="Drag to reorder">☰</span>
                            <div>
                                <strong style="font-size: 16px;">{{ $field->label }}</strong>
                                <span class="status status-awaiting-interview" style="margin-left: 10px;">{{ ucfirst($field->type) }}</span>
                                @if($field->required) <span class="status status-needs-review" style="margin-left: 5px;">Required</span> @endif
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-sm" style="background: #3a245a;" onclick="toggleEdit('{{ $field->id }}')">Edit</button>
                            <form method="POST" action="{{ route('organizations.application-templates.fields.destroy', [$organization, $applicationTemplate, $field]) }}" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this field?')">Delete</button>
                            </form>
                        </div>
                    </div>

                    <!-- Hidden Edit Form for each field -->
                    <div id="edit-form-{{ $field->id }}" style="display: {{ $errors->has('label') && old('type') !== null ? 'block' : 'none' }}; margin-top: 20px; padding-top: 20px; border-top: 1px solid #2f343a;">
                        <form method="POST" action="{{ route('organizations.application-templates.fields.update', [$organization, $applicationTemplate, $field]) }}">
                            @csrf
                            @method('PATCH')

                            @php
                                $currentType = old('type') !== null ? old('type') : $field->type;
                                $showOptions = in_array($currentType, ['select', 'checkbox', 'radio']);
                                $oldOptions = old('options');
                                $opts = is_array($oldOptions) ? $oldOptions : ($field->options ?? []);
                            @endphp

                            <label>Question Label</label>
                            <input type="text" name="label" value="{{ old('label', $field->label) }}" style="max-width: 100%;" required>
                            
                            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 200px;">
                                    <label>Type</label>
                                    <select name="type" onchange="toggleOptions(this, 'edit-options-container-{{ $field->id }}', 'edit-options-list-{{ $field->id }}')">
                                        @foreach (['text','textarea','select','checkbox','radio','file','date'] as $type)
                                            <option value="{{ $type }}" {{ $currentType === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1; display: flex; align-items: center; min-width: 150px; padding-top: 10px;">
                                    <label style="color: #e6e6e6; cursor: pointer;">
                                        <input type="checkbox" name="required" value="1" {{ old('required', $field->required) ? 'checked' : '' }}> Required
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Improved Multiple Options Dynamic Inputs UI -->
                            <div id="edit-options-container-{{ $field->id }}" style="display: {{ $showOptions ? 'block' : 'none' }}; margin-top: 15px; background: #1a1d21; padding: 15px; border-radius: 6px; border: 1px solid #2f343a;">
                                <label style="color: #a97dff; margin-bottom: 10px;">Multiple Choice Options</label>
                                <div id="edit-options-list-{{ $field->id }}">
                                    @foreach($opts as $opt)
                                        <div class="option-item" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                            <span style="color: #bdbdbd;">&bull;</span>
                                            <input type="text" name="options[]" value="{{ $opt }}" style="margin: 0; flex-grow: 1;" required placeholder="Option value">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm" style="background: #24282d; border: 1px dashed #6d3fa9; margin-top: 10px;" onclick="addOption('edit-options-list-{{ $field->id }}')">+ Add New Option</button>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button type="submit" class="btn btn-sm">Update Field</button>
                                <button type="button" class="btn btn-sm" style="background: #24282d; border: 1px solid #3a3f45; margin-left: 10px;" onclick="toggleEdit('{{ $field->id }}')">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="card"><p>No custom fields added yet.</p></div>
            @endforelse
        </div>

        <div class="card" style="margin-top: 30px; border: 1px dashed #6d3fa9; background: #1a1d21;">
            <h2 style="margin-top: 0;">Add New Field</h2>
            <form method="POST" action="{{ route('organizations.application-templates.fields.store', [$organization, $applicationTemplate]) }}">
                @csrf

                @php
                    $addType = old('type', 'text');
                    $addShowOptions = in_array($addType, ['select', 'checkbox', 'radio']);
                    $addOptions = is_array(old('options')) ? old('options') : [];
                @endphp

                <label>Question Label</label>
                <input type="text" name="label" value="{{ old('label') }}" style="max-width: 100%;" required>
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label>Type</label>
                        <select name="type" onchange="toggleOptions(this, 'add-options-container', 'add-options-list')">
                            @foreach (['text','textarea','select','checkbox','radio','file','date'] as $type)
                                <option value="{{ $type }}" {{ $addType === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 1; display: flex; align-items: center; min-width: 150px; padding-top: 10px;">
                        <label style="color: #e6e6e6; cursor: pointer;">
                            <input type="checkbox" name="required" value="1" {{ old('required') ? 'checked' : '' }}> Required
                        </label>
                    </div>
                </div>

                <!-- Improved Multiple Options Dynamic Inputs UI -->
                <div id="add-options-container" style="display: {{ $addShowOptions ? 'block' : 'none' }}; margin-top: 15px; background: #24282d; padding: 15px; border-radius: 6px; border: 1px solid #3a3f45;">
                    <label style="color: #a97dff; margin-bottom: 10px;">Multiple Choice Options</label>
                    <div id="add-options-list">
                        @foreach($addOptions as $opt)
                            <div class="option-item" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                <span style="color: #bdbdbd;">&bull;</span>
                                <input type="text" name="options[]" value="{{ $opt }}" style="margin: 0; flex-grow: 1;" required placeholder="Option value">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm" style="background: #1a1d21; border: 1px dashed #6d3fa9; margin-top: 10px;" onclick="addOption('add-options-list')">+ Add New Option</button>
                </div>
                
                <div style="margin-top: 15px;">
                    <button type="submit" class="btn">Add Field</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Interactive Builder Preview QoL Tab -->
    <div id="tab-preview" style="display: none;">
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
</div>

<!-- SortableJS library for Drag and Drop functionality -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    // Tab Switching functionality
    function switchTab(tab) {
        document.getElementById('tab-builder').style.display = tab === 'builder' ? 'block' : 'none';
        document.getElementById('tab-preview').style.display = tab === 'preview' ? 'block' : 'none';
        
        document.getElementById('btn-tab-builder').style.background = tab === 'builder' ? '#6d3fa9' : '#24282d';
        document.getElementById('btn-tab-builder').style.border = tab === 'builder' ? 'none' : '1px solid #3a3f45';
        
        document.getElementById('btn-tab-preview').style.background = tab === 'preview' ? '#6d3fa9' : '#24282d';
        document.getElementById('btn-tab-preview').style.border = tab === 'preview' ? 'none' : '1px solid #3a3f45';
    }

    // Toggle hidden field edit form
    function toggleEdit(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }

    // Toggle options container visibility based on input type
    function toggleOptions(selectElement, containerId, listId) {
        const container = document.getElementById(containerId);
        const list = document.getElementById(listId);
        if (['select', 'checkbox', 'radio'].includes(selectElement.value)) {
            container.style.display = 'block';
            if (list.children.length === 0) {
                addOption(listId); // auto-add a default blank option row if empty
            }
        } else {
            container.style.display = 'none';
        }
    }

    // Add dynamic UI option field
    function addOption(listId) {
        const list = document.getElementById(listId);
        const div = document.createElement('div');
        div.className = 'option-item';
        div.style.display = 'flex';
        div.style.gap = '10px';
        div.style.marginBottom = '10px';
        div.style.alignItems = 'center';
        div.innerHTML = `
            <span style="color: #bdbdbd;">&bull;</span>
            <input type="text" name="options[]" style="margin: 0; flex-grow: 1;" required placeholder="New Option">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>
        `;
        list.appendChild(div);
    }

    // Initialize SortableJS for drag-and-drop automatic reordering
    document.addEventListener("DOMContentLoaded", function() {
        const el = document.getElementById('fields-list');
        if(el) {
            new Sortable(el, {
                handle: '.drag-handle', // Class defining the dragging icon
                animation: 150,
                onEnd: function () {
                    // Collect new order logic
                    const items = document.querySelectorAll('.field-item');
                    const order = Array.from(items).map(item => item.dataset.id);
                    
                    // Dispatch fetch API to the Controller's reorder endpoint
                    fetch("{{ route('organizations.application-templates.fields.reorder', [$organization, $applicationTemplate]) }}", {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: order })
                    }).then(res => res.json()).then(data => {
                        console.log(data.message); // Field order updated.
                    }).catch(error => {
                        console.error('Failed to update field order:', error);
                    });
                }
            });
        }
    });
</script>
@endsection