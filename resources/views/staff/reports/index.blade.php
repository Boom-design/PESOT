@extends($layout ?? 'staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-bar-chart-fill me-2" style="color:#4dd9c0;"></i>
        @if($staffRole === 'lra') Local
        @elseif($staffRole === 'sra') Overseas
        @else Job Fair
        @endif Reports
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        @if($staffRole === 'job_fair')
            Overall job fair event success rate
        @else
            Job applicants registered, placed, and referred
        @endif
    </p>
</div>

@php
$tabs = [
    'attendance'         => ['icon' => 'bi-clipboard-check-fill', 'label' => 'Attendance'],
    'companies'          => ['icon' => 'bi-building-fill', 'label' => $staffRole === 'sra' ? 'Overseas Companies' : 'Local Companies'],
    'further_interview'  => ['icon' => 'bi-person-lines-fill', 'label' => 'Further Interview'],
    'hots'               => ['icon' => 'bi-lightning-charge-fill', 'label' => 'Hired on the Spot'],
    'summary'            => ['icon' => 'bi-clipboard-data-fill', 'label' => 'Post Job Fair Summary'],
    'industry'           => ['icon' => 'bi-diagram-3-fill', 'label' => 'Companies w/ Vacancies'],
];
if ($staffRole !== 'sra') {
    $tabs['placement'] = ['icon' => 'bi-briefcase-fill', 'label' => 'Company Placement'];
}
@endphp

@if(($layout ?? '') === 'admin.layouts.app')
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
            <div class="fw-semibold mb-2" style="color:#2d7a5f;font-size:14px;">
                <i class="bi bi-briefcase-fill me-2"></i>Job Solicitation Statistics
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3" style="background:#f0fdf9;border:1px solid #a8e6cf;">
                        <div class="fw-bold" style="font-size:24px;color:#2d7a5f;">{{ $solicitationStats['lra'] ?? 0 }}</div>
                        <div class="small" style="color:#4dd9c0;">LRA Job Solicitation</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3" style="background:#f0fdf9;border:1px solid #a8e6cf;">
                        <div class="fw-bold" style="font-size:24px;color:#2d7a5f;">{{ $solicitationStats['sra'] ?? 0 }}</div>
                        <div class="small" style="color:#4dd9c0;">SRA Job Solicitation</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3" style="background:#f0fdf9;border:1px solid #a8e6cf;">
                        <div class="fw-bold" style="font-size:24px;color:#2d7a5f;">{{ $solicitationStats['overall'] ?? 0 }}</div>
                        <div class="small" style="color:#4dd9c0;">Overall Job Solicitation</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'lra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="{{ $staffRole === 'lra' ? 'background:linear-gradient(135deg,#90d870,#4dd9c0);' : 'background:#f0f9f6;' }}">
                <i class="bi bi-person-lines-fill mb-2" style="font-size:26px;color:{{ $staffRole === 'lra' ? '#fff' : '#4dd9c0' }};"></i>
                <div class="fw-semibold" style="font-size:13px;color:{{ $staffRole === 'lra' ? '#fff' : '#2d7a5f' }};">LRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'sra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="{{ $staffRole === 'sra' ? 'background:linear-gradient(135deg,#90d870,#4dd9c0);' : 'background:#f0f9f6;' }}">
                <i class="bi bi-globe mb-2" style="font-size:26px;color:{{ $staffRole === 'sra' ? '#fff' : '#4dd9c0' }};"></i>
                <div class="fw-semibold" style="font-size:13px;color:{{ $staffRole === 'sra' ? '#fff' : '#2d7a5f' }};">SRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'job_fair']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="{{ $staffRole === 'job_fair' ? 'background:linear-gradient(135deg,#90d870,#4dd9c0);' : 'background:#f0f9f6;' }}">
                <i class="bi bi-calendar-event-fill mb-2" style="font-size:26px;color:{{ $staffRole === 'job_fair' ? '#fff' : '#4dd9c0' }};"></i>
                <div class="fw-semibold" style="font-size:13px;color:{{ $staffRole === 'job_fair' ? '#fff' : '#2d7a5f' }};">Job Fair Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staffJobVacancy') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:#f0f9f6;">
                <i class="bi bi-briefcase-fill mb-2" style="font-size:26px;color:#4dd9c0;"></i>
                <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">Job Vacancy Reports</div>
            </div>
        </a>
    </div>
</div>
@endif

@if($staffRole === 'sra')
<div class="mb-3">
    <select id="reportViewSelector" class="form-select form-select-sm" style="max-width:260px;border-color:#a8e6cf;font-size:13px;" onchange="changeReportView(this.value)">
        <option value="staff" {{ ($reportView ?? 'staff') === 'staff' ? 'selected' : '' }}>Overseas Reports</option>
        <option value="jobfair" {{ ($reportView ?? 'staff') === 'jobfair' ? 'selected' : '' }}>Job Fair Reports (Overseas)</option>
    </select>
</div>
@endif

{{-- JOB FAIR STAFF — 7 TABS (SRA: overseas-filtered, walay Placement tab) --}}
@if($staffRole === 'job_fair' || ($staffRole === 'sra' && ($reportView ?? 'staff') === 'jobfair'))

{{-- EVENT SELECTOR --}}
<div class="mb-3">
    <select id="eventSelector" class="form-select form-select-sm"
        style="max-width:320px;width:100%;border-color:#a8e6cf;font-size:13px;"
        onchange="changeEvent(this.value)">
        <option value="">— Select Job Fair Event —</option>
        @foreach($allEvents as $ev)
        <option value="{{ $ev->job_fair_events_id }}" {{ $eventId == $ev->job_fair_events_id ? 'selected' : '' }}>
            {{ $ev->title }} ({{ $ev->event_date->format('M d, Y') }})
        </option>
        @endforeach
    </select>
</div>

{{-- TABS NAV --}}
<div class="d-flex gap-2 mb-4" style="flex-wrap:wrap;">
    @foreach($tabs as $key => $t)
    <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => $key, 'event_id' => $eventId])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === $key
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
        <i class="bi {{ $t['icon'] }} me-1"></i>{{ $t['label'] }}
    </a>
    @endforeach
    <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'top_employers', 'event_id' => $eventId])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'top_employers'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-building-fill me-1"></i>Top Employers
    </a>
</div>

@if(!$eventId)
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-calendar-event" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">Select a job fair event to view reports</div>
    </div>

@else

    {{-- ── TAB 1: ATTENDANCE ── --}}
    @if($tab === 'attendance')

        @if($staffRole !== 'sra')
        <div class="d-flex flex-wrap gap-2 mb-3">
            <select id="attFilter" class="form-select form-select-sm"
                style="max-width:180px;border-color:#a8e6cf;font-size:13px;"
                onchange="changeAttendanceFilter(this.value)">
                <option value="all" {{ $attendanceFilter === 'all' ? 'selected' : '' }}>All (Local + Overseas)</option>
                <option value="local" {{ $attendanceFilter === 'local' ? 'selected' : '' }}>Local Only</option>
                <option value="overseas" {{ $attendanceFilter === 'overseas' ? 'selected' : '' }}>Overseas Only</option>
            </select>
        </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalRegistered }}</div>
                    <div class="text-muted small">Registered</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $totalAttended }}</div>
                    <div class="text-muted small">Attended</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalLocalAttendance }}</div>
                    <div class="text-muted small">Local</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#8b5cf6;">{{ $totalOverseasAttendance }}</div>
                    <div class="text-muted small">Overseas</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Slip No.</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Type</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $i => $r)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $registrations->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $r->slip_number }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ trim(($r->jobseeker->first_name ?? '').' '.($r->jobseeker->surname ?? '')) ?: 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">
                                {{ ucfirst($r->jobseeker->nsrp->type ?? 'None') }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @if($r->is_attended)
                                    <span class="badge fw-semibold" style="background:#2d7a5f;font-size:11px;padding:4px 10px;border-radius:20px;">Attended</span>
                                @else
                                    <span class="badge fw-semibold" style="background:#f59e0b;font-size:11px;padding:4px 10px;border-radius:20px;">Not Yet</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4" style="color:#888;font-size:13px;">No registrations found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ── TAB 2: LOCAL/OVERSEAS COMPANIES ── --}}
    @elseif($tab === 'companies')

        <div class="row g-2 mb-3">
            @if($staffRole !== 'sra')
            <div class="col-12 col-md-6">
                <h6 class="fw-bold" style="color:#2d7a5f;font-size:13px;">Local Companies</h6>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">#</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">Company</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($companiesLocal as $i => $p)
                                <tr style="font-size:12px;">
                                    <td style="padding:10px 14px;color:#888;">{{ $i+1 }}</td>
                                    <td style="padding:10px 14px;font-weight:600;color:#2d7a5f;">{{ $p->employer->company_name ?? 'None' }}</td>
                                    <td style="padding:10px 14px;color:#555;">{{ $p->employer->est_barangay ?? '' }} {{ $p->employer->est_city_municipality ?? '' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-3" style="color:#888;font-size:12px;">None yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-12 col-md-6">
                <h6 class="fw-bold" style="color:#2d7a5f;font-size:13px;">Overseas Companies</h6>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">#</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">Company</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($companiesOverseas as $i => $p)
                                <tr style="font-size:12px;">
                                    <td style="padding:10px 14px;color:#888;">{{ $i+1 }}</td>
                                    <td style="padding:10px 14px;font-weight:600;color:#2d7a5f;">{{ $p->employer->company_name ?? 'None' }}</td>
                                    <td style="padding:10px 14px;color:#555;">{{ $p->employer->est_barangay ?? '' }} {{ $p->employer->est_city_municipality ?? '' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-3" style="color:#888;font-size:12px;">None yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    {{-- ── TAB 3: FURTHER INTERVIEW ── --}}
    @elseif($tab === 'further_interview')

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Type</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Applied</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($furtherInterview as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $furtherInterview->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ trim(($app->jobseeker->first_name ?? '').' '.($app->jobseeker->surname ?? '')) ?: 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">
                                {{ ucfirst($app->jobseeker->nsrp->type ?? 'None') }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#888;">{{ $app->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4" style="color:#888;font-size:13px;">No applicants on waiting list</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($furtherInterview && $furtherInterview->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $furtherInterview->firstItem() }}–{{ $furtherInterview->lastItem() }} of {{ $furtherInterview->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $furtherInterview->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $furtherInterview->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        @foreach($furtherInterview->getUrlRange(1, $furtherInterview->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $furtherInterview->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $furtherInterview->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$furtherInterview->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $furtherInterview->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
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
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Name</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Gender</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Position</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Hiring Company</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Local/Overseas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hots as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $hots->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ trim(($app->jobseeker->first_name ?? '').' '.($app->jobseeker->surname ?? '')) ?: 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $app->jobseeker->sex ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">
                                {{ ($app->job->company->is_overseas ?? false) ? 'Overseas' : 'Local' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4" style="color:#888;font-size:13px;">No one hired on the spot yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($hots && $hots->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $hots->firstItem() }}–{{ $hots->lastItem() }} of {{ $hots->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $hots->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $hots->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        @foreach($hots->getUrlRange(1, $hots->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $hots->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $hots->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$hots->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $hots->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
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
                    <div class="fs-4 fw-bold" style="color:#4dd9c0;">{{ $summaryTotals['vacancies'] }}</div>
                    <div class="text-muted small">Vacancies</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:#2d7a5f;">{{ $summaryTotals['interviewed'] }}</div>
                    <div class="text-muted small">Interviewed</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:#f59e0b;">{{ $summaryTotals['male'] }}</div>
                    <div class="text-muted small">Male</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:#8b5cf6;">{{ $summaryTotals['female'] }}</div>
                    <div class="text-muted small">Female</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:#0ea5e9;">{{ $summaryTotals['qualified'] }}</div>
                    <div class="text-muted small">Qualified</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-4 fw-bold" style="color:#2d7a5f;">{{ $summaryTotals['hired'] }}</div>
                    <div class="text-muted small">Hired</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Employer</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Vacancies</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Interviewed</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Male</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Female</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Qualified</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryParticipants as $i => $p)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $i+1 }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">{{ $p->employer->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $p->vacancies }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $p->interviewed }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $p->male }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $p->female }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $p->qualified }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#2d7a5f;font-weight:600;">{{ $p->hired }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4" style="color:#888;font-size:13px;">No confirmed employers for this event</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ── TAB 6: TOTAL COMPANIES WITH VACANCIES (Industry Group) ── --}}
    @elseif($tab === 'industry')

        <div class="row g-2 mb-3">
            @if($staffRole !== 'sra')
            <div class="col-12 col-md-6">
                <h6 class="fw-bold" style="color:#2d7a5f;font-size:13px;">Local — by Industry Group</h6>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">Industry Group</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;text-align:center;">Total Vacancies</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($industryLocal as $group => $total)
                                <tr style="font-size:12px;">
                                    <td style="padding:10px 14px;color:#2d7a5f;font-weight:600;">{{ $group ?: 'Uncategorized' }}</td>
                                    <td style="padding:10px 14px;text-align:center;color:#555;">{{ $total }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center py-3" style="color:#888;font-size:12px;">No data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-12 col-md-6">
                <h6 class="fw-bold" style="color:#2d7a5f;font-size:13px;">Overseas — by Industry Group</h6>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;">Industry Group</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 14px;text-align:center;">Total Vacancies</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($industryOverseas as $group => $total)
                                <tr style="font-size:12px;">
                                    <td style="padding:10px 14px;color:#2d7a5f;font-weight:600;">{{ $group ?: 'Uncategorized' }}</td>
                                    <td style="padding:10px 14px;text-align:center;color:#555;">{{ $total }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center py-3" style="color:#888;font-size:12px;">No data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    {{-- ── TAB 7: TOP EMPLOYERS ── --}}
    @elseif($tab === 'top_employers')

        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
            <div class="fw-semibold mb-2" style="color:#2d7a5f;font-size:14px;">
                <i class="bi bi-building-fill me-2"></i>Top 5 Employers by Job Fair Participation
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <div class="btn-group btn-group-sm" role="group" aria-label="Top employers filter">
                    <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'top_employers', 'event_id' => $eventId, 'top_employers_filter' => 'monthly', 'page' => 1])) }}"
                       class="btn {{ ($topEmployersFilter ?? 'monthly') === 'monthly' ? 'btn-success' : 'btn-outline-success' }}"
                       style="font-size:12px;">Monthly</a>
                    <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'top_employers', 'event_id' => $eventId, 'top_employers_filter' => 'yearly', 'page' => 1])) }}"
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
            @php($topEmployers = $topEmployersByOfficeBasedInterviews ?? collect())
            @if($topEmployers->isEmpty())
                <div class="text-muted small">No job fair participation data found for this period.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="background:#f0fdf9;color:#2d7a5f;border:none;padding:8px 10px;">#</th>
                                <th style="background:#f0fdf9;color:#2d7a5f;border:none;padding:8px 10px;">Employer</th>
                                <th style="background:#f0fdf9;color:#2d7a5f;border:none;padding:8px 10px;text-align:center;">Job Fair Participations</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topEmployers as $index => $entry)
                                <tr style="font-size:13px;">
                                    <td style="padding:8px 10px;color:#888;">{{ $index + 1 }}</td>
                                    <td style="padding:8px 10px;color:#2d7a5f;font-weight:600;">{{ $entry['employer']->company_name ?? 'Unknown Employer' }}</td>
                                    <td style="padding:8px 10px;text-align:center;color:#555;">{{ $entry['participation_count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    {{-- ── TAB 8: COMPANY PLACEMENT REPORT ── --}}
    @else

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Name of Applicant</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Gender</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Position</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($placementReport as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $placementReport->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ trim(($app->jobseeker->first_name ?? '').' '.($app->jobseeker->surname ?? '')) ?: 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $app->jobseeker->sex ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#888;">{{ $app->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4" style="color:#888;font-size:13px;">No placements recorded yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($placementReport && $placementReport->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $placementReport->firstItem() }}–{{ $placementReport->lastItem() }} of {{ $placementReport->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $placementReport->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $placementReport->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        @foreach($placementReport->getUrlRange(1, $placementReport->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $placementReport->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $placementReport->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$placementReport->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $placementReport->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

    @endif

@endif

@else

{{-- LRA/SRA — 3 TABS (with inline counts, no separate stat cards) --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;max-width:100%;">
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'registered', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab','registered') === 'registered'
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="bi bi-person-check me-1"></i> Job Applicant Registered ({{ $totalRegistered }})
        </a>
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'placed', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'placed'
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }} 
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="bi bi-briefcase-fill me-1"></i> Placed Applicants ({{ $totalPlaced }})
        </a>
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'referred', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'referred'
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="bi bi-people-fill me-1"></i> Job Applicants Referred ({{ $totalReferred }})
        </a>
        @if($staffRole === 'sra')
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'vacancies', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'vacancies'
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="bi bi-briefcase-fill me-1"></i> Job Vacancies Solicited ({{ $totalVacanciesSolicited ?? 0 }})
        </a>
        @endif
        <a href="{{ route($reportRouteName ?? 'staff.reports', array_merge(request()->query(), ['tab' => 'top_employers', 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('tab') === 'top_employers'
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            <i class="bi bi-building-fill me-1"></i> Top Employers
        </a>
    </div>
    <div class="input-group" style="max-width:260px;width:100%;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search name or email..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- ── TAB 1: JOB APPLICANT REGISTERED ── --}}
@if(request('tab', 'registered') === 'registered')

    {{-- FILTER DROPDOWN: All Local Jobseekers vs In-house Participants --}}
    <div class="mb-3">
        <select id="registeredViewFilter" class="form-select form-select-sm"
            style="max-width:280px;width:100%;border-color:#a8e6cf;font-size:13px;"
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

        @if(($registeredAll ?? collect())->isEmpty())
            <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
                <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
                <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No registered jobseekers found</div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                                <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                                <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Email</th>
                                <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registeredAll as $i => $reg)
                            <tr style="font-size:13px;">
                                <td style="padding:12px 16px;color:#888;">{{ $registeredAll->firstItem() + $i }}</td>
                                <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                    {{ trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? '')) ?: ($reg->user->name ?? 'None') }}
                                </td>
                                <td style="padding:12px 16px;color:#555;">
                                    {{ $reg->reg_email ?? $reg->user->email ?? 'None' }}
                                </td>
                                <td style="padding:12px 16px;text-align:center;color:#888;">
                                    {{ $reg->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($registeredAll->hasPages())
                <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                    <div style="font-size:12px;color:#888;">
                        Showing {{ $registeredAll->firstItem() }}–{{ $registeredAll->lastItem() }} of {{ $registeredAll->total() }} results
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1">
                            <li class="page-item {{ $registeredAll->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $registeredAll->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            @foreach($registeredAll->getUrlRange(1, $registeredAll->lastPage()) as $page => $url)
                            <li class="page-item {{ $page == $registeredAll->currentPage() ? 'active' : '' }}">
                                <a class="page-link rounded-2"
                                   style="{{ $page == $registeredAll->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                                   href="{{ $url }}">{{ $page }}</a>
                            </li>
                            @endforeach
                            <li class="page-item {{ !$registeredAll->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $registeredAll->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        @endif

    @elseif($registeredParticipants->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No registered applicants found</div>
            <div class="text-muted small mt-1">Jobseekers who accepted a confirmed in-house interview will appear here.</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Employer</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Interview Date</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Accepted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registeredParticipants as $i => $p)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $registeredParticipants->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;">
                                <div class="fw-semibold" style="color:#2d7a5f;">{{ trim(($p->jobseeker->first_name ?? '') . ' ' . ($p->jobseeker->surname ?? '')) ?: 'None' }}</div>
                                <div style="font-size:11px;color:#888;">{{ $p->jobseeker->reg_email ?? $p->jobseeker->user->email ?? 'None' }}</div>
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $p->job->company->company_name ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $p->job->preferred_date ? \Carbon\Carbon::parse($p->job->preferred_date)->format('M d, Y') : 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#888;">
                                {{ $p->updated_at?->format('M d, Y h:i A') ?? 'None' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($registeredParticipants->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $registeredParticipants->firstItem() }}–{{ $registeredParticipants->lastItem() }} of {{ $registeredParticipants->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $registeredParticipants->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $registeredParticipants->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        @foreach($registeredParticipants->getUrlRange(1, $registeredParticipants->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $registeredParticipants->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $registeredParticipants->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$registeredParticipants->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $registeredParticipants->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif

{{-- ── TAB 2: PLACED APPLICANTS ── --}}
@elseif(request('tab') === 'placed')

    @if($placedApplications->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No placed applicants found</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Name of Applicant</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Gender</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Referred As</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Referred To</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Placed As</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Placed To</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($placedApplications as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $placedApplications->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;">
                                <div class="fw-semibold" style="color:#2d7a5f;">{{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: 'None' }}</div>
                                <div style="font-size:11px;color:#888;">{{ $app->jobseeker->user->email ?? 'None' }}</div>
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">
                                {{ $app->jobseeker->sex ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#888;">{{ $app->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($placedApplications->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $placedApplications->firstItem() }}–{{ $placedApplications->lastItem() }} of {{ $placedApplications->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $placedApplications->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $placedApplications->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        @foreach($placedApplications->getUrlRange(1, $placedApplications->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $placedApplications->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $placedApplications->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$placedApplications->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $placedApplications->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif

{{-- ── TAB 3: TOP EMPLOYERS ── --}}
@elseif(request('tab') === 'top_employers')

    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
        <div class="fw-semibold mb-2" style="color:#2d7a5f;font-size:14px;">
            <i class="bi bi-building-fill me-2"></i>Top 5 Employers by In-House Interviews
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
        @php($topEmployers = $topEmployersByOfficeBasedInterviews ?? collect())
        @if($topEmployers->isEmpty())
            <div class="text-muted small">No in-house interview data found for this period.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="background:#f0fdf9;color:#2d7a5f;border:none;padding:8px 10px;">#</th>
                            <th style="background:#f0fdf9;color:#2d7a5f;border:none;padding:8px 10px;">Employer</th>
                            <th style="background:#f0fdf9;color:#2d7a5f;border:none;padding:8px 10px;text-align:center;">In-House Interviews</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topEmployers as $index => $entry)
                            <tr style="font-size:13px;">
                                <td style="padding:8px 10px;color:#888;">{{ $index + 1 }}</td>
                                <td style="padding:8px 10px;color:#2d7a5f;font-weight:600;">{{ $entry['employer']->company_name ?? 'Unknown Employer' }}</td>
                                <td style="padding:8px 10px;text-align:center;color:#555;">{{ $entry['interview_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

{{-- ── TAB: JOB VACANCIES SOLICITED (SRA ra) ── --}}
@elseif(request('tab') === 'vacancies' && $staffRole === 'sra')

    <div class="mb-3">
        <input type="month" class="form-control form-control-sm" style="max-width:220px;" value="{{ $vacancyMonth }}" onchange="changeVacancyMonth(this.value)">
    </div>

    @if($solicitedJobs->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job vacancies solicited this month</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Slots</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Requirements</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitedJobs as $i => $job)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $solicitedJobs->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">{{ $job->title }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">{{ $job->slots }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $job->education_required ?? 'Any' }}, {{ $job->experience_months ?? 0 }} mo. exp.</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($solicitedJobs->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $solicitedJobs->firstItem() }}–{{ $solicitedJobs->lastItem() }} of {{ $solicitedJobs->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $solicitedJobs->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $solicitedJobs->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        @foreach($solicitedJobs->getUrlRange(1, $solicitedJobs->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $solicitedJobs->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $solicitedJobs->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$solicitedJobs->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $solicitedJobs->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif

{{-- ── TAB 4: JOB APPLICANTS REFERRED ── --}}
@elseif(request('tab') === 'referred')

    @if(($referredApplications ?? collect())->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No referred applicants found</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($referredApplications ?? collect() as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ ($referredApplications ?? collect())->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;">
                                <div class="fw-semibold" style="color:#2d7a5f;">{{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: 'None' }}</div>
                                <div style="font-size:11px;color:#888;">{{ $app->jobseeker->user->email ?? 'None' }}</div>
                            </td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->title ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                <span class="badge fw-semibold"
                                    style="background:{{ $app->status === 'waiting' ? '#f59e0b' : '#e05252' }};
                                           font-size:11px;padding:4px 10px;border-radius:20px;">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#888;">{{ $app->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(($referredApplications ?? collect())->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ ($referredApplications ?? collect())->firstItem() }}–{{ ($referredApplications ?? collect())->lastItem() }} of {{ ($referredApplications ?? collect())->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ ($referredApplications ?? collect())->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ ($referredApplications ?? collect())->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        @foreach(($referredApplications ?? collect())->getUrlRange(1, ($referredApplications ?? collect())->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $referredApplications->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $referredApplications->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$referredApplications->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $referredApplications->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif

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
        url.searchParams.set('vacancy_month', value);
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
        const url = new URL(window.location.href);
        url.searchParams.set('attendance_filter', type);
        window.location.href = url.toString();
    }

    function changeRegisteredView(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('registered_view', val);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }
</script>
@endpush

@endsection