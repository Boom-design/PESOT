{{--
    The activity calendar — one partial for the four staff dashboards and the
    admin dashboard.

    LRA, SRA, Job Vacancy and Job Fair used to keep four near-identical copies
    of this markup and script, which is how they drifted apart on what an
    activity even is. What each role sees is decided server-side by
    App\Support\StaffCalendar; this file only draws it.

    Colour coding: purple for a PESO office day, blue for a job fair, amber for
    an in-house interview. A hollow dot means the day is still only requested —
    the employer has asked for it and staff have not approved it yet. A solid
    dot means it is reserved: an in-house booking holds every day of its range,
    and the employer chooses which of them to interview on.

    Include it inside the content section. It brings its own script.

    Optional data:
      calendarRole  — which role's view to draw the legend for. Defaults to the
                      signed-in staff member's role; the admin dashboard passes
                      'admin'.
      calendarFeed  — the JSON endpoint. Defaults to the staff feed.
      calendarTitle — heading on the calendar card.
      calendarPick  — when true, clicking a day fires window.pesoOnPickDate(iso)

                      instead of only listing that day. The admin add-schedule
                      form uses it.
      calendarTypes — which activity types to put in the legend. Defaults to
                      what the role sees. The jobseeker dashboard passes
                      ['job_fair', 'inhouse'] because office days are internal.
      calendarRequested — show the hollow "Requested" swatch. Defaults to true.
                      False where the feed only carries confirmed activities.
      calendarNote  — the red line under the legend. Pass '' to drop it; it is
                      about booking, which only staff and employers do.
      calendarNoun  — what the day list calls the things it lists, plural then
                      singular. Defaults to ['Activities', 'activity']. The
                      jobseeker dashboard passes ['Events', 'event'].
      calendarBare  — drop the card shell and the green header, leaving the
                      calendar and the day list bare. Use it when the caller
                      already has a card of its own to sit them in; a card
                      inside a card reads as two separate panels.
--}}
@php
    // Kuhaon dinhi, dili gikan sa layout: ang content section mag-render una sa
    // layout, mao nga wala pa didto ang $staffRole nga gitakda sa layout.
    $calRole  = $calendarRole  ?? (optional(Auth::user()->staff)->staff_role ?? 'staff');
    $calFeed  = $calendarFeed  ?? route('staff.inhouse.calendarData');
    $calTitle = $calendarTitle ?? 'Office Calendar';
    $calPick  = $calendarPick  ?? false;
    $calTypes = $calendarTypes ?? null;
    $calReq   = $calendarRequested ?? true;
    $calNote  = $calendarNote  ?? 'Red = weekend or holiday (still bookable). A PESO schedule blocks booking.';
    $calBare  = $calendarBare  ?? false;
    [$calNoun, $calNounOne] = $calendarNoun ?? ['Activities', 'activity'];
@endphp
{{-- One panel, two halves.
     It used to be two separate cards side by side, each with its own green
     header, so the calendar and the list of what is on the chosen day read as
     two unrelated things. They are one thing: you click a day on the left and
     the right tells you what is on it. A single card with a divider says that;
     two cards said the opposite. --}}
<style>
    .peso-cal-panel .peso-cal-half + .peso-cal-half { border-top: 1px solid var(--n-100); padding-top: 14px; margin-top: 14px; }

    /* The dashboard calendar stretches across its half instead of sitting at
       the compact 260px the inline calendar uses elsewhere. At 260px it left a
       wide empty strip down one side of a half that is twice that wide, so the
       card looked oversized for what was in it. Filling the width keeps the
       card exactly the size it was and only spends the space that was already
       empty; the small inset stops the days touching the card edge and the
       divider. Day height follows the wider cells so the grid stays a grid
       rather than a stack of bars.

       Scoped to .peso-cal-panel: the admin add-schedule form and the employer
       in-house picker sit in narrow columns and keep the 260px size. */
    .peso-cal-panel #pesoActivityCalendar { padding: 0 6px; }
    .peso-cal-panel .flatpickr-calendar.inline { width: 100%; max-width: none; font-size: 13px; }
    .peso-cal-panel .flatpickr-calendar.inline .flatpickr-days,
    .peso-cal-panel .flatpickr-calendar.inline .dayContainer {
        min-width: 0; max-width: none; width: 100%;
    }
    .peso-cal-panel .flatpickr-calendar.inline .flatpickr-day { height: 40px; line-height: 40px; }
    .peso-cal-panel .flatpickr-calendar.inline .flatpickr-weekday { font-size: 11px; }
    .peso-cal-panel .flatpickr-calendar.inline .flatpickr-current-month { font-size: 14px; padding: 5px 0; }
    .peso-cal-panel .peso-day-dots { bottom: 4px; }
    @media (min-width: 768px) {
        .peso-cal-panel .peso-cal-half + .peso-cal-half {
            border-top: none; padding-top: 0; margin-top: 0;
            border-left: 1px solid var(--n-100); padding-left: 18px;
        }
        .peso-cal-panel .peso-cal-half:first-child { padding-right: 18px; }
    }
</style>
<div class="peso-cal-panel {{ $calBare ? '' : 'card border-0 shadow-sm rounded-3 mb-3' }}">
    @unless($calBare)
    <div class="card-header border-0 py-2 px-3"
        style="background:var(--g-600);border-radius:12px 12px 0 0;">
        <h6 class="mb-0 fw-bold text-white" style="font-size:13px;">
            <i class="ph ph-calendar-blank me-2"></i>{{ $calTitle }}
        </h6>
    </div>
    @endunless

    <div class="{{ $calBare ? '' : 'card-body p-3' }}">
        <div class="row g-0">

            <div class="col-12 col-md-6 peso-cal-half">
                @if($calBare)
                <div class="fw-semibold mb-2" style="font-size:12px;color:var(--g-700);">
                    <i class="ph ph-calendar-blank me-1" style="color:var(--g-600);"></i>{{ $calTitle }}
                </div>
                @endif

                <div id="pesoActivityCalendar"></div>

                {{-- Legend. Built from App\Support\StaffCalendar::TYPES so the
                     swatches can never disagree with the dots. --}}
                <div class="d-flex flex-wrap gap-2 mt-2 px-1" style="font-size:11px;">
                    @foreach(\App\Support\StaffCalendar::TYPES as $key => $meta)
                        @if($calTypes !== null)
                            @if(!in_array($key, $calTypes, true))
                                @continue
                            @endif
                        @elseif($key === 'inhouse' && !in_array($calRole, \App\Support\StaffCalendar::rolesWithInhouse(), true))
                            @continue
                        @endif
                        <span class="d-inline-flex align-items-center gap-1" style="color:var(--n-700);">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $meta['color'] }};display:inline-block;"></span>
                            {{ $meta['label'] }}
                        </span>
                    @endforeach
                    @if($calReq)
                    <span class="d-inline-flex align-items-center gap-1" style="color:var(--n-700);">
                        <span style="width:8px;height:8px;border-radius:50%;border:1.5px solid var(--n-500);display:inline-block;"></span>
                        Requested
                    </span>
                    @endif
                </div>
                @if($calNote !== '')
                <div class="mt-1 px-1" style="font-size:11px;color:var(--danger);">
                    {{ $calNote }}
                </div>
                @endif
            </div>

            <div class="col-12 col-md-6 peso-cal-half">
                <div class="fw-semibold mb-2" id="pesoActivityTitle"
                     style="font-size:12.5px;color:var(--g-700);">
                    <i class="ph-fill ph-calendar-dots me-1" style="color:var(--g-600);"></i>{{ $calNoun }}
                </div>
                <div id="pesoActivityList"></div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
@include('partials.calendar-rest-days')
<script>
const PESO_TYPE_META = @json(\App\Support\StaffCalendar::TYPES);

let pesoActivityData = {};
let pesoBlockedDays  = {};

fetch(`{{ $calFeed }}`)
    .then(res => res.json())
    .then(data => {
        pesoActivityData = data.dates    || {};
        pesoHolidays     = data.holidays || {};
        pesoBlockedDays  = data.blocked  || {};
        initPesoActivityCalendar();
    })
    .catch(() => initPesoActivityCalendar());

function pesoActivityDayCreate(dObj, dStr, fp, dayElem) {
    pesoResetDay(dayElem);

    const iso = pesoIsoDate(dayElem.dateObj);
    const items = pesoActivityData[iso];

    if (items && items.length) {
        dayElem.classList.add('fp-date-has-schedule');

        // One dot per activity TYPE, not per activity: three in-house
        // interviews on one day are still one thing the office is doing, and
        // three dots would not fit a 30px cell anyway.
        const seen = [];
        items.forEach(item => {
            if (seen.some(s => s.type === item.type && s.state === item.state)) return;
            seen.push({ type: item.type, state: item.state });
        });

        const dots = document.createElement('span');
        dots.className = 'peso-day-dots';
        seen.slice(0, 3).forEach(s => {
            const color = (PESO_TYPE_META[s.type] || {}).color || 'var(--n-500)';
            const dot = document.createElement('i');
            dot.style.background = s.state === 'pending' ? 'transparent' : color;
            dot.style.border = '1.5px solid ' + color;
            dots.appendChild(dot);
        });
        dayElem.appendChild(dots);

        dayElem.title = items
            .map(i => i.label + ': ' + i.title + (i.state === 'pending' ? ' (requested)' : ''))
            .join(' — ');
    }

    if (pesoBlockedDays[iso]) {
        dayElem.classList.add('fp-day-blocked');
        const note = 'Office occupied: ' + pesoBlockedDays[iso].title;
        dayElem.title = dayElem.title ? dayElem.title + ' — ' + note : note;
    }

    pesoMarkRestDay(dayElem, iso);
}

function initPesoActivityCalendar() {
    const today = new Date();
    const todayStr = pesoIsoDate(today);

    flatpickr('#pesoActivityCalendar', {
        inline: true,
        // Walay slide animation: samtang nag-animate, duha ka dayContainer ang
        // magkuyog sulod sa 260px nga compact nga calendar, mao nga magbalhin
        // ang mga column ug masayop ang petsa nga makita.
        animate: false,
        defaultDate: today,
        onDayCreate: pesoActivityDayCreate,
        onChange: (selectedDates, dateStr) => {
            renderPesoActivityDate(dateStr);
            @if($calPick)
            // Ang admin nga porma nagpaabot sa gipili nga adlaw.
            if (typeof window.pesoOnPickDate === 'function') window.pesoOnPickDate(dateStr);
            @endif
        }
    });

    renderPesoActivityDate(todayStr);
}

// The heading spells the date out. "2026-08-28" is a value, not a sentence,
// and the office reads this line aloud.
const PESO_MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];

function pesoLongDate(iso) {
    const parts = String(iso || '').split('-');
    if (parts.length !== 3) return iso;
    const month = PESO_MONTH_NAMES[parseInt(parts[1], 10) - 1];
    if (!month) return iso;
    return month + ' ' + parseInt(parts[2], 10) + ', ' + parts[0];
}

function pesoEscape(text) {
    const el = document.createElement('div');
    el.textContent = text == null ? '' : text;
    return el.innerHTML;
}

function renderPesoActivityDate(dateStr) {
    const container = document.getElementById('pesoActivityList');
    const title     = document.getElementById('pesoActivityTitle');
    const items     = pesoActivityData[dateStr] || [];
    const blocked   = pesoBlockedDays[dateStr];

    title.innerHTML = `<i class="ph-fill ph-calendar-dots me-1" style="color:var(--g-600);"></i>{{ $calNoun }} on ${pesoEscape(pesoLongDate(dateStr))}`;

    let html = '';

    if (blocked) {
        html += `
        <div class="d-flex align-items-start gap-2 mb-2 p-2 rounded-3"
             style="background:var(--warn-bg);border:1px solid var(--warn);">
            <i class="ph-fill ph-lock-simple" style="color:var(--warn);"></i>
            <div style="font-size:11.5px;color:var(--n-700);line-height:1.5;">
                <strong style="color:var(--warn);">Not available for booking.</strong><br>
                ${pesoEscape(blocked.label)} — ${pesoEscape(blocked.title)}
            </div>
        </div>`;
    }

    if (!items.length) {
        html += `<div class="text-center py-4" style="color:var(--n-400);font-size:13px;">
            <i class="ph ph-calendar-x" style="font-size:32px;color:var(--n-300);"></i>
            <div class="mt-2">No {{ $calNounOne }} on this date</div>
        </div>`;
        container.innerHTML = html;
        return;
    }

    items.forEach(item => {
        const meta  = PESO_TYPE_META[item.type] || {};
        const color = meta.color || 'var(--n-500)';
        const icon  = meta.icon  || 'ph-dot';

        html += `
        <div class="d-flex align-items-start gap-2 mb-2 p-2 rounded-3"
             style="background:var(--n-50);border-left:3px solid ${color};">
            <i class="ph ${pesoEscape(icon)}" style="color:${color};margin-top:1px;"></i>
            <div style="min-width:0;">
                <div style="font-size:12.5px;font-weight:600;color:var(--g-700);">
                    ${pesoEscape(item.title)}
                    ${item.state === 'pending'
                        ? `<span class="ms-1" style="color:var(--n-700);font-size:9.5px;font-weight:600;">Requested</span>`
                        : ''}
                </div>
                <div style="font-size:11px;color:var(--n-500);">
                    <span style="color:${color};font-weight:600;">${pesoEscape(item.label)}</span>
                    ${item.time   ? ` &middot; <i class="ph ph-clock"></i> ${pesoEscape(item.time)}`   : ''}
                    ${item.detail ? ` &middot; <i class="ph ph-map-pin"></i> ${pesoEscape(item.detail)}` : ''}
                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
}
</script>
@endpush
