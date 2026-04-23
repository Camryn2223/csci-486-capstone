function initTomSelects(root = document) {
    if (typeof TomSelect === 'undefined') {
        return;
    }

    root.querySelectorAll('.file-type-select').forEach(el => {
        if (el.tomselect || el.classList.contains('tomselected')) {
            return;
        }

        Array.from(el.options).forEach(opt => {
            if (typeof opt.value === 'undefined') {
                opt.value = '';
            }
        });

        new TomSelect(el, {
            plugins: ['remove_button'],
            create: false,
            maxOptions: null
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initTomSelects();

    const fieldsList = document.getElementById('fields-list');
    const formEl = document.getElementById('template-settings-form');
    const addCard = document.getElementById('add-field-card');

    const isDeferredPreviewContext = function(el) {
        return !!(
            el &&
            (
                el.closest('#add-field-card') ||
                el.closest('.field-edit-panel')
            )
        );
    };

    window.toggleEdit = function(id) {
        const form = document.getElementById('edit-form-' + id);
        if (!form) return;

        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    };

    window.finishFieldEdit = function(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form) {
            form.style.display = 'none';
        }

        if (window.updateLivePreview) {
            window.updateLivePreview(true);
        }
    };

    window.toggleFieldType = function(selectElement) {
        const wrapper = selectElement.closest('.field-form-wrapper');
        const type = selectElement.value;

        const optionsContainer = wrapper.querySelector('.options-container-block');
        const fileOptionsContainer = wrapper.querySelector('.file-options-container');
        const multipleWrapper = wrapper.querySelector('.file-multiple-wrapper');
        const charMaxWrapper = wrapper.querySelector('.char-max-wrapper');
        const fileSizeMaxWrapper = wrapper.querySelector('.file-size-max-wrapper');

        // Reset visibility
        if (optionsContainer) optionsContainer.style.display = 'none';
        if (fileOptionsContainer) fileOptionsContainer.style.display = 'none';
        if (multipleWrapper) multipleWrapper.style.display = 'none';
        if (charMaxWrapper) charMaxWrapper.style.display = 'none';
        if (fileSizeMaxWrapper) fileSizeMaxWrapper.style.display = 'none';

        if (['select', 'checkbox', 'radio'].includes(type)) {
            if (optionsContainer) optionsContainer.style.display = 'block';
            
            const list = optionsContainer.querySelector('.options-list');
            if (list && list.children.length === 0) {
                const btn = optionsContainer.querySelector('.btn-add-opt-trigger');
                if(btn) btn.click();
            }
        } else if (type === 'file') {
            if (fileOptionsContainer) fileOptionsContainer.style.display = 'block';
            if (multipleWrapper) multipleWrapper.style.display = 'flex';
            if (fileSizeMaxWrapper) fileSizeMaxWrapper.style.display = 'flex';
        } else if (['text', 'textarea', 'rich_text'].includes(type)) {
            if (charMaxWrapper) {
                charMaxWrapper.style.display = 'flex';
                const charMaxInput = charMaxWrapper.querySelector('input');
                if (charMaxInput && !charMaxInput.value) {
                    charMaxInput.value = (type === 'text') ? 128 : 1024;
                }
            }
        }
        
        if (window.updateLivePreview) window.updateLivePreview();
    };

    window.addOption = function(listId, nameStr = 'options[]') {
        const list = document.getElementById(listId);
        if (!list) return;

        const isRealFieldOption = nameStr.startsWith('fields[');

        const div = document.createElement('div');
        div.className = 'option-item';
        div.innerHTML = `
            <span class="text-muted">&bull;</span>
            <input type="text" name="${nameStr}" class="m-0 flex-grow-1" ${isRealFieldOption ? 'required' : ''} placeholder="New Option">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove();">X</button>
        `;
        list.appendChild(div);
    };

    window.addFieldToCreateForm = function() {
        try {
            const labelInput = addCard.querySelector('[name="add_label"]');
            if(!labelInput || !labelInput.value) { alert('Please enter a Question Label.'); return; }
            
            const typeInput = addCard.querySelector('[name="add_type"]');
            const reqInput = addCard.querySelector('[name="add_required"]');
            const multInput = addCard.querySelector('[name="add_file_multiple"]');
            const maxInput = addCard.querySelector('[name="add_file_max"]');
            const charMaxInput = addCard.querySelector('[name="add_char_max"]');
            const fileSizeMaxInput = addCard.querySelector('[name="add_file_size_max"]');
            
            let opts = [];
            if (typeInput && typeInput.value === 'file') {
                const select = addCard.querySelector('.file-type-select');
                if(select && select.tomselect) {
                    const vals = select.tomselect.getValue();
                    opts = Array.isArray(vals) ? vals : (vals ? [vals] : []);
                }
            } else if (typeInput && ['select', 'checkbox', 'radio'].includes(typeInput.value)) {
                opts = Array.from(addCard.querySelectorAll('[name="add_options[]"]')).map(i => i.value).filter(v => v);
            }

            let isMultiple = typeInput && typeInput.value === 'file' && multInput && multInput.checked;
            
            // Generate a unique ID to ensure PHP parses every added field uniquely (Fixes overwrite bug)
            const fieldId = 'new_' + Date.now() + Math.floor(Math.random() * 1000);
            const tval = typeInput ? typeInput.value : 'text';

            let html = `
                <div class="card field-item-box" data-id="${fieldId}">
                    <input type="hidden" name="fields[${fieldId}][id]" value="${fieldId}">
                    <div class="card-header-flex">
                        <div class="flex-gap-15 items-center">
                            <span class="drag-handle-icon" title="Drag to reorder">☰</span>
                            <div>
                                <strong class="fs-16 field-display-label">${labelInput.value.replace(/</g, '&lt;')}</strong>
                                <span class="status status-awaiting-interview ml-10 field-display-type">${tval.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                                <span class="status status-needs-review ml-5 field-display-req" style="display:${reqInput && reqInput.checked ? 'inline-block' : 'none'}">Required</span>
                            </div>
                        </div>
                        <div class="flex-gap-10">
                            <button type="button" class="btn btn-sm" onclick="toggleEdit('${fieldId}')">Edit</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.field-item-box').remove(); if(window.updateLivePreview) window.updateLivePreview();">Remove</button>
                        </div>
                    </div>
                    
                    <div id="edit-form-${fieldId}" class="field-edit-panel" style="display:none;">
                        <div class="field-form-wrapper">
                            <label>Question Label</label>
                            <input type="text" name="fields[${fieldId}][label]" value="${labelInput.value.replace(/"/g, '&quot;')}" class="w-full" required oninput="let lbl = this.closest('.field-item-box')?.querySelector('.field-display-label'); if(lbl) lbl.textContent = this.value; if(window.updateLivePreview) window.updateLivePreview();">

                            <div class="flex-wrap-15">
                                <div class="flex-1 min-w-200">
                                    <label>Type</label>
                                    <select name="fields[${fieldId}][type]" class="field-type-select" onchange="toggleFieldType(this); let tlbl = this.closest('.field-item-box')?.querySelector('.field-display-type'); if(tlbl) tlbl.textContent = this.options[this.selectedIndex].text;">
                                        ${['text','textarea','rich_text','select','checkbox','radio','file','date'].map(t => `<option value="${t}" ${tval===t?'selected':''}>${t.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>`).join('')}
                                    </select>
                                </div>
                                
                                <div class="flex-1 d-flex items-center min-w-150 pt-2 flex-wrap-15">
                                    <label class="text-light cursor-pointer items-center flex-gap-10 mb-0">
                                        <input type="checkbox" name="fields[${fieldId}][required]" value="1" ${reqInput&&reqInput.checked?'checked':''} onchange="let rlbl = this.closest('.field-item-box')?.querySelector('.field-display-req'); if(rlbl) rlbl.style.display = this.checked ? 'inline-block' : 'none'; if(window.updateLivePreview) window.updateLivePreview();"> Required
                                    </label>
                                    
                                    <div class="file-multiple-wrapper items-center flex-gap-15" style="display: ${tval==='file'?'flex':'none'};">
                                        <label class="text-light cursor-pointer items-center flex-gap-10 mb-0">
                                            <input type="checkbox" name="fields[${fieldId}][file_multiple]" class="file-multiple-checkbox" value="1" ${isMultiple?'checked':''} onchange="this.closest('.file-multiple-wrapper').querySelector('.file-max-wrapper').style.display = this.checked ? 'block' : 'none'; if(window.updateLivePreview) window.updateLivePreview();"> Allow Multiple
                                        </label>
                                        
                                        <div class="file-max-wrapper" style="display: ${isMultiple?'block':'none'};">
                                            <label class="d-inline text-muted mr-5 mb-0">Max Files:</label>
                                            <select name="fields[${fieldId}][file_max]" class="d-inline w-auto mb-0 file-max-select" style="padding: 5px 30px 5px 10px;" onchange="if(window.updateLivePreview) window.updateLivePreview();">
                                                ${[2,3,4,5,6,7,8,9,10].map(i => `<option value="${i}" ${maxInput && maxInput.value==i ? 'selected' : ''}>${i}</option>`).join('')}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="char-max-wrapper flex-gap-10 items-center mt-15" style="display: ${['text','textarea','rich_text'].includes(tval)?'flex':'none'};">
                                <label class="mb-0 text-light">Max Characters:</label>
                                <input type="number" name="fields[${fieldId}][char_max]" value="${charMaxInput && charMaxInput.value ? charMaxInput.value : (tval==='text'?128:1024)}" class="m-0 w-auto" min="1" max="5000" oninput="if(window.updateLivePreview) window.updateLivePreview();">
                            </div>

                            <div class="file-size-max-wrapper flex-gap-10 items-center mt-15" style="display: ${tval==='file'?'flex':'none'};">
                                <label class="mb-0 text-light">Max File Size (MB):</label>
                                <input type="number" name="fields[${fieldId}][file_size_max]" value="${fileSizeMaxInput && fileSizeMaxInput.value ? fileSizeMaxInput.value : 7}" class="m-0 w-auto" min="1" max="100" oninput="if(window.updateLivePreview) window.updateLivePreview();">
                            </div>

                            <div class="options-container-block options-container-edit" style="display: ${['select','checkbox','radio'].includes(tval)?'block':'none'};">
                                <label class="text-primary mb-10">Multiple Choice Options</label>
                                <div class="options-list" id="${fieldId}-options-list">
                                    ${opts.filter(o => !o.startsWith('application/') && !o.startsWith('image/')).map(opt => `
                                        <div class="option-item">
                                            <span class="text-muted">&bull;</span>
                                            <input type="text" name="fields[${fieldId}][options][]" value="${opt.replace(/"/g, '&quot;')}" class="m-0 flex-grow-1" required placeholder="Option value" oninput="if(window.updateLivePreview) window.updateLivePreview();">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove(); if(window.updateLivePreview) window.updateLivePreview();">X</button>
                                        </div>
                                    `).join('')}
                                </div>
                                <button type="button" class="btn btn-sm btn-add-opt-trigger btn-add-opt" onclick="addOption('${fieldId}-options-list', 'fields[${fieldId}][options][]')">+ Add New Option</button>
                            </div>

                            <div class="file-options-container options-container-edit" style="display: ${tval==='file'?'block':'none'};">
                                <label class="text-primary mb-10">Allowed File Types</label>
                                <select name="fields[${fieldId}][options][]" class="file-type-select w-full" multiple autocomplete="off" onchange="if(window.updateLivePreview) window.updateLivePreview();">
                                    <option value="application/pdf" ${opts.includes('application/pdf')?'selected':''}>PDF (.pdf)</option>
                                    <option value="application/msword" ${opts.includes('application/msword')?'selected':''}>Word Document (.doc)</option>
                                    <option value="application/vnd.openxmlformats-officedocument.wordprocessingml.document" ${opts.includes('application/vnd.openxmlformats-officedocument.wordprocessingml.document')?'selected':''}>Word Document (.docx)</option>
                                    <option value="image/jpeg" ${opts.includes('image/jpeg')?'selected':''}>JPEG Image (.jpg, .jpeg)</option>
                                    <option value="image/png" ${opts.includes('image/png')?'selected':''}>PNG Image (.png)</option>
                                </select>
                            </div>

                            <div class="mt-15">
                                <button type="button" class="btn btn-sm btn-outline" onclick="finishFieldEdit('${fieldId}')">Done</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            fieldsList.insertAdjacentHTML('beforeend', html);

            const newField = fieldsList.lastElementChild;
            if (newField) {
                initTomSelects(newField);
            }
            
            // Robustly reset the "Add New Field" card safely ensuring execution proceeds (Fixes reset bug)
            if (labelInput) labelInput.value = '';
            if (reqInput) reqInput.checked = false;
            if (multInput) multInput.checked = false;
            if (maxInput) maxInput.value = "5";
            if (charMaxInput) charMaxInput.value = '128';
            if (fileSizeMaxInput) fileSizeMaxInput.value = '7';
            
            if (typeInput) {
                typeInput.value = 'text';
                window.toggleFieldType(typeInput);
            }
            
            const optsList = addCard.querySelector('.options-list');
            if(optsList) optsList.innerHTML = '';
            
            const fileSelect = addCard.querySelector('.file-type-select');
            if(fileSelect && fileSelect.tomselect) fileSelect.tomselect.clear(true);

            if (window.updateLivePreview) window.updateLivePreview(true);
            
        } catch (error) {
            console.error('Error adding field:', error);
            alert('An error occurred while adding the field. Please check the console.');
        }
    };

    let previewTimeout;

    window.updateLivePreview = function(force = false) {
        if (!window.previewUrl) return;

        const previewContainer = document.getElementById('preview-content');
        if (!previewContainer || !formEl) return;

        if (!force && isDeferredPreviewContext(document.activeElement)) {
            return;
        }

        clearTimeout(previewTimeout);

        previewTimeout = setTimeout(() => {
            const titleEl = document.getElementById('preview-template-name');
            if (titleEl) {
                const nameInp = formEl.querySelector('[name="name"]');
                titleEl.textContent = nameInp && nameInp.value ? nameInp.value : 'Untitled Template';
            }

            const payload = {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                name: formEl.querySelector('[name="name"]')?.value,
                request_name: formEl.querySelector('[name="request_name"]')?.checked ? 1 : 0,
                request_email: formEl.querySelector('[name="request_email"]')?.checked ? 1 : 0,
                request_phone: formEl.querySelector('[name="request_phone"]')?.checked ? 1 : 0,
                fields: []
            };

            const fieldBoxes = document.querySelectorAll('.field-item-box');

            fieldBoxes.forEach((box, index) => {
                const labelInput = box.querySelector('[name="label"]') || box.querySelector(`[name$="[label]"]`);
                if (!labelInput) return;

                const typeInput = box.querySelector('[name="type"]') || box.querySelector(`[name$="[type]"]`);
                const reqInput = box.querySelector('[name="required"]') || box.querySelector(`[name$="[required]"]`);
                const multInput = box.querySelector('[name="file_multiple"]') || box.querySelector(`[name$="[file_multiple]"]`);
                const maxInput = box.querySelector('[name="file_max"]') || box.querySelector(`[name$="[file_max]"]`);
                const charMaxInput = box.querySelector('[name="char_max"]') || box.querySelector(`[name$="[char_max]"]`);
                const fileSizeMaxInput = box.querySelector('[name="file_size_max"]') || box.querySelector(`[name$="[file_size_max]"]`);

                let opts = [];

                if (typeInput && typeInput.value === 'file') {
                    const fileSelect = box.querySelector('.file-type-select');

                    if (fileSelect) {
                        if (fileSelect.tomselect) {
                            const vals = fileSelect.tomselect.getValue();
                            opts = Array.isArray(vals) ? vals : (vals ? [vals] : []);
                        } else {
                            Array.from(fileSelect.selectedOptions).forEach(opt => opts.push(opt.value));
                        }
                    } else {
                        const hiddenOpts = box.querySelectorAll(`[name$="[options][]"]`);
                        hiddenOpts.forEach(opt => {
                            if (opt.value) opts.push(opt.value);
                        });
                    }
                } else {
                    const optInputs = box.querySelectorAll('[name="options[]"], [name$="[options][]"]');
                    optInputs.forEach(opt => {
                        if (opt.value) opts.push(opt.value);
                    });
                }

                payload.fields.push({
                    id: box.dataset.id || index,
                    label: labelInput.value,
                    type: typeInput ? typeInput.value : 'text',
                    required: reqInput ? (reqInput.checked || reqInput.value == "1") : false,
                    file_multiple: multInput ? (multInput.checked || multInput.value == "1") : false,
                    file_max: maxInput ? maxInput.value : null,
                    char_max: charMaxInput ? charMaxInput.value : null,
                    file_size_max: fileSizeMaxInput ? fileSizeMaxInput.value : null,
                    options: opts
                });
            });

            fetch(window.previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/html'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.text())
            .then(html => {
                previewContainer.innerHTML = html;

                if (typeof window.initCharCounters === 'function') {
                    window.initCharCounters(previewContainer);
                }

                if (typeof tinymce !== 'undefined') {
                    tinymce.remove('.tinymce-applicant');

                    if (typeof window.initTinyMceEditors === 'function') {
                        window.initTinyMceEditors('.tinymce-applicant');
                    }
                }
            })
            .catch(err => console.error('Preview update failed:', err));
        }, 200);
    };

    if (formEl) {
        formEl.addEventListener('input', function(e) {
            if (isDeferredPreviewContext(e.target)) return;
            window.updateLivePreview(true);
        });

        formEl.addEventListener('change', function(e) {
            if (isDeferredPreviewContext(e.target)) return;
            window.updateLivePreview(true);
        });
    }

    if (fieldsList && typeof Sortable !== 'undefined') {
        new Sortable(fieldsList, {
            handle: '.drag-handle-icon',
            animation: 150,
            onEnd: function () {
                if (window.updateLivePreview) window.updateLivePreview(true);
            }
        });
    }
});