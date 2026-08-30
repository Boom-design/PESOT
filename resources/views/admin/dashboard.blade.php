@extends('admin.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph ph-gauge me-2" style="color:var(--g-600);"></i>Admin Dashboard
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">Public Employment Service Office — Overview</p>
</div>

{{-- QUICK ACTIONS (unified — count + link) --}}
<h6 class="fw-semibold mb-3" style="color:var(--g-700);">Quick Actions</h6>
<div class="row g-3 mb-4 align-items-stretch">
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.users.manage') }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:var(--g-600);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="ph-fill ph-users-three"></i>
                </div>
                <div class="fw-bold" style="color:var(--g-700);font-size:14px;">Total Users</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $totalUsers }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.registrations') }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:var(--g-600);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="ph-fill ph-user-list"></i>
                </div>
                <div class="fw-bold" style="color:var(--g-700);font-size:14px;">Jobseekers</div>
                <div class="fs-4 fw-bold mt-1" style="color:var(--g-600);">{{ $jobseekerCount }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.users.manage') }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:var(--g-600);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="ph-fill ph-buildings"></i>
                </div>
                <div class="fw-bold" style="color:var(--g-700);font-size:14px;">Companies</div>
                <div class="fs-4 fw-bold mt-1" style="color:var(--g-700);">{{ $companyCount }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.job.activities', ['tab' => 'jobfair']) }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100 d-flex flex-column justify-content-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:var(--g-600);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="ph-fill ph-calendar-dots"></i>
                </div>
                <div class="fw-bold" style="color:var(--g-700);font-size:14px;">Job Fair Events</div>
                <div class="fs-4 fw-bold mt-1" style="color:var(--g-600);">{{ $monthlyJobFairCount }}</div>
                <div class="text-muted small mt-1">for month of {{ now()->format('F') }}</div>
            </div>
        </a>
    </div>
</div>

{{-- ── OFFICE CALENDAR ──
     Dinhi mag-marka ang admin sa adlaw nga puliki ang opisina. Ang upat ka
     staff calendar mobasa niini dayon, ug dili na ma-book ang maong adlaw. --}}
<div class="d-flex justify-content-between align-items-center mb-3 mt-4 flex-wrap gap-2">
    <div>
        <h6 class="fw-semibold mb-1" style="color:var(--g-700);">
            <i class="ph ph-calendar-blank me-2" style="color:var(--g-600);"></i>Office Calendar
        </h6>
        <div style="font-size:12px;color:var(--n-500);">
            Mark the days the office is occupied. Staff see them at once, and no in-house
            interview or job fair can be booked on those dates.
        </div>
    </div>
    <button type="button" class="btn btn-sm fw-semibold"
        style="background:var(--g-600);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;"
        data-bs-toggle="modal" data-bs-target="#officeEventModal">
        <i class="ph ph-plus me-1"></i>Add Schedule
    </button>
</div>

@include('partials.activity-calendar', [
    'calendarRole'  => 'admin',
    'calendarFeed'  => route('admin.calendar.data'),
    'calendarTitle' => 'Office Calendar',
    'calendarPick'  => true,
])

{{-- Upcoming office schedules --}}
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header border-0 py-2 px-3" style="background:var(--g-600);border-radius:12px 12px 0 0;">
        <h6 class="mb-0 fw-bold text-white" style="font-size:13px;">
            <i class="ph ph-list-checks me-2"></i>Upcoming Office Schedules
        </h6>
    </div>
    <div class="card-body p-3">
        @if($officeEvents->isEmpty())
            <div class="text-center py-4" style="color:var(--n-400);font-size:13px;">
                <i class="ph ph-calendar-x" style="font-size:32px;color:var(--n-300);"></i>
                <div class="mt-2">No office schedule set. Every date is open for booking.</div>
            </div>
        @else
            @foreach($officeEvents as $event)
            <div class="d-flex align-items-start justify-content-between gap-2 p-2 mb-2 rounded-3"
                 style="background:var(--n-50);border-left:3px solid #7c3aed;">
                <div style="min-width:0;">
                    <div style="font-size:13px;font-weight:600;color:var(--g-700);">{{ $event->title }}</div>
                    <div style="font-size:11.5px;color:var(--n-500);">
                        <span style="color:#7c3aed;font-weight:600;">{{ $event->type_label }}</span>
                        &middot; <i class="ph ph-calendar-blank"></i> {{ $event->date_range_label }}
                        @if($event->time_label)
                            &middot; <i class="ph ph-clock"></i> {{ $event->time_label }}
                        @endif
                        @if($event->location)
                            &middot; <i class="ph ph-map-pin"></i> {{ $event->location }}
                        @endif
                    </div>
                    @if($event->notes)
                        <div style="font-size:11px;color:var(--n-500);margin-top:2px;">{{ $event->notes }}</div>
                    @endif
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <button type="button" class="btn btn-sm"
                        style="border:1px solid var(--n-200);background:#fff;color:var(--g-700);border-radius:6px;font-size:11px;padding:4px 10px;"
                        data-bs-toggle="modal" data-bs-target="#officeEventEdit{{ $event->office_calendar_events_id }}">
                        <i class="ph ph-pencil-simple"></i>
                    </button>
                    <form action="{{ route('admin.calendar.delete', $event->office_calendar_events_id) }}" method="POST"
                          onsubmit="return confirm('Remove &quot;{{ $event->title }}&quot;? The date opens for booking again.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm"
                            style="border:1px solid var(--danger-br);background:#fff;color:var(--danger);border-radius:6px;font-size:11px;padding:4px 10px;">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Edit modal, one per entry --}}
            <div class="modal fade" id="officeEventEdit{{ $event->office_calendar_events_id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius:16px;border:none;">
                        <div class="modal-header" style="background:var(--g-600);">
                            <h6 class="modal-title text-white fw-bold">
                                <i class="ph ph-pencil-simple me-2"></i>Edit Office Schedule
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.calendar.update', $event->office_calendar_events_id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                @include('admin.partials.office-event-fields', ['event' => $event])
                            </div>
                            <div class="modal-footer" style="border-top:1px solid var(--n-100);">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm fw-semibold"
                                    style="background:var(--g-600);color:#fff;border:none;border-radius:8px;padding:8px 20px;">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- Add modal --}}
<div class="modal fade" id="officeEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:var(--g-600);">
                <h6 class="modal-title text-white fw-bold">
                    <i class="ph ph-calendar-plus me-2"></i>Add Office Schedule
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.calendar.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="d-flex align-items-start gap-2 p-2 mb-3 rounded-3"
                         style="background:var(--n-50);border:1px solid var(--n-200);">
                        <i class="ph ph-info" style="color:var(--g-600);margin-top:1px;"></i>
                        <div style="font-size:11.5px;color:var(--n-700);line-height:1.5;">
                            All four staff — LRA, SRA, Job Vacancy and Job Fair — will see this on their
                            calendar, and the date will be closed to new in-house and job fair bookings.
                            Anything already booked stays; it is not cancelled.
                        </div>
                    </div>
                    @include('admin.partials.office-event-fields', ['event' => null])
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--n-100);">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:var(--g-600);color:#fff;border:none;border-radius:8px;padding:8px 20px;">
                        <i class="ph ph-check me-1"></i>Add Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ang pag-klik sa adlaw sa kalendaryo mopuno sa petsa sa "Add" nga porma, aron
// dili na siya i-type pag-usab. Ang modal dili awtomatikong moabli — basin
// tan-aw ra ang admin sa adlaw.
window.pesoOnPickDate = function (iso) {
    const start = document.querySelector('#officeEventModal input[name="start_date"]');
    if (start) start.value = iso;
};
</script>
@endpush

{{-- TODAY'S ACTIVITIES --}}
<h6 class="fw-semibold mb-3 mt-4" style="color:var(--g-700);">
    <i class="ph ph-calendar-check me-2" style="color:var(--g-600);"></i>Today's Activities
</h6>
<div class="row g-3">

    {{-- Job Fair Events --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="fw-semibold mb-2" style="font-size:13px;color:var(--g-700);">
                <i class="ph-fill ph-users-three me-1" style="color:var(--g-600);"></i> Job Fair Events
            </div>
            @if($todayJobFairs->isEmpty())
                <div class="text-muted" style="font-size:13px;padding:8px 0;">
                    No job fair events today.
                </div>
            @else
                @foreach($todayJobFairs as $event)
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2"
                     style="background:var(--n-50);border:1px solid var(--n-200);">
                    <i class="ph ph-calendar-dots" style="color:var(--g-600);font-size:20px;"></i>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--g-700);">{{ $event->title }}</div>
                        <div style="font-size:12px;color:var(--n-500);">
                            <i class="ph ph-map-pin me-1"></i>{{ $event->venue }}
                            &nbsp;•&nbsp;
                            <span style="color:var(--g-600);font-weight:600;text-transform:capitalize;">{{ $event->status }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- In-house Interviews --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="fw-semibold mb-2" style="font-size:13px;color:var(--g-700);">
                <i class="ph ph-buildings me-1" style="color:var(--g-600);"></i> In-house Interviews
            </div>
            @if($todayInhouse->isEmpty())
                <div class="text-muted" style="font-size:13px;padding:8px 0;">
                    No in-house interviews today.
                </div>
            @else
                @foreach($todayInhouse as $schedule)
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2"
                     style="background:var(--n-50);border:1px solid var(--n-200);">
                    <i class="ph ph-clock" style="color:var(--g-600);font-size:20px;"></i>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--g-700);">
                            {{ $schedule->employer->company_name ?? $schedule->employer->name ?? 'None' }}
                        </div>
                        <div style="font-size:12px;color:var(--n-500);">
                            <i class="ph ph-clock me-1"></i>{{ \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') }}
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

</div>

@endsection