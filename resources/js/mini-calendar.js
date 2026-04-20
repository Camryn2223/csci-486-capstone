document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('mini-calendar-view');
    var dataEl = document.getElementById('mini-calendar-events-data');
    if (!calendarEl || !dataEl) return;

    var eventsData = JSON.parse(dataEl.textContent);
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var showOnlyMine = false;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 500,
        headerToolbar: { 
            left: 'title', 
            center: '', 
            right: 'prev,next' 
        },
        events: eventsData,
        eventDidMount: function(info) {
            let actionButtons = `<div class="mt-15 flex-wrap-8">`;
            actionButtons += `<a href="${info.event.url}" class="btn btn-sm">View Details</a>`;
            
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

            tippy(info.el, {
                content: `
                    <div style="text-align:left; padding:5px;">
                        <strong class="text-primary fs-16">Applicant: ${info.event.title.replace('Interview: ', '')}</strong><br>
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

    var toggleBtn = document.getElementById('toggle-expand');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const wrapper = document.getElementById('calendar-wrapper');
            const isExpanded = wrapper.classList.toggle('calendar-expanded');
            
            this.textContent = isExpanded ? 'Collapse Calendar' : '⛶ Expand Calendar';
            document.getElementById('filter-container').style.display = isExpanded ? 'inline-block' : 'none';

            if (isExpanded) {
                let overlay = document.createElement('div');
                overlay.id = 'calendar-overlay';
                overlay.className = 'calendar-overlay';
                overlay.onclick = () => document.getElementById('toggle-expand').click();
                document.body.appendChild(overlay);
                
                calendar.setOption('height', 'calc(90vh - 100px)');
                calendar.setOption('headerToolbar', { 
                    left: 'prev,next today', 
                    center: 'title', 
                    right: 'dayGridMonth,timeGridWeek,listWeek' 
                });
            } else {
                document.getElementById('calendar-overlay')?.remove();
                calendar.setOption('height', 500);
                calendar.setOption('headerToolbar', { 
                    left: 'title', 
                    center: '', 
                    right: 'prev,next' 
                });
            }
            
            setTimeout(() => calendar.updateSize(), 200);
        });
    }

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