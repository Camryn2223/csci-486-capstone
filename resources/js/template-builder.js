function initTomSelects() {
    if (typeof TomSelect !== 'undefined') {
        document.querySelectorAll('.file-type-select:not(.tomselected)').forEach(el => {
            new TomSelect(el, {
                plugins: ['remove_button'],
                create: false,
                maxOptions: null
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initTomSelects();

    const fieldsList = document.getElementById('fields-list');
    const formEl = document.getElementById('template-settings-form');
    const addCard = document.getElementById('add-field-card');
    let fieldCounter = 0;

    window.toggleEdit = function(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
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
        } else if (['text', 'textarea'].includes(type)) {
            if (charMaxWrapper) {
                charMaxWrapper.style.display = 'flex';
                const charMaxInput = charMaxWrapper.querySelector('input');
                if (charMaxInput && !charMaxInput.value) {
                    charMaxInput.value = (type === 'textarea') ? 1024 : 128;
                }
            }
        }
        
        if (window.updateLivePreview) window.updateLivePreview();
    };

    window.addOption = function(listId, nameStr = 'options[]') {
        const list = document.getElementById(listId);
        const div = document.createElement('div');
        div.className = 'option-item';
        div.innerHTML = `
            <span class="text-muted">&bull;</span>
            <input type="text" name="${nameStr}" class="m-0 flex-grow-1" required placeholder="New Option">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove(); if(window.updateLivePreview) window.updateLivePreview();">X</button>
        `;
        list.appendChild(div);
        if(window.updateLivePreview) window.updateLivePreview();
    };

    window.addFieldToCreateForm = function() {
        const labelInput = addCard.querySelector('[name="add_label"]');
        if(!labelInput.value) { alert('Please enter a Question Label.'); return; }
        
        const typeInput = addCard.querySelector('[name="add_type"]');
        const reqInput = addCard.querySelector('[name="add_required"]');
        const multInput = addCard.querySelector('[name="add_file_multiple"]') || addCard.querySelector('.file-multiple-checkbox');
        const maxInput = addCard.querySelector('[name="add_file_max"]') || addCard.querySelector('.file-max-select');
        const charMaxInput = addCard.querySelector('[name="add_char_max"]');
        const fileSizeMaxInput = addCard.querySelector('[name="add_file_size_max"]');
        
        let opts = [];
        if (typeInput.value === 'file') {
            const select = addCard.querySelector('.file-type-select');
            if(select && select.tomselect) {
                const vals = select.tomselect.getValue();
                opts = Array.isArray(vals) ? vals : (vals ? [vals] : []);
            }
        } else if (['select', 'checkbox', 'radio'].includes(typeInput.value)) {
            opts = Array.from(addCard.querySelectorAll('[name="add_options[]"]')).map(i => i.value).filter(v => v);
        }

        let isMultiple = typeInput.value === 'file' && multInput && multInput.checked;

        let html = `
            <div class="card field-item-box" data-id="new_${fieldCounter}">
                <div class="card-header-flex">
                    <div class="flex-gap-15 items-center">
                        <span class="drag-handle-icon" title="Drag to reorder">☰</span>
                        <div>
                            <strong class="fs-16 field-display-label">${labelInput.value.replace(/</g, '&lt;')}</strong>
                            <span class="status status-awaiting-interview ml-10 field-display-type">${typeInput.value}</span>
                            <span class="status status-needs-review ml-5 field-display-req" style="display:${reqInput.checked ? 'inline-block' : 'none'}">Required</span>
                        </div>
                    </div>
                    <div class="flex-gap-10">
                        <button type="button" class="btn btn-sm" onclick="toggleEdit('new_${fieldCounter}')">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.field-item-box').remove(); if(window.updateLivePreview) window.updateLivePreview();">Remove</button>
                    </div>
                </div>
                
                <div id="edit-form-new_${fieldCounter}" class="field-edit-panel" style="display:none;">
                    <div class="field-form-wrapper">
                        <label>Question Label</label>
                        <input type="text" name="fields[${fieldCounter}][label]" value="${labelInput.value.replace(/"/g, '&quot;')}" class="w-full" required oninput="this.closest('.field-item-box').querySelector('.field-display-label').textContent = this.value;">

                        <div class="flex-wrap-15">
                            <div class="flex-1 min-w-200">
                                <label>Type</label>
                                <select name="fields[${fieldCounter}][type]" class="field-type-select" onchange="toggleFieldType(this); this.closest('.field-item-box').querySelector('.field-display-type').textContent = this.value;">
                                    ${['text','textarea','select','checkbox','radio','file','date'].map(t => `<option value="${t}" ${typeInput.value===t?'selected':''}>${t.charAt(0).toUpperCase() + t.slice(1)}</option>`).join('')}
                                </select>
                            </div>
                            
                            <div class="flex-1 d-flex items-center min-w-150 pt-2 flex-wrap-15">
                                <label class="text-light cursor-pointer items-center flex-gap-10 mb-0">
                                    <input type="checkbox" name="fields[${fieldCounter}][required]" value="1" ${reqInput.checked?'checked':''} onchange="this.closest('.field-item-box').querySelector('.field-display-req').style.display = this.checked ? 'inline-block' : 'none';"> Required
                                </label>
                                
                                <div class="file-multiple-wrapper items-center flex-gap-15" style="display: ${typeInput.value==='file'?'flex':'none'};">
                                    <label class="text-light cursor-pointer items-center flex-gap-10 mb-0">
                                        <input type="checkbox" name="fields[${fieldCounter}][file_multiple]" class="file-multiple-checkbox" value="1" ${isMultiple?'checked':''} onchange="this.closest('.file-multiple-wrapper').querySelector('.file-max-wrapper').style.display = this.checked ? 'block' : 'none';"> Allow Multiple
                                    </label>
                                    
                                    <div class="file-max-wrapper" style="display: ${isMultiple?'block':'none'};">
                                        <label class="d-inline text-muted mr-5 mb-0">Max Files:</label>
                                        <select name="fields[${fieldCounter}][file_max]" class="d-inline w-auto mb-0 file-max-select" style="padding: 5px 30px 5px 10px;">
                                            ${[2,3,4,5,6,7,8,9,10].map(i => `<option value="${i}" ${maxInput && maxInput.value==i ? 'selected' : ''}>${i}</option>`).join('')}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="char-max-wrapper flex-gap-10 items-center mt-15" style="display: ${['text','textarea'].includes(typeInput.value)?'flex':'none'};">
                            <label class="mb-0 text-light">Max Characters:</label>
                            <input type="number" name="fields[${fieldCounter}][char_max]" value="${charMaxInput ? charMaxInput.value : (typeInput.value==='textarea'?1024:128)}" class="m-0 w-auto" min="1" max="5000">
                        </div>

                        <div class="file-size-max-wrapper flex-gap-10 items-center mt-15" style="display: ${typeInput.value==='file'?'flex':'none'};">
                            <label class="mb-0 text-light">Max File Size (MB):</label>
                            <input type="number" name="fields[${fieldCounter}][file_size_max]" value="${fileSizeMaxInput ? fileSizeMaxInput.value : 7}" class="m-0 w-auto" min="1" max="100">
                        </div>

                        <div class="options-container-block options-container-edit" style="display: ${['select','checkbox','radio'].includes(typeInput.value)?'block':'none'};">
                            <label class="text-primary mb-10">Multiple Choice Options</label>
                            <div class="options-list" id="new_${fieldCounter}-options-list">
                                ${opts.filter(o => !o.startsWith('application/') && !o.startsWith('image/')).map(opt => `
                                    <div class="option-item">
                                        <span class="text-muted">&bull;</span>
                                        <input type="text" name="fields[${fieldCounter}][options][]" value="${opt.replace(/"/g, '&quot;')}" class="m-0 flex-grow-1" required placeholder="Option value">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove(); if(window.updateLivePreview) window.updateLivePreview();">X</button>
                                    </div>
                                `).join('')}
                            </div>
                            <button type="button" class="btn btn-sm btn-add-opt-trigger btn-add-opt" onclick="addOption('new_${fieldCounter}-options-list', 'fields[${fieldCounter}][options][]')">+ Add New Option</button>
                        </div>

                        <div class="file-options-container options-container-edit" style="display: ${typeInput.value==='file'?'block':'none'};">
                            <label class="text-primary mb-10">Allowed File Types</label>
                            <select name="fields[${fieldCounter}][options][]" class="file-type-select w-full" multiple autocomplete="off">
                                <option value="application/pdf" ${opts.includes('application/pdf')?'selected':''}>PDF (.pdf)</option>
                                <option value="application/msword" ${opts.includes('application/msword')?'selected':''}>Word Document (.doc)</option>
                                <option value="application/vnd.openxmlformats-officedocument.wordprocessingml.document" ${opts.includes('application/vnd.openxmlformats-officedocument.wordprocessingml.document')?'selected':''}>Word Document (.docx)</option>
                                <option value="image/jpeg" ${opts.includes('image/jpeg')?'selected':''}>JPEG Image (.jpg, .jpeg)</option>
                                <option value="image/png" ${opts.includes('image/png')?'selected':''}>PNG Image (.png)</option>
                            </select>
                        </div>

                        <div class="mt-15">
                            <button type="button" class="btn btn-sm btn-outline" onclick="toggleEdit('new_${fieldCounter}')">Done</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        fieldsList.insertAdjacentHTML('beforeend', html);
        initTomSelects();
        fieldCounter++;
        
        // Reset add form safely
        if (labelInput) labelInput.value = '';
        if (reqInput) reqInput.checked = false;
        if (multInput) multInput.checked = false;
        if (maxInput) maxInput.value = "5";
        if (typeInput) typeInput.value = 'text';
        if (charMaxInput) charMaxInput.value = '128';
        if (fileSizeMaxInput) fileSizeMaxInput.value = '7';
        
        if (typeInput) window.toggleFieldType(typeInput);
        
        const optsList = addCard.querySelector('.options-list');
        if(optsList) optsList.innerHTML = '';
        
        const fileSelect = addCard.querySelector('.file-type-select');
        if(fileSelect && fileSelect.tomselect) fileSelect.tomselect.clear();

        if(window.updateLivePreview) window.updateLivePreview();
    };

    let previewTimeout;
    window.updateLivePreview = function() {
        if (!window.previewUrl) return;

        const previewContainer = document.getElementById('preview-content');
        if (!previewContainer || !formEl) return;
        
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
            };

            payload.fields = [];

            const fieldBoxes = document.querySelectorAll('.field-item-box');
            fieldBoxes.forEach((box, index) => {
                let labelInput = box.querySelector('[name="label"]') || box.querySelector(`[name$="[label]"]`);
                if (!labelInput) return;

                let typeInput = box.querySelector('[name="type"]') || box.querySelector(`[name$="[type]"]`);
                let reqInput = box.querySelector('[name="required"]') || box.querySelector(`[name$="[required]"]`);
                let multInput = box.querySelector('.file-multiple-checkbox') || box.querySelector(`[name$="[file_multiple]"]`);
                let maxInput = box.querySelector('.file-max-select') || box.querySelector(`[name$="[file_max]"]`);
                let charMaxInput = box.querySelector('[name="char_max"]') || box.querySelector(`[name$="[char_max]"]`);
                let fileSizeMaxInput = box.querySelector('[name="file_size_max"]') || box.querySelector(`[name$="[file_size_max]"]`);
                
                let opts = [];
                if (typeInput && typeInput.value === 'file') {
                    let fileSelect = box.querySelector('.file-type-select');
                    if (fileSelect) {
                        if (fileSelect.tomselect) {
                            const vals = fileSelect.tomselect.getValue();
                            opts = Array.isArray(vals) ? vals : (vals ? [vals] : []);
                        } else {
                            Array.from(fileSelect.selectedOptions).forEach(opt => opts.push(opt.value));
                        }
                    } else {
                        let hiddenOpts = box.querySelectorAll(`[name$="[options][]"]`);
                        hiddenOpts.forEach(opt => { if(opt.value) opts.push(opt.value); });
                    }
                } else {
                    let optInputs = box.querySelectorAll('[name="options[]"], [name$="[options][]"]');
                    optInputs.forEach(opt => { if(opt.value) opts.push(opt.value); });
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
            })
            .catch(err => console.error('Preview update failed:', err));
        }, 200);
    };

    if (formEl) {
        formEl.addEventListener('input', updateLivePreview);
        formEl.addEventListener('change', updateLivePreview);
    }
    if (addCard) {
        addCard.addEventListener('input', updateLivePreview);
        addCard.addEventListener('change', updateLivePreview);
    }

    if (fieldsList && typeof Sortable !== 'undefined') {
        new Sortable(fieldsList, {
            handle: '.drag-handle-icon',
            animation: 150,
            onEnd: function () {
                const reorderUrl = fieldsList.getAttribute('data-reorder-url');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                if (window.updateLivePreview) window.updateLivePreview();

                if (reorderUrl && csrfToken) {
                    const items = document.querySelectorAll('.field-item-box');
                    const order = Array.from(items).map(item => item.dataset.id);
                    
                    fetch(reorderUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ order: order })
                    }).then(res => res.json()).then(data => {
                        console.log(data.message);
                    }).catch(error => {
                        console.error('Failed to update field order:', error);
                    });
                }
            }
        });
    }
});