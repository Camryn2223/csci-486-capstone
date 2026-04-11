document.addEventListener('DOMContentLoaded', function() {
    
    const appForm = document.getElementById('application-form');
    if (appForm) {
        const formId = window.location.pathname; 
        const savedData = sessionStorage.getItem('appForm_' + formId);
        
        // Restore from sessionStorage on load (to prevent user input loss on refresh)
        if (savedData) {
            try {
                const parsed = JSON.parse(savedData);
                Object.keys(parsed).forEach(key => {
                    const val = parsed[key];
                    if (Array.isArray(val)) {
                        val.forEach(v => {
                            const matchingInput = appForm.querySelector(`[name="${key}"][value="${v}"]`);
                            if (matchingInput && !matchingInput.checked) {
                                matchingInput.checked = true;
                            }
                        });
                    } else {
                        const inputs = appForm.querySelectorAll(`[name="${key}"]`);
                        if (inputs.length > 0) {
                            const input = inputs[0];
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                const matchingInput = appForm.querySelector(`[name="${key}"][value="${val}"]`);
                                if (matchingInput && !matchingInput.checked) matchingInput.checked = true;
                            } else if (input.type !== 'file' && input.type !== 'hidden') {
                                // Only restore if there's no pre-populated value (e.g., from Laravel old() validation flashing)
                                if (!input.value) {
                                    input.value = val;
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Failed to parse saved form data');
            }
        }

        // Save to sessionStorage on input
        // Note: File inputs cannot be safely restored programmatically due to browser security restrictions.
        // Therefore, we do not save files to sessionStorage to avoid crashing quota limits with base64 blobs.
        appForm.addEventListener('input', function(e) {
            const formData = new FormData(appForm);
            const dataObj = {};
            
            for (let [key, value] of formData.entries()) {
                if (value instanceof File || key === '_token') continue;
                
                if (dataObj[key] !== undefined) {
                    if (!Array.isArray(dataObj[key])) dataObj[key] = [dataObj[key]];
                    dataObj[key].push(value);
                } else {
                    dataObj[key] = value;
                }
            }
            sessionStorage.setItem('appForm_' + formId, JSON.stringify(dataObj));
        });

        appForm.addEventListener('submit', function(e) {
            const wrappers = document.querySelectorAll('.multi-file-wrapper');
            let exceedMax = false;
            
            wrappers.forEach(wrapper => {
                const max = parseInt(wrapper.getAttribute('data-max'), 10) || 1;
                
                // Count inputs that actually have a file selected
                const activeInputs = Array.from(wrapper.querySelectorAll('input[type="file"]')).filter(i => i.files.length > 0);
                if (activeInputs.length > max) {
                    exceedMax = true;
                }
            });

            if (exceedMax) {
                e.preventDefault();
                alert('One of your file upload fields exceeds the maximum number of allowed files. Please check and remove extra files.');
                return;
            }

            const fileInputs = document.querySelectorAll('.tracked-upload, .preview-input-file, .preview-input-file-mb30');
            let errorMsg = null;

            fileInputs.forEach(input => {
                if (input.files && input.files.length > 0) {
                    // Default to 7MB if data attribute missing
                    let maxMB = 7;
                    const wrapper = input.closest('.multi-file-wrapper');
                    if (wrapper && wrapper.hasAttribute('data-max-mb')) {
                        maxMB = parseFloat(wrapper.getAttribute('data-max-mb'));
                    } else if (input.hasAttribute('data-max-mb')) {
                        maxMB = parseFloat(input.getAttribute('data-max-mb'));
                    }
                    
                    for (let i = 0; i < input.files.length; i++) {
                        const fileSizeMB = input.files[i].size / 1024 / 1024;
                        if (fileSizeMB > maxMB) {
                            errorMsg = `One or more selected files exceed the maximum allowed size of ${maxMB}MB. Please upload smaller files.`;
                        }
                    }
                }
            });

            if (errorMsg) {
                e.preventDefault();
                alert(errorMsg);
                return;
            }

            // On successful submission process start, clean up the storage so a fresh form is presented next time
            sessionStorage.removeItem('appForm_' + formId);
        });
    }

    // Dynamic multiple file upload logic
    document.querySelectorAll('.btn-add-file').forEach(button => {
        button.addEventListener('click', function() {
            const wrapper = this.closest('.multi-file-wrapper');
            const max = parseInt(wrapper.getAttribute('data-max'), 10) || 1;
            const container = wrapper.querySelector('.file-inputs-container');
            const currentInputs = container.querySelectorAll('input[type="file"]');
            
            if (currentInputs.length >= max) {
                alert('You cannot add more than ' + max + ' files for this field.');
                return;
            }
            
            const firstInput = currentInputs[0];
            const clone = firstInput.cloneNode();
            clone.value = '';
            
            // For clones after the first one, removing the 'required' attribute prevents blocking submissions
            // if a user clicks "+ Add another" but leaves it empty.
            clone.required = false;
            
            // Add a small margin to space out the stacked inputs
            clone.classList.add('mt-10');
            
            container.appendChild(clone);
            
            if (currentInputs.length + 1 >= max) {
                this.style.display = 'none';
            }
        });
    });

    // Character counter live updates
    document.querySelectorAll('.char-counted').forEach(input => {
        const max = parseInt(input.getAttribute('data-char-max'), 10);
        const counter = input.nextElementSibling;
        
        const updateCounter = () => {
            const current = input.value.length;
            if(counter) {
                counter.textContent = `${current} / ${max}`;
                if (current >= max) {
                    counter.classList.add('text-danger');
                } else {
                    counter.classList.remove('text-danger');
                }
            }
        };
        
        input.addEventListener('input', updateCounter);
        updateCounter(); // initialize state on load
    });

});