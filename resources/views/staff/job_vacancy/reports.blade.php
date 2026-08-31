@extends($layout ?? 'staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-chart-bar me-2" style="color:var(--g-600);"></i>
        Job Vacancies Solicited Report
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        List of approved job vacancies solicited from local employers
    </p>
</div>

@if(($layout ?? '') === 'admin.layouts.app')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'lra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:var(--n-50);">
                <i class="ph-fill ph-user-list mb-2" style="font-size:26px;color:var(--g-600);"></i>
                <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">LRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'sra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:var(--n-50);">
                <i class="ph ph-globe mb-2" style="font-size:26px;color:var(--g-600);"></i>
                <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">SRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'job_fair']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:var(--n-50);">
                <i class="ph-fill ph-calendar-dots mb-2" style="font-size:26px;color:var(--g-600);"></i>
                <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">Job Fair Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staffJobVacancy') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:var(--g-600);">
                <i class="ph-fill ph-briefcase mb-2" style="font-size:26px;color:#fff;"></i>
                <div class="fw-semibold" style="font-size:13px;color:#fff;">Job Vacancy Reports</div>
            </div>
        </a>
    </div>
</div>
@endif

{{-- TABS --}}
<div class="d-flex gap-2 mb-3" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <a href="{{ route($reportRouteName ?? 'staff.reports.employers', array_merge(request()->query(), ['tab' => 'vacancies'])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ ($tab ?? 'vacancies') === 'vacancies'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-briefcase me-1"></i>Job Vacancies
    </a>
    {{-- The desk owns company interviews too, and until now no report said so. --}}
    <a href="{{ route($reportRouteName ?? 'staff.reports.employers', array_merge(request()->query(), ['tab' => 'company_interview', 'page' => 1])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ ($tab ?? 'vacancies') === 'company_interview'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-video-camera me-1"></i>Company Interviews
    </a>
    <a href="{{ route($reportRouteName ?? 'staff.reports.employers', array_merge(request()->query(), ['tab' => 'top_employers'])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ ($tab ?? 'vacancies') === 'top_employers'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-buildings me-1"></i>Top 10 Employers
    </a>
    {{-- Ang report sa opisina nga gi-upload. Kaugalingon niyang tab kay lahi
         siya nga report — walay laray dinhi nga giapil sa numero sa sistema. --}}
    @unless(isset($reportRouteName))
    <a href="{{ route('staff.reports.employers', ['tab' => 'imported']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ ($tab ?? 'vacancies') === 'imported'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:11px;padding:5px 12px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-upload-simple me-1"></i>Imported
    </a>
    @endunless
</div>

@if(($tab ?? 'vacancies') === 'vacancies')

{{-- MONTH FILTER + SEARCH --}}
<form method="GET" action="{{ route($reportRouteName ?? 'staff.reports.employers') }}" class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <label class="fw-semibold" style="font-size:12px;color:var(--g-700);">Month:</label>
        <input type="month" name="month" value="{{ $month }}"
            class="form-control form-control-sm"
            style="border:1px solid var(--n-200);border-radius:8px;font-size:12px;width:auto;"
            onchange="this.form.submit()">
        {{-- Ang gi-download mao gyud ang gipakita nga laray. --}}
        @unless(isset($reportRouteName))
        <a href="{{ route('staff.reports.employers.export', array_filter(['month' => $month, 'search' => $search])) }}"
           class="btn btn-sm fw-semibold"
           style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;white-space:nowrap;">
            <i class="ph-fill ph-download-simple me-1"></i> Export Excel
        </a>
        @endunless
    </div>
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" name="search" class="form-control"
            placeholder="Search job title or employer..."
            style="border-color:var(--n-200);font-size:13px;outline:none;box-shadow:none;"
            autocomplete="off"
            value="{{ $search }}">
    </div>
</form>

{{-- STAT CARD --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $totalVacancies }}</div>
            <div class="text-muted small">Total No. of Vacancies</div>
        </div>
    </div>
</div>

{{-- TABLE --}}
@if($jobs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-tray" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No job vacancies solicited for this month</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="p-3" style="border-bottom:1px solid var(--n-50);">
            <div class="fw-bold text-center" style="color:var(--g-700);font-size:14px;">JOB VACANCIES SOLICITED</div>
            <div class="text-center" style="font-size:12px;color:var(--n-500);">
                For the month of {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;">No.</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;">Job Title</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">No. of Vacancies</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">Age</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">Sex</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">Civil Status</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;">Educational Attainment</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;">Work Experience</th>
                        <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:10px 12px;">Employer Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $job)
                    <tr style="font-size:12px;">
                        <td style="padding:10px 12px;color:var(--n-500);">{{ $jobs->firstItem() + $loop->index }}</td>
                        <td style="padding:10px 12px;font-weight:600;color:var(--g-700);">
                            {{ strtoupper($job->title) }}
                        </td>
                        {{-- The count opens that employer's whole posting history.
                             This column is where the desk first notices a company
                             that has gone quiet, and the question it raises — when
                             did they last post? — is the one the inactivity rule
                             turns on. Answering it here saves a trip to Employers
                             and a guess. --}}
                        <td style="padding:10px 12px;text-align:center;">
                            @if($job->company)
                            <button type="button" class="btn btn-sm fw-semibold p-0"
                                style="background:none;border:none;color:var(--g-600);
                                       text-decoration:underline;font-size:12px;"
                                data-bs-toggle="modal"
                                data-bs-target="#solicitedModal{{ $job->company->employer_nsrp_registrations_id }}"
                                title="See every vacancy from this employer">
                                {{ $job->slots }}
                            </button>
                            @else
                                <span style="color:var(--n-700);">{{ $job->slots }}</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:var(--n-700);">
                            @if($job->age_required && $job->age_min && $job->age_max)
                                {{ $job->age_min }}-{{ $job->age_max }}
                            @else
                                None
                            @endif
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:var(--n-700);">
                            @php
                                $sexMap = ['Any' => 'M/F', 'Male' => 'M', 'Female' => 'F'];
                            @endphp
                            {{ $sexMap[$job->sex_preference] ?? ($job->sex_preference ?? 'None') }}
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:var(--n-700);">
                            {{ $job->civil_status && $job->civil_status !== 'Any' ? strtoupper($job->civil_status) : 'None' }}
                        </td>
                        <td style="padding:10px 12px;color:var(--n-700);">
                            {{ $job->education_required ? strtoupper($job->education_required) : 'None' }}
                        </td>
                        <td style="padding:10px 12px;color:var(--n-700);">
                            @if($job->experience_months)
                                {{ $job->experience_months }} mo/s
                            @else
                                None
                            @endif
                        </td>
                        <td style="padding:10px 12px;color:var(--n-700);">
                            {{ strtoupper($job->company->company_name ?? 'None') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid var(--n-50);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }}
                of {{ $jobs->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $jobs->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $jobs->previousPageUrl() }}">
                            <i class="ph ph-caret-left"></i>
                        </a>
                    </li>
                    @foreach($jobs->getUrlRange(1, $jobs->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $jobs->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $jobs->currentPage()
                                ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                : 'border-color:var(--n-200);color:var(--g-700);' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$jobs->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $jobs->nextPageUrl() }}">
                            <i class="ph ph-caret-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

@elseif(($tab ?? 'vacancies') === 'top_employers')

{{-- TOP EMPLOYERS TAB --}}
<div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
    <div class="fw-semibold mb-2" style="color:var(--g-700);font-size:14px;">
        <i class="ph-fill ph-buildings me-2"></i>Top 10 Employers by Total Job Vacancies
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="btn-group btn-group-sm" role="group" aria-label="Top employers filter">
            <a href="{{ route($reportRouteName ?? 'staff.reports.employers', array_merge(request()->query(), ['tab' => 'top_employers', 'top_employers_filter' => 'monthly', 'page' => 1])) }}"
               class="btn {{ ($topEmployersFilter ?? 'monthly') === 'monthly' ? 'btn-success' : 'btn-outline-success' }}"
               style="font-size:12px;">Monthly</a>
            <a href="{{ route($reportRouteName ?? 'staff.reports.employers', array_merge(request()->query(), ['tab' => 'top_employers', 'top_employers_filter' => 'yearly', 'page' => 1])) }}"
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
    {{-- Block form on purpose. The one-line variant with the expression in
         parentheses stopped being recognised here and compiled to an opening
         PHP tag with no closing one, which swallowed the rest of the file:
         from this point down, the Top 10 table and every tab after it
         rendered as raw Blade text instead of a page. --}}
    @php $topEmployers = $topEmployers ?? collect(); @endphp
    @if(($topEmployersMonth ?? '') === '' && ($topEmployersYear ?? '') === '')
        @php $topEmployersMonth = now()->format('Y-m'); @endphp
    @endif
    @if($topEmployers->isEmpty())
        <div class="text-muted small">No job vacancies solicited in this period.</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;">#</th>
                        <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;">Employer</th>
                        <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;text-align:center;">Total Vacancies</th>
                        <th style="background:var(--n-50);color:var(--g-700);border:none;padding:8px 10px;text-align:center;">Postings</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topEmployers as $index => $entry)
                        <tr style="font-size:13px;">
                            <td style="padding:8px 10px;color:var(--n-500);">{{ $index + 1 }}</td>
                            <td style="padding:8px 10px;color:var(--g-700);font-weight:600;">{{ $entry['employer']->company_name ?? 'Unknown Employer' }}</td>
                            <td style="padding:8px 10px;text-align:center;color:var(--g-700);font-weight:700;">{{ $entry['total_vacancies'] }}</td>
                            <td style="padding:8px 10px;text-align:center;color:var(--n-700);">{{ $entry['posting_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@elseif(($tab ?? 'vacancies') === 'company_interview')

{{-- COMPANY INTERVIEW TAB
     The other half of this desk. A company interview is run by the employer at
     their own place, so the desk never sees it happen — the only record of it
     is this list, and the only outcome worth reporting is what became of the
     applicants: taken on, turned away, or still waiting to hear. --}}
<form method="GET" action="{{ route($reportRouteName ?? 'staff.reports.employers') }}" class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <input type="hidden" name="tab" value="company_interview">
    <div class="d-flex align-items-center gap-2">
        <label class="fw-semibold" style="font-size:12px;color:var(--g-700);">Interview month:</label>
        <input type="month" name="month" value="{{ $month }}"
            class="form-control form-control-sm"
            style="border:1px solid var(--n-200);border-radius:8px;font-size:12px;width:auto;"
            onchange="this.form.submit()">
    </div>
    <div class="input-group input-group-sm" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" name="search" value="{{ $search }}" class="form-control"
            placeholder="Company or job title..." style="border-color:var(--n-200);font-size:12px;">
    </div>
</form>

@php $ciRows = $companyInterviews ?? null; @endphp

@if(!$ciRows || $ciRows->total() === 0)
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-video-camera" style="font-size:44px;color:var(--n-200);"></i>
        <p class="text-muted mt-3 mb-0" style="font-size:13px;">
            No company interview was scheduled for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}.
        </p>
    </div>
@else
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">#</th>
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Company Name</th>
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Job Title</th>
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Interview Date</th>
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Date Posted</th>
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Application Deadline</th>
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;text-align:center;">Slots</th>
                    {{-- "Status" alone did not say whose. It is the state of
                         this company interview, not of an applicant on it. --}}
                    <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 14px;">Interview Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ciRows as $i => $ci)
                <tr>
                    <td style="font-size:13px;padding:12px 14px;color:var(--n-500);">
                        {{ $ciRows->firstItem() + $i }}
                    </td>
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
                            // The interview itself, approved or declined — not
                            // a tally of what happened to each applicant.
                            [$ciLabel, $ciColor, $ciIcon] = match ($ci->posting_status) {
                                'approved' => ['Approved', 'var(--g-600)',  'ph-check-circle'],
                                'rejected' => ['Declined', 'var(--danger)', 'ph-x-circle'],
                                default    => ['Pending',  'var(--warn)',   'ph-clock'],
                            };
                        @endphp
                        <span style="color:{{ $ciColor }};font-weight:600;">
                            <i class="ph-fill {{ $ciIcon }} me-1"></i>{{ $ciLabel }}
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
        Showing {{ $ciRows->firstItem() }}–{{ $ciRows->lastItem() }} of {{ $ciRows->total() }} interview(s)
    </div>
    {{ $ciRows->links() }}
</div>
@endif

@elseif(($tab ?? 'vacancies') === 'imported')

@include('staff.job_vacancy._imported')

@endif

@push('scripts')
<script>
    function changeTopEmployersDate(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', 'top_employers');
        url.searchParams.set('top_employers_filter', 'monthly');
        url.searchParams.set('top_employers_month', value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function changeTopEmployersYear(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', 'top_employers');
        url.searchParams.set('top_employers_filter', 'yearly');
        url.searchParams.set('top_employers_year', value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }
</script>
@endpush

{{-- ── ANG KASAYSAYAN SA PAG-POST SA USA KA EMPLOYER ──
     Usa ka modal kada kompanya nga naa sa page, dili kada laray: ang usa ka
     employer mahimong naay lima ka posting dinhi, ug lima ka parehas nga modal
     usa ka pagbalik-balik nga walay gidugang. --}}
@php
    $solicitedCompanies = $jobs->pluck('company')->filter()->unique('employer_nsrp_registrations_id');
@endphp
@foreach($solicitedCompanies as $solicitedCompany)
@php
    // Tanan nga posting sa kompanya, dili ang sa gipili nga bulan ra — ang
    // pangutana mao kung kanus-a siya katapusang nag-post, ug ang tubag niana
    // mahimong wala sa bulan nga gitan-aw karon.
    $companyJobs = $solicitedCompany->jobs()->latest('created_at')->get();
    $lastPosted  = $companyJobs->first()?->created_at;
    $status      = $solicitedCompany->companyStatus();
@endphp
<div class="modal fade" id="solicitedModal{{ $solicitedCompany->employer_nsrp_registrations_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header py-2 px-3" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white fw-bold" style="font-size:13.5px;">
                    <i class="ph-fill ph-briefcase me-2"></i>
                    {{ $solicitedCompany->company_name ?? 'Employer' }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">

                <div class="d-flex flex-wrap gap-3 p-3 mb-3 rounded-3" style="background:var(--n-50);">
                    <div>
                        <div style="font-size:11px;color:var(--n-500);">Last posted</div>
                        <div class="fw-bold" style="color:var(--g-700);font-size:13px;">
                            {{ $lastPosted ? $lastPosted->format('M d, Y') : 'Never' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--n-500);">Company status</div>
                        <div class="fw-bold" style="font-size:13px;color:{{ $status['color'] }};">
                            {{ $status['label'] }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--n-500);">Postings on record</div>
                        <div class="fw-bold" style="color:var(--g-700);font-size:13px;">
                            {{ $companyJobs->count() }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--n-500);">Total vacancies</div>
                        <div class="fw-bold" style="color:var(--g-700);font-size:13px;">
                            {{ $companyJobs->sum('slots') }}
                        </div>
                    </div>
                </div>

                @if($companyJobs->isEmpty())
                <div class="text-center py-4" style="color:var(--n-500);font-size:13px;">
                    No postings on record.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 10px;">Job Title</th>
                                <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 10px;text-align:center;">Vacancies</th>
                                <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 10px;text-align:center;">Posted</th>
                                <th style="background:var(--g-600);color:#fff;font-size:11px;border:none;padding:8px 10px;text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companyJobs as $companyJob)
                            <tr style="font-size:12px;">
                                <td style="padding:8px 10px;font-weight:600;color:var(--g-700);">
                                    {{ $companyJob->title }}
                                </td>
                                <td style="padding:8px 10px;text-align:center;color:var(--n-700);">
                                    {{ $companyJob->slots }}
                                </td>
                                <td style="padding:8px 10px;text-align:center;color:var(--n-500);white-space:nowrap;">
                                    {{ $companyJob->created_at?->format('M d, Y') ?? 'None' }}
                                </td>
                                <td style="padding:8px 10px;text-align:center;">
                                    <span class="fw-semibold" style="font-size:11px;
                                          color:{{ $companyJob->status === 'open' ? 'var(--g-700)' : 'var(--n-500)' }};">
                                        {{ ucfirst($companyJob->status ?? 'None') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                    style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection