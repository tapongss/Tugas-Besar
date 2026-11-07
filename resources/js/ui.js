/*
 * Interaksi antarmuka Modul Ruang & Fasilitas Sidang.
 * Progressive enhancement tanpa dependensi eksternal.
 */
document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initDropdowns();
    initModals();
    initConfirmForms();
    initAutoSubmit();
    initCopyButtons();
    initFlashMessages();
    initFacilityRepeater();
    initPasswordToggles();
    initPrintButtons();
    initSegmentedInputs();
    initScheduleAvailability();
});

/* ---------------------------------------------------------------- Sidebar */
function initSidebar() {
    const sidebar = document.getElementById('app-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const openers = document.querySelectorAll('[data-sidebar-open]');
    const closers = document.querySelectorAll('[data-sidebar-close]');

    if (!sidebar) return;

    const setOpen = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        overlay?.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
    };

    openers.forEach((button) => button.addEventListener('click', () => setOpen(true)));
    closers.forEach((button) => button.addEventListener('click', () => setOpen(false)));
    overlay?.addEventListener('click', () => setOpen(false));

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            overlay?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    });
}

/* -------------------------------------------------------------- Dropdown */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');

    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = !menu.classList.contains('hidden');

            document.querySelectorAll('[data-dropdown-menu]').forEach((item) => item.classList.add('hidden'));

            if (!isOpen) {
                menu.classList.remove('hidden');
                menu.classList.add('animate-scale-in');
            }
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => menu.classList.add('hidden'));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => menu.classList.add('hidden'));
        }
    });
}

/* ----------------------------------------------------------------- Modals */
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    const focusTarget = modal.querySelector('[data-autofocus]');
    window.setTimeout(() => focusTarget?.focus(), 50);
}

function closeModal(modal) {
    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openModal(trigger.dataset.modalOpen);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            closeModal(trigger.closest('[data-modal]'));
        });
    });

    document.querySelectorAll('[data-modal-backdrop]').forEach((backdrop) => {
        backdrop.addEventListener('click', () => closeModal(backdrop.closest('[data-modal]')));
    });

    document.querySelectorAll('[data-modal]').forEach((modal) => {
        if (modal.dataset.static === 'true') return;

        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[data-modal]:not(.hidden)').forEach((modal) => closeModal(modal));
    });
}

window.openModal = openModal;
window.closeModal = closeModal;

/* ------------------------------------------------------- Konfirmasi hapus */
function initConfirmForms() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    const titleEl = modal.querySelector('[data-confirm-title]');
    const messageEl = modal.querySelector('[data-confirm-message]');
    const submitButton = modal.querySelector('[data-confirm-submit]');
    let pendingForm = null;

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') return;

            event.preventDefault();
            pendingForm = form;

            titleEl.textContent = form.dataset.confirmTitle || 'Konfirmasi tindakan';
            messageEl.textContent = form.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan?';
            submitButton.textContent = form.dataset.confirmButton || 'Ya, lanjutkan';
            submitButton.classList.toggle('btn-danger', form.dataset.confirmTone !== 'primary');
            submitButton.classList.toggle('btn-primary', form.dataset.confirmTone === 'primary');

            openModal('confirm-modal');
        });
    });

    submitButton?.addEventListener('click', () => {
        if (!pendingForm) return;

        pendingForm.dataset.confirmed = 'true';
        closeModal(modal);
        pendingForm.requestSubmit();
        delete pendingForm.dataset.confirmed;
        pendingForm = null;
    });

    modal.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => {
            pendingForm = null;
        });
    });
}

/* ----------------------------------------------------------- Auto-submit */
function initAutoSubmit() {
    document.querySelectorAll('[data-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => field.form?.requestSubmit());
    });
}

/* ------------------------------------------------------------- Copy text */
function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copyValue || button.dataset.copy;
            if (!value) return;

            try {
                await navigator.clipboard.writeText(value);
            } catch {
                const helper = document.createElement('textarea');
                helper.value = value;
                helper.style.position = 'fixed';
                helper.style.opacity = '0';
                document.body.appendChild(helper);
                helper.select();
                document.execCommand('copy');
                helper.remove();
            }

            const original = button.innerHTML;
            button.innerHTML = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Tersalin</span>';
            window.setTimeout(() => {
                button.innerHTML = original;
            }, 1800);
        });
    });
}

/* -------------------------------------------------------- Flash messages */
function initFlashMessages() {
    document.querySelectorAll('[data-flash]').forEach((flash) => {
        const dismiss = flash.querySelector('[data-dismiss]');

        dismiss?.addEventListener('click', () => {
            flash.classList.add('opacity-0', 'translate-y-2');
            window.setTimeout(() => flash.remove(), 200);
        });

        if (flash.dataset.autohide !== 'false') {
            window.setTimeout(() => {
                if (!document.body.contains(flash)) return;
                flash.classList.add('opacity-0', 'translate-y-2');
                window.setTimeout(() => flash.remove(), 250);
            }, 6500);
        }
    });
}

/* --------------------------------------------------- Repeater fasilitas */
function initFacilityRepeater() {
    const container = document.getElementById('facility-rows');
    const template = document.getElementById('facility-row-template');
    const addButton = document.getElementById('add-facility');
    const emptyState = document.getElementById('facility-empty');

    if (!container || !template || !addButton) return;

    let index = Number(container.dataset.nextIndex || container.querySelectorAll('[data-facility-row]').length);

    const refresh = () => {
        const selects = [...container.querySelectorAll('select[data-facility-select]')];
        const selected = selects.map((select) => select.value).filter(Boolean);
        const rows = container.querySelectorAll('[data-facility-row]');

        selects.forEach((select) => {
            [...select.options].forEach((option) => {
                if (!option.value) return;
                const usedElsewhere = selected.includes(option.value) && option.value !== select.value;
                option.disabled = usedElsewhere;
            });
        });

        emptyState?.classList.toggle('hidden', rows.length > 0);
        rows.forEach((row, rowIndex) => {
            const badge = row.querySelector('[data-facility-index]');
            if (badge) badge.textContent = String(rowIndex + 1).padStart(2, '0');
        });
    };

    const bindRow = (row) => {
        row.querySelector('[data-remove-facility]')?.addEventListener('click', () => {
            row.remove();
            refresh();
        });

        row.querySelector('[data-facility-select]')?.addEventListener('change', refresh);
    };

    addButton.addEventListener('click', () => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        index += 1;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;

        container.appendChild(row);
        bindRow(row);
        refresh();
        row.querySelector('select')?.focus();
    });

    container.querySelectorAll('[data-facility-row]').forEach(bindRow);
    refresh();
}

/* -------------------------------------------------------- Password reveal */
function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            button.classList.toggle('text-brand-600', isPassword);
        });
    });
}

/* ---------------------------------------------------------------- Print */
function initPrintButtons() {
    document.querySelectorAll('[data-print]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });
}

/* ------------------------------------------------------------- Segmented */
function initSegmentedInputs() {
    document.querySelectorAll('[data-segmented]').forEach((group) => {
        const inputs = group.querySelectorAll('input[type="radio"]');

        const refresh = () => {
            inputs.forEach((input) => {
                const label = input.closest('label');
                if (!label) return;
                const active = input.checked;
                label.classList.toggle('border-brand-600', active);
                label.classList.toggle('bg-brand-50', active);
                label.classList.toggle('text-brand-800', active);
                label.classList.toggle('ring-2', active);
                label.classList.toggle('ring-brand-600/20', active);
                label.classList.toggle('border-slate-200', !active);
                label.classList.toggle('bg-white', !active);
            });
        };

        inputs.forEach((input) => input.addEventListener('change', refresh));
        refresh();
    });
}

/* ------------------------------------------- Cek ketersediaan ruang sidang */
function initScheduleAvailability() {
    const form = document.getElementById('schedule-form');
    if (!form) return;

    const panel = document.getElementById('room-availability');
    const roomSelect = form.querySelector('#room_id');
    const dateInput = form.querySelector('#date');
    const startInput = form.querySelector('#start_time');
    const endInput = form.querySelector('#end_time');
    const endHourSelect = form.querySelector('#end_hour');
    const endMinuteSelect = form.querySelector('#end_minute');
    const endpoint = form.dataset.availabilityEndpoint;

    if (!panel || !roomSelect || !dateInput || !endInput || !endHourSelect || !endMinuteSelect || !endpoint) return;

    const message = panel.querySelector('[data-availability-message]');
    const list = panel.querySelector('[data-availability-list]');
    const ignoreId = String(form.dataset.sessionId || '');

    const pad = (value) => String(value).padStart(2, '0');

    const timeToMinutes = (value) => {
        if (!value || !value.includes(':')) return null;

        const [hour, minute] = value.split(':').map(Number);

        if (!Number.isInteger(hour) || !Number.isInteger(minute)) return null;

        return hour * 60 + minute;
    };

    const minutesToTime = (total) => `${pad(Math.floor(total / 60))}:${pad(total % 60)}`;

    const startLimit = () => {
        const start = timeToMinutes(startInput?.value || '');

        if (start === null) return null;

        // Jam selesai minimal satu menit setelah jam mulai.
        return Math.min(start + 1, 23 * 60 + 59);
    };

    const selectedEndMinutes = () => {
        const hour = Number(endHourSelect.value);
        const minute = Number(endMinuteSelect.value);

        if (!Number.isInteger(hour) || !Number.isInteger(minute)) return null;

        return hour * 60 + minute;
    };

    const fillMinuteOptions = (hour, preferredMinute = 0) => {
        const limit = startLimit();
        const minMinute = limit !== null && hour === Math.floor(limit / 60) ? limit % 60 : 0;
        const target = Math.min(Math.max(preferredMinute, minMinute), 59);

        endMinuteSelect.replaceChildren();
        for (let minute = minMinute; minute <= 59; minute += 1) {
            endMinuteSelect.add(new Option(pad(minute), String(minute), false, minute === target));
        }
        endMinuteSelect.value = String(target);
    };

    const updateEndValue = () => {
        const total = selectedEndMinutes();

        if (total === null) return;

        endInput.value = minutesToTime(total);
    };

    const rebuildEndOptions = () => {
        const limit = startLimit();

        if (limit === null) return;

        const limitHour = Math.floor(limit / 60);
        const limitMinute = limit % 60;
        let current = selectedEndMinutes() ?? timeToMinutes(endInput.value);

        if (current === null || current < limit) {
            current = Math.min(limit + 60, 23 * 60 + 59);
        }

        const currentHour = Math.floor(current / 60);
        const currentMinute = current % 60;

        endHourSelect.replaceChildren();
        for (let hour = limitHour; hour <= 23; hour += 1) {
            endHourSelect.add(new Option(pad(hour), String(hour), false, hour === currentHour));
        }
        endHourSelect.value = String(currentHour);

        fillMinuteOptions(currentHour, currentMinute);
        updateEndValue();
    };

    const idleText = 'Pilih ruang dan tanggal untuk melihat jadwal lain pada waktu yang sama.';
    const tones = {
        idle: ['border-slate-200', 'bg-slate-50/60'],
        loading: ['border-sky-200', 'bg-sky-50/50'],
        available: ['border-emerald-200', 'bg-emerald-50/50'],
        conflict: ['border-rose-200', 'bg-rose-50/50'],
        error: ['border-amber-200', 'bg-amber-50/50'],
    };
    const allTones = Object.values(tones).flat();

    let timer = null;
    let controller = null;

    const render = (state, text, items = []) => {
        panel.dataset.state = state;
        panel.classList.remove(...allTones);
        panel.classList.add(...(tones[state] ?? tones.idle));

        if (message) message.textContent = text;

        list?.replaceChildren();
        list?.classList.toggle('hidden', items.length === 0);

        items.forEach((item) => {
            const row = document.createElement('li');
            row.className = 'flex flex-wrap items-center gap-x-2 gap-y-0.5 rounded-lg bg-white/80 px-2.5 py-1.5 text-[11px] ring-1 ring-inset ring-slate-200/70';

            const time = document.createElement('span');
            time.className = 'shrink-0 font-semibold text-slate-700';
            time.textContent = `${item.start_time}–${item.end_time}`;

            const title = document.createElement('span');
            title.className = 'min-w-0 flex-1 truncate text-slate-600';
            title.textContent = item.title || 'Kegiatan sidang';

            const student = document.createElement('span');
            student.className = 'w-full truncate text-[10px] text-slate-400 sm:w-auto';
            student.textContent = item.student_name || '';

            row.append(time, title, student);
            list?.appendChild(row);
        });
    };

    const overlaps = (item, start, end) => {
        if (!start || !end) return true;

        return String(item.start_time) < end && String(item.end_time) > start;
    };

    const check = async () => {
        const roomId = roomSelect.value;
        const date = dateInput.value;
        const start = startInput?.value || '';
        const end = endInput?.value || '';

        if (!roomId || !date) {
            render('idle', idleText);
            return;
        }

        if (start && end && start >= end) {
            render('error', 'Waktu selesai harus lebih besar daripada waktu mulai.');
            return;
        }

        controller?.abort();
        controller = new AbortController();

        render('loading', 'Memeriksa jadwal lain pada ruang dan tanggal tersebut…');

        try {
            const params = new URLSearchParams({
                room_id: roomId,
                date_from: date,
                date_to: date,
                per_page: '100',
            });

            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const payload = await response.json();
            const items = Array.isArray(payload.data) ? payload.data : [];
            const conflicts = items.filter((item) => (
                String(item.id) !== ignoreId
                && item.status !== 'batal'
                && overlaps(item, start, end)
            ));

            if (conflicts.length > 0) {
                render(
                    'conflict',
                    `${conflicts.length} jadwal lain menggunakan ruang ini pada rentang waktu tersebut. Pilih waktu atau ruang berbeda.`,
                    conflicts,
                );
            } else {
                render('available', 'Ruang tersedia pada waktu tersebut. Tidak ada jadwal yang bentrok.');
            }
        } catch (error) {
            if (error.name === 'AbortError') return;

            render('error', 'Ketersediaan ruang tidak dapat dimuat. Periksa koneksi lalu coba lagi.');
        }
    };

    const schedule = () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(check, 320);
    };

    const handleStartChange = () => {
        rebuildEndOptions();
        schedule();
    };

    const handleEndHourChange = () => {
        const hour = Number(endHourSelect.value);
        const minute = Number(endMinuteSelect.value);

        fillMinuteOptions(hour, Number.isInteger(minute) ? minute : 0);
        updateEndValue();
        schedule();
    };

    const handleEndMinuteChange = () => {
        updateEndValue();
        schedule();
    };

    [roomSelect, dateInput].forEach((field) => {
        field?.addEventListener('change', schedule);
        field?.addEventListener('input', schedule);
    });

    startInput?.addEventListener('change', handleStartChange);
    startInput?.addEventListener('input', handleStartChange);
    endHourSelect.addEventListener('change', handleEndHourChange);
    endMinuteSelect.addEventListener('change', handleEndMinuteChange);

    rebuildEndOptions();
    check();
}
