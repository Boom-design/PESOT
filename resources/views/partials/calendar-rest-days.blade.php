{{--
    Weekend and holiday marking for the four staff activity calendars.

    One copy so LRA, SRA, Job Vacancy and Job Fair cannot drift apart on what
    counts as a rest day. Include it before the dashboard's own calendar script
    and call pesoMarkRestDay(dayElem, iso) inside that calendar's onDayCreate.

    Red is a warning, never a lock: employers do ask for in-house interviews
    and job fairs on weekends and holidays, and staff may book them.
--}}
<script>
    // Filled from the calendar feed — { 'Y-m-d': 'Holiday name' }.
    let pesoHolidays = {};

    function pesoIsoDate(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // Clear anything a previous month left on this cell. flatpickr may hand the
    // same element back after a month change, and without this the red and the
    // tooltip stay behind on whatever date now sits in that square.
    function pesoResetDay(dayElem) {
        dayElem.classList.remove('fp-day-rest', 'fp-day-holiday', 'fp-date-has-schedule');
        dayElem.removeAttribute('title');
        dayElem.querySelectorAll('.peso-day-dots').forEach(el => el.remove());
    }

    function pesoMarkRestDay(dayElem, iso) {
        // Idempotent: safe to run twice on the same cell.
        dayElem.classList.remove('fp-day-rest', 'fp-day-holiday');

        const weekday = dayElem.dateObj.getDay();
        const isWeekend = weekday === 0 || weekday === 6;
        const holiday = pesoHolidays[iso];

        if (!isWeekend && !holiday) return;

        dayElem.classList.add('fp-day-rest');
        if (holiday) dayElem.classList.add('fp-day-holiday');

        // Keep whatever the schedule already put in the tooltip.
        const note = holiday || 'Weekend';
        dayElem.title = dayElem.title ? `${dayElem.title} — ${note}` : note;
    }
</script>
