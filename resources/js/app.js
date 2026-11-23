import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const salesChart = document.getElementById('sales-chart');
    if (salesChart) {
        new Chart(salesChart, {
            type: 'bar',
            data: {
                labels: JSON.parse(salesChart.dataset.labels),
                datasets: [{
                    label: 'Verified sales (PHP)',
                    data: JSON.parse(salesChart.dataset.values),
                    backgroundColor: '#f7c948',
                    borderColor: '#171717',
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    const element = document.querySelector('[data-catering-calendar]');
    if (!element) {
        return;
    }

    const dateInput = document.getElementById('slot_date');
    const message = document.getElementById('calendar-selection-message');
    const earliestDate = element.dataset.earliestDate;
    let availabilityByDate = {};

    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        height: 'auto',
        selectable: true,
        validRange: { start: earliestDate },
        events: {
            url: element.dataset.eventsUrl,
            method: 'GET',
        },
        eventSourceSuccess(events) {
            availabilityByDate = Object.fromEntries(
                events.map((event) => [event.start, event.extendedProps.status])
            );
            return events;
        },
        dateClick(info) {
            const status = availabilityByDate[info.dateStr];
            if (info.dateStr < earliestDate) {
                message.textContent = 'Bookings must be made at least 3 days in advance.';
                return;
            }
            if (status !== 'Available') {
                message.textContent = status === 'Fully Booked'
                    ? 'This date is fully booked.'
                    : 'This date is not available.';
                return;
            }

            dateInput.value = info.dateStr;
            message.textContent = `Selected date: ${info.dateStr}`;
        },
    });

    calendar.render();
});
