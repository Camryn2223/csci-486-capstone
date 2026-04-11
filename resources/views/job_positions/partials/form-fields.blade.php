<label>Title</label>
<input type="text" name="title" id="input-title" value="{{ old('title', $jobPosition->title ?? '') }}" required oninput="updatePreview()">

<label>Application Template</label>
@if($isEdit)
    <div class="form-inline-start mb-18">
        <div class="flex-grow-1">
            <select name="template_id" id="input-template" required onchange="updatePreview(); updateTemplateLink()" class="mb-0">
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" {{ old('template_id', $jobPosition->template_id) == $template->id ? 'selected' : '' }}>
                        {{ $template->name }} ({{ $template->fields_count }} fields)
                    </option>
                @endforeach
            </select>
        </div>
        <a href="#" id="edit-template-btn" class="btn btn-slate white-space-nowrap" style="display: none;">Edit Selected Template</a>
    </div>
@else
    <select name="template_id" id="input-template" required onchange="updatePreview()">
        <option value="">-- Select Template --</option>
        @foreach ($templates as $template)
            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                {{ $template->name }} ({{ $template->fields_count }} fields)
            </option>
        @endforeach
    </select>
@endif

<label>Description</label>
<textarea name="description" id="input-desc" rows="4" required oninput="updatePreview()">{{ old('description', $jobPosition->description ?? '') }}</textarea>

<label>Requirements</label>
<textarea name="requirements" id="input-reqs" rows="4" required oninput="updatePreview()">{{ old('requirements', $jobPosition->requirements ?? '') }}</textarea>

<label>Status</label>
<select name="status">
    <option value="open" {{ old('status', $jobPosition->status ?? 'open') === 'open' ? 'selected' : '' }}>Open</option>
    <option value="closed" {{ old('status', $jobPosition->status ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
</select>

<div class="mt-15">
    <button type="submit" class="btn">{{ $isEdit ? 'Save Changes' : 'Create Position' }}</button>
</div>