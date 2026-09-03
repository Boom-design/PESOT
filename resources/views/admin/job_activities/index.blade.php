@extends('admin.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-calendar-check me-2" style="color:var(--g-600);"></i>Job Activities
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">Overview of in-house interviews, company interviews and job fair participants.</p>
</div>

{{-- TABS
     Each button carries its own unseen count, so the number on the sidebar can
     be traced to the tab that explains it. Opening the tab clears it — the
     admin approves nothing here, so "I have looked at it" is the only thing
     that can mean read. See App\Support\AdminInbox. --}}
@php $tabAlerts = \App\Support\AdminInbox::jobActivityCounts(); @endphp
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('admin.job.activities', ['tab' => 'inhouse']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('tab', 'inhouse') === 'inhouse'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        {{-- "LRA/SRA" named the two desks, which only the desks recognise.
             The split the reader is looking at is local against overseas. --}}
        <i class="ph ph-buildings me-1"></i> In-house Local/Overseas
        @if(($tabAlerts['inhouse'] ?? 0) > 0)
            {{-- .nav-dot only exists inside the sidebar, so the tab carries its
                 own copy of the same red. --}}
            <span title="{{ $tabAlerts['inhouse'] }} new since you last opened this tab"
                  style="display:inline-flex;align-items:center;justify-content:center;min-width:17px;
                         height:17px;padding:0 5px;margin-left:6px;border-radius:999px;
                         background:var(--danger);color:#fff;font-size:10px;font-weight:700;
                         line-height:1;vertical-align:middle;">{{ $tabAlerts['inhouse'] > 9 ? '9+' : $tabAlerts['inhouse'] }}</span>
        @endif
        <span class="ms-1" style="font-size:10px;opacity:0.8;">({{ $inhouseSchedules->count() }})</span>
    </a>
    <a href="{{ route('admin.job.activities', ['tab' => 'companyinterview']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('tab') === 'companyinterview'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="ph ph-video-camera me-1"></i> Company Interview
        @if(($tabAlerts['companyinterview'] ?? 0) > 0)
            {{-- .nav-dot only exists inside the sidebar, so the tab carries its
                 own copy of the same red. --}}
            <span title="{{ $tabAlerts['companyinterview'] }} new since you last opened this tab"
                  style="display:inline-flex;align-items:center;justify-content:center;min-width:17px;
                         height:17px;padding:0 5px;margin-left:6px;border-radius:999px;
                         background:var(--danger);color:#fff;font-size:10px;font-weight:700;
                         line-height:1;vertical-align:middle;">{{ $tabAlerts['companyinterview'] > 9 ? '9+' : $tabAlerts['companyinterview'] }}</span>
        @endif
        <span class="ms-1" style="font-size:10px;opacity:0.8;">({{ $companyInterviewJobs->count() }})</span>
    </a>
    <a href="{{ route('admin.job.activities', ['tab' => 'jobfair']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('tab') === 'jobfair'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="ph-fill ph-users-three me-1"></i> Job Fair
        @if(($tabAlerts['jobfair'] ?? 0) > 0)
            {{-- .nav-dot only exists inside the sidebar, so the tab carries its
                 own copy of the same red. --}}
            <span title="{{ $tabAlerts['jobfair'] }} new since you last opened this tab"
                  style="display:inline-flex;align-items:center;justify-content:center;min-width:17px;
                         height:17px;padding:0 5px;margin-left:6px;border-radius:999px;
                         background:var(--danger);color:#fff;font-size:10px;font-weight:700;
                         line-height:1;vertical-align:middle;">{{ $tabAlerts['jobfair'] > 9 ? '9+' : $tabAlerts['jobfair'] }}</span>
        @endif
        <span class="ms-1" style="font-size:10px;opacity:0.8;">({{ $jobFairEvents->count() }})</span>
    </a>
</div>

{{-- ── TAB 1: IN-HOUSE INTERVIEWS ── --}}
@if(request('tab', 'inhouse') === 'inhouse')

        <div class="d-flex justify-content-end mb-2">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
                    <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
                </span>
                <input type="text" id="inhouseSearch" class="form-control" placeholder="Search..." style="border-color:var(--n-200);font-size:13px;">
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="inhouseTable">
                    <thead>
                        <tr>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">#</th>
                            <th data-sort-col="1" style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;cursor:pointer;">
                                Company
                                <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                    <i class="ph-fill ph-caret-up" style="font-size:9px;color:var(--g-600);"></i>
                                    <i class="ph-fill ph-caret-down" style="font-size:9px;color:var(--g-600);"></i>
                                </span>
                            </th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Preferred Date</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Preferred Time</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Number of Applicants</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Job Offer</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Classification</th>
                            {{-- "Status" alone did not say whose. It is the
                                 office's answer to the requested schedule. --}}
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Schedule Status</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inhouseSchedules as $i => $schedule)
                        <tr>
                            <td data-row-number style="font-size:13px;padding:12px 16px;color:var(--n-500);">{{ $i + 1 }}</td>
                            <td style="font-size:13px;padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ $schedule->company->company_name ?? 'None' }}
                                {{-- Which door this came through. Both are in-house
                                     interviews at the same office; only the paperwork
                                     the employer filled in differs. --}}
                                <div style="font-size:10.5px;font-weight:500;color:var(--n-500);margin-top:2px;">
                                    {{ $schedule->source_label }}
                                </div>
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                                {{ $schedule->window_label ?: 'Not set' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                                {{ $schedule->time_label ?: 'Not set' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                                {{ $schedule->applicant_count }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                                {{ $schedule->offer_count }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                {{-- The desk that owns this request. It used to be read
                                     through a relation the NSRP row does not have, so
                                     every line said None. --}}
                                @if($schedule->company)
                                    <span style="color:{{ $schedule->company->is_overseas ? 'var(--warn)' : 'var(--g-600)' }};font-weight:600;font-size:11px;">
                                        {{ $schedule->company->is_overseas ? 'SRA' : 'LRA' }}
                                    </span>
                                @else
                                    <span style="color:var(--n-400);font-size:12px;">None</span>
                                @endif
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                @php
                                    // "Pending", "Accepted", "Rejected" say what
                                    // the column is called, not what happened.
                                    // What the office actually did is pick a date,
                                    // refuse one, or not answer yet, so the cell
                                    // says that and shows the date it landed on.
                                    $ihConfirmed = $schedule->confirmed_date
                                        ? $schedule->confirmed_date
                                          . ($schedule->confirmed_time ? ' at ' . $schedule->confirmed_time : '')
                                        : null;

                                    [$ihLabel, $ihColor, $ihIcon, $ihNote] = match ($schedule->state) {
                                        'accepted' => [
                                            'Schedule confirmed', 'var(--g-600)', 'ph-check-circle',
                                            $ihConfirmed ?: 'No date recorded',
                                        ],
                                        'rejected' => [
                                            'Request declined', 'var(--danger)', 'ph-x-circle',
                                            $schedule->decline_reason ?: 'No reason given',
                                        ],
                                        default => [
                                            'Waiting for a date', 'var(--warn)', 'ph-clock',
                                            'The office has not answered yet',
                                        ],
                                    };
                                @endphp
                                <span style="color:{{ $ihColor }};font-weight:600;">
                                    <i class="ph-fill {{ $ihIcon }} me-1"></i>{{ $ihLabel }}
                                </span>
                                <div style="font-size:11px;color:var(--n-500);margin-top:2px;">{{ $ihNote }}</div>
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                <button type="button" class="btn btn-sm fw-semibold"
                                    data-bs-toggle="modal" data-bs-target="#inhouseModal{{ $schedule->row_key }}"
                                    style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);background:#fff;font-size:12px;padding:5px 14px;">
                                    <i class="ph ph-eye me-1"></i>View Details
                                </button>
                            </td>
                        </tr>
                        @empty
                        {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                             gihapon ug ang pahina dili mag-usab ug porma. --}}
                        <tr>
                            <td colspan="9" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                <i class="ph ph-calendar-x me-1"
                                   style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                No in-house interview schedules found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-3" id="inhousePagination">
            <button type="button" id="inhousePaginationPrev" class="btn btn-sm" style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;"><i class="ph ph-caret-left"></i></button>
            <span id="inhousePaginationInfo" style="font-size:13px;color:var(--g-700);font-weight:600;"></span>
            <button type="button" id="inhousePaginationNext" class="btn btn-sm" style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;"><i class="ph ph-caret-right"></i></button>
        </div>

    {{-- One modal per schedule. The row is deliberately narrow — it has to fit
         eight columns — so everything the office keeps about the employer, and
         every position they are interviewing for, lives in here. A schedule
         with five job offers shows all five; the row could only ever show a
         number. --}}
    @foreach($inhouseSchedules as $schedule)
    @php
        $emp      = $schedule->company;
        $ihVenue  = $schedule->venue_label;
        $ihPlace  = collect([$emp->est_barangay ?? null, $emp->est_city_municipality ?? null, $emp->est_province ?? null])
            ->filter()->implode(', ');
        $ihOffers = $schedule->positions;
    @endphp
    <div class="modal fade" id="inhouseModal{{ $schedule->row_key }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                <div class="modal-header border-0" style="background:var(--g-600);padding:16px 20px;">
                    <h6 class="modal-title fw-bold text-white mb-0">
                        <i class="ph-fill ph-buildings me-2"></i>{{ $emp->company_name ?? 'None' }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="font-size:13px;color:var(--g-700);">

                    <div class="fw-bold mb-3" style="font-size:12px;text-transform:uppercase;letter-spacing:.4px;color:var(--g-600);">
                        <i class="ph-fill ph-buildings me-1"></i>Company
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><strong>Company Name</strong><div class="text-muted">{{ $emp->company_name ?? 'None' }}</div></div>
                        <div class="col-md-6"><strong>Trade Name</strong><div class="text-muted">{{ $emp->trade_name ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Contact Person</strong><div class="text-muted">{{ $emp->contact_person ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Position</strong><div class="text-muted">{{ $emp->position_title ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Mobile Number</strong><div class="text-muted">{{ $emp->mobile_number ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Telephone</strong><div class="text-muted">{{ $emp->telephone_no ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Email</strong><div class="text-muted">{{ optional($emp->employer)->email ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Placement</strong><div class="text-muted">{{ ($emp->is_overseas ?? false) ? 'Overseas' : 'Local' }}</div></div>
                        <div class="col-md-6"><strong>Employer Type</strong><div class="text-muted">{{ $emp->employer_type ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Industry</strong><div class="text-muted">{{ $emp->industry_group ?: 'Not set' }}</div></div>
                        <div class="col-md-6"><strong>Line of Business</strong><div class="text-muted">{{ $emp->line_of_business ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Total Workforce</strong><div class="text-muted">{{ $emp->total_workforce ?: 'None' }}</div></div>
                        <div class="col-12"><strong>Address</strong><div class="text-muted">{{ $ihPlace ?: 'None' }}</div></div>
                    </div>

                    <div class="fw-bold mb-3" style="font-size:12px;text-transform:uppercase;letter-spacing:.4px;color:var(--g-600);">
                        <i class="ph-fill ph-calendar-check me-1"></i>Schedule
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><strong>Request Type</strong><div class="text-muted">{{ $schedule->source_label }}</div></div>
                        <div class="col-md-6"><strong>Requested Date</strong><div class="text-muted">{{ $schedule->requested_label ?: 'Not set' }}</div></div>
                        <div class="col-md-6"><strong>Requested Time</strong><div class="text-muted">{{ $schedule->time_label ?: 'Not set' }}</div></div>
                        <div class="col-md-6"><strong>Confirmed Date</strong><div class="text-muted">{{ $schedule->confirmed_date ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Confirmed Time</strong><div class="text-muted">{{ $schedule->confirmed_time ?: 'None' }}</div></div>
                        <div class="col-md-6"><strong>Venue</strong><div class="text-muted">{{ $ihVenue }}</div></div>
                        <div class="col-md-6"><strong>Number of Applicants</strong><div class="text-muted">{{ $schedule->applicant_count }}</div></div>
                        <div class="col-md-6"><strong>Schedule Status</strong>
                            <div class="text-muted">{{ ['accepted' => 'Accepted', 'rejected' => 'Declined'][$schedule->state] ?? 'Waiting for a date' }}</div>
                        </div>
                        <div class="col-md-6"><strong>Job Offers Made</strong><div class="text-muted">{{ $schedule->offer_count }}</div></div>
                        @if($schedule->state === 'rejected')
                        <div class="col-12"><strong>Reason for Declining</strong><div class="text-muted">{{ $schedule->decline_reason ?: 'No reason given' }}</div></div>
                        @endif
                        @if($schedule->notes)
                        <div class="col-12"><strong>Employer Notes</strong><div class="text-muted">{{ $schedule->notes }}</div></div>
                        @endif
                    </div>

                    <div class="fw-bold mb-3" style="font-size:12px;text-transform:uppercase;letter-spacing:.4px;color:var(--g-600);">
                        <i class="ph-fill ph-briefcase me-1"></i>Job Vacancies for this Interview
                        <span style="font-weight:400;opacity:.7;">({{ $ihOffers->count() }})</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">#</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">Job Vacancy</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;text-align:center;">Slots</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">Salary</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">Posting</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ihOffers as $k => $offer)
                                @php
                                    // The position is a name the employer typed on the
                                    // request. Where a posting of that name exists for
                                    // the same company, its slots and pay are shown;
                                    // where it does not, the name still gets a row —
                                    // a position with no posting is exactly the sort
                                    // of gap this report is meant to expose.
                                    $match = $schedule->postings
                                        ->first(fn($j) => strcasecmp(trim($j->title), trim($offer)) === 0);
                                @endphp
                                <tr>
                                    <td style="font-size:12.5px;padding:10px 12px;color:var(--n-500);">{{ $k + 1 }}</td>
                                    <td style="font-size:12.5px;padding:10px 12px;font-weight:600;color:var(--g-700);">{{ $offer }}</td>
                                    <td style="font-size:12.5px;padding:10px 12px;text-align:center;color:var(--n-700);">{{ $match->slots ?? '—' }}</td>
                                    <td style="font-size:12.5px;padding:10px 12px;color:var(--n-700);">{{ $match->salary ?? '—' }}</td>
                                    <td style="font-size:12.5px;padding:10px 12px;">
                                        @if($match)
                                            <span style="color:{{ $match->status === 'open' ? 'var(--g-600)' : 'var(--n-500)' }};font-weight:600;">
                                                {{ ucfirst($match->status) }}
                                            </span>
                                        @else
                                            <span style="color:var(--warn);font-weight:600;">Not posted</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                                     gihapon ug ang pahina dili mag-usab ug porma. --}}
                                <tr>
                                    <td colspan="5" class="text-center"
                                        style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                        <i class="ph ph-tray me-1"
                                           style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                        The employer did not name the positions for this schedule.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach


{{-- ── TAB 2: JOB FAIR ──
     One row per event, not per invitation. The employers on a fair are the
     detail behind the count, and they open in a modal that repeats the same
     five headings so the reader never loses which event they are inside. --}}
@elseif(request('tab') === 'jobfair')

    @if($jobFairEvents->isNotEmpty())
    <div class="d-flex justify-content-end mb-2">
        <div class="input-group" style="max-width:280px;">
            <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
                <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
            </span>
            <input type="text" id="jobFairSearch" class="form-control" placeholder="Search..." style="border-color:var(--n-200);font-size:13px;">
        </div>
    </div>
    @endif
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="jobFairTable">
                <thead>
                    <tr>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">#</th>
                        <th data-sort-col="1" style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;cursor:pointer;">
                            Event Title
                            <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                <i class="ph-fill ph-caret-up" style="font-size:9px;color:var(--g-600);"></i>
                                <i class="ph-fill ph-caret-down" style="font-size:9px;color:var(--g-600);"></i>
                            </span>
                        </th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Venue</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Invitation Date</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Event Date</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">No. of Participants</th>
                        <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobFairEvents as $i => $event)
                    <tr>
                        <td data-row-number style="font-size:13px;padding:12px 16px;color:var(--n-500);">{{ $i + 1 }}</td>
                        <td style="font-size:13px;padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $event->title }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                            {{ $event->venue ?: 'Not set' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                            {{ $event->invited_on ? \Carbon\Carbon::parse($event->invited_on)->format('M d, Y') : 'Not sent yet' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                            {{ $event->event_date->format('M d, Y') }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $event->roster->count() }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;">
                            <button type="button" class="btn btn-sm fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#fairEventModal{{ $event->job_fair_events_id }}"
                                style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);background:#fff;font-size:12px;padding:5px 14px;">
                                <i class="ph ph-eye me-1"></i>View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="ph ph-users-three" style="font-size:40px;color:var(--n-200);"></i>
                            <p class="text-muted mt-2 mb-0">No job fair events yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($jobFairEvents->isNotEmpty())
    <div class="d-flex justify-content-center align-items-center gap-3 mt-3" id="jobFairPagination">
        <button type="button" id="jobFairPaginationPrev" class="btn btn-sm" style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;"><i class="ph ph-caret-left"></i></button>
        <span id="jobFairPaginationInfo" style="font-size:13px;color:var(--g-700);font-weight:600;"></span>
        <button type="button" id="jobFairPaginationNext" class="btn btn-sm" style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;"><i class="ph ph-caret-right"></i></button>
    </div>
    @endif

    {{-- One modal per event. The five headings from the row are repeated at the
         top so the list of employers cannot be read against the wrong fair. --}}
    @foreach($jobFairEvents as $event)
    <div class="modal fade" id="fairEventModal{{ $event->job_fair_events_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
                <div class="modal-header border-0" style="background:var(--g-600);padding:16px 20px;">
                    <h6 class="modal-title fw-bold text-white mb-0">
                        <i class="ph-fill ph-users-three me-2"></i>{{ $event->title }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="font-size:13px;color:var(--g-700);">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <strong>Event Title</strong>
                            <div class="text-muted">{{ $event->title }}</div>
                        </div>
                        <div class="col-md-6">
                            <strong>Venue</strong>
                            <div class="text-muted">{{ $event->venue ?: 'Not set' }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong>Invitation Date</strong>
                            <div class="text-muted">{{ $event->invited_on ? \Carbon\Carbon::parse($event->invited_on)->format('M d, Y') : 'Not sent yet' }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong>Event Date</strong>
                            <div class="text-muted">{{ $event->event_date->format('M d, Y') }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong>No. of Participants</strong>
                            <div class="text-muted">{{ $event->roster->count() }}</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">#</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">Company Name</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">Placement</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">Status</th>
                                    <th style="background:var(--n-100);color:var(--g-700);font-size:11.5px;padding:10px 12px;">Answered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($event->roster as $j => $participant)
                                @php
                                    // "Waiting" is not a third answer — it is the
                                    // absence of one. The employer has the
                                    // invitation and has pressed neither button.
                                    //
                                    // An overseas agency has two more places to
                                    // stand: it answered and PESO has not yet
                                    // picked, or it answered and PESO did not
                                    // take it. Neither is "Waiting", and calling
                                    // the second one that would show the head of
                                    // the office an open question that is closed.
                                    $status = $participant->confirmation_status;
                                    $look   = [
                                        'confirmed'    => ['Confirmed',        'var(--g-600)',  'ph-check-circle'],
                                        'declined'     => ['Declined',         'var(--danger)', 'ph-x-circle'],
                                        'accepted'     => ['Accepted — with SRA', 'var(--warn)', 'ph-hourglass-medium'],
                                        'not_selected' => ['Not selected',     'var(--n-500)',  'ph-minus-circle'],
                                    ][$status] ?? ['Waiting', 'var(--warn)', 'ph-clock'];
                                @endphp
                                <tr>
                                    <td style="font-size:12.5px;padding:10px 12px;color:var(--n-500);">{{ $j + 1 }}</td>
                                    <td style="font-size:12.5px;padding:10px 12px;font-weight:600;color:var(--g-700);">
                                        {{ $participant->employer->company_name ?? 'None' }}
                                    </td>
                                    <td style="font-size:12.5px;padding:10px 12px;">
                                        <span style="color:{{ ($participant->employer->is_overseas ?? false) ? 'var(--warn)' : 'var(--g-600)' }};font-weight:600;font-size:11px;">
                                            {{ ($participant->employer->is_overseas ?? false) ? 'Overseas' : 'Local' }}
                                        </span>
                                    </td>
                                    <td style="font-size:12.5px;padding:10px 12px;">
                                        <span style="color:{{ $look[1] }};font-weight:600;">
                                            <i class="ph-fill {{ $look[2] }} me-1"></i>{{ $look[0] }}
                                        </span>
                                    </td>
                                    <td style="font-size:12.5px;padding:10px 12px;color:var(--n-500);">
                                        {{ $participant->responded_at?->format('M d, Y') ?? '—' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted" style="font-size:12.5px;">
                                        No employer has been invited to this event yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

{{-- ── TAB 3: COMPANY INTERVIEW ── --}}
@elseif(request('tab') === 'companyinterview')

        <div class="d-flex justify-content-end mb-2">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
                    <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
                </span>
                <input type="text" id="companyInterviewSearch" class="form-control" placeholder="Search..." style="border-color:var(--n-200);font-size:13px;">
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="companyInterviewTable">
                    <thead>
                        <tr>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">#</th>
                            <th data-sort-col="1" style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;cursor:pointer;">
                                Job Title
                                <span style="display:inline-flex;flex-direction:column;margin-left:4px;vertical-align:middle;line-height:0.6;">
                                    <i class="ph-fill ph-caret-up" style="font-size:9px;color:var(--g-600);"></i>
                                    <i class="ph-fill ph-caret-down" style="font-size:9px;color:var(--g-600);"></i>
                                </span>
                            </th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Company Name</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Location</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Interview Date</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Slots</th>
                            {{-- "Deadline" on its own read like the interview
                                 deadline. It is the day the posting stops
                                 taking applications, so it says so. --}}
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Application Deadline</th>
                            <th style="background:var(--n-200);color:var(--g-700);font-size:12px;padding:12px 16px;">Classification</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companyInterviewJobs as $i => $job)
                        <tr>
                            <td data-row-number style="font-size:13px;padding:12px 16px;color:var(--n-500);">{{ $i + 1 }}</td>
                            <td style="font-size:13px;padding:12px 16px;font-weight:600;color:var(--g-700);">{{ $job->title }}</td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">{{ $job->company->company_name ?? 'None' }}</td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">{{ $job->location }}</td>
                            {{-- The employer names the day themselves for a
                                 company interview; there is no PESO date to
                                 confirm, so preferred_date is the interview. --}}
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                                {{ $job->preferred_date?->format('M d, Y') ?? 'Not set' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">{{ $job->slots }}</td>
                            <td style="font-size:13px;padding:12px 16px;color:var(--n-500);">
                                {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : 'None' }}
                            </td>
                            <td style="font-size:13px;padding:12px 16px;">
                                {{-- $job->company IS the NSRP row; the old
                                     $job->company->employerNsrp was always null,
                                     so this column read "None" on every line. --}}
                                @if($job->company)
                                    <span style="color:{{ $job->company->is_overseas ? 'var(--warn)' : 'var(--g-600)' }};font-weight:600;font-size:11px;">
                                        {{ $job->company->is_overseas ? 'SRA' : 'LRA' }}
                                    </span>
                                @else
                                    <span style="color:var(--n-400);font-size:12px;">None</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                             gihapon ug ang pahina dili mag-usab ug porma. --}}
                        <tr>
                            <td colspan="8" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                <i class="ph ph-video-camera me-1"
                                   style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                No company interview postings found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-3" id="companyInterviewPagination">
            <button type="button" id="companyInterviewPaginationPrev" class="btn btn-sm" style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;"><i class="ph ph-caret-left"></i></button>
            <span id="companyInterviewPaginationInfo" style="font-size:13px;color:var(--g-700);font-weight:600;"></span>
            <button type="button" id="companyInterviewPaginationNext" class="btn btn-sm" style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;"><i class="ph ph-caret-right"></i></button>
        </div>

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

            // The # column counts down the page and keeps counting across
            // pages. Sorting alphabetically used to carry each row's original
            // number with it, so the first column read 7, 2, 9, 4 and looked
            // like an ID somebody could quote back to the office.
            filtered.forEach((r, idx) => {
                const cell = r.querySelector('[data-row-number]');
                if (cell) cell.textContent = idx + 1;
            });

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
    setupTableTools('companyInterviewTable', 'companyInterviewSearch', 'companyInterviewPagination', 5);
    setupTableTools('jobFairTable', 'jobFairSearch', 'jobFairPagination', 5);
</script>

@endsection