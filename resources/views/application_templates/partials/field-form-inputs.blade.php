@php
    $idPrefix = $idPrefix ?? 'add';
    $labelName = $labelName ?? 'label';
    $typeName = $typeName ?? 'type';
    $reqName = $reqName ?? 'required';
    $fileMultName = $fileMultName ?? 'file_multiple';
    $fileMaxName = $fileMaxName ?? 'file_max';
    $charMaxName = $charMaxName ?? 'char_max';
    $fileSizeMaxName = $fileSizeMaxName ?? 'file_size_max';
    $optionsName = $optionsName ?? 'options';
    $optionsNameArray = $optionsNameArray ?? 'options[]';
    $fileOptionsNameArray = $fileOptionsNameArray ?? 'file_options[]';
    
    $currentType = old($typeName, $field->type ?? 'text');
    $showOptions = in_array($currentType, ['select', 'checkbox', 'radio']);
    $opts = old($optionsName, $field->options ?? []);
    if (!is_array($opts)) $opts = [];
    $isAdd = $isAdd ?? false;
@endphp

<div class="field-form-wrapper">
    <label>Question Label</label>
    <input type="text" name="{{ $labelName }}" value="{{ old($labelName, $field->label ?? '') }}" class="w-full" {{ !$isAdd ? 'required' : '' }}>

    <div class="flex-wrap-15">
        <div class="flex-1 min-w-200">
            <label>Type</label>
            <select name="{{ $typeName }}" class="field-type-select" onchange="toggleFieldType(this)">
                @foreach (['text','textarea','select','checkbox','radio','file','date'] as $type)
                    <option value="{{ $type }}" {{ $currentType === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="flex-1 d-flex items-center min-w-150 pt-2 flex-wrap-15">
            <label class="text-light cursor-pointer items-center flex-gap-10 mb-0">
                <input type="checkbox" name="{{ $reqName }}" value="1" {{ old($reqName, $field->required ?? false) ? 'checked' : '' }}> Required
            </label>
            
            <div class="file-multiple-wrapper items-center flex-gap-15" style="display: {{ $currentType === 'file' ? 'flex' : 'none' }};">
                <label class="text-light cursor-pointer items-center flex-gap-10 mb-0">
                    <input type="checkbox" name="{{ $fileMultName }}" class="file-multiple-checkbox" value="1" {{ old($fileMultName, $field->file_multiple ?? false) ? 'checked' : '' }} onchange="this.closest('.file-multiple-wrapper').querySelector('.file-max-wrapper').style.display = this.checked ? 'block' : 'none'; if(window.updateLivePreview) window.updateLivePreview();"> Allow Multiple
                </label>
                
                <div class="file-max-wrapper" style="display: {{ old($fileMultName, $field->file_multiple ?? false) ? 'block' : 'none' }};">
                    <label class="d-inline text-muted mr-5 mb-0">Max Files:</label>
                    <select name="{{ $fileMaxName }}" class="d-inline w-auto mb-0 file-max-select" style="padding: 5px 30px 5px 10px;" onchange="if(window.updateLivePreview) window.updateLivePreview();">
                        @for($i=2; $i<=10; $i++)
                            <option value="{{ $i }}" {{ old($fileMaxName, $field->file_max ?? 5) == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="char-max-wrapper flex-gap-10 items-center mt-15" style="display: {{ in_array($currentType, ['text', 'textarea']) ? 'flex' : 'none' }};">
        <label class="mb-0 text-light">Max Characters:</label>
        <input type="number" name="{{ $charMaxName }}" value="{{ old($charMaxName, $field->char_max ?? ($currentType === 'textarea' ? 1024 : 128)) }}" class="m-0 w-auto" min="1" max="5000" oninput="if(window.updateLivePreview) window.updateLivePreview();">
    </div>

    <div class="file-size-max-wrapper flex-gap-10 items-center mt-15" style="display: {{ $currentType === 'file' ? 'flex' : 'none' }};">
        <label class="mb-0 text-light">Max File Size (MB):</label>
        <input type="number" name="{{ $fileSizeMaxName }}" value="{{ old($fileSizeMaxName, $field->file_size_max ?? 7) }}" class="m-0 w-auto" min="1" max="100" oninput="if(window.updateLivePreview) window.updateLivePreview();">
    </div>

    <div class="options-container-block {{ $isAdd ? 'options-container-add' : 'options-container-edit' }}" style="display: {{ $showOptions ? 'block' : 'none' }};">
        <label class="text-primary mb-10">Multiple Choice Options</label>
        <div class="options-list" id="{{ $idPrefix }}-options-list">
            @foreach($opts as $opt)
                @if(!str_starts_with($opt, 'application/') && !str_starts_with($opt, 'image/'))
                    <div class="option-item">
                        <span class="text-muted">&bull;</span>
                        <input type="text" name="{{ $optionsNameArray }}" value="{{ $opt }}" class="m-0 flex-grow-1" required placeholder="Option value">
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove(); if(window.updateLivePreview) window.updateLivePreview();">X</button>
                    </div>
                @endif
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-add-opt-trigger {{ $isAdd ? 'btn-add-opt-alt' : 'btn-add-opt' }}" onclick="addOption('{{ $idPrefix }}-options-list', '{{ $optionsNameArray }}')">+ Add New Option</button>
    </div>

    <div class="file-options-container {{ $isAdd ? 'options-container-add' : 'options-container-edit' }}" style="display: {{ $currentType === 'file' ? 'block' : 'none' }};">
        <label class="text-primary mb-10">Allowed File Types</label>
        <select name="{{ $fileOptionsNameArray }}" class="file-type-select w-full" multiple autocomplete="off" onchange="if(window.updateLivePreview) window.updateLivePreview();">
            <option value="application/pdf" {{ in_array('application/pdf', $opts) ? 'selected' : '' }}>PDF (.pdf)</option>
            <option value="application/msword" {{ in_array('application/msword', $opts) ? 'selected' : '' }}>Word Document (.doc)</option>
            <option value="application/vnd.openxmlformats-officedocument.wordprocessingml.document" {{ in_array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $opts) ? 'selected' : '' }}>Word Document (.docx)</option>
            <option value="image/jpeg" {{ in_array('image/jpeg', $opts) ? 'selected' : '' }}>JPEG Image (.jpg, .jpeg)</option>
            <option value="image/png" {{ in_array('image/png', $opts) ? 'selected' : '' }}>PNG Image (.png)</option>
        </select>
    </div>
</div>