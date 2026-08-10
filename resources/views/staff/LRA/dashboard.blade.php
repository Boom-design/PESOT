@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-person-lines-fill me-2" style="color:#4dd9c0;"></i>LRA Dashboard
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Local Jobseeker Registrations</p>
</div>

{{-- STAT CARD --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
            <div class="fs-1 fw-bold" style="color:#4dd9c0;">{{ $total }}</div>
            <div class="text-muted small">Total Local Registrations</div>
        </div>
    </div>
</div>

{{-- TODAY'S ACTIVITIES --}}
<div class="row g-3 mb-4">
    {{-- In-house --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header border-0 py-3 px-4"
                style="background:linear-gradient(90deg,#90d870,#4dd9c0);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-calendar-check-fill me-2"></i>In-house Activities Today
                </h6>
            </div>
            <div class="card-body p-3">
                @forelse($todayInhouse as $schedule)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3"
                    style="border-bottom:1px solid #f0f9f6;">
                    <div style="width:40px;height:40px;
                                background:linear-gradient(135deg,#90d870,#4dd9c0);
                                border-radius:10px;display:flex;align-items:center;
                                justify-content:center;flex-shrink:0;">
                        <i class="bi bi-building text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="color:#2d7a5f;font-size:13px;">
                            {{ $schedule->employer->company_name ?? '—' }}
                        </div>
                        <div style="font-size:11px;color:#888;">
                            <i class="bi bi-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') }}
                        </div>
                        <div style="font-size:11px;color:#888;">
                            <i class="bi bi-people me-1"></i>
                            {{ $schedule->num_applicants }} applicants
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4" style="color:#aaa;font-size:13px;">
                    <i class="bi bi-calendar-x" style="font-size:32px;color:#c0e8dc;"></i>
                    <div class="mt-2">No in-house schedules today</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header border-0 py-3 px-4"
                style="background:linear-gradient(90deg,#90d870,#4dd9c0);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-calendar3 me-2"></i>In-house Schedule Calendar
                </h6>
            </div>
            <div class="card-body p-3">
                <div id="lraInhouseCalendar"></div>
                <div id="lraCalendarSelectedList" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

{{-- QUICK LINKS --}}
<div class="row g-3">
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.registrations') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">Local Jobseekers</div>
                <div class="text-muted small mt-1">View all local registrations</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.inhouse') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">In-house Schedules</div>
                <div class="text-muted small mt-1">Manage interview schedules</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.profile') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">My Profile</div>
                <div class="text-muted small mt-1">Manage your account</div>
            </div>
        </a>
    </div>
</div>

@push('scripts')
<script>
let lraCalendarData = {};

fetch(`{{ route('staff.inhouse.calendarData') }}`)
    .then(res => res.json())
    .then(data => {
        lraCalendarData = data.dates || {};
        initLraCalendar();
    })
    .catch(() => initLraCalendar());

function lraDayCreate(dObj, dStr, fp, dayElem) {
    const y = dayElem.dateObj.getFullYear();
    const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
    const iso = `${y}-${m}-${d}`;
    if (lraCalendarData[iso]) {
        dayElem.classList.add('fp-date-has-schedule');
        dayElem.title = lraCalendarData[iso].length + ' employer(s) scheduled';
    }
}

function initLraCalendar() {
    flatpickr('#lraInhouseCalendar', {
        inline: true,
        onDayCreate: lraDayCreate,
        onChange: function(selectedDates, dateStr) {
            renderLraSelectedDate(dateStr);
        }
    });
}

function renderLraSelectedDate(dateStr) {
    const container = document.getElementById('lraCalendarSelectedList');
    const items = lraCalendarData[dateStr];

    if (!items || items.length === 0) {
        container.innerHTML = `<div style="font-size:12px;color:#888;padding:10px;">No employers scheduled on ${dateStr}.</div>`;
        return;
    }

    let html = `<div style="font-size:12px;font-weight:700;color:#2d7a5f;margin-bottom:6px;">Employers on ${dateStr}:</div>`;
    items.forEach(item => {
        html += `
        <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-3" style="background:#f0f9f6;">
            <i class="bi bi-building" style="color:#4dd9c0;"></i>
            <div>
                <div style="font-size:12.5px;font-weight:600;color:#2d7a5f;">${item.company}</div>
                ${item.time ? `<div style="font-size:11px;color:#888;">${item.time}</div>` : ''}
            </div>
        </div>`;
    });
    container.innerHTML = html;
}
</script>
@endpush

@endsection