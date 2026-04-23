document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initTinyMceEditors === 'function') {
        window.initTinyMceEditors('.tinymce-note');
    }

    const forms = document.querySelectorAll('.ajax-note-form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const textarea = form.querySelector('textarea[name="notes"]');
            const url = form.getAttribute('data-url');
            const statusEl = form.querySelector('.save-status');
            const btn = form.querySelector('button');

            if (typeof tinymce !== 'undefined' && textarea && textarea.id) {
                const editor = tinymce.get(textarea.id);
                if (editor) {
                    editor.save();
                }
            }

            const notes = textarea ? textarea.value : '';

            btn.disabled = true;
            statusEl.textContent = 'Saving.';
            statusEl.className = 'save-status text-muted fs-13';

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ notes: notes })
            })
            .then(async res => {
                if (!res.ok) {
                    throw new Error('Failed to save');
                }

                return res.json();
            })
            .then(() => {
                statusEl.textContent = 'Saved!';
                statusEl.classList.add('text-success');

                setTimeout(() => {
                    statusEl.textContent = '';
                    statusEl.classList.remove('text-success');
                }, 2500);
            })
            .catch(() => {
                statusEl.textContent = 'Error saving.';
                statusEl.classList.add('text-danger');
            })
            .finally(() => {
                btn.disabled = false;
            });
        });
    });
});

window.toggleMyNotesOnly = function(checkbox) {
    const isChecked = checkbox.checked;
    const otherNotes = document.querySelectorAll('.other-note');

    otherNotes.forEach(el => {
        el.style.display = isChecked ? 'none' : 'block';
    });
};