@extends($layout ?? 'staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-chart-bar me-2" style="color:var(--g-600);"></i>
        @if($staffRole === 'lra') Local
        @elseif($staffRole === 'sra') Overseas
        @else Job Fair
        @endif Reports
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        @if($staffRole === 'job_fair')
            Overall job fair event success rate
        @else
            Job applicants registered, placed, and referred
        @endif
    </p>
</div>

@php
// Every tab carries a second, broken form of its own name. The row holds all
// seven side by side, so a long name has to wrap — and where it wraps is a
// decision, not something to leave to whatever width the browser happens to
// give the box. "Hired on the Spot" breaks after "on", never mid-phrase.
$tabs = [
    'attendance'         => ['icon' => 'ph-fill ph-clipboard-text', 'label' => 'Attendance', 'html' => 'Attendance'],
    'companies'          => ['icon' => 'ph-fill ph-buildings',
                             'label' => $staffRole === 'sra' ? 'Overseas Companies' : 'Local Companies',
                             'html'  => $staffRole === 'sra' ? 'Overseas<br>Companies' : 'Local<br>Companies'],
    'further_interview'  => ['icon' => 'ph-fill ph-user-list', 'label' => 'Further Interview', 'html' => 'Further<br>Interview'],
    'hots'               => ['icon' => 'ph-fill ph-lightning', 'label' => 'Hired on the Spot', 'html' => 'Hired on<br>the Spot'],
    'vacancy_list'       => ['icon' => 'ph-fill ph-list-numbers', 'label' => 'Local Job Vacancies', 'html' => 'Local Job<br>Vacancies'],
    'summary'            => ['icon' => 'ph-fill ph-clipboard-text', 'label' => 'Post Job Fair Summary', 'html' => 'Post Job Fair<br>Summary'],
    'industry'           => ['icon' => 'ph-fill ph-tree-structure', 'label' => 'Companies w/ Vacancies', 'html' => 'Companies w/<br>Vacancies'],
];
if ($staffRole !== 'sra') {
    $tabs['placement'] = ['icon' => 'ph-fill ph-briefcase', 'label' => 'Company Placement', 'html' => 'Company<br>Placement'];
}
@endphp

@if(($layout ?? '') === 'admin.layouts.app')
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
            <div class="fw-semibold mb-2" style="color:var(--g-700);font-size:14px;">
                <i class="ph-fill ph-briefcase me-2"></i>Job Solicitation Statistics
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3" style="background:var(--n-50);border:1px solid var(--n-200);">
                        <div class="fw-bold" style="font-size:24px;color:var(--g-700);">{{ $solicitationStats['lra'] ?? 0 }}</div>
                        <div class="small" style="color:var(--g-600);">LRA Job Solicitation</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3" style="background:var(--n-50);border:1px solid var(--n-200);">
                        <div class="fw-bold" style="font-size:24px;color:var(--g-700);">{{ $solicitationStats['sra'] ?? 0 }}</div>
                        <div class="small" style="color:var(--g-600);">SRA Job Solicitation</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3" style="background:var(--n-50);border:1px solid var(--n-200);">
                        <div class="fw-bold" style="font-size:24px;color:var(--g-700);">{{ $solicitationStats['overall'] ?? 0 }}</div>
                        <div class="small" style="color:var(--g-600);">Overall Job Solicitation</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'lra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="{{ $staffRole === 'lra' ? 'background:var(--g-600);' : 'background:var(--n-50);' }}">
                <i class="ph-fill ph-user-list mb-2" style="font-size:26px;color:{{ $staffRole === 'lra' ? '#fff' : 'var(--g-600)' }};"></i>
                <div class="fw-semibold" style="font-size:13px;color:{{ $staffRole === 'lra' ? '#fff' : 'var(--g-700)' }};">LRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'sra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="{{ $staffRole === 'sra' ? 'background:var(--g-600);' : 'background:var(--n-50);' }}">
                <i class="ph ph-globe mb-2" style="font-size:26px;color:{{ $staffRole === 'sra' ? '#fff' : 'var(--g-600)' }};"></i>
                <div class="fw-semibold" style="font-size:13px;color:{{ $staffRole === 'sra' ? '#fff' : 'var(--g-700)' }};">SRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'job_fair']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="{{ $staffRole === 'job_fair' ? 'background:var(--g-600);' : 'background:var(--n-50);' }}">
                <i class="ph-fill ph-calendar-dots mb-2" style="font-size:26px;color:{{ $staffRole === 'job_fair' ? '#fff' : 'var(--g-600)' }};"></i>
                <div class="fw-semibold" style="font-size:13px;color:{{ $staffRole === 'job_fair' ? '#fff' : 'var(--g-700)' }};">Job Fair Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staffJobVacancy') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:var(--n-50);">
                <i class="ph-fill ph-briefcase mb-2" style="font-size:26px;color:var(--g-600);"></i>
                <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">Job Vacancy Reports</div>
            </div>
        </a>
    </div>
</div>
@endif

{{-- The view selector is drawn inside each view's own first row rather than on
     a line of its own above them. It is the first thing on that line, the fair
     picker is next to it, and the reports that do not read a fair sit at the
     far end — one line that says what you are looking at and what else there
     is to look at. --}}
@php
    $sraViewSelector = $staffRole === 'sra';
@endphp

{{-- JOB FAIR STAFF — 7 TABS (SRA: overseas-filtered, walay Placement tab) --}}
@if($staffRole === 'job_fair' || ($staffRole === 'sra' && ($reportView ?? 'staff') === 'jobfair'))

{{-- ── THE TWO ROWS ──

     Top row: which set of reports you are in, the fair being read, and at the
     far end the reports that are not about a fair at all. Bottom row: the tabs
     of the chosen fair.

     They were one row before, with the last few sitting after Company
     Placement, which read as tabs of the same kind — and the desk had no way to
     see that they ignore the dropdown entirely. Pushed to the right of the same
     line as the pickers, they are plainly the other thing: the reports you open
     without choosing a fair. --}}
@php $tabNeedsEvent = !in_array($tab, ['top_employers', 'imported', 'archived'], true); @endphp

<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    @if($sraViewSelector ?? false)
    <select id="reportViewSelector" class="form-select form-select-sm"
        style="max-width:250px;border-color:var(--n-200);font-size:13px;"
        onchange="changeReportView(this.value)">
        <option value="staff" {{ ($reportView ?? 'staff') === 'staff' ? 'selected' : '' }}>Overseas Reports</option>
        <option value="jobfair" {{ ($reportView ?? 'staff') === 'jobfair' ? 'selected' : '' }}>Job Fair Reports (Overseas)</option>
    </select>
    @endif
    @if($tabNeedsEvent)
    <select id="eventSelector" class="form-select form-select-sm"
        style="max-width:320px;border-color:var(--n-200);font-size:13px;"
        onchange="changeEvent(this.value)">
        <option value="">— Select Job Fair Event —</option>
        @foreach($allEvents as $ev)
        <option value="{{ $ev->job_fair_events_id }}" {{ $eventId == $ev->job_fair_events_id ? 'selected' : '' }}>
            {{ $ev->title }} ({{ $ev->event_date->format('M d, Y') }})
        </option>
        @endforeach
    </select>
    @else
    {{-- Ang tab nga giablihan wala nagbasa ug fair, mao nga walay dropdown
         dinhi — usa ka pilianan nga walay giusab mabasa isip guba.

         Ang gipulihan usa ka Back nga buton. Kining tulo ka report nagtago sa
         tab row sa fair, mao nga kung walay agianan pabalik, ang desk kinahanglan
         mo-Back sa browser o mopislit sa sidebar — ug ang sidebar mobalik sa
         ulohan sa Reports, dili sa tab nga iyang gibiyaan. --}}
    <a href="{{ route($reportRouteName ?? 'staff.reports', ['tab' => 'attendance']) }}"
       class="btn btn-sm fw-semibold"
       style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;
              border-radius:8px;font-size:12px;padding:5px 14px;white-space:nowrap;">
        <i class="ph ph-arrow-left me-1"></i> Back to job fair reports
    </a>
    <span style="font-size:12px;color:var(--n-500);">
        <i class="ph ph-info me-1" style="color:var(--g-600);"></i>
        This report is not read against one fair.
    </span>
    @endif

    <div class="d-flex gap-2 flex-wrap ms-auto">
        <a href="{{ route($reportRouteName ?? 'staff.reports', ['tab' => 'top_employers']) }}"
           class="btn btn-sm fw-semibold"
           style="{{ $tab === 'top_employers'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
            <i class="ph-fill ph-buildings me-1"></i>Top 10 Employers
        </a>
        {{-- Ang kaugalingon nga report sa staff. Job Fair staff ra — dili siya
             bahin sa panglantaw sa SRA ug wala siya sa admin nga kopya. --}}
        @if($staffRole === 'job_fair' && ($reportRouteName ?? 'staff.reports') === 'staff.reports')
        <a href="{{ route('staff.reports', ['tab' => 'imported']) }}"
           class="btn btn-sm fw-semibold"
           style="{{ $tab === 'imported'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
            <i class="ph-fill ph-upload-simple me-1"></i>My Imported Reports
        </a>
        @endif
        <a href="{{ route($reportRouteName ?? 'staff.reports', ['tab' => 'archived']) }}"
           class="btn btn-sm fw-semibold"
           style="{{ $tab === 'archived'
               ? 'background:var(--warn);color:#fff;border:none;'
               : 'border:1px solid var(--warn-br);color:var(--warn);background:#fff;' }}
               border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
            <i class="ph-fill ph-archive me-1"></i>Archived Job Postings
        </a>
    </div>
</div>

{{-- ── TABS NAV — ang tab sa gipiling fair ──

     One line, all of them. They used to be nowrap and free to be whatever width
     their name needed, so the row broke after four and the last three sat under
     the first three looking like a second, lesser group.

     Each box now takes an equal share of the line (flex:1 1 0) and the name
     wraps inside it at the break written into $tabs. min-width keeps the row
     from squeezing past reading size — below that it wraps, which is the right
     answer on a phone. --}}
@if($tabNeedsEvent)
<div class="d-flex gap-2 mb-4" style="flex-wrap:wrap;">
    @foreach($tabs as $key => $t)
    <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => $key, 'event_id' => $eventId])) }}"
       class="btn btn-sm fw-semibold d-flex align-items-center justify-content-center text-center"
       title="{{ $t['label'] }}"
       style="{{ $tab === $key
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:10.5px;line-height:1.25;padding:6px 8px;
           flex:1 1 0;min-width:104px;min-height:46px;white-space:normal;">
        <i class="{{ $t['icon'] }} me-1" style="flex-shrink:0;"></i>
        <span>{!! $t['html'] !!}</span>
    </a>
    @endforeach
</div>
@endif

{{-- ── DOWNLOAD ──
     PESO Job Fair staff, 2026-08-23: every tab has to come out as a file that
     opens in Excel. Not on Archived (a record of the posting, not a count of
     people — the same rule the LRA/SRA export already follows), not on My
     Imported Reports (each import has its own Download), and not on the admin
     copy of this blade, which renders it under a different route name. --}}
@php
    $downloadableTab = array_key_exists($tab, \App\Support\JobFairReport::TABS);
    $downloadNeedsEvent = !in_array($tab, \App\Support\JobFairReport::TABS_WITHOUT_EVENT, true);
@endphp
@if($downloadableTab && (!$downloadNeedsEvent || $eventId)
    && ($reportRouteName ?? 'staff.reports') === 'staff.reports')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('staff.reports.jobfair.export', array_merge(request()->query(), ['tab' => $tab, 'event_id' => $eventId])) }}"
       class="btn btn-sm fw-semibold"
       style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);
              border-radius:8px;font-size:12px;padding:6px 14px;">
        <i class="ph ph-download-simple me-1"></i>Download Excel
    </a>
</div>
@endif

@if($tab === 'archived')

    @include('staff.reports._archived')

@elseif($tab === 'imported')

    {{-- Job Fair staff ra. Ang tab nga buton gitago na sa SRA, apan ang tab
         maabot gihapon pinaagi sa URL, ug ang importJobFairReport mosalikway
         kaniya — dili angay ipakita ang porma nga dili niya magamit. --}}
    @if($staffRole === 'job_fair')
        @include('staff.reports._imported')
    @else
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="ph ph-lock-simple" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">
                Imported reports belong to the Job Fair staff
            </div>
        </div>
    @endif

{{-- Ang "pagpili ug event" nga pahimangno para sa tab nga nagsalig gyud sa usa
     ka fair. Ang Top 10 Employers wala niini: kung walay gipili, ang ranggo sa
     tanang fair, ug kana ang tubag nga gipangita. --}}
@elseif(!$eventId && $tab !== 'top_employers')
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-calendar-dots" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">Select a job fair event to view reports</div>
    </div>

@else

    {{-- ── TAB 1: ATTENDANCE ── --}}
    @if($tab === 'attendance')

@php
            // Ang Job Fair staff ra ang makamarka, ug sa iyang kaugalingon nga
            // pahina ra — ang kopya sa admin parehas ug blade apan lahi ug ruta.
            $canMarkAttendance = $staffRole === 'job_fair'
                && ($reportRouteName ?? 'staff.reports') === 'staff.reports'
                && ($event->status ?? null) !== 'completed';
            $attState = $attendanceState ?? 'attended';
        @endphp

        @if($staffRole !== 'sra')
        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
            {{-- Duha ka pangutana, usa ka listahan: kinsa ang ni-join (trabahoan
                 sa adlaw sa fair), ug kinsa ang miabot (ang moadto sa DOLE).
                 Ang Excel mosunod sa parehas nga pagpili. --}}
            <select id="attState" class="form-select form-select-sm"
                style="max-width:190px;border-color:var(--n-200);font-size:13px;"
                onchange="changeAttendanceParam('attendance_state', this.value)">
                @foreach(\App\Support\JobFairReport::STATES as $key => $label)
                <option value="{{ $key }}" {{ $attState === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select id="attFilter" class="form-select form-select-sm"
                style="max-width:180px;border-color:var(--n-200);font-size:13px;"
                onchange="changeAttendanceFilter(this.value)">
                <option value="all" {{ $attendanceFilter === 'all' ? 'selected' : '' }}>All (Local + Overseas)</option>
                <option value="local" {{ $attendanceFilter === 'local' ? 'selected' : '' }}>Local Only</option>
                <option value="overseas" {{ $attendanceFilter === 'overseas' ? 'selected' : '' }}>Overseas Only</option>
            </select>

            <div class="input-group ms-auto" style="max-width:260px;">
                <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
                    <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
                </span>
                <input type="text" id="attendanceSearchInput" class="form-control"
                    placeholder="Search name or slip no..."
                    style="border-color:var(--n-200);font-size:13px;"
                    value="{{ $attendanceSearch ?? '' }}">
            </div>
        </div>
        @endif

        {{-- Duha ra ka numero: pila ang ni-join, ug pila ang miabot. Ang
             kalainan sa duha mao ang wala pa mahibaloan, ug ang badge sa matag
             laray ang nagsulti kung kinsa sila. --}}
        <div class="row g-3 mb-4" style="max-width:420px;">
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $totalRegistered }}</div>
                    <div class="text-muted small">Joined</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:var(--g-700);">{{ $totalAttended }}</div>
                    <div class="text-muted small">Attended</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Slip No.</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Type</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Attendance</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Attended At</th>
                            @if($canMarkAttendance)
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $i => $r)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $registrations->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $r->slip_number }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ trim(($r->jobseeker->first_name ?? '').' '.($r->jobseeker->surname ?? '')) ?: 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                                {{ ucfirst($r->jobseeker->nsrp->type ?? 'None') }}
                            </td>
                            {{-- Tulo ka kahimtang, dili duha: ang wala mitubag ug ang
                                 misulti nga dili siya makaadto managlahi ug buhaton. --}}
                            <td style="padding:12px 16px;text-align:center;">
                                @if($r->is_attended)
                                    <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">
                                        <i class="ph-fill ph-check-circle me-1"></i>Attended
                                    </span>
                                @elseif($r->is_attended === null)
                                    <span class="fw-semibold" style="color:var(--n-500);font-size:11px;">
                                        Joined — no reply
                                    </span>
                                @else
                                    <span class="fw-semibold" style="color:var(--warn);font-size:11px;">
                                        Said they cannot come
                                    </span>
                                @endif
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                                {{ $r->attended_at?->format('M d, Y h:i A') ?? 'None' }}
                            </td>
                            @if($canMarkAttendance)
                            <td style="padding:12px 16px;text-align:center;">
                                @if($r->is_attended)
                                <form action="{{ route('staff.jobfair.attendance.unmark', $r->job_fair_registrations_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm fw-semibold"
                                        style="border:1px solid var(--danger);color:var(--danger);background:#fff;
                                               border-radius:8px;font-size:11px;padding:4px 12px;">
                                        <i class="ph ph-x-circle me-1"></i>Unmark
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('staff.jobfair.attendance.mark', $r->job_fair_registrations_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm fw-semibold"
                                        style="background:var(--g-600);color:#fff;border:none;
                                               border-radius:8px;font-size:11px;padding:4px 12px;">
                                        <i class="ph ph-check-circle me-1"></i>Mark Attended
                                    </button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="{{ $canMarkAttendance ? 7 : 6 }}" class="text-center py-4" style="color:var(--n-500);font-size:13px;">
                            @if($attState === 'attended')
                                No one has been marked as attended for this event yet.
                            @else
                                No jobseeker has joined this event yet.
                            @endif
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($registrations && $registrations->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }} of {{ $registrations->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $registrations->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $registrations->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($registrations->getUrlRange(1, $registrations->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $registrations->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $registrations->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$registrations->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $registrations->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

    {{-- ── TAB 2: LOCAL/OVERSEAS COMPANIES ── --}}
    {{-- ── TAB 2: PARTICIPATING COMPANIES ──

         Gikopya gikan sa papel nga gipasa sa PESO sa DOLE. Ang kolum didto:
         NO. | NAME OF AGENCY | NAME OF REPRESENTATIVES | ADDRESS | CONTACT INFO
         | NO. OF VACANCIES, ug ang TOTAL sa ubos.

         Kaniadto tulo ka kolum ra dinhi — kompanya ug address — mao nga ang
         desk kinahanglan pa mangita sa representative ug sa numero sa lain nga
         page sa dili pa niya masulat ang papel. --}}
    @elseif($tab === 'companies')

    @php
        // Usa ka kopya sa lamesa para sa duha ka listahan. Ang han-ay sa kolum
        // parehas gyud sa papel, mao nga ang pag-encode usa ka pagbasa gikan sa
        // wala paingon sa tuo.
        $companyTable = function ($rows, string $heading) {
            return [$rows, $heading];
        };
    @endphp

    {{-- Usa ka lamesa, dili duha.

         Ang papel usa ka porma kada klase — ang gihatag sa PESO naay "(FOR
         LOCAL)" sa taas — mao nga usa ra ang gipangayo niini nga tab. Ang
         Job Fair desk nagbasa sa lokal; ang SRA nagbasa sa overseas, ug ang
         iyang tab ginganlan na niana sa taas. Ang duha ka lamesa sa usa ka
         tab mao ang naghimo sa ngalan nga bakak: "Local Companies" nga naay
         overseas sa sulod. --}}
    @php
        $companyRows  = $isSraJobFairView ? $companiesOverseas : $companiesLocal;
        $heading      = $isSraJobFairView ? 'Overseas Companies' : 'Local Companies';
        $vacancyTotal = $isSraJobFairView ? $companyVacancyTotals['overseas'] : $companyVacancyTotals['local'];
        $rows         = $companyRows;
    @endphp
    <div class="mb-4">
        <h6 class="fw-bold mb-2" style="color:var(--g-700);font-size:13px;">
            {{ $heading }}
            <span style="font-weight:400;color:var(--n-500);font-size:12px;">
                — {{ $rows->total() }} compan{{ $rows->total() === 1 ? 'y' : 'ies' }}
            </span>
        </h6>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;width:50px;">NO.</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;">NAME OF AGENCY</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;">NAME OF REPRESENTATIVE</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;">ADDRESS</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;">CONTACT INFO</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;text-align:center;">NO. OF VACANCIES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $p)
                        @php
                            $company = $p->employer;
                            $rowNumber = $rows->firstItem() + $i;
                            $address = collect([
                                $company->est_barangay ?? null,
                                $company->est_city_municipality ?? null,
                                $company->est_province ?? null,
                            ])->filter()->implode(', ');
                        @endphp
                        <tr style="font-size:12px;">
                            <td style="padding:10px 14px;color:var(--n-500);">{{ $rowNumber }}</td>
                            <td style="padding:10px 14px;font-weight:600;color:var(--g-700);">
                                {{ $company->company_name ?? 'None' }}
                            </td>
                            <td style="padding:10px 14px;color:var(--n-700);">
                                {{ $company->contact_person ?? 'None' }}
                                @if($company->position_title ?? null)
                                    <div style="font-size:10.5px;color:var(--n-500);">{{ $company->position_title }}</div>
                                @endif
                            </td>
                            <td style="padding:10px 14px;color:var(--n-700);">{{ $address ?: 'None' }}</td>
                            <td style="padding:10px 14px;color:var(--n-700);">
                                {{ $company->mobile_number ?? $company->telephone_no ?? 'None' }}
                            </td>
                            <td style="padding:10px 14px;text-align:center;color:var(--g-700);font-weight:600;">
                                {{ $p->vacancies ?? 0 }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding:22px 14px;color:var(--n-500);font-size:12.5px;">
                                No company has confirmed for this fair yet.
                            </td>
                        </tr>
                        @endforelse

                        {{-- Ang TOTAL nga laray sa papel. Gisulat gihapon bisan
                             walay laray: ang zero usa ka tubag, ug ang porma
                             kinahanglan parehas kada bulan aron siya matandi. --}}
                        {{-- Ang TOTAL sa TIBUOK listahan, dili sa panid nga
                             giablihan. Ang kinatibuk-an sa lima ka laray dili
                             mao ang gipangayo sa papel. --}}
                        <tr style="font-size:12.5px;border-top:2px solid var(--n-200);">
                            <td colspan="5" class="fw-bold" style="padding:10px 14px;color:var(--g-700);">
                                TOTAL
                                <span style="font-weight:400;color:var(--n-500);font-size:11px;">
                                    — all {{ $rows->total() }} compan{{ $rows->total() === 1 ? 'y' : 'ies' }}
                                </span>
                            </td>
                            <td class="fw-bold" style="padding:10px 14px;text-align:center;color:var(--g-700);">
                                {{ number_format($vacancyTotal) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($rows->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $rows->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $rows->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($rows->getUrlRange(1, $rows->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $rows->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $rows->currentPage()
                                    ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                    : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$rows->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $rows->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>

    {{-- ── TAB: LIST OF LOCAL JOB VACANCIES ──

         Ang kopya sa siyudad. Tulo ka kolum ra: kompanya, address, pila ka
         bakante — walay representative ug walay contact, kay dili man kini
         hikapan nga listahan. Ang kopya sa DOLE naa sa Participating
         Companies nga tab. --}}
    @elseif($tab === 'vacancy_list')

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 14px;width:60px;">No.</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 14px;">NAME OF COMPANY/OFFICE</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 14px;">ADDRESS</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 14px;text-align:center;width:150px;">No. of Vacancies</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacancyList as $i => $row)
                        <tr style="font-size:12.5px;">
                            <td style="padding:10px 14px;color:var(--n-500);">{{ $i + 1 }}</td>
                            <td style="padding:10px 14px;font-weight:600;color:var(--g-700);">
                                {{ $row['company'] }}
                                @if($row['overseas'])
                                    <span style="color:var(--info);font-size:10px;font-weight:600;">Overseas</span>
                                @endif
                            </td>
                            <td style="padding:10px 14px;color:var(--n-700);">{{ $row['address'] ?: 'None' }}</td>
                            <td style="padding:10px 14px;text-align:center;color:var(--g-700);font-weight:600;">{{ $row['vacancies'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center" style="padding:22px 14px;color:var(--n-500);font-size:12.5px;">
                                No vacancy has been brought to this fair yet.
                            </td>
                        </tr>
                        @endforelse

                        <tr style="font-size:13px;border-top:2px solid var(--n-200);">
                            <td colspan="3" class="fw-bold" style="padding:10px 14px;color:var(--g-700);">TOTAL</td>
                            <td class="fw-bold" style="padding:10px 14px;text-align:center;color:var(--g-700);">
                                {{ number_format($vacancyList->sum('vacancies')) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($tab === 'further_interview')

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">No.</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">Name</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">Gender</th>
                            <th colspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">Contact Details</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">Job Position</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">Hiring Company</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">Local/Overseas</th>
                            {{-- Blangko sa print, tinuyo.

                                 Ang tubag moabot usa ka bulan human sa fair, sa
                                 telepono ug sa employer — ug walay gutlo sa
                                 sistema nga makadakop niini. Ang papel gi-print
                                 ug gisulatan sa kamot, mao nga ang kolum naa
                                 dinhi ug blangko. --}}
                            <th colspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">
                                Status After One (1) Month (Put ✓)
                            </th>
                        </tr>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;">Address</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;">Tel/Cellphone Number</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;text-align:center;width:70px;">Hired</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;text-align:center;width:80px;">Not Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($furtherInterview as $i => $app)
                        <tr style="font-size:13px;">
                            @php
                                $seeker = $app->jobseeker;
                                $seekerAddress = collect([
                                    $seeker->present_barangay ?? null,
                                    $seeker->present_city_municipality ?? null,
                                ])->filter()->implode(', ');
                            @endphp
                            <td style="padding:10px 12px;color:var(--n-500);">{{ $furtherInterview->firstItem() + $i }}</td>
                            <td style="padding:10px 12px;font-weight:600;color:var(--g-700);">
                                {{ trim(($seeker->first_name ?? '').' '.($seeker->surname ?? '')) ?: 'None' }}
                            </td>
                            <td style="padding:10px 12px;text-align:center;color:var(--n-700);">
                                {{ $seeker->sex ? mb_substr($seeker->sex, 0, 1) : 'None' }}
                            </td>
                            <td style="padding:10px 12px;color:var(--n-700);">{{ $seekerAddress ?: 'None' }}</td>
                            <td style="padding:10px 12px;color:var(--n-700);">{{ $seeker->contact_number ?? 'None' }}</td>
                            <td style="padding:10px 12px;color:var(--n-700);">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:10px 12px;color:var(--n-700);">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:10px 12px;text-align:center;color:var(--n-700);">
                                {{ ($app->job->company->is_overseas ?? false) ? 'Overseas' : 'Local' }}
                            </td>
                            <td style="padding:10px 12px;background:var(--n-50);"></td>
                            <td style="padding:10px 12px;background:var(--n-50);"></td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center py-4" style="color:var(--n-500);font-size:13px;">No applicant is waiting for a further interview.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($furtherInterview && $furtherInterview->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $furtherInterview->firstItem() }}–{{ $furtherInterview->lastItem() }} of {{ $furtherInterview->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $furtherInterview->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $furtherInterview->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($furtherInterview->getUrlRange(1, $furtherInterview->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $furtherInterview->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $furtherInterview->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$furtherInterview->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $furtherInterview->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

    {{-- ── TAB 4: HOTS ── --}}
    @elseif($tab === 'hots')

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">No.</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">Name</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">Gender</th>
                            <th colspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">Contact Details</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">Job Position</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">Hiring Company</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">Local/Overseas</th>
                        </tr>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;">Address</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;">Tel/Cellphone Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hots as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $hots->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ trim(($app->jobseeker->first_name ?? '').' '.($app->jobseeker->surname ?? '')) ?: 'None' }}
                            </td>
                            @php
                                $seeker = $app->jobseeker;
                                $seekerAddress = collect([
                                    $seeker->present_barangay ?? null,
                                    $seeker->present_city_municipality ?? null,
                                ])->filter()->implode(', ');
                            @endphp
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                                {{ $seeker->sex ? mb_substr($seeker->sex, 0, 1) : 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $seekerAddress ?: 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $seeker->contact_number ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                                {{ ($app->job->company->is_overseas ?? false) ? 'Overseas' : 'Local' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4" style="color:var(--n-500);font-size:13px;">No one was hired on the spot at this fair.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($hots && $hots->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $hots->firstItem() }}–{{ $hots->lastItem() }} of {{ $hots->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $hots->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $hots->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($hots->getUrlRange(1, $hots->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $hots->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $hots->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$hots->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $hots->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

    {{-- ── TAB 5: POST JOB FAIR SUMMARY ── --}}
    @elseif($tab === 'summary')

        <div class="row g-3 mb-4">
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:var(--g-600);">{{ $summaryTotals['vacancies'] }}</div>
                    <div class="text-muted small">Vacancies</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:var(--g-700);">{{ $summaryTotals['interviewed'] }}</div>
                    <div class="text-muted small">Interviewed</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:var(--warn);">{{ $summaryTotals['female'] }}</div>
                    <div class="text-muted small">Male</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:var(--info);">{{ $summaryTotals['female'] }}</div>
                    <div class="text-muted small">Female</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:var(--info);">{{ $summaryTotals['qualified'] }}</div>
                    <div class="text-muted small">Qualified</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:var(--g-700);">{{ $summaryTotals['hired'] }}</div>
                    <div class="text-muted small">Hired</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">NO</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;">NAME OF EMPLOYER</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">NO. OF VACANCIES</th>
                            <th colspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">NO. OF APPLICANTS</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">CLUSTER QUALIFIED</th>
                            <th rowspan="2" style="background:var(--g-600);color:#fff;font-size:11.5px;border:none;padding:10px 12px;text-align:center;">HIRED ON THE SPOT</th>
                        </tr>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;text-align:center;width:90px;">Total</th>
                            <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 12px;text-align:center;width:90px;">Female</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryParticipants as $i => $p)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $i+1 }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">{{ $p->employer->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">{{ $p->vacancies }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">{{ $p->interviewed }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">{{ $p->female }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">{{ $p->qualified }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--g-700);font-weight:600;">{{ $p->hired }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4" style="color:var(--n-500);font-size:13px;">No employer has confirmed for this fair.</td></tr>
                        @endforelse

                        {{-- Ang TOTAL nga laray sa papel, ug ang Total No. of
                             Registrants sa ubos niini. Kining ulahi mao ang
                             tanang jobseeker nga niapil sa fair — dili siya
                             kinatibuk-an sa kolum sa ibabaw, mao nga lain siya
                             nga linya sama sa papel. --}}
                        <tr style="font-size:13px;border-top:2px solid var(--n-200);">
                            <td colspan="2" class="fw-bold" style="padding:10px 12px;color:var(--g-700);">TOTAL:</td>
                            <td class="fw-bold" style="padding:10px 12px;text-align:center;color:var(--g-700);">{{ number_format($summaryTotals['vacancies']) }}</td>
                            <td class="fw-bold" style="padding:10px 12px;text-align:center;color:var(--g-700);">{{ number_format($summaryTotals['interviewed']) }}</td>
                            <td class="fw-bold" style="padding:10px 12px;text-align:center;color:var(--g-700);">{{ number_format($summaryTotals['female']) }}</td>
                            <td class="fw-bold" style="padding:10px 12px;text-align:center;color:var(--g-700);">{{ number_format($summaryTotals['qualified']) }}</td>
                            <td class="fw-bold" style="padding:10px 12px;text-align:center;color:var(--g-700);">{{ number_format($summaryTotals['hired']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Ang linya sa ubos sa papel. Ang gi-ihap kay ang tawo nga
                 niapil sa fair, dili ang aplikasyon: usa ka tawo nga miduol sa
                 tulo ka employer usa ra gihapon ka registrant. --}}
            <div class="px-3 py-3" style="border-top:1px solid var(--n-50);font-size:12.5px;color:var(--n-700);">
                <span style="color:var(--n-500);">Total No. of Registrants:</span>
                <strong style="color:var(--g-700);">LOCAL: {{ number_format($summaryRegistrants['local']) }}</strong>
                <span class="mx-2" style="color:var(--n-200);">|</span>
                <strong style="color:var(--g-700);">OVERSEAS: {{ number_format($summaryRegistrants['overseas']) }}</strong>
            </div>
        </div>

    {{-- ── TAB 6: TOTAL COMPANIES WITH VACANCIES (Industry Group) ── --}}
    @elseif($tab === 'industry')

        <div class="row g-2 mb-3">
            @if($staffRole !== 'sra')
            <div class="col-12 col-md-6">
                <h6 class="fw-bold" style="color:var(--g-700);font-size:13px;">Local — by Industry Group</h6>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;">Industry Group</th>
                                    <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;text-align:center;">Total Vacancies</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($industryLocal as $group => $total)
                                <tr style="font-size:12px;">
                                    <td style="padding:10px 14px;color:var(--g-700);font-weight:600;">{{ $group ?: 'Uncategorized' }}</td>
                                    <td style="padding:10px 14px;text-align:center;color:var(--n-700);">{{ $total }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center py-3" style="color:var(--n-500);font-size:12px;">No data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-12 col-md-6">
                <h6 class="fw-bold" style="color:var(--g-700);font-size:13px;">Overseas — by Industry Group</h6>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;">Industry Group</th>
                                    <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 14px;text-align:center;">Total Vacancies</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($industryOverseas as $group => $total)
                                <tr style="font-size:12px;">
                                    <td style="padding:10px 14px;color:var(--g-700);font-weight:600;">{{ $group ?: 'Uncategorized' }}</td>
                                    <td style="padding:10px 14px;text-align:center;color:var(--n-700);">{{ $total }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center py-3" style="color:var(--n-500);font-size:12px;">No data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    {{-- ── TAB 7: TOP 10 OCCUPATION, AND THE INDUSTRY SHARE ──

         Gikopya gikan sa papel nga gipasa sa PESO sa DOLE. Duha ka lamesa, ug
         walay usa nila naghisgot ug employer: ang trabaho nga gipangita, ug
         ang bahin sa matag industriya. Ang "Top 10 Employers" nga naa dinhi
         kaniadto tubag sa lain nga pangutana. --}}
    @elseif($tab === 'top_employers')

        {{-- ── ANG RUN DOWN ──
             Ang linya sa ulohan sa papel: "46 COMPANIES WITH 1,990 VACANCIES",
             dayon gibahin sa lokal ug overseas. --}}
        @if($runDown)
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
            <div class="fw-semibold" style="color:var(--g-700);font-size:13.5px;">
                <i class="ph-fill ph-clipboard-text me-2" style="color:var(--g-600);"></i>
                Total: {{ number_format($runDown['companies']) }} compan{{ $runDown['companies'] === 1 ? 'y' : 'ies' }}
                with {{ number_format($runDown['vacancies']) }} vacanc{{ $runDown['vacancies'] === 1 ? 'y' : 'ies' }}
            </div>
            <div class="d-flex gap-4 flex-wrap mt-2" style="font-size:12.5px;color:var(--n-700);">
                <div>
                    <span style="color:var(--n-500);">Local:</span>
                    <strong style="color:var(--g-700);">{{ number_format($runDown['local_companies']) }}</strong>
                    with <strong style="color:var(--g-700);">{{ number_format($runDown['local_vacancies']) }}</strong> vacancies
                </div>
                <div>
                    <span style="color:var(--n-500);">Overseas:</span>
                    <strong style="color:var(--g-700);">{{ number_format($runDown['overseas_companies']) }}</strong>
                    with <strong style="color:var(--g-700);">{{ number_format($runDown['overseas_vacancies']) }}</strong> vacancies
                </div>
            </div>
            <div class="mt-2" style="font-size:11px;color:var(--n-500);">
                {{ $event ? $event->title . ' — ' . $event->event_date->format('M d, Y') : 'Every job fair so far.' }}
                The run down of vacancies and companies is what establishes the top 10 occupation and the industry share.
            </div>
        </div>
        @endif

        {{-- ── LAMESA 1: TOP 10 OCCUPATION ── --}}
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
            <div class="fw-semibold mb-2" style="color:var(--g-700);font-size:14px;">
                <i class="ph-fill ph-briefcase me-2"></i>Top 10 Occupation
            </div>
            <div style="font-size:12px;color:var(--n-500);" class="mb-3">
                The ten occupations with the most vacancies. The number is the vacancies asked for,
                counted from the postings themselves, so nobody types it in.
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;width:60px;">NO.</th>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;">OCCUPATION</th>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;text-align:center;width:120px;">NUMBER</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topOccupations as $index => $row)
                        <tr style="font-size:13px;">
                            <td style="padding:8px 10px;color:var(--n-500);">{{ $index + 1 }}</td>
                            <td style="padding:8px 10px;color:var(--g-700);font-weight:600;">
                                {{ $row['occupation'] }}
                                <span style="font-size:10.5px;font-weight:400;color:var(--n-500);">
                                    · {{ $row['postings'] }} posting(s)
                                </span>
                            </td>
                            <td style="padding:8px 10px;text-align:center;color:var(--g-700);font-weight:600;">{{ $row['number'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center" style="padding:20px 10px;color:var(--n-500);font-size:12.5px;">
                                No vacancy has been brought to a job fair yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── LAMESA 2: MAJOR INDUSTRY GROUP ──

             Gilista ang TANANG grupo, apil ang zero. Ang papel usa ka porma nga
             naay nakaimprinta nga laray, ug ang blangko usa ka tubag: walay
             Construction niini nga fair. Kung tagoon ang zero, lain ang porma
             sa report kada bulan ug dili na siya matandi. --}}
        @php
            // Ang upat ka ulohan sa papel gikan na sa JobFairReport, aron ang
            // page ug ang download dili gyud magkalahi ug han-ay.
            $industrySections = \App\Support\JobFairReport::INDUSTRY_SECTIONS;
        @endphp
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
            <div class="fw-semibold mb-2" style="color:var(--g-700);font-size:14px;">
                <i class="ph-fill ph-tree-structure me-2"></i>Major Industry Group
            </div>
            <div style="font-size:12px;color:var(--n-500);" class="mb-3">
                The vacancies of this run down, split by the industry each posting belongs to.
                The share is of {{ number_format($industryShares['total']) }} vacanc{{ $industryShares['total'] === 1 ? 'y' : 'ies' }}.
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;">MAJOR INDUSTRY GROUP</th>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;text-align:center;width:120px;">QUANTITY</th>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;text-align:center;width:120px;">% SHARE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($industrySections as $section => $groups)
                        <tr>
                            <td colspan="3" class="fw-bold"
                                style="background:#FEF3C7;color:var(--g-700);padding:6px 10px;font-size:12px;letter-spacing:0.3px;">
                                {{ $section }}
                            </td>
                        </tr>
                        @foreach($groups as $group)
                        @php $row = $industryShares['rows'][$group] ?? ['quantity' => 0, 'share' => 0]; @endphp
                        <tr style="font-size:12.5px;">
                            <td style="padding:7px 10px;color:var(--n-700);">{{ $group }}</td>
                            <td style="padding:7px 10px;text-align:center;color:{{ $row['quantity'] ? 'var(--g-700)' : 'var(--n-400)' }};font-weight:{{ $row['quantity'] ? '600' : '400' }};">
                                {{ $row['quantity'] ?: '' }}
                            </td>
                            <td style="padding:7px 10px;text-align:center;color:{{ $row['quantity'] ? 'var(--g-700)' : 'var(--n-400)' }};">
                                {{ $row['quantity'] ? number_format($row['share'], 2) . '%' : '' }}
                            </td>
                        </tr>
                        @endforeach
                        @endforeach

                        {{-- Ang employer nga walay industriya nga naka-set. Kung
                             itago siya, ang % share dili moabot sa 100 ug walay
                             makasulti ngano. --}}
                        @if($industryShares['unclassified']['quantity'] > 0)
                        <tr style="font-size:12.5px;">
                            <td style="padding:7px 10px;color:var(--warn);">
                                <i class="ph-fill ph-warning-circle me-1"></i>No industry group set
                            </td>
                            <td style="padding:7px 10px;text-align:center;color:var(--warn);font-weight:600;">
                                {{ $industryShares['unclassified']['quantity'] }}
                            </td>
                            <td style="padding:7px 10px;text-align:center;color:var(--warn);">
                                {{ number_format($industryShares['unclassified']['share'], 2) }}%
                            </td>
                        </tr>
                        @endif

                        <tr style="font-size:13px;border-top:2px solid var(--n-200);">
                            <td class="fw-bold" style="padding:8px 10px;color:var(--g-700);">TOTAL</td>
                            <td class="fw-bold" style="padding:8px 10px;text-align:center;color:var(--g-700);">
                                {{ number_format($industryShares['total']) }}
                            </td>
                            <td class="fw-bold" style="padding:8px 10px;text-align:center;color:var(--g-700);">
                                {{ $industryShares['total'] > 0 ? '100.00%' : '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ── TAB 8: COMPANY PLACEMENT REPORT ── --}}
    @else

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Name of Applicant</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Gender</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Position</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($placementReport as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $placementReport->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ trim(($app->jobseeker->first_name ?? '').' '.($app->jobseeker->surname ?? '')) ?: 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">{{ $app->jobseeker->sex ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-500);">{{ $app->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4" style="color:var(--n-500);font-size:13px;">No placements recorded yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($placementReport && $placementReport->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $placementReport->firstItem() }}–{{ $placementReport->lastItem() }} of {{ $placementReport->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $placementReport->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $placementReport->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($placementReport->getUrlRange(1, $placementReport->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $placementReport->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $placementReport->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$placementReport->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $placementReport->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

    @endif

@endif

@else

{{-- ── ROW ONE (SRA) ──

     The view selector, and at the far end the reports that are not a count of
     jobseekers: who got a room, the interviews employers ran, what each
     employer did with the people it took, and the postings that have lapsed.

     They were in the row below with Registered / Placed / Referred, which made
     eight tabs of two different kinds reading as one list. Split by kind, and
     pushed to the right so the selector is not crowded. --}}
@if($staffRole === 'sra')
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <select id="reportViewSelector" class="form-select form-select-sm"
        style="max-width:250px;border-color:var(--n-200);font-size:13px;"
        onchange="changeReportView(this.value)">
        <option value="staff" {{ ($reportView ?? 'staff') === 'staff' ? 'selected' : '' }}>Overseas Reports</option>
        <option value="jobfair" {{ ($reportView ?? 'staff') === 'jobfair' ? 'selected' : '' }}>Job Fair Reports (Overseas)</option>
    </select>

    <div class="d-flex gap-2 flex-wrap ms-auto">
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'schedules', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'schedules'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
            <i class="ph-fill ph-calendar-check me-1"></i>In-house Schedules
        </a>
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'company_interview', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'company_interview'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
            <i class="ph-fill ph-video-camera me-1"></i>Company Interviews
        </a>
        @if(($reportRouteName ?? 'staff.reports') === 'staff.reports')
        <a href="{{ route('staff.reports', array_merge(request()->except('employer'), ['tab' => 'employer_hires', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'employer_hires'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
            <i class="ph-fill ph-buildings me-1"></i>Employer Reports
        </a>
        @endif
        {{-- The same list of lapsed postings the Job Fair Reports view shows.
             One tab key, one partial: the two views are two ways into the same
             record, not two records. --}}
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'archived', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'archived'
               ? 'background:var(--warn);color:#fff;border:none;'
               : 'border:1px solid var(--warn-br);color:var(--warn);background:#fff;' }}
               border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
            <i class="ph-fill ph-archive me-1"></i>Archived Job Postings
        </a>
    </div>
</div>
@endif

{{-- LRA/SRA — the jobseeker counts (with inline counts, no separate stat cards) --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;max-width:100%;">
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'registered', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab','registered') === 'registered'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph ph-user-list me-1"></i> Job Applicant Registered ({{ $totalRegistered }})
        </a>
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'placed', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'placed'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }} 
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph-fill ph-briefcase me-1"></i> Placed Applicants ({{ $totalPlaced }})
        </a>
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'referred', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'referred'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph-fill ph-users-three me-1"></i> Job Applicants Referred ({{ $totalReferred }})
        </a>
        {{-- Who got a room and who was turned down. Both desks keep one, each
             for their own side. The SRA's is in the row above, with the other
             reports that are not a count of jobseekers. --}}
        @if($staffRole !== 'sra')
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'schedules', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'schedules'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph-fill ph-calendar-check me-1"></i> In-house Schedules
        </a>
        @endif
        @if($staffRole === 'sra')
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'vacancies', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'vacancies'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph-fill ph-briefcase me-1"></i> Job Vacancies Solicited ({{ $totalVacanciesSolicited ?? 0 }})
        </a>
        @endif
        {{-- PESO SRA, 2026-08-26: wala nay Top Employers sa overseas nga reports.
             Ang ranggo sa employer napulot na sa job fair nga reports, diin
             ang giihap mao ang bakante nga ilang gidala sa fair. Ang usa dinhi
             nag-ihap ug in-house nga interview — tubag kana sa laing pangutana,
             ug ang LRA ra ang nangutana niini. --}}
        @if($staffRole !== 'sra')
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'top_employers', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'top_employers'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph-fill ph-buildings me-1"></i> Top 5 Employers
        </a>
        @endif
        {{-- Pila ang gikuha sa matag employer, ug kinsa. Ang "Total Hired" nga
             numero sa Registered Employer nga listahan mo-abot diri, mao nga
             ang duha ka desk naay usa — ang SRA sa overseas, ang LRA sa local.
             Wala sa admin nga kopya sa parehas nga blade. --}}
        @if($staffRole !== 'sra' && ($reportRouteName ?? 'staff.reports') === 'staff.reports')
        <a href="{{ route('staff.reports', array_merge(request()->except('employer'), ['tab' => 'employer_hires', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'employer_hires'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph-fill ph-buildings me-1"></i> Employer Reports
        </a>
        @endif
        {{-- LRA staff, 2026-08-23: unsay nahitabo sa usa ka employer usa ka
             semana human sa iyang in-house interview. Tab ni, dili kaugalingon
             nga sidebar entry — taas na ang nav sa LRA, ug report man gihapon
             siya. Wala sa admin nga kopya sa parehas nga blade. --}}
        @if($staffRole === 'lra' && ($reportRouteName ?? 'staff.reports') === 'staff.reports')
        <a href="{{ route('staff.reports', array_merge(request()->query(), ['tab' => 'employer_report', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'employer_report'
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="ph-fill ph-clipboard-text me-1"></i> Employer Report
        </a>
        @endif
        {{-- Walay Archived Job Postings para sa LRA: ang posting gidumala sa
             Job Vacancy staff ug sa SRA, dili niya. Ang sa SRA naa sa laray sa
             ibabaw. --}}
    </div>
    <div class="input-group" style="max-width:260px;width:100%;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search name or email..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- ── DATE RANGE + EXPORT — para sa tulo ka listahan nga isumite sa
     Mayor's Office ug DOLE (PESO interview 2026-08-13). Ang job fair nga
     report naay kaugalingon nga event filter, mao nga dili siya apil dinhi. ── --}}
@php
    // Ang Archived Job Postings wala giapil: rekord kadto sa posting mismo,
    // dili ihap sa tawo sulod sa usa ka panahon.
    $exportableTab = in_array(request('tab', 'registered'), ['registered', 'placed', 'referred'], true);
    $exportTab     = request('tab', 'registered');
@endphp

@if(isset($range) && $exportableTab && ($reportRouteName ?? 'staff.reports') === 'staff.reports')
    @include('partials.date-range-filter', [
        'range'   => $range,
        'action'  => route('staff.reports'),
        'keep'    => array_filter([
            'tab'             => $exportTab,
            'registered_view' => request('registered_view'),
            'search'          => request('search'),
        ]),
        'exports' => [
            [
                'url'   => route('staff.reports.export', array_merge($range->queryParams(), ['tab' => $exportTab])),
                'label' => 'Download Excel',
                'icon'  => 'ph-download-simple',
            ],
        ],
    ])
@endif

{{-- ── TAB 1: JOB APPLICANT REGISTERED ── --}}
@if(request('tab', 'registered') === 'registered')

    {{-- FILTER DROPDOWN: All Local Jobseekers vs In-house Participants --}}
    <div class="mb-3">
        <select id="registeredViewFilter" class="form-select form-select-sm"
            style="max-width:280px;width:100%;border-color:var(--n-200);font-size:13px;"
            onchange="changeRegisteredView(this.value)">
            <option value="all" {{ ($registeredView ?? 'all') === 'all' ? 'selected' : '' }}>
                All {{ $staffRole === 'lra' ? 'Local' : 'Overseas' }} Jobseekers ({{ $totalRegisteredAll ?? 0 }})
            </option>
            <option value="inhouse" {{ ($registeredView ?? 'all') === 'inhouse' ? 'selected' : '' }}>
                Joined In-house ({{ $totalRegistered ?? 0 }})
            </option>
        </select>
    </div>

    @if(($registeredView ?? 'all') === 'all')

            {{-- The table is drawn whether or not it has rows. The columns
                 are the answer to what this tab reports on, and a page that
                 changes shape when there is nothing to show gives the desk
                 nothing to read that result against. --}}
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                                <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                                <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Email</th>
                                <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registeredAll ?? collect() as $i => $reg)
                            <tr style="font-size:13px;">
                                <td style="padding:12px 16px;color:var(--n-500);">{{ $registeredAll->firstItem() + $i }}</td>
                                <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                    {{ trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? '')) ?: ($reg->user->name ?? 'None') }}
                                </td>
                                <td style="padding:12px 16px;color:var(--n-700);">
                                    {{ $reg->reg_email ?? $reg->user->email ?? 'None' }}
                                </td>
                                <td style="padding:12px 16px;text-align:center;color:var(--n-500);">
                                    {{ $reg->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center"
                                    style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                    No registered jobseeker in this range.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($registeredAll->hasPages())
                <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                    <div style="font-size:12px;color:var(--n-500);">
                        Showing {{ $registeredAll->firstItem() }}–{{ $registeredAll->lastItem() }} of {{ $registeredAll->total() }} results
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item {{ $registeredAll->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $registeredAll->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                            </li>
                            @foreach($registeredAll->getUrlRange(1, $registeredAll->lastPage()) as $page => $url)
                            <li class="page-item {{ $page == $registeredAll->currentPage() ? 'active' : '' }}">
                                <a class="page-link rounded-2"
                                   style="{{ $page == $registeredAll->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                                   href="{{ $url }}">{{ $page }}</a>
                            </li>
                            @endforeach
                            <li class="page-item {{ !$registeredAll->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $registeredAll->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                @endif
            </div>

    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Employer</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Interview Date</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Accepted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registeredParticipants as $i => $p)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $registeredParticipants->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;">
                                <div class="fw-semibold" style="color:var(--g-700);">{{ trim(($p->jobseeker->first_name ?? '') . ' ' . ($p->jobseeker->surname ?? '')) ?: 'None' }}</div>
                                <div style="font-size:11px;color:var(--n-500);">{{ $p->jobseeker->reg_email ?? $p->jobseeker->user->email ?? 'None' }}</div>
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $p->job->company->company_name ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $p->job->interview_date ? $p->job->interview_date->format('M d, Y') : 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-500);">
                                {{ $p->updated_at?->format('M d, Y h:i A') ?? 'None' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                A jobseeker appears here once they accept a confirmed in-house interview.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($registeredParticipants->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $registeredParticipants->firstItem() }}–{{ $registeredParticipants->lastItem() }} of {{ $registeredParticipants->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $registeredParticipants->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $registeredParticipants->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($registeredParticipants->getUrlRange(1, $registeredParticipants->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $registeredParticipants->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $registeredParticipants->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$registeredParticipants->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $registeredParticipants->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif

{{-- ── TAB 2: PLACED APPLICANTS ── --}}
@elseif(request('tab') === 'placed')

    @if(isset($placedChart))
        @include('partials.bar-chart', [
            'title' => 'Placed applicants per month',
            'rows'  => $placedChart,
        ])
    @endif

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Name of Applicant</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Gender</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Referred As</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Referred To</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Placed As</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Placed To</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($placedApplications as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $placedApplications->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;">
                                <div class="fw-semibold" style="color:var(--g-700);">{{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: 'None' }}</div>
                                <div style="font-size:11px;color:var(--n-500);">{{ $app->jobseeker->user->email ?? 'None' }}</div>
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                                {{ $app->jobseeker->sex ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-500);">{{ $app->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                No placed applicant in this range.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($placedApplications->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $placedApplications->firstItem() }}–{{ $placedApplications->lastItem() }} of {{ $placedApplications->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $placedApplications->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $placedApplications->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($placedApplications->getUrlRange(1, $placedApplications->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $placedApplications->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $placedApplications->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$placedApplications->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $placedApplications->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

{{-- ── TAB 3: TOP EMPLOYERS ── --}}
@elseif(request('tab') === 'top_employers')

    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
        <div class="fw-semibold mb-2" style="color:var(--g-700);font-size:14px;">
            <i class="ph-fill ph-buildings me-2"></i>Top 5 Employers by In-House Interviews
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <div class="btn-group btn-group-sm" role="group" aria-label="Top employers filter">
                <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'top_employers', 'top_employers_filter' => 'monthly', 'page' => 1])) }}"
                   class="btn {{ ($topEmployersFilter ?? 'monthly') === 'monthly' ? 'btn-success' : 'btn-outline-success' }}"
                   style="font-size:12px;">Monthly</a>
                <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'top_employers', 'top_employers_filter' => 'yearly', 'page' => 1])) }}"
                   class="btn {{ ($topEmployersFilter ?? 'monthly') === 'yearly' ? 'btn-success' : 'btn-outline-success' }}"
                   style="font-size:12px;">Yearly</a>
            </div>
            @if(($topEmployersFilter ?? 'monthly') === 'monthly')
                <input type="month" class="form-control form-control-sm" style="max-width:220px;" value="{{ $topEmployersMonth ?: now()->format('Y-m') }}" onchange="changeTopEmployersDate(this.value)">
            @else
                <select class="form-select form-select-sm" style="max-width:180px;" onchange="changeTopEmployersYear(this.value)">
                    @for($year = now()->year; $year >= now()->year - 5; $year--)
                        <option value="{{ $year }}" {{ ($topEmployersYear ?: now()->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            @endif
        </div>
        @php
            $topEmployers = $topEmployersByCompanyInterviews ?? collect();
        @endphp
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;">#</th>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;">Employer</th>
                            <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;text-align:center;">In-House Interviews</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topEmployers as $index => $entry)
                            <tr style="font-size:13px;">
                                <td style="padding:8px 10px;color:var(--n-500);">{{ $index + 1 }}</td>
                                <td style="padding:8px 10px;color:var(--g-700);font-weight:600;">{{ $entry['employer']->company_name ?? 'Unknown Employer' }}</td>
                                <td style="padding:8px 10px;text-align:center;color:var(--n-700);">{{ $entry['interview_count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center"
                                    style="padding:20px 10px;color:var(--n-500);font-size:12.5px;">
                                    No in-house interview in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>

{{-- ── TAB: JOB VACANCIES SOLICITED (SRA ra) ── --}}
@elseif(request('tab') === 'vacancies' && $staffRole === 'sra')

    {{-- A month, or every month. The Total Jobs card on the dashboard counts
         the whole thing, so it opens this list on "all months" — the number
         that was pressed and the list that answers it have to be the same
         number. --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <input type="month" class="form-control form-control-sm" style="max-width:220px;"
               value="{{ $vacancyMonth === 'all' ? '' : $vacancyMonth }}"
               onchange="changeVacancyMonth(this.value)">
        @if($vacancyMonth === 'all')
            <span style="font-size:11.5px;color:var(--n-500);">Showing every month.</span>
        @else
            <a href="{{ route('staff.reports', array_merge(request()->query(), ['tab' => 'vacancies', 'vacancy_month' => 'all', 'page' => 1])) }}"
               class="btn btn-sm fw-semibold"
               style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;
                      border-radius:8px;font-size:11.5px;padding:5px 12px;white-space:nowrap;">
                <i class="ph ph-x me-1"></i>All months
            </a>
        @endif
    </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Slots</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Requirements</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitedJobs as $i => $job)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $solicitedJobs->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">{{ $job->title }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-700);">{{ $job->slots }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $job->education_required ?? 'Any' }}, {{ $job->experience_months ?? 0 }} mo. exp.</td>
                        </tr>
                        @empty
                        {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                             gihapon ug ang pahina dili mag-usab ug porma. --}}
                        <tr>
                            <td colspan="5" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                <i class="ph ph-tray me-1"
                                   style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                No job vacancies solicited this month
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($solicitedJobs->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $solicitedJobs->firstItem() }}–{{ $solicitedJobs->lastItem() }} of {{ $solicitedJobs->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $solicitedJobs->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $solicitedJobs->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($solicitedJobs->getUrlRange(1, $solicitedJobs->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $solicitedJobs->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $solicitedJobs->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$solicitedJobs->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $solicitedJobs->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

{{-- ── TAB 4: JOB APPLICANTS REFERRED ── --}}
@elseif(request('tab') === 'employer_hires')

    @include('staff.reports._employer_hires')

@elseif(request('tab') === 'employer_report' && $staffRole === 'lra')

    @include('staff.reports._employer')

@elseif(request('tab') === 'referred')

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referredApplications ?? collect() as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ ($referredApplications ?? collect())->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;">
                                <div class="fw-semibold" style="color:var(--g-700);">{{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: 'None' }}</div>
                                <div style="font-size:11px;color:var(--n-500);">{{ $app->jobseeker->user->email ?? 'None' }}</div>
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                <span class="fw-semibold" style="color:{{ $app->status === 'waiting' ? 'var(--warn)' : 'var(--danger)' }};font-size:11px;">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:var(--n-500);">{{ $app->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                No referred applicant in this range.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(($referredApplications ?? collect())->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ ($referredApplications ?? collect())->firstItem() }}–{{ ($referredApplications ?? collect())->lastItem() }} of {{ ($referredApplications ?? collect())->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ ($referredApplications ?? collect())->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ ($referredApplications ?? collect())->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach(($referredApplications ?? collect())->getUrlRange(1, ($referredApplications ?? collect())->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $referredApplications->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $referredApplications->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$referredApplications->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $referredApplications->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

{{-- ── TAB 5: ARCHIVED JOB POSTINGS ── --}}
{{-- ── IN-HOUSE SCHEDULES ──
     Who was given a room and who was turned down. Both desks answer for their
     own side: the LRA for local employers, the SRA for overseas ones. --}}
@elseif(request('tab') === 'schedules')

    @php $ihRows = $inhouseReport ?? null; @endphp

    {{-- Drawn empty as well as full: the columns say what an in-house
         schedule record holds, and that is worth showing before the first
         request arrives. --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="inhouseReportTable">
                <thead>
                    <tr>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">#</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Company Name</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Requested Date</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Requested Time</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Confirmed Date</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Venue</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;text-align:center;">Applicants</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Schedule Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ihRows ?? collect() as $i => $sc)
                    <tr>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-500);">{{ $ihRows->firstItem() + $i }}</td>
                        <td style="font-size:13px;padding:12px 14px;font-weight:600;color:var(--g-700);">
                            {{ $sc->employer->company_name ?? 'None' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-700);">
                            {{ $sc->schedule_window_label ?? ($sc->preferred_date?->format('M d, Y') ?? 'Not set') }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-700);">
                            {{ $sc->preferred_time ? \Carbon\Carbon::parse($sc->preferred_time)->format('h:i A') : 'Not set' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-700);">
                            {{ $sc->confirmed_date ? \Carbon\Carbon::parse($sc->confirmed_date)->format('M d, Y') : '—' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-700);">
                            {{ $sc->venue_type === 'other' ? ($sc->venue_address ?: 'Other venue') : 'PESO Office' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;text-align:center;color:var(--n-700);">{{ $sc->num_applicants }}</td>
                        <td style="font-size:13px;padding:12px 14px;">
                            @php
                                // The office either gave them a day, refused
                                // one, or has not answered — say which.
                                [$scLabel, $scColor, $scIcon, $scNote] = match ($sc->status) {
                                    'accepted' => ['Accepted', 'var(--g-600)', 'ph-check-circle',
                                        $sc->confirmed_time
                                            ? 'at ' . \Carbon\Carbon::parse($sc->confirmed_time)->format('h:i A')
                                            : ''],
                                    'rejected' => ['Declined', 'var(--danger)', 'ph-x-circle',
                                        $sc->rejection_reason ?: 'No reason given'],
                                    default    => ['Waiting for a date', 'var(--warn)', 'ph-clock',
                                        'The office has not answered yet'],
                                };
                            @endphp
                            <span style="color:{{ $scColor }};font-weight:600;">
                                <i class="ph-fill {{ $scIcon }} me-1"></i>{{ $scLabel }}
                            </span>
                            @if($scNote)
                                <div style="font-size:11px;color:var(--n-500);margin-top:2px;">{{ $scNote }}</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center"
                            style="padding:26px 14px;color:var(--n-500);font-size:13px;">
                            No in-house schedule request in this range.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ihRows && $ihRows->total() > 0)
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div style="font-size:12px;color:var(--n-500);">
            Showing {{ $ihRows->firstItem() }}–{{ $ihRows->lastItem() }} of {{ $ihRows->total() }} request(s)
        </div>
        {{ $ihRows->links() }}
    </div>
    @endif

{{-- ── COMPANY INTERVIEWS (SRA) ──
     The overseas half of the same list the Job Vacancy desk keeps for local
     employers. The employer runs these at their own place, so this list is the
     only record the office has of them. --}}
@elseif(request('tab') === 'company_interview' && $staffRole === 'sra')

    @php $sraCi = $companyInterviews ?? null; @endphp

    @if(!$sraCi || $sraCi->total() === 0)
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="ph ph-video-camera" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No company interview from an overseas employer yet</div>
        </div>
    @else
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="sraCompanyInterviewTable">
                <thead>
                    <tr>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">#</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Company Name</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Job Title</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Interview Date</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Date Posted</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Application Deadline</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;text-align:center;">Slots</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Interview Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sraCi as $i => $ci)
                    <tr>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-500);">{{ $sraCi->firstItem() + $i }}</td>
                        <td style="font-size:13px;padding:12px 14px;font-weight:600;color:var(--g-700);">
                            {{ $ci->company->company_name ?? 'None' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-700);">{{ $ci->title }}</td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-700);">
                            {{ $ci->preferred_date?->format('M d, Y') ?? 'Not set' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-500);">
                            {{ $ci->created_at?->format('M d, Y') ?? '—' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;color:var(--n-500);">
                            {{ $ci->deadline?->format('M d, Y') ?? 'None' }}
                        </td>
                        <td style="font-size:13px;padding:12px 14px;text-align:center;color:var(--n-700);">{{ $ci->slots }}</td>
                        <td style="font-size:13px;padding:12px 14px;">
                            @php
                                [$sciLabel, $sciColor, $sciIcon] = match ($ci->posting_status) {
                                    'approved' => ['Approved', 'var(--g-600)',  'ph-check-circle'],
                                    'rejected' => ['Declined', 'var(--danger)', 'ph-x-circle'],
                                    default    => ['Pending',  'var(--warn)',   'ph-clock'],
                                };
                            @endphp
                            <span style="color:{{ $sciColor }};font-weight:600;">
                                <i class="ph-fill {{ $sciIcon }} me-1"></i>{{ $sciLabel }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div style="font-size:12px;color:var(--n-500);">
            Showing {{ $sraCi->firstItem() }}–{{ $sraCi->lastItem() }} of {{ $sraCi->total() }} interview(s)
        </div>
        {{ $sraCi->links() }}
    </div>
    @endif

@elseif($tab === 'archived')

    @include('staff.reports._archived')

@endif

@endif {{-- end LRA/SRA vs Job Fair --}}

@push('scripts')
<script>
    let searchTimer;

    function changeTopEmployersDate(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('top_employers_filter', 'monthly');
        url.searchParams.set('top_employers_month', value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function changeTopEmployersYear(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('top_employers_filter', 'yearly');
        url.searchParams.set('top_employers_year', value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function changeVacancyMonth(value) {
        const url = new URL(window.location.href);
        // Clearing the month box means every month, not "fall back to this one".
        url.searchParams.set('vacancy_month', value || 'all');
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function changeReportView(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('report_view', value);
        url.searchParams.set('tab', value === 'jobfair' ? 'summary' : 'registered');
        window.location.href = url.toString();
    }

    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });

    function changeEvent(eventId) {
        const url = new URL(window.location.href);
        url.searchParams.set('event_id', eventId);
        window.location.href = url.toString();
    }

    function changeAttendanceFilter(type) {
        changeAttendanceParam('attendance_filter', type);
    }

    function changeAttendanceParam(key, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    let attendanceSearchTimer;
    document.getElementById('attendanceSearchInput')?.addEventListener('input', function() {
        clearTimeout(attendanceSearchTimer);
        const value = this.value.trim();
        attendanceSearchTimer = setTimeout(() => changeAttendanceParam('attendance_search', value), 500);
    });

    function changeRegisteredView(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('registered_view', val);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }
</script>
@endpush

@endsection