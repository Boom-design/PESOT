@extends('staff.layouts.app')

@section('content')

@php
    $isPending = $isPendingInterview ?? false;

    // Which of the three lists this is. The In-house one is read-only
    // monitoring — LRA owns that calendar — so it is named for what it is
    // rather than for job vacancies, which is what it used to say on both tabs.
    $isInhouseList = !$isPending && (
        $staffRole === 'lra'
        || request('type') === 'inhouse'
        || ($staffRole === 'sra' && request('type') === null)
    );
@endphp

{{-- No Total Jobs count at the end of the row any more. It was the same number
     the table underneath it states by being read, and the question it was
     really being asked — how many vacancies has each employer offered — is a
     per-employer answer, which is what the Employer Reports tab is for. --}}
@include('partials.staff-activity-tabs')

<div class="mb-3">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-{{ $isPending ? 'hourglass-medium' : ($isInhouseList ? 'calendar-check' : 'briefcase') }} me-2" style="color:var(--g-600);"></i>
        @if($isPending)
            Pending Company Interview
        @elseif($isInhouseList)
            List of In-house Interview
        @else
            List of Company Interview
        @endif
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        @if($isPending)
            Company interviews that have not happened yet — soonest first
        @elseif($isInhouseList)
            Approved in-house interviews, for monitoring
        @else
            Job vacancies solicited for company interviews
        @endif
    </p>
</div>

{{-- FILTER + SEARCH --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    {{-- A month, not a date range. The desk reads this list one month at a
         time — it is the same period the reports are cut on — and two date
         boxes to say "August" is three more clicks than the answer is worth.

         Not on the pending list: that one is cut on the interview date, and a
         month box that filtered on the posting date instead would hide the very
         rows the tab exists for. --}}
    <div class="d-flex align-items-center gap-2" @if($isPending) hidden @endif>
        <input type="month" id="monthFilter" class="form-control form-control-sm"
            style="max-width:170px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;"
            value="{{ $month === 'all' ? '' : $month }}">
        @if($month !== 'all')
        <a href="{{ route('staff.jobs', array_merge(request()->except('page'), ['month' => 'all'])) }}"
           class="btn btn-sm fw-semibold"
           style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;
                  border-radius:8px;font-size:11.5px;padding:5px 12px;white-space:nowrap;">
            <i class="ph ph-x me-1"></i>All months
        </a>
        @else
        <span style="font-size:11.5px;color:var(--n-500);">Showing every month.</span>
        @endif
    </div>
    <div class="input-group" style="max-width:260px;width:100%;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search job or company..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company Name</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Location</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Type</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Slots</th>
                        {{-- Posted and Deadline replace the Open/Closed badge. The
                             badge repeated the filter chip above it, and neither
                             answered the question the desk actually has: when did
                             this come in, and when does it lapse. --}}
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Posted</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Deadline</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($jobs->isEmpty())
                    <tr>
                        <td colspan="9" class="text-center py-5" style="border:none;">
                            <i class="ph ph-briefcase" style="font-size:48px;color:var(--n-300);"></i>
                            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No job vacancies here</div>
                            <div class="text-muted small mt-1">
                                @if($month !== 'all')
                                    Nothing was posted in {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}.
                                @else
                                    Nothing has been posted yet.
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    @foreach($jobs as $i => $job)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $jobs->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $job->title }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $job->company->company_name ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $job->location ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ ucfirst(str_replace('_', ' ', $job->type ?? $job->job_type ?? 'None')) }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                            {{ $job->slots }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:var(--n-500);white-space:nowrap;">
                            {{ $job->created_at?->format('M d, Y') ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;white-space:nowrap;">
                            @if($job->deadline)
                                @php $lapsed = \Carbon\Carbon::parse($job->deadline)->isPast(); @endphp
                                <span style="color:{{ $lapsed ? 'var(--danger)' : 'var(--n-500)' }};">
                                    {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                                </span>
                                @if($lapsed)
                                <div style="font-size:10px;color:var(--danger);">lapsed</div>
                                @endif
                            @else
                                <span style="color:var(--n-400);">None</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;">
                            <div class="d-flex flex-column gap-1" style="min-width:150px;">
                                <button type="button" class="btn btn-sm fw-semibold d-flex align-items-center justify-content-center gap-1"
                                    style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 12px;"
                                    data-bs-toggle="modal" data-bs-target="#jobModal{{ $job->job_qualifications_id }}">
                                    <i class="ph-fill ph-eye"></i> View Details
                                </button>

                                @if($job->posting_status !== 'pending')
                                    <a href="{{ route('staff.jobs.qualified', $job->job_qualifications_id) }}"
                                       class="btn btn-sm fw-semibold d-flex align-items-center justify-content-center gap-1"
                                       style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);border-radius:8px;font-size:12px;padding:6px 12px;">
                                        <i class="ph-fill ph-user-list"></i> View Applicants
                                    </a>
                                @endif

                            </div>
                        </td>
                    </tr>

                    {{-- JOB DETAIL MODAL --}}
                    <div class="modal fade" id="jobModal{{ $job->job_qualifications_id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
                            <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
                                <div class="modal-header" style="background:var(--g-600);flex-shrink:0;">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="ph-fill ph-briefcase me-2"></i>{{ $job->title }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4" style="overflow-y:auto;flex:1;">

                                    <div class="mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                                        <span class="fw-bold" style="color:var(--g-700);font-size:13px;">
                                            <i class="ph ph-briefcase me-1" style="color:var(--g-600);"></i> III. Vacancy Details
                                        </span>
                                    </div>
                                    <div class="row g-2 mb-3" style="font-size:13px;">
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Company</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->company->company_name ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Position Title</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->title }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div style="color:var(--n-500);font-size:11px;">Job Description</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->description ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Nature of Work</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->type ? ucfirst(str_replace('_',' ', $job->type)) : 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Place of Work</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->location ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Salary</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->salary ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Vacancy Count</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->slots ?? 'None' }}</div>
                                        </div>

                                        {{-- Ang schedule. Kini ang gikinahanglan sa usa ka
                                             post sa social media — kanus-a ug asa moadto ang
                                             tawo — ug wala ni sa modal kaniadto. --}}
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Schedule Type</div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                {{ \App\Models\Job::SCHEDULE_TYPE_LABELS[$job->schedule_type] ?? 'None' }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">
                                                {{ $job->confirmed_date ? 'Interview Date' : 'Preferred Date' }}
                                            </div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                @if($job->confirmed_date)
                                                    {{ $job->confirmed_date->format('M d, Y') }}
                                                @elseif($job->preferred_date)
                                                    {{ $job->preferred_date->format('M d, Y') }}@if($job->preferred_date_end && $job->preferred_date_end->ne($job->preferred_date)) – {{ $job->preferred_date_end->format('M d, Y') }}@endif
                                                @else
                                                    None
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Venue</div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                @if($job->venue_address)
                                                    {{ $job->venue_address }}
                                                @elseif($job->venue_type === 'peso_office')
                                                    PESO Office
                                                @elseif($job->venue_type)
                                                    {{ ucfirst(str_replace('_', ' ', $job->venue_type)) }}
                                                @else
                                                    None
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Deadline</div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                {{ $job->deadline ? $job->deadline->format('M d, Y') : 'None' }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Ang poster nga gi-upload sa employer mismo. Ang staff
                                         mo-download niini para sa social media ug para i-print
                                         sa bulletin board sa gawas sa opisina. --}}
                                    @if($job->poster_image)
                                    <div class="mb-3 pb-2 mt-4" style="border-bottom:2px solid var(--n-200);">
                                        <span class="fw-bold" style="color:var(--g-700);font-size:13px;">
                                            <i class="ph ph-image me-1" style="color:var(--g-600);"></i> Hiring Poster
                                        </span>
                                    </div>
                                    <div class="mb-3 text-center">
                                        <img src="{{ asset('storage/' . $job->poster_image) }}"
                                             alt="Hiring poster for {{ $job->title }}"
                                             style="max-width:100%;max-height:420px;border-radius:12px;border:1px solid var(--n-200);">
                                        <div class="mt-2">
                                            <a href="{{ route('staff.jobs.poster', $job->job_qualifications_id) }}"
                                               class="btn btn-sm fw-semibold"
                                               style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;">
                                                <i class="ph-fill ph-download-simple me-1"></i> Download poster
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="mb-3 pb-2 mt-4" style="border-bottom:2px solid var(--n-200);">
                                        <span class="fw-bold" style="color:var(--g-700);font-size:13px;">
                                            <i class="ph ph-clipboard-text me-1" style="color:var(--g-600);"></i> IV. Qualification Requirements
                                        </span>
                                    </div>
                                    <div class="row g-2 mb-3" style="font-size:13px;">
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Work Experience (months)</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->experience_months ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Religion</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->religion ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Sex Preference</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->sex_preference ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Civil Status</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->civil_status ?? 'None' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div style="color:var(--n-500);font-size:11px;">Other Qualifications</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->other_qualifications ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Educational Level</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->education_required ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Course / Major</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->course_major ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">License</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->eligibility ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Certification</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->certification ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Language / Dialect</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->language ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Preferred Residence</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $job->preferred_residence ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Accepts Disability?</div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                {{ $job->acceptsPwd() ? 'Yes' : 'No' }}
                                                @if($job->acceptsPwd() && $job->disability_types)
                                                    — {{ implode(', ', $job->disability_types) }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div style="color:var(--n-500);font-size:11px;">Accepts Programs</div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                {{ is_array($job->accepts_programs) && count($job->accepts_programs) > 0 ? implode(', ', $job->accepts_programs) : 'None' }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Deadline</div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : 'None' }}
                                            </div>
                                        </div>
                                    </div>

                                    @if($job->posting_status === 'pending')
                                    <div class="mt-3 p-3 rounded-3" style="background:var(--warn-bg);border:1px solid var(--warn);">
                                        <div style="font-size:13px;color:var(--warn);" class="mb-3 fw-semibold">
                                            <i class="ph ph-warning-circle me-1"></i>
                                            Job posting pending — review and approve or reject
                                        </div>

                                        <form action="{{ route('staff.jobs.approve', $job->job_qualifications_id) }}" method="POST" class="mb-3">
                                            @csrf
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:var(--g-600);
                                                       color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="ph-fill ph-check-circle me-1"></i> Approve Job Posting
                                            </button>
                                        </form>

                                        <form action="{{ route('staff.jobs.reject', $job->job_qualifications_id) }}" method="POST">
                                            @csrf
                                            <label class="form-label fw-semibold small" style="color:var(--warn);">
                                                Reason for Rejection
                                            </label>
                                            <textarea name="remarks" rows="2" required
                                                class="form-control mb-2"
                                                style="border:1px solid var(--warn-br);border-radius:8px;font-size:13px;"
                                                placeholder="State the reason for rejection..."></textarea>
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:var(--danger);color:#fff;border:none;
                                                       border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="ph-fill ph-x-circle me-1"></i> Reject Job Posting
                                            </button>
                                        </form>
                                    </div>
                                    @endif

                                </div>
                                <div class="modal-footer" style="border-top:1px solid var(--n-200);flex-shrink:0;">
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        data-bs-dismiss="modal"
                                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="d-flex justify-content-center px-3 py-3" style="border-top:1px solid var(--n-50);">
            <div class="d-inline-flex align-items-center gap-2 px-2 py-1"
                style="background:#fff;border:1px solid var(--n-200);border-radius:30px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">

                @if($jobs->onFirstPage())
                    <span class="d-flex align-items-center justify-content-center"
                        style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--n-300);">
                        <i class="ph ph-caret-left"></i>
                    </span>
                @else
                    <a href="{{ $jobs->previousPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}"
                       class="d-flex align-items-center justify-content-center"
                       style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--g-700);text-decoration:none;">
                        <i class="ph ph-caret-left"></i>
                    </a>
                @endif

                <span class="fw-semibold px-2" style="font-size:13px;color:var(--g-700);white-space:nowrap;">
                    Step {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}
                </span>

                @if($jobs->hasMorePages())
                    <a href="{{ $jobs->nextPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}"
                       class="d-flex align-items-center gap-1 fw-semibold text-decoration-none"
                       style="background:var(--g-600);color:#fff;border-radius:20px;padding:8px 18px;font-size:13px;">
                        Next <i class="ph ph-caret-right"></i>
                    </a>
                @else
                    <span class="d-flex align-items-center gap-1 fw-semibold"
                        style="background:var(--n-200);color:var(--n-400);border-radius:20px;padding:8px 18px;font-size:13px;">
                        Next <i class="ph ph-caret-right"></i>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>

{{-- REJECT MODAL --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:var(--danger);">
                <h6 class="modal-title text-white fw-bold">
                    <i class="ph ph-x-circle me-2"></i>Reject Job Posting
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px;color:var(--n-700);" id="rejectJobTitle"></p>
                    <label class="fw-semibold mb-1" style="font-size:13px;color:var(--g-700);">Reason for Rejection *</label>
                    <textarea name="remarks" class="form-control" rows="3"
                        placeholder="State the reason for rejection..."
                        style="border-color:var(--n-200);font-size:13px;" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:var(--danger);color:#fff;border:none;border-radius:8px;">
                        <i class="ph ph-x me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openRejectModal(id, title) {
        document.getElementById('rejectJobTitle').textContent = 'Reject "' + title + '"?';
        document.getElementById('rejectForm').action = '/staff/jobs/' + id + '/reject';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    // Picking a month reloads with it in the query string; clearing the box
    // drops it, which is the same as the All months link.
    document.getElementById('monthFilter')?.addEventListener('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('month', this.value || 'all');
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    });

    let searchTimer;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });
</script>
@endpush

@endsection