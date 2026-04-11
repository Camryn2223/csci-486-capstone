document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var dataEl = document.getElementById('calendar-events-data');
    if (!calendarEl || !dataEl) return;

    var eventsData = JSON.parse(dataEl.textContent);
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var showOnlyMine = false;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 700,
        headerToolbar: { 
            left: 'prev,next today', 
            center: 'title', 
            right: 'dayGridMonth,timeGridWeek,listWeek' 
        },
        events: eventsData,
        eventDidMount: function(info) {
            let actionButtons = `<div class="mt-15 flex-wrap-8">`;
            actionButtons += `<a href="${info.event.url}" class="btn btn-sm">View Details</a>`;
            
            if (info.event.extendedProps.canUpdate && info.event.extendedProps.status === 'scheduled') {
                actionButtons += `
                    <a href="/interviews/${info.event.id}/edit" class="btn btn-sm btn-purple-dark">Reschedule</a>
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

            tippy(info.el, {
                content: `
                    <div style="text-align:left; padding:5px;">
                        <strong class="text-primary fs-16">Applicant: ${info.event.title.replace('Interview: ', '')}</strong><br>
                        <div>
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

    var filterInput = document.getElementById('filter-mine');
    if (filterInput) {
        filterInput.addEventListener('change', function(e) {
            showOnlyMine = e.target.checked;
            var filtered = eventsData.filter(function(ev) {
                return showOnlyMine ? ev.extendedProps.isMine : true;
            });
            calendar.removeAllEvents();
            calendar.addEventSource(filtered);
        });
    }
});