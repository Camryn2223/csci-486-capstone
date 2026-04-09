document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('scheduled_at_picker') && typeof flatpickr !== 'undefined') {
        flatpickr("#scheduled_at_picker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: false
        });
    }

    if (document.getElementById('interviewers-select') && typeof TomSelect !== 'undefined') {
        new TomSelect("#interviewers-select", {
            plugins: ['remove_button'],
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    }
});