<div class="card field-item-box" data-id="{{ $field->id }}">
    <input type="hidden" name="fields[{{ $field->id }}][id]" value="{{ $field->id }}">
    <div class="card-header-flex">
        <div class="flex-gap-15 items-center">
            <span class="drag-handle-icon" title="Drag to reorder">☰</span>
            <div>
                <strong class="fs-16 field-display-label">{{ $field->label }}</strong>
                <span class="status status-awaiting-interview ml-10 field-display-type">{{ ucwords(str_replace('_', ' ', $field->type)) }}</span>
                <span class="status status-needs-review ml-5 field-display-req" style="display:{{ $field->required ? 'inline-block' : 'none' }}">Required</span>
            </div>
        </div>
        <div class="flex-gap-10">
            <button type="button" class="btn btn-sm" onclick="toggleEdit('{{ $field->id }}')">Edit</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.field-item-box').remove(); if(window.updateLivePreview) window.updateLivePreview();">Remove</button>
        </div>
    </div>

    <div id="edit-form-{{ $field->id }}" class="field-edit-panel" style="display: none;">
        @include('application_templates.partials.field-form-inputs', [
            'idPrefix' => 'edit-' . $field->id,
            'labelName' => "fields[{$field->id}][label]",
            'typeName' => "fields[{$field->id}][type]",
            'reqName' => "fields[{$field->id}][required]",
            'fileMultName' => "fields[{$field->id}][file_multiple]",
            'fileMaxName' => "fields[{$field->id}][file_max]",
            'charMaxName' => "fields[{$field->id}][char_max]",
            'fileSizeMaxName' => "fields[{$field->id}][file_size_max]",
            'optionsName' => "fields[{$field->id}][options]",
            'optionsNameArray' => "fields[{$field->id}][options][]",
            'fileOptionsNameArray' => "fields[{$field->id}][options][]",
            'field' => $field,
            'isAdd' => false
        ])
        
        <div class="mt-15">
            <button type="button" class="btn btn-sm btn-outline" onclick="finishFieldEdit('{{ $field->id }}')">Done</button>
        </div>
    </div>
</div>