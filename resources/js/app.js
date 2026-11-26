import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import Chart from 'chart.js/auto';

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ------------------------------------------------------------------
 * Confirm dialogs (replaces native confirm()) + double-submit guard.
 *   <form data-confirm="Delete this?">            -> confirm on form submit
 *   <button data-confirm="Cancel order?">         -> confirm for that button only
 *   <form data-once>                              -> disable buttons after submit
 * ------------------------------------------------------------------ */
function initConfirmAndOnce() {
    const modalEl = document.getElementById('confirmModal');
    const modal = modalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    let pending = null;

    const proceed = (form, submitter) => {
        form.dataset.confirmed = '1';
        try {
            form.requestSubmit(submitter ?? undefined);
        } finally {
            delete form.dataset.confirmed;
        }
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const trigger = event.submitter?.dataset.confirm
            ? event.submitter
            : form.dataset.confirm ? form : null;

        if (trigger && !form.dataset.confirmed) {
            event.preventDefault();
            const message = trigger.dataset.confirm;

            if (!modal) {
                if (window.confirm(message)) proceed(form, event.submitter);
                return;
            }

            pending = { form, submitter: event.submitter ?? null };
            modalEl.querySelector('#confirmModalBody').textContent = message;
            modalEl.querySelector('#confirmModalOk').textContent = trigger.dataset.confirmLabel || 'Yes, continue';
            modal.show();
            return;
        }

        if (form.hasAttribute('data-once')) {
            setTimeout(() => {
                form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
                    button.dataset.originalHtml = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Please wait…';
                });
            }, 0);
        }
    });

    // Restore buttons if the browser brings the page back from its back/forward cache.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;
        document.querySelectorAll('button[data-original-html]').forEach((button) => {
            button.innerHTML = button.dataset.originalHtml;
            button.disabled = false;
        });
    });

    modalEl?.querySelector('#confirmModalOk')?.addEventListener('click', () => {
        if (!pending) return;
        const { form, submitter } = pending;
        pending = null;
        modal.hide();
        proceed(form, submitter);
    });
}

/* ------------------------------------------------------------------
 * Quantity steppers. Forms with data-qty-autosubmit update by themselves.
 * ------------------------------------------------------------------ */
function initQuantitySteppers() {
    const timers = new WeakMap();

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-step]');
        if (!button) return;

        const input = button.closest('[data-qty-stepper]')?.querySelector('input');
        if (!input) return;

        const min = Number(input.min || 1);
        const max = Number(input.max || 999);
        const next = Math.min(max, Math.max(min, (parseInt(input.value, 10) || min) + Number(button.dataset.step)));
        if (String(next) === input.value) return;

        input.value = next;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    document.addEventListener('change', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.name !== 'quantity') return;

        const form = input.form;
        if (!form || !form.hasAttribute('data-qty-autosubmit') || !input.checkValidity()) return;

        clearTimeout(timers.get(form));
        timers.set(form, setTimeout(() => form.requestSubmit(), 650));
    });
}

/* ------------------------------------------------------------------
 * Misc small behaviours
 * ------------------------------------------------------------------ */
function initMisc() {
    // Auto-submit filter forms when a select / checkbox changes.
    document.addEventListener('change', (event) => {
        const field = event.target;
        const form = field.closest?.('form[data-autosubmit]');
        if (form && (field.matches('select') || field.matches('input[type="checkbox"]'))) {
            form.requestSubmit();
        }
    });

    // Fallback image if a product photo 404s.
    document.addEventListener('error', (event) => {
        const img = event.target;
        if (img instanceof HTMLImageElement && img.dataset.fallback && !img.dataset.failed) {
            img.dataset.failed = '1';
            img.src = img.dataset.fallback;
        }
    }, true);

    // Success flashes fade away on their own.
    document.querySelectorAll('.alert[data-autodismiss]').forEach((alert) => {
        setTimeout(() => {
            if (window.bootstrap) window.bootstrap.Alert.getOrCreateInstance(alert).close();
            else alert.remove();
        }, 6000);
    });

    // Live photo preview in the admin product form.
    const photoInput = document.querySelector('[data-image-input]');
    const photoPreview = document.querySelector('[data-image-preview]');
    photoInput?.addEventListener('change', () => {
        const file = photoInput.files?.[0];
        if (file && photoPreview) {
            photoPreview.src = URL.createObjectURL(file);
            photoPreview.classList.remove('d-none');
        }
    });
}

/* ------------------------------------------------------------------
 * Checkout: hide delivery address for pickup, show payment instructions.
 * ------------------------------------------------------------------ */
function initCheckout() {
    const method = document.querySelector('[data-fulfillment]');
    const deliveryFields = document.querySelector('[data-delivery-fields]');
    const address = document.getElementById('delivery_address');

    if (method && deliveryFields) {
        const update = () => {
            const isDelivery = method.value === 'Delivery';
            deliveryFields.hidden = !isDelivery;
            if (address) address.required = isDelivery;
        };
        method.addEventListener('change', update);
        update();
    }

    const radios = document.querySelectorAll('input[name="payment_method"]');
    const panels = document.querySelectorAll('[data-pay-panel]');
    if (radios.length && panels.length) {
        const update = () => {
            const selected = document.querySelector('input[name="payment_method"]:checked')?.value;
            panels.forEach((panel) => { panel.hidden = panel.dataset.payPanel !== selected; });
        };
        radios.forEach((radio) => radio.addEventListener('change', update));
        update();
    }
}

/* ------------------------------------------------------------------
 * Catering: live guest range for the chosen package.
 * ------------------------------------------------------------------ */
function initPackageRange() {
    const select = document.querySelector('[data-package-select]');
    const guests = document.getElementById('guest_count');
    const hint = document.getElementById('package-range-hint');
    if (!select || !guests || !hint) return;

    const update = () => {
        const option = select.selectedOptions[0];
        const min = option?.dataset.min;
        const max = option?.dataset.max;
        if (min && max) {
            guests.min = min;
            guests.max = max;
            hint.textContent = `This package serves ${min}–${max} guests.`;
        } else {
            guests.min = 1;
            guests.removeAttribute('max');
            hint.textContent = 'Pick a package to see its guest capacity.';
        }
    };
    select.addEventListener('change', update);
    update();
}

/* ------------------------------------------------------------------
 * Order payment form: show proof/reference fields only for online methods.
 * ------------------------------------------------------------------ */
function initPaymentForms() {
    document.querySelectorAll('[data-payment-form]').forEach((form) => {
        const select = form.querySelector('[data-pay-method]');
        if (!select) return;
        const card = form.closest('.card-body') ?? document;

        const update = () => {
            const method = select.value;
            form.querySelectorAll('[data-pay-online]').forEach((el) => { el.hidden = method === 'Cash'; });
            card.querySelectorAll('[data-pay-info]').forEach((el) => { el.hidden = el.dataset.payInfo !== method; });
        };
        select.addEventListener('change', update);
        update();
    });
}

function initSalesChart() {
    const salesChart = document.getElementById('sales-chart');
    if (!salesChart) return;

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

function initAdminCalendar() {
    const element = document.querySelector('[data-admin-calendar]');
    if (!element) return;

    const title = document.getElementById('day-title');
    const capacity = document.getElementById('day-capacity');
    const list = document.getElementById('day-orders');
    const dateInput = document.getElementById('slot_date');
    const maxInput = document.getElementById('max_bookings');
    const blockInput = document.getElementById('is_blocked');
    const remarksInput = document.getElementById('remarks');
    let days = {};
    let defaultMax = 2;
    let selected = '';

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const paintDays = () => {
        element.querySelectorAll('.fc-daygrid-day[data-date]').forEach((cell) => {
            const info = days[cell.dataset.date];
            cell.classList.toggle('adm-blocked', !!info?.blocked);
            cell.classList.toggle('adm-full', !!info && !info.blocked && info.booked >= info.max);
            cell.classList.toggle('adm-selected', cell.dataset.date === selected);
        });
    };

    const showDay = (dateStr) => {
        selected = dateStr;
        const info = days[dateStr];
        const pretty = new Date(`${dateStr}T00:00:00`).toLocaleDateString('en-PH', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        title.textContent = pretty;

        const max = info?.max ?? defaultMax;
        const booked = info?.booked ?? 0;
        capacity.textContent = info?.blocked
            ? `Blocked${info.remarks ? ' - ' + info.remarks : ''}`
            : `${booked} of ${max} event slots used`;

        const items = calendar.getEvents()
            .filter((event) => event.startStr === dateStr && event.extendedProps.kind)
            .sort((a, b) => (a.extendedProps.time || '').localeCompare(b.extendedProps.time || ''));

        list.innerHTML = items.length
            ? items.map((event) => {
                const p = event.extendedProps;
                return `<a class="day-item ${esc(event.classNames[0] ?? '')}" href="${esc(p.url)}">
                    <div class="d-flex justify-content-between gap-2"><strong>${esc(p.ref)}</strong><span class="small">${esc(p.time ?? '')}</span></div>
                    <div class="small">${esc(p.customer)} &middot; ${esc(p.type)}</div>
                    <div class="small text-muted">${esc(p.status)}${p.payment ? ' &middot; ' + esc(p.payment) : ''}${p.total ? ' &middot; ₱' + esc(p.total) : ''}</div>
                </a>`;
            }).join('')
            : '<p class="small text-muted mb-0">No orders on this date.</p>';

        dateInput.value = dateStr;
        maxInput.value = max;
        blockInput.checked = !!info?.blocked;
        remarksInput.value = info?.remarks ?? '';
        paintDays();
    };

    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        height: 'auto',
        fixedWeekCount: false,
        dayMaxEvents: 3,
        headerToolbar: { left: 'title', right: 'today prev,next' },
        buttonText: { today: 'Today' },
        eventDisplay: 'block',
        dayCellDidMount: (info) => {
            const data = days[info.el.dataset.date];
            info.el.classList.toggle('adm-blocked', !!data?.blocked);
        },
        events(info, success, failure) {
            const url = new URL(element.dataset.eventsUrl, window.location.origin);
            url.searchParams.set('start', info.startStr.slice(0, 10));
            url.searchParams.set('end', info.endStr.slice(0, 10));
            fetch(url, { headers: { Accept: 'application/json' } })
                .then((response) => (response.ok ? response.json() : Promise.reject(response)))
                .then((data) => {
                    days = { ...days, ...data.days };
                    defaultMax = data.default_max;
                    success(data.events);
                    setTimeout(() => { paintDays(); if (selected) showDay(selected); }, 0);
                })
                .catch(failure);
        },
        dateClick: (info) => showDay(info.dateStr),
        eventClick(info) {
            info.jsEvent.preventDefault();
            showDay(info.event.startStr);
        },
    });

    calendar.render();
}

function initCateringCalendar() {
    const element = document.querySelector('[data-catering-calendar]');
    if (!element) return;

    const dateInput = document.getElementById('slot_date');
    const message = document.getElementById('calendar-selection-message');
    const banner = document.getElementById('calendar-selection');
    const form = document.getElementById('booking-form');
    const earliestDate = element.dataset.earliestDate;
    let availabilityByDate = {};
    let selectedDate = dateInput?.value || '';

    const pretty = (dateStr) => new Date(`${dateStr}T00:00:00`).toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });

    const say = (text, state = '') => {
        if (message) message.textContent = text;
        if (banner) {
            banner.classList.toggle('has-date', state === 'date');
            banner.classList.toggle('has-warning', state === 'warning');
        }
    };

    const classify = (dateStr) => {
        if (dateStr < earliestDate) return 'soon';
        const status = availabilityByDate[dateStr];
        if (status === 'Available') return 'available';
        if (status === 'Fully Booked') return 'full';
        if (status === 'Not Available') return 'blocked';
        return null;
    };

    const labels = { available: 'Open', full: 'Full', blocked: 'Closed', soon: '' };

    // Paint a single day cell from the availability map.
    const paintCell = (cell) => {
        const dateStr = cell.dataset.date;
        if (!dateStr) return;

        cell.classList.remove('cal-available', 'cal-full', 'cal-blocked', 'cal-soon');
        const kind = classify(dateStr);
        if (kind) cell.classList.add(`cal-${kind}`);
        cell.classList.toggle('cal-selected', dateStr === selectedDate);
        cell.setAttribute('aria-disabled', kind === 'available' ? 'false' : 'true');

        let tag = cell.querySelector('.day-tag');
        if (!tag) {
            tag = document.createElement('div');
            tag.className = 'day-tag';
            cell.querySelector('.fc-daygrid-day-frame')?.appendChild(tag);
        }
        tag.textContent = dateStr === selectedDate ? 'Selected' : (kind ? labels[kind] : '');
    };

    const paintAll = () => element.querySelectorAll('.fc-daygrid-day[data-date]').forEach(paintCell);

    const choose = (dateStr, { scroll = true } = {}) => {
        selectedDate = dateStr;
        if (dateInput) dateInput.value = dateStr;
        paintAll();
        say(`Selected: ${pretty(dateStr)}. Now fill in your event details below.`, 'date');
        if (scroll) form?.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'start' });
    };

    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        initialDate: selectedDate || earliestDate,
        height: 'auto',
        fixedWeekCount: false,
        showNonCurrentDates: false,
        validRange: { start: earliestDate },
        headerToolbar: { left: 'title', right: 'today prev,next' },
        buttonText: { today: 'Today' },
        dayHeaderFormat: { weekday: window.matchMedia('(max-width: 575.98px)').matches ? 'narrow' : 'short' },
        dayCellDidMount: (info) => paintCell(info.el),
        datesSet(info) {
            element.classList.add('is-loading');
            const url = new URL(element.dataset.eventsUrl, window.location.origin);
            url.searchParams.set('start', info.startStr.slice(0, 10));
            url.searchParams.set('end', info.endStr.slice(0, 10));

            fetch(url, { headers: { Accept: 'application/json' } })
                .then((response) => (response.ok ? response.json() : Promise.reject(response)))
                .then((events) => {
                    availabilityByDate = { ...availabilityByDate, ...Object.fromEntries(events.map((event) => [event.start, event.extendedProps.status])) };
                    paintAll();
                })
                .catch(() => say('We could not load availability. Please refresh the page.', 'warning'))
                .finally(() => element.classList.remove('is-loading'));
        },
        dateClick(info) {
            const kind = classify(info.dateStr);
            if (kind === 'soon') return say('Bookings must be made at least 3 days in advance.', 'warning');
            if (kind === 'full') return say('That date is fully booked. Please pick an open date.', 'warning');
            if (kind !== 'available') return say('That date is not available. Please pick an open date.', 'warning');
            choose(info.dateStr);
        },
    });

    calendar.render();
    if (selectedDate) say(`Selected: ${pretty(selectedDate)}.`, 'date');

    // Keep the calendar in sync when the date is typed into the form field.
    dateInput?.addEventListener('change', () => {
        if (!dateInput.value) return;
        selectedDate = dateInput.value;
        calendar.gotoDate(selectedDate);
        paintAll();
        say(`Selected: ${pretty(selectedDate)}.`, 'date');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initConfirmAndOnce();
    initQuantitySteppers();
    initMisc();
    initCheckout();
    initPackageRange();
    initPaymentForms();
    initSalesChart();
    initCateringCalendar();
    initAdminCalendar();
});
