{{--
    Employer Report — what came of each in-house interview.

    LRA staff, 2026-08-23: one week after an employer's in-house interview, the
    staff sees that employer's report and the status of each jobseeker.

    The week is the employer's, not a delay: right after the interview they have
    not decided yet, and a report read the next day is a list of blanks. Nothing
    here asks the employer for anything new — the results are the ones they
    already record on their own applicant screen.

    Expects: $employerPostings and $employerRoomOnly from StaffWebController::reports().
    The search term is read straight off the request, the same box the tab bar owns.
--}}
{{-- Ang Download ug ang pasabot. Ang tab bar ug ang search naa na sa
     Reports nga page mismo, mao nga wala na sila gibalik dinhi. --}}
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div style="font-size:12.5px;color:var(--n-500);max-width:620px;">
        What came of each in-house interview held at least
        {{ config('peso.schedule.report_delay_days') }} days ago. An interview more recent than
        that is not here yet — the employer is still deciding on the people they saw.
    </div>

    @if($employerPostings->isNotEmpty() || $employerRoomOnly->isNotEmpty())
    <a href="{{ route('staff.reports.inhouse.export', request()->query()) }}"
       class="btn btn-sm fw-semibold flex-shrink-0"
       style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);
              border-radius:8px;font-size:12px;padding:6px 14px;">
        <i class="ph ph-download-simple me-1"></i>Download Excel
    </a>
    @endif
</div>


{{-- Gigamit sa duha ka listahan sa ubos. Ang pageName lahi sa matag usa, mao
     nga ang pagbalhin sa usa dili mag-usab sa lain. --}}
@php
$erPager = function ($rows, string $label) {
    if (!$rows->hasPages()) return '';
    ob_start(); ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"
         style="font-size:12px;color:var(--n-500);">
        <div>Showing <?= $rows->firstItem() ?>–<?= $rows->lastItem() ?> of <?= $rows->total() ?> <?= $label ?></div>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <li class="page-item <?= $rows->onFirstPage() ? 'disabled' : '' ?>">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                       href="<?= $rows->previousPageUrl() ?>"><i class="ph ph-caret-left"></i></a>
                </li>
                <?php foreach ($rows->getUrlRange(1, $rows->lastPage()) as $page => $url): ?>
                <li class="page-item <?= $page == $rows->currentPage() ? 'active' : '' ?>">
                    <a class="page-link rounded-2"
                       style="<?= $page == $rows->currentPage()
                            ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                            : 'border-color:var(--n-200);color:var(--g-700);' ?>"
                       href="<?= $url ?>"><?= $page ?></a>
                </li>
                <?php endforeach; ?>
                <li class="page-item <?= !$rows->hasMorePages() ? 'disabled' : '' ?>">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                       href="<?= $rows->nextPageUrl() ?>"><i class="ph ph-caret-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
    <?php return ob_get_clean();
};
@endphp

{{-- ── ONE CARD PER INTERVIEW ── --}}
@forelse($employerPostings as $job)
@php
    $attendees = \App\Support\InhouseEmployerReport::attendees($job);
    $totals    = \App\Support\InhouseEmployerReport::totals($attendees);
@endphp
<div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 p-3"
         style="border-bottom:1px solid var(--n-50);">
        <div style="min-width:0;">
            <div class="fw-bold" style="color:var(--g-700);font-size:14px;">
                {{ $job->company->company_name ?? 'None' }}
            </div>
            <div style="font-size:11.5px;color:var(--n-500);">
                <i class="ph ph-calendar-blank me-1"></i>{{ $job->interview_date->format('M d, Y') }}
                &nbsp;•&nbsp;
                <i class="ph ph-map-pin me-1"></i>{{ $job->venue_type === 'other' ? $job->venue_address : 'PESO Office' }}
                &nbsp;•&nbsp; {{ $job->title }}
                @if($job->company->employer_type)
                    &nbsp;•&nbsp; {{ $job->company->employer_type }}
                @endif
            </div>
        </div>

        <div class="text-end flex-shrink-0" style="font-size:11.5px;color:var(--n-500);">
            <span class="fw-bold" style="color:var(--g-700);font-size:15px;">{{ $totals['interviewed'] }}</span> interviewed
            &nbsp;•&nbsp;
            <span class="fw-bold" style="color:var(--g-600);font-size:15px;">{{ $totals['hired'] }}</span> hired
            @if($totals['undecided'] > 0)
            <div style="color:var(--warn);">
                <i class="ph-fill ph-clock me-1"></i>{{ $totals['undecided'] }} with no result yet
            </div>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    @foreach(['Jobseeker', 'Contact', 'Match', 'Result'] as $heading)
                    <th style="background:var(--n-50);color:var(--g-700);font-size:11px;border:none;padding:8px 14px;white-space:nowrap;">
                        {{ $heading }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($attendees as $application)
                @php
                    $seeker = $application->jobseeker;
                    $result = $application->status;
                    $colour = match ($result) {
                        'hired'    => 'var(--g-700)',
                        'rejected' => 'var(--danger)',
                        'pending'  => 'var(--n-500)',
                        default    => 'var(--warn)',
                    };
                @endphp
                <tr style="font-size:12.5px;">
                    <td style="padding:8px 14px;color:var(--n-700);font-weight:600;">
                        {{ trim(($seeker->first_name ?? '') . ' ' . ($seeker->surname ?? '')) ?: 'None' }}
                    </td>
                    <td style="padding:8px 14px;color:var(--n-700);">
                        {{ $seeker->contact_number ?? 'None' }}
                    </td>
                    <td style="padding:8px 14px;color:var(--n-500);">
                        {{ $application->match_percentage !== null
                            ? number_format((float) $application->match_percentage, 0) . '%'
                            : 'None' }}
                    </td>
                    <td style="padding:8px 14px;">
                        <span class="fw-semibold" style="color:{{ $colour }};">
                            {{ \App\Support\InhouseEmployerReport::RESULT_LABELS[$result] ?? ucfirst((string) $result) }}
                        </span>
                    </td>
                </tr>
                @empty
                {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                     gihapon ug ang pahina dili mag-usab ug porma. --}}
                <tr>
                    <td colspan="4" class="text-center"
                        style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                        <i class="ph ph-tray me-1"
                           style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                        No jobseeker took part in this interview.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@empty
@if($employerRoomOnly->isEmpty())
{{-- The columns of an interview report, drawn before there is one to fill
     them. A blank panel says the tab is empty; this says what the tab holds
     and that nothing has reached it yet. --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    @foreach(['Employer', 'Interview Date', 'Venue', 'Job Position', 'Interviewed', 'Hired'] as $heading)
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;white-space:nowrap;">
                        {{ $heading }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="text-center"
                        style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                        {{ request('search')
                            ? 'No employer matches that search.'
                            : 'No in-house interview has reached its report yet.' }}
                        <div style="font-size:11.5px;margin-top:4px;">
                            An interview appears here {{ config('peso.schedule.report_delay_days') }} days after it is held.
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif
@endforelse

{!! $erPager($employerPostings, 'interview report(s)') !!}

{{-- ── ROOM BOOKED, NO VACANCY POSTED ──
     A schedule-only request reserves the office without posting a job, so there
     are no applications to report on. Listed anyway: dropping them silently
     would make this page disagree with the calendar it is meant to explain. --}}
@if($employerRoomOnly->isNotEmpty())
<div class="fw-semibold mt-4 mb-2" style="color:var(--g-700);font-size:13px;">
    Room booked only
</div>
@foreach($employerRoomOnly as $schedule)
@php $when = $schedule->confirmed_date ?: $schedule->preferred_date; @endphp
<div class="card border-0 shadow-sm rounded-3 p-3 mb-2"
     style="border-left:3px solid var(--n-200) !important;">
    <div class="fw-bold" style="color:var(--g-700);font-size:13.5px;">
        {{ $schedule->employer->company_name ?? 'None' }}
    </div>
    <div style="font-size:11.5px;color:var(--n-500);">
        <i class="ph ph-calendar-blank me-1"></i>{{ \Carbon\Carbon::parse($when)->format('M d, Y') }}
        &nbsp;•&nbsp;
        <i class="ph ph-map-pin me-1"></i>{{ $schedule->venue_type === 'custom' ? $schedule->venue_address : 'PESO Office' }}
        &nbsp;•&nbsp; {{ collect((array) $schedule->job_positions)->implode(', ') ?: 'Positions not stated' }}
    </div>
    <div style="font-size:11.5px;color:var(--n-500);margin-top:4px;">
        <i class="ph ph-info me-1"></i>
        No vacancy was posted through the system for this booking, so there is no jobseeker
        list to report.
    </div>
</div>
@endforeach

{!! $erPager($employerRoomOnly, 'room booking(s)') !!}
@endif
