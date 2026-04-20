document.addEventListener('DOMContentLoaded', function() {
    const selectEl = document.getElementById('interviewers-select');
    const jumpDateEl = document.getElementById('jump_date_picker');
    const calendarEl = document.getElementById('availability-calendar');
    const hiddenInput = document.getElementById('scheduled_at_hidden');
    const timeText = document.getElementById('selected_time_text');
    const schedulesDataEl = document.getElementById('schedules-data');

    if (!calendarEl || !selectEl || !schedulesDataEl) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const allSchedules = JSON.parse(schedulesDataEl.textContent);
    let currentSchedules = [];
    let selectedEvent = null;

    // Init TomSelect
    let tomSelect;
    if (typeof TomSelect !== 'undefined') {
        tomSelect = new TomSelect(selectEl, {
            plugins: ['remove_button'],
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            onChange: function(values) {
                updateCalendarEvents(values);
            }
        });
    }

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        height: 500,
        allDaySlot: false,
        slotMinTime: '07:00:00',
        slotMaxTime: '20:00:00',
        slotDuration: '00:10:00',
        slotLabelInterval: '00:30',
        expandRows: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        events: [],
        dateClick: function(info) {
            handleTimeSelection(info.date);
        },
        eventClick: function(info) {
            if (info.event.id === 'selected_slot') return;
            alert('This time conflicts with a selected interviewer\'s schedule.');
        },
        eventDidMount: function(info) {
            let actionButtons = '';

            // Only show action buttons for already scheduled events
            if (info.event.id !== 'selected_slot') {
                actionButtons += `<div class="mt-15 flex-wrap-8">
                    <a href="${info.event.url}" class="btn btn-sm">View Details</a>`;
                
                if (info.event.extendedProps.canUpdate && info.event.extendedProps.status === 'scheduled') {
                    actionButtons += `
                        <a href="/interviews/${info.event.id}/edit" class="btn btn-sm">Reschedule</a>
                        <form method="POST" action="/interviews/${info.event.id}/complete" class="m-0">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="PATCH">
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark completed?')">Complete</button>
                        </form>
                        <form method="POST" action="/interviews/${info.event.id}/cancel" class="m-0">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="PATCH">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel interview?')">Cancel</button>
                        </form>
                    `;
                }
                actionButtons += `</div>`;
            }

            tippy(info.el, {
                content: `
                    <div style="text-align:left; padding:5px;">
                        <strong class="text-primary fs-16">${info.event.title}</strong><br>
                        <div>
                            <strong>Date & Time:</strong> ${info.event.start.toLocaleString(undefined, { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })}<br>
                            <strong>Position:</strong> ${info.event.extendedProps.position}<br>
                            <strong>Interviewer(s):</strong> ${info.event.extendedProps.interviewer}<br>
                            <strong>Status:</strong> ${info.event.extendedProps.status.toUpperCase()}
                        </div>
                        ${actionButtons}
                    </div>
                `,
                allowHTML: true,
                interactive: true,
                placement: 'auto',
                appendTo: document.body,
                maxWidth: 550,
                theme: 'translucent',
                delay: [50, 50],
            });
        }
    });

    calendar.render();

    // Init Flatpickr for Jump Date
    let fp;
    if (typeof flatpickr !== 'undefined') {
        fp = flatpickr(jumpDateEl, {
            enableTime: false,
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    calendar.gotoDate(selectedDates[0]);
                }
            }
        });
    }

    function hasConflict(proposedStart) {
        let proposedEnd = new Date(proposedStart.getTime() + 60 * 60 * 1000);
        return currentSchedules.some(event => {
            let evStart = new Date(event.start);
            let evEnd = new Date(event.end);
            return (proposedStart < evEnd && proposedEnd > evStart);
        });
    }

    function handleTimeSelection(dateObj) {
        if (hasConflict(dateObj)) {
            alert('This time conflicts with a selected interviewer\'s schedule. Please pick an open slot.');
            return;
        }
        setSelection(dateObj);
    }

    function setSelection(dateObj) {
        if (selectedEvent) {
            let existing = calendar.getEventById('selected_slot');
            if (existing) existing.remove();
        }

        let appName = calendarEl.getAttribute('data-applicant');
        let posTitle = calendarEl.getAttribute('data-position');
        let ints = 'None selected';
        if (tomSelect && tomSelect.items.length > 0) {
            ints = tomSelect.items.map(val => tomSelect.options[val].text.replace(/\s*\(.*?\)$/, '').trim()).join(', ');
        }

        selectedEvent = {
            id: 'selected_slot',
            title: `${appName} (with: ${ints})`,
            start: dateObj,
            end: new Date(dateObj.getTime() + 60 * 60 * 1000),
            color: 'var(--brand-base)',
            extendedProps: {
                position: posTitle,
                interviewer: ints,
                status: 'Unsaved (New)'
            }
        };
        calendar.addEvent(selectedEvent);

        let pad = n => n < 10 ? '0'+n : n;
        let dateStr = dateObj.getFullYear() + '-' + 
                      pad(dateObj.getMonth() + 1) + '-' + 
                      pad(dateObj.getDate()) + ' ' + 
                      pad(dateObj.getHours()) + ':' + 
                      pad(dateObj.getMinutes());
                      
        hiddenInput.value = dateStr;
        
        let options = { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' };
        timeText.textContent = dateObj.toLocaleString(undefined, options);
        
        if (fp) fp.setDate(dateObj, false);
    }

    function updateCalendarEvents(selectedInterviewerIds) {
        if (!selectedInterviewerIds) selectedInterviewerIds = [];
        
        currentSchedules = allSchedules.filter(event => {
            return event.interviewer_ids.some(id => selectedInterviewerIds.includes(id.toString()));
        });

        calendar.removeAllEvents();
        calendar.addEventSource(currentSchedules);
        
        if (selectedEvent) {
            let existing = calendar.getEventById('selected_slot');
            if (existing) existing.remove();
            
            let appName = calendarEl.getAttribute('data-applicant');
            let posTitle = calendarEl.getAttribute('data-position');
            let ints = 'None selected';
            if (tomSelect && tomSelect.items.length > 0) {
                ints = tomSelect.items.map(val => tomSelect.options[val].text.replace(/\s*\(.*?\)$/, '').trim()).join(', ');
            }
            
            selectedEvent.title = `${appName} (with: ${ints})`;
            selectedEvent.extendedProps.interviewer = ints;

            calendar.addEvent(selectedEvent);

            if (hasConflict(selectedEvent.start)) {
                alert('Warning: The currently selected time now conflicts with the newly added interviewer.');
                existing = calendar.getEventById('selected_slot');
                if (existing) existing.remove();
                selectedEvent = null;
                hiddenInput.value = '';
                timeText.textContent = 'None';
            }
        }
    }

    if (tomSelect) {
        updateCalendarEvents(tomSelect.getValue());
    }

    if (hiddenInput.value) {
        let parts = hiddenInput.value.split(/[- :]/);
        if (parts.length >= 5) {
            let initDate = new Date(parts[0], parts[1]-1, parts[2], parts[3], parts[4]);
            setSelection(initDate);
            calendar.gotoDate(initDate);
        }
    }
});