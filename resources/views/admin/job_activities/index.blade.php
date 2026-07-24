@extends('admin.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-calendar2-check-fill me-2" style="color:#4dd9c0;"></i>Job Activities
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Overview of in-house interviews, company interviews, job solicitations, and job fair participants.</p>
</div>

{{-- TABS --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('admin.job.activities', ['tab' => 'inhouse']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('tab', 'inhouse') === 'inhouse'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="bi bi-building me-1"></i> In-house LRA/SRA
        <span class="ms-1" style="font-size:10px;opacity:0.8;">({{ $inhouseSchedules->count() }})</span>
    </a>
    <a href="{{ route('admin.job.activities', ['tab' => 'companyinterview']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('tab') === 'companyinterview'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="bi bi-camera-video me-1"></i> Company Interview
        <span class="ms-1" style="font-size:10px;opacity:0.8;">({{ $companyInterviewJobs->count() }})</span>
    </a>
    <a href="{{ route('admin.job.activities', ['tab' => 'jobfair']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('tab') === 'jobfair'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="bi bi-people-fill me-1"></i> Job Fair
        <span class="ms-1" style="font-size:10px;opacity:0.8;">({{ $jobFairParticipants->count() }})</span>
    </a>
    <a href="{{ route('admin.job.activities', ['tab' => 'officebased']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('tab') === 'officebased'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="bi bi-briefcase me-1"></i> Job Solicitation
        <span class="ms-1" style="font-size:10px;opacity:0.8;">({{ $officeBasedJobs->count() }})</span>
    </a>
</div>

{{-- ── TAB 1: IN-HOUSE INTERVIEWS ── --}}
@if(request('tab', 'inhouse') === 'inhouse')

    @if($inhouseSchedules->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-calendar-x" style="font-size:48px;color:#a8e6cf;"></i>
            <p class="text-muted mt-3 mb-0">No in-house interview schedules found.</p>
        </div>
    @else
        <div class="d-flex justify-content-end mb-2">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
                    <i class="bi bi-search" style="color:#4dd9c0;"></i>
                </span>
                <input type="text" id="inhouseSearch" class="form-control" placeholder="Search..." style="border-color:#a8e6cf;font-size:13px;">
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="inhouseTable">
                    <thead>
                        <tr>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">#</th>
                            <th data-sort-col="1" style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;cursor:pointer;">
                                Company
                                <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                    <i class="bi bi-caret-up-fill" style="font-size:9px;color:#4dd9c0;"></i>
                                    <i class="bi bi-caret-down-fill" style="font-size:9px;color:#4dd9c0;"></i>
                                </span>
                            </th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Preferred Date</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Preferred Time</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Number of Applicants</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Job Offer</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Classification</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Status</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inhouseSchedules as $i => $schedule)
                        <tr>
                            <td style="font-size:13px;padding:12px 16px;color:#888;">{{ $i + 1 }}</td>
                            <td style="font-size:13px;padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ $schedule->employer->company_name ?? $schedule->employer->name ?? '—' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ \Carbon\Carbon::parse($schedule->preferred_date)->format('M d, Y') }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ \Carbon\Carbon::parse($schedule->preferred_time)->format('h:i A') }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ $schedule->num_applicants }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ $schedule->job_offers }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                @if($schedule->employer && $schedule->employer->employerNsrp)
                                    <span class="badge" style="background:{{ $schedule->employer->employerNsrp->is_overseas ? '#f59e0b' : '#4dd9c0' }};color:#fff;font-weight:600;">
                                        {{ $schedule->employer->employerNsrp->is_overseas ? 'SRA' : 'LRA' }}
                                    </span>
                                @else
                                    <span style="color:#aaa;font-size:12px;">None</span>
                                @endif
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                @php
                                    $colors = [
                                        'pending'  => '#f59e0b',
                                        'accepted' => '#2d7a5f',
                                        'rejected' => '#e05252',
                                    ];
                                    $color = $colors[$schedule->status] ?? '#888';
                                @endphp
                                <span style="color:{{ $color }};font-weight:600;">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;">
                                <button type="button" class="btn btn-sm fw-semibold inhouse-view-btn"
                                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;"
                                    data-bs-toggle="modal" data-bs-target="#inhouseViewModal"
                                    data-company="{{ $schedule->employer->company_name ?? $schedule->employer->name ?? '—' }}"
                                    data-preferred-date="{{ \Carbon\Carbon::parse($schedule->preferred_date)->format('M d, Y') }}"
                                    data-preferred-time="{{ \Carbon\Carbon::parse($schedule->preferred_time)->format('h:i A') }}"
                                    data-confirmed-date="{{ $schedule->confirmed_date ? \Carbon\Carbon::parse($schedule->confirmed_date)->format('M d, Y') : '—' }}"
                                    data-confirmed-time="{{ $schedule->confirmed_time ? \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') : '—' }}"
                                    data-applicants="{{ $schedule->num_applicants }}"
                                    data-offers="{{ $schedule->job_offers }}"
                                    data-status="{{ ucfirst($schedule->status) }}">
                                    <i class="bi bi-eye-fill me-1"></i>View
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-3" id="inhousePagination">
            <button type="button" id="inhousePaginationPrev" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-left"></i></button>
            <span id="inhousePaginationInfo" style="font-size:13px;color:#2d7a5f;font-weight:600;"></span>
            <button type="button" id="inhousePaginationNext" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-right"></i></button>
        </div>

        {{-- IN-HOUSE VIEW MODAL --}}
        <div class="modal fade" id="inhouseViewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                    <div class="modal-header border-0" style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:16px 20px;">
                        <h6 class="modal-title fw-bold text-white mb-0"><i class="bi bi-building me-2"></i><span id="modalIhCompany"></span></h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4" style="font-size:13px;color:#2d7a5f;">
                        <div class="row g-3">
                            <div class="col-6"><strong>Preferred Date</strong><div id="modalIhPreferredDate" class="text-muted"></div></div>
                            <div class="col-6"><strong>Preferred Time</strong><div id="modalIhPreferredTime" class="text-muted"></div></div>
                            <div class="col-6"><strong>Confirmed Date</strong><div id="modalIhConfirmedDate" class="text-muted"></div></div>
                            <div class="col-6"><strong>Confirmed Time</strong><div id="modalIhConfirmedTime" class="text-muted"></div></div>
                            <div class="col-6"><strong>Number of Applicants</strong><div id="modalIhApplicants" class="text-muted"></div></div>
                            <div class="col-6"><strong>Job Offers</strong><div id="modalIhOffers" class="text-muted"></div></div>
                            <div class="col-6"><strong>Status</strong><div id="modalIhStatus" class="text-muted"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

{{-- ── TAB 2: JOB FAIR PARTICIPANTS ── --}}
@elseif(request('tab') === 'jobfair')

    @if($jobFairParticipants->isNotEmpty())
    <div class="d-flex justify-content-end mb-2">
        <div class="input-group" style="max-width:280px;">
            <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
                <i class="bi bi-search" style="color:#4dd9c0;"></i>
            </span>
            <input type="text" id="jobFairSearch" class="form-control" placeholder="Search..." style="border-color:#a8e6cf;font-size:13px;">
        </div>
    </div>
    @endif
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="jobFairTable">
                <thead>
                    <tr>
                        <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">#</th>
                        <th data-sort-col="1" style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;cursor:pointer;">
                            Company
                            <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                <i class="bi bi-caret-up-fill" style="font-size:9px;color:#4dd9c0;"></i>
                                <i class="bi bi-caret-down-fill" style="font-size:9px;color:#4dd9c0;"></i>
                            </span>
                        </th>
                        <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Event Title</th>
                        <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Event Date</th>
                        <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Venue</th>
                        <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Confirmation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobFairParticipants as $i => $participant)
                    <tr>
                        <td style="font-size:13px;padding:12px 16px;color:#888;">{{ $i + 1 }}</td>
                        <td style="font-size:13px;padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $participant->employer->company_name ?? $participant->employer->name ?? '—' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:#555;">
                            {{ $participant->jobFair->title ?? '—' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:#555;">
                            {{ $participant->jobFair?->event_date?->format('M d, Y') ?? '—' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:#555;">
                            {{ $participant->jobFair->venue ?? '—' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;">
                            @php
                                $colors = [
                                    'confirmed' => '#2d7a5f',
                                    'pending'   => '#f59e0b',
                                    'declined'  => '#e05252',
                                ];
                                $color = $colors[$participant->confirmation_status] ?? '#888';
                            @endphp
                            <span style="color:{{ $color }};font-weight:600;">
                                {{ ucfirst($participant->confirmation_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-people" style="font-size:40px;color:#a8e6cf;"></i>
                            <p class="text-muted mt-2 mb-0">No job fair participants found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($jobFairParticipants->isNotEmpty())
    <div class="d-flex justify-content-center align-items-center gap-3 mt-3" id="jobFairPagination">
        <button type="button" id="jobFairPaginationPrev" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-left"></i></button>
        <span id="jobFairPaginationInfo" style="font-size:13px;color:#2d7a5f;font-weight:600;"></span>
        <button type="button" id="jobFairPaginationNext" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-right"></i></button>
    </div>
    @endif

{{-- ── TAB 3: JOB SOLICITATION ── --}}
@elseif(request('tab') === 'officebased')

    <div class="d-flex justify-content-end mb-3">
        <form method="GET" action="{{ route('admin.job.activities') }}" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="officebased">
            <label class="fw-semibold" style="font-size:12px;color:#2d7a5f;">Filter:</label>
            <select name="classification" class="form-select form-select-sm"
                style="border:1.5px solid #a8e6cf;border-radius:8px;font-size:12px;width:auto;"
                onchange="this.form.submit()">
                <option value="all" {{ $classification === 'all' ? 'selected' : '' }}>All</option>
                <option value="lra" {{ $classification === 'lra' ? 'selected' : '' }}>LRA</option>
                <option value="sra" {{ $classification === 'sra' ? 'selected' : '' }}>SRA</option>
            </select>
        </form>
    </div>

    @if($officeBasedJobs->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-briefcase" style="font-size:48px;color:#a8e6cf;"></i>
            <p class="text-muted mt-3 mb-0">No job solicitation postings found.</p>
        </div>
    @else
        <div class="d-flex justify-content-end mb-2">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
                    <i class="bi bi-search" style="color:#4dd9c0;"></i>
                </span>
                <input type="text" id="jobSolicitationSearch" class="form-control" placeholder="Search..." style="border-color:#a8e6cf;font-size:13px;">
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="jobSolicitationTable">
                    <thead>
                        <tr>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">#</th>
                            <th data-sort-col="1" style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;cursor:pointer;">
                                Job Title
                                <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                    <i class="bi bi-caret-up-fill" style="font-size:9px;color:#4dd9c0;"></i>
                                    <i class="bi bi-caret-down-fill" style="font-size:9px;color:#4dd9c0;"></i>
                                </span>
                            </th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Company</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Location</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Type</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Slots</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Deadline</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Classification</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($officeBasedJobs as $i => $job)
                        <tr>
                            <td style="font-size:13px;padding:12px 16px;color:#888;">{{ $i + 1 }}</td>
                            <td style="font-size:13px;padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ $job->title }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ $job->company->company_name ?? '—' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ $job->location }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ ucfirst(str_replace('_', ' ', $job->type)) }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">
                                {{ $job->slots }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:#888;">
                                {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : '—' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                @if($job->company && $job->company->employerNsrp)
                                    <span class="badge" style="background:{{ $job->company->employerNsrp->is_overseas ? '#f59e0b' : '#4dd9c0' }};color:#fff;font-weight:600;">
                                        {{ $job->company->employerNsrp->is_overseas ? 'SRA' : 'LRA' }}
                                    </span>
                                @else
                                    <span style="color:#aaa;font-size:12px;">None</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-3" id="jobSolicitationPagination">
            <button type="button" id="jobSolicitationPaginationPrev" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-left"></i></button>
            <span id="jobSolicitationPaginationInfo" style="font-size:13px;color:#2d7a5f;font-weight:600;"></span>
            <button type="button" id="jobSolicitationPaginationNext" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-right"></i></button>
        </div>
    @endif

{{-- ── TAB 4: COMPANY INTERVIEW ── --}}
@elseif(request('tab') === 'companyinterview')

    @if($companyInterviewJobs->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-camera-video" style="font-size:48px;color:#a8e6cf;"></i>
            <p class="text-muted mt-3 mb-0">No company interview postings found.</p>
        </div>
    @else
        <div class="d-flex justify-content-end mb-2">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
                    <i class="bi bi-search" style="color:#4dd9c0;"></i>
                </span>
                <input type="text" id="companyInterviewSearch" class="form-control" placeholder="Search..." style="border-color:#a8e6cf;font-size:13px;">
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="companyInterviewTable">
                    <thead>
                        <tr>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">#</th>
                            <th data-sort-col="1" style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;cursor:pointer;">
                                Job Title
                                <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                    <i class="bi bi-caret-up-fill" style="font-size:9px;color:#4dd9c0;"></i>
                                    <i class="bi bi-caret-down-fill" style="font-size:9px;color:#4dd9c0;"></i>
                                </span>
                            </th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Company</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Location</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Slots</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Deadline</th>
                            <th style="background:#dde4e1;color:#2d7a5f;font-size:12px;padding:12px 16px;">Classification</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companyInterviewJobs as $i => $job)
                        <tr>
                            <td style="font-size:13px;padding:12px 16px;color:#888;">{{ $i + 1 }}</td>
                            <td style="font-size:13px;padding:12px 16px;font-weight:600;color:#2d7a5f;">{{ $job->title }}</td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">{{ $job->company->company_name ?? '—' }}</td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">{{ $job->location }}</td>
                            <td style="font-size:13px;padding:12px 16px;color:#555;">{{ $job->slots }}</td>
                            <td style="font-size:13px;padding:12px 16px;color:#888;">
                                {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : '—' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                @if($job->company && $job->company->employerNsrp)
                                    <span class="badge" style="background:{{ $job->company->employerNsrp->is_overseas ? '#f59e0b' : '#4dd9c0' }};color:#fff;font-weight:600;">
                                        {{ $job->company->employerNsrp->is_overseas ? 'SRA' : 'LRA' }}
                                    </span>
                                @else
                                    <span style="color:#aaa;font-size:12px;">None</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-3" id="companyInterviewPagination">
            <button type="button" id="companyInterviewPaginationPrev" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-left"></i></button>
            <span id="companyInterviewPaginationInfo" style="font-size:13px;color:#2d7a5f;font-weight:600;"></span>
            <button type="button" id="companyInterviewPaginationNext" class="btn btn-sm" style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;"><i class="bi bi-chevron-right"></i></button>
        </div>
    @endif

@endif

<script>
    function setupTableTools(tableId, searchInputId, paginationPrefix, perPage) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const allRows      = Array.from(tbody.querySelectorAll('tr'));
        let currentPage     = 1;
        const searchEl       = document.getElementById(searchInputId);
        const prevBtn         = document.getElementById(paginationPrefix + 'Prev');
        const nextBtn         = document.getElementById(paginationPrefix + 'Next');
        const infoEl           = document.getElementById(paginationPrefix + 'Info');

        function render() {
            const search   = (searchEl?.value || '').toLowerCase().trim();
            const filtered = allRows.filter(r => search === '' || r.textContent.toLowerCase().includes(search));
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * perPage;

            allRows.forEach(r => r.style.display = 'none');
            filtered.slice(start, start + perPage).forEach(r => r.style.display = '');

            if (infoEl) infoEl.textContent = `Page ${currentPage} of ${totalPages}`;
            if (prevBtn) prevBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        }

        searchEl?.addEventListener('input', () => { currentPage = 1; render(); });
        prevBtn?.addEventListener('click', () => { if (currentPage > 1) { currentPage--; render(); } });
        nextBtn?.addEventListener('click', () => { currentPage++; render(); });

        table.querySelectorAll('th[data-sort-col]').forEach(th => {
            th.addEventListener('click', () => {
                const col = parseInt(th.dataset.sortCol);
                const dir = th.dataset.sortDir === 'asc' ? 'desc' : 'asc';
                th.dataset.sortDir = dir;
                allRows.sort((a, b) => {
                    const av = a.children[col].textContent.trim().toLowerCase();
                    const bv = b.children[col].textContent.trim().toLowerCase();
                    return dir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
                });
                allRows.forEach(r => tbody.appendChild(r));
                currentPage = 1;
                render();
            });
        });

        render();
    }

    setupTableTools('inhouseTable', 'inhouseSearch', 'inhousePagination', 5);
    setupTableTools('jobSolicitationTable', 'jobSolicitationSearch', 'jobSolicitationPagination', 5);
    setupTableTools('companyInterviewTable', 'companyInterviewSearch', 'companyInterviewPagination', 5);
    setupTableTools('jobFairTable', 'jobFairSearch', 'jobFairPagination', 5);

    document.querySelectorAll('.inhouse-view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalIhCompany').textContent        = this.dataset.company;
            document.getElementById('modalIhPreferredDate').textContent  = this.dataset.preferredDate;
            document.getElementById('modalIhPreferredTime').textContent  = this.dataset.preferredTime;
            document.getElementById('modalIhConfirmedDate').textContent  = this.dataset.confirmedDate;
            document.getElementById('modalIhConfirmedTime').textContent  = this.dataset.confirmedTime;
            document.getElementById('modalIhApplicants').textContent     = this.dataset.applicants;
            document.getElementById('modalIhOffers').textContent         = this.dataset.offers;
            document.getElementById('modalIhStatus').textContent         = this.dataset.status;
        });
    });
</script>

@endsection