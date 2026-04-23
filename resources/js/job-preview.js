document.addEventListener('DOMContentLoaded', function() {
    const configEl = document.getElementById('preview-config');
    if (!configEl) return;

    const orgId = configEl.getAttribute('data-org-id');

    window.updateTemplateLink = function() {
        const templateId = document.getElementById('input-template')?.value;
        const btn = document.getElementById('edit-template-btn');
        if (!btn) return;
        if (templateId) {
            btn.style.display = 'inline-block';
            btn.href = `/organizations/${orgId}/application-templates/${templateId}/edit`;
        } else {
            btn.style.display = 'none';
        }
    };

    window.updatePreview = function() {
        const titleInput = document.getElementById('input-title');
        const descInput = document.getElementById('input-desc');
        const reqsInput = document.getElementById('input-reqs');
        const templateInput = document.getElementById('input-template');
        
        if (!titleInput || !descInput || !reqsInput || !templateInput) return;

        const title = titleInput.value || 'Untitled Position';
        
        // For rich text, use value sync'd by TinyMCE or directly from textarea
        const desc = descInput.value || 'No description provided.';
        const reqs = reqsInput.value || 'No requirements provided.';
        const templateId = templateInput.value;

        const titleEl = document.getElementById('preview-title');
        if (titleEl) titleEl.textContent = title;
        
        const descEl = document.getElementById('preview-desc');
        if (descEl) descEl.innerHTML = desc;
        
        const reqsEl = document.getElementById('preview-reqs');
        if (reqsEl) reqsEl.innerHTML = reqs;

        document.querySelectorAll('.template-preview-block').forEach(el => el.style.display = 'none');
        
        if (templateId) {
            const selected = document.getElementById('template-preview-' + templateId);
            if (selected) {
                selected.style.display = 'block';
            } else {
                const noneSelected = document.getElementById('template-preview-none');
                if (noneSelected) noneSelected.style.display = 'block';
            }
        } else {
            const noneSelected = document.getElementById('template-preview-none');
            if (noneSelected) noneSelected.style.display = 'block';
        }
    };

    window.updateTemplateLink();
    window.updatePreview();
});