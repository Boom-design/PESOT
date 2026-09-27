{{--
    Employer Report — one row per employer.

    LRA staff, 2026-09-14: Reports had two tabs a letter apart. "Employer
    Reports" listed who each employer hired; "Employer Report" drew a card for
    every in-house interview, one after another, and read as a wall. Both
    answer questions about one employer, so they are one tab now: a plain row
    per employer, and View opens both answers for that employer.

    Hires count every hire ever made, with no date range. The Total Hired
    number on the Registered Employer list opens this tab on that employer, and
    a filtered count here would disagree with the number that was clicked.

    Interview results only cover interviews held at least
    peso.schedule.report_delay_days ago. Before that the employer is still
    deciding, and the report would be a list of blanks.

    Expects: $employerHires from InhouseEmployerReport::byEmployer(), and
    $employerFocusId when opened on one employer.
--}}
@php
    $rows  = $employerHires ?? null;
    $delay = config('peso.schedule.report_delay_days');
    $reportRoute = $reportRouteName ?? 'staff.reports';
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div style="font-size:12.5px;color:var(--n-500);max-width:640px;">
        Jobs offered and people hired through PESO, per employer. <strong>View</strong> shows who was
        hired and the result of each in-house interview held at least {{ $delay }} days ago.
    </div>

    <div class="d-flex gap-2 flex-shrink-0">
        @if($employerFocusId ?? null)
        <a href="{{ route($reportRoute, ['tab' => 'employer_report']) }}"
           class="btn btn-sm fw-semibold"
           style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;padding:6px 14px;">
            <i class="ph ph-list me-1"></i>Show all employers
        </a>
        @endif

        {{-- The desk's own download. The export route is LRA only, like the
             other exports on this page. --}}
        @if($staffRole === 'lra' && $reportRoute === 'staff.reports' && $rows && $rows->total() > 0)
        <a href="{{ route('staff.reports.inhouse.export', request()->query()) }}"
           class="btn btn-sm fw-semibold"
           style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);border-radius:8px;font-size:12px;padding:6px 14px;">
            <i class="ph ph-download-simple me-1"></i>Download Excel
        </a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Jobs Offered</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">In-house Interviews</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Total Hired</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows ?? [] as $company)
                @php
                    $hires      = $company->jobs->flatMap->applications;
                    $interviews = $company->interviewPostings->count() + $company->roomBookings->count();
                @endphp
                <tr style="font-size:13px;">
                    <td style="padding:12px 16px;color:var(--n-500);">{{ $rows->firstItem() + $loop->index }}</td>
                    <td style="padding:12px 16px;">
                        <div class="fw-semibold" style="color:var(--g-700);">{{ $company->company_name ?? 'None' }}</div>
                        <div style="font-size:11px;color:var(--n-500);">{{ $company->employer->email ?? 'None' }}</div>
                    </td>
                    <td style="padding:12px 16px;text-align:center;font-weight:700;color:var(--g-700);">{{ $company->jobs_count }}</td>
                    <td style="padding:12px 16px;text-align:center;font-weight:700;color:var(--g-700);">{{ $interviews }}</td>
                    <td style="padding:12px 16px;text-align:center;font-weight:700;color:var(--g-600);">{{ $hires->count() }}</td>
                    <td style="padding:12px 16px;text-align:center;">
                        <button type="button" class="btn btn-sm fw-semibold"
                            data-bs-toggle="modal"
                            data-bs-target="#employerReportModal{{ $company->employer_nsrp_registrations_id }}"
                            style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;">
                            <i class="ph ph-eye me-1"></i> View
                        </button>
                    </td>
                </tr>
                @empty
                {{-- The table stays drawn when empty, so the columns still say
                     what this report holds. --}}
                <tr>
                    <td colspan="6" class="text-center" style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                        {{ request('search') ? 'No employer matches that search.' : 'No approved employer yet.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rows && $rows->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-3" style="border-top:1px solid var(--n-50);">
        <div style="font-size:12px;color:var(--n-500);">
            Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }} employer(s)
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <li class="page-item {{ $rows->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $rows->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                </li>
                @include('partials.page-links', ['pager' => $rows, 'activeStyle' => 'background:var(--g-600);border-color:transparent;color:#fff;', 'idleStyle' => 'border-color:var(--n-200);color:var(--g-700);'])
                <li class="page-item {{ !$rows->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $rows->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

{{-- The modals live outside the table: a <div> between two <tr> elements is
     not valid table markup, and the browser moves it out anyway. --}}
@foreach($rows ?? [] as $company)
@php $hires = $company->jobs->flatMap->applications; @endphp
<div class="modal fade" id="employerReportModal{{ $company->employer_nsrp_registrations_id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-3">
            <div class="modal-header border-0" style="background:var(--g-600);">
                <h6 class="modal-title fw-bold text-white">
                    <i class="ph-fill ph-buildings me-2"></i>{{ $company->company_name ?? 'None' }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                <div class="d-flex flex-wrap gap-4 mb-4">
                    <div>
                        <div class="fw-bold" style="color:var(--g-700);font-size:24px;line-height:1;">{{ $company->jobs_count }}</div>
                        <div style="font-size:11.5px;color:var(--n-500);">job(s) offered</div>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:var(--g-700);font-size:24px;line-height:1;">{{ $company->interviewPostings->count() + $company->roomBookings->count() }}</div>
                        <div style="font-size:11.5px;color:var(--n-500);">in-house interview(s)</div>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:var(--g-600);font-size:24px;line-height:1;">{{ $hires->count() }}</div>
                        <div style="font-size:11.5px;color:var(--n-500);">hired through PESO</div>
                    </div>
                </div>

                {{-- ── Who was hired ── --}}
                <div class="fw-semibold mb-2" style="color:var(--g-700);font-size:13px;">Hired through PESO</div>
                @if($hires->isEmpty())
                <div class="mb-4" style="font-size:12.5px;color:var(--n-500);">
                    No jobseeker has been marked hired by this employer yet.
                </div>
                @else
                <div class="table-responsive mb-4">
                    <table class="table table-sm mb-0" style="font-size:12px;">
                        <thead>
                            <tr>
                                @foreach(['#', 'Jobseeker', 'Position', 'Date Hired', 'Start of Work'] as $heading)
                                <th style="background:var(--n-50);color:var(--g-700);white-space:nowrap;">{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php $hireNumber = 0; @endphp
                            @foreach($company->jobs as $job)
                                @foreach($job->applications as $hire)
                                @php $hireNumber++; @endphp
                                <tr>
                                    <td style="color:var(--n-500);">{{ $hireNumber }}</td>
                                    <td style="font-weight:600;color:var(--g-700);">
                                        {{ trim(($hire->jobseeker->first_name ?? '') . ' ' . ($hire->jobseeker->surname ?? '')) ?: 'None' }}
                                    </td>
                                    <td>{{ $job->title ?? 'None' }}</td>
                                    <td>{{ $hire->hired_at?->format('M d, Y') ?? 'Not recorded' }}</td>
                                    <td>{{ $hire->start_date?->format('M d, Y') ?? 'Not recorded' }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- ── What came of each in-house interview ── --}}
                <div class="fw-semibold mb-2" style="color:var(--g-700);font-size:13px;">In-house interview results</div>

                @forelse($company->interviewPostings as $job)
                @php
                    $attendees = \App\Support\InhouseEmployerReport::attendees($job);
                    $totals    = \App\Support\InhouseEmployerReport::totals($attendees);
                @endphp
                <div class="rounded-3 mb-3" style="border:1px solid var(--n-50);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2" style="background:var(--n-50);font-size:12px;">
                        <div style="color:var(--n-700);">
                            <span class="fw-semibold" style="color:var(--g-700);">{{ $job->title }}</span>
                            &nbsp;•&nbsp; {{ $job->interview_date->format('M d, Y') }}
                            &nbsp;•&nbsp; {{ $job->venue_type === 'other' ? $job->venue_address : 'PESO Office' }}
                        </div>
                        <div style="color:var(--n-500);">
                            {{ $totals['interviewed'] }} interviewed • <span style="color:var(--g-600);font-weight:600;">{{ $totals['hired'] }} hired</span>
                            @if($totals['undecided'] > 0)
                                • <span style="color:var(--warn);">{{ $totals['undecided'] }} no result yet</span>
                            @endif
                        </div>
                    </div>
                    @if($attendees->isEmpty())
                    <div class="px-3 py-2" style="font-size:12px;color:var(--n-500);">No jobseeker took part in this interview.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:12px;">
                            <thead>
                                <tr>
                                    @foreach(['Jobseeker', 'Contact', 'Match', 'Result'] as $heading)
                                    <th style="color:var(--g-700);border-top:none;white-space:nowrap;">{{ $heading }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendees as $application)
                                @php
                                    $seeker = $application->jobseeker;
                                    $colour = match ($application->status) {
                                        'hired'    => 'var(--g-700)',
                                        'rejected' => 'var(--danger)',
                                        'pending'  => 'var(--n-500)',
                                        default    => 'var(--warn)',
                                    };
                                @endphp
                                <tr>
                                    <td style="font-weight:600;color:var(--n-700);">
                                        {{ trim(($seeker->first_name ?? '') . ' ' . ($seeker->surname ?? '')) ?: 'None' }}
                                    </td>
                                    <td>{{ $seeker->contact_number ?? 'None' }}</td>
                                    <td>{{ $application->match_percentage !== null ? number_format((float) $application->match_percentage, 0) . '%' : 'None' }}</td>
                                    <td>
                                        <span class="fw-semibold" style="color:{{ $colour }};">
                                            {{ \App\Support\InhouseEmployerReport::RESULT_LABELS[$application->status] ?? ucfirst((string) $application->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                @empty
                    @if($company->roomBookings->isEmpty())
                    <div style="font-size:12.5px;color:var(--n-500);">
                        No in-house interview held at least {{ $delay }} days ago.
                    </div>
                    @endif
                @endforelse

                {{-- A room booked without a posting has no applicants to report,
                     but it still happened, and the calendar shows it. --}}
                @foreach($company->roomBookings as $schedule)
                @php $when = $schedule->confirmed_date ?: $schedule->preferred_date; @endphp
                <div class="rounded-3 px-3 py-2 mb-2" style="border:1px solid var(--n-50);font-size:12px;color:var(--n-700);">
                    <span class="fw-semibold" style="color:var(--g-700);">
                        {{ collect((array) $schedule->job_positions)->implode(', ') ?: 'Positions not stated' }}
                    </span>
                    &nbsp;•&nbsp; {{ \Carbon\Carbon::parse($when)->format('M d, Y') }}
                    &nbsp;•&nbsp; {{ $schedule->venue_type === 'custom' ? $schedule->venue_address : 'PESO Office' }}
                    <div style="color:var(--n-500);">Room booked only — no vacancy was posted through the system, so there is no applicant list.</div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
@endforeach
