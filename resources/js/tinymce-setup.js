document.addEventListener('DOMContentLoaded', () => {
    if (typeof tinymce === 'undefined') return;

    const root = document.documentElement;

    const isDarkMode = () => root.classList.contains('dark');

    const cssVar = (name, fallback = '') => {
        const value = getComputedStyle(root).getPropertyValue(name).trim();
        return value || fallback;
    };

    const buildConfig = (selector) => {
        const isDark = isDarkMode();

        const bgInput = cssVar('--bg-input', isDark ? '#1f2327' : '#ffffff');
        const textMain = cssVar('--text-main', isDark ? '#e6e6e6' : '#1f2937');
        const textMuted = cssVar('--text-muted', isDark ? '#bdbdbd' : '#6b7280');
        const textPrimary = cssVar('--text-primary', isDark ? '#a97dff' : '#6d3fa9');
        const borderDefault = cssVar('--border-default', isDark ? '#3a3f45' : '#d1d5db');
        const bgPage = cssVar('--bg-page', isDark ? '#1a1d21' : '#f3f4f6');

        return {
            selector,
            license_key: 'gpl',
            promotion: false,
            height: 350,
            menubar: false,
            skin_url: isDark
                ? '/tinymce/skins/ui/hireflow-dark'
                : '/tinymce/skins/ui/hireflow-light',

            content_css: isDark
                ? '/tinymce/content/hireflow-dark/content.min.css'
                : '/tinymce/content/hireflow-light/content.min.css',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | removeformat',
            setup: (editor) => {
                const syncEditorState = () => {
                    editor.save();

                    if (typeof window.updateCharCounter === 'function') {
                        window.updateCharCounter(editor.getElement());
                    }

                    editor.getElement().dispatchEvent(new Event('input', { bubbles: true }));
                };

                editor.on('init SetContent change keyup paste input undo redo', syncEditorState);
            }
        };
    };

    const initTinyMceEditors = (selector) => {
        if (!document.querySelector(selector)) return;
        tinymce.init(buildConfig(selector));
    };

    const rebuildTinyMceEditors = () => {
        tinymce.remove('.tinymce-editor');
        tinymce.remove('.tinymce-applicant');
        initTinyMceEditors('.tinymce-editor');
        initTinyMceEditors('.tinymce-applicant');
    };

    window.initTinyMceEditors = initTinyMceEditors;
    window.rebuildTinyMceEditors = rebuildTinyMceEditors;

    rebuildTinyMceEditors();

    document.addEventListener('themechange', rebuildTinyMceEditors);
});