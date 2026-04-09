document.addEventListener('DOMContentLoaded', function() {
    const fieldsList = document.getElementById('fields-list');
    
    window.switchTab = function(tab) {
        document.getElementById('tab-builder').style.display = tab === 'builder' ? 'block' : 'none';
        document.getElementById('tab-preview').style.display = tab === 'preview' ? 'block' : 'none';
        
        const btnBuilder = document.getElementById('btn-tab-builder');
        const btnPreview = document.getElementById('btn-tab-preview');
        
        if (tab === 'builder') {
            btnBuilder.className = 'btn btn-tab-active';
            btnPreview.className = 'btn btn-tab-inactive';
        } else {
            btnBuilder.className = 'btn btn-tab-inactive';
            btnPreview.className = 'btn btn-tab-active';
        }
    };

    window.toggleEdit = function(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    };

    window.toggleOptions = function(selectElement, containerId, listId) {
        const container = document.getElementById(containerId);
        const list = document.getElementById(listId);
        if (['select', 'checkbox', 'radio'].includes(selectElement.value)) {
            container.style.display = 'block';
            if (list.children.length === 0) {
                addOption(listId);
            }
        } else {
            container.style.display = 'none';
        }
    };

    window.addOption = function(listId) {
        const list = document.getElementById(listId);
        const div = document.createElement('div');
        div.className = 'option-item';
        div.innerHTML = `
            <span class="text-muted">&bull;</span>
            <input type="text" name="options[]" class="m-0 flex-grow-1" required placeholder="New Option">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">X</button>
        `;
        list.appendChild(div);
    };

    // Live preview standard section toggling
    const nameCheck = document.querySelector('input[name="request_name"]');
    const emailCheck = document.querySelector('input[name="request_email"]');
    const phoneCheck = document.querySelector('input[name="request_phone"]');
    const resumeCheck = document.querySelector('input[name="request_resume"]');
    const titleInput = document.querySelector('input[name="name"]');

    function updateLivePreview() {
        if(titleInput) {
            const titleEl = document.getElementById('preview-template-name');
            if (titleEl) titleEl.textContent = titleInput.value || 'Untitled Template';
        }

        const reqName = nameCheck ? nameCheck.checked : true;
        const reqEmail = emailCheck ? emailCheck.checked : true;
        const reqPhone = phoneCheck ? phoneCheck.checked : true;
        const reqResume = resumeCheck ? resumeCheck.checked : true;

        const groupName = document.getElementById('preview-group-name');
        const groupEmail = document.getElementById('preview-group-email');
        const groupPhone = document.getElementById('preview-group-phone');
        const headerInfo = document.getElementById('preview-user-info-header');
        
        if(groupName) groupName.style.display = reqName ? '' : 'none';
        if(groupEmail) groupEmail.style.display = reqEmail ? '' : 'none';
        if(groupPhone) groupPhone.style.display = reqPhone ? '' : 'none';
        
        if(headerInfo) {
            headerInfo.style.display = (reqName || reqEmail || reqPhone) ? '' : 'none';
        }

        const groupResume = document.getElementById('preview-group-resume');
        if(groupResume) {
            groupResume.style.display = reqResume ? '' : 'none';
        }
        
        const hr1 = document.getElementById('preview-hr-1');
        const hr2 = document.getElementById('preview-hr-2');
        
        const hasInfo = reqName || reqEmail || reqPhone;
        const hasFields = document.getElementById('preview-questions-header') !== null;
        
        if(hr1) hr1.style.display = (hasInfo && hasFields) ? '' : 'none';
        if(hr2) hr2.style.display = ((hasInfo || hasFields) && reqResume) ? '' : 'none';
    }

    if (nameCheck) nameCheck.addEventListener('change', updateLivePreview);
    if (emailCheck) emailCheck.addEventListener('change', updateLivePreview);
    if (phoneCheck) phoneCheck.addEventListener('change', updateLivePreview);
    if (resumeCheck) resumeCheck.addEventListener('change', updateLivePreview);
    if (titleInput) titleInput.addEventListener('input', updateLivePreview);

    if (fieldsList && typeof Sortable !== 'undefined') {
        const reorderUrl = fieldsList.getAttribute('data-reorder-url');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        new Sortable(fieldsList, {
            handle: '.drag-handle-icon',
            animation: 150,
            onEnd: function () {
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
        });
    }
});