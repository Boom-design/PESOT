@extends('company.layouts.app')

@section('page-title', 'Jobseekers')

@section('content')

<div class="mb-4 fade-in d-flex align-items-end justify-content-between flex-wrap gap-3">
    <div>
        <a href="{{ route('company.jobseekers') }}" style="font-size:13px;color:var(--g-600);text-decoration:none;">
            <i class="ph ph-arrow-left me-1"></i> Back to Active Job Postings
        </a>
        <h5 class="fw-bold mt-2 mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-user-list me-2" style="color:var(--g-600);"></i>
            Jobseekers
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            {{ $job->title }}
            @if($isInhouse)
                <span style="color:var(--n-500);">
                    — In-house Dates: {{ $job->schedule_window_label }}
                </span>
            @endif
        </p>
    </div>
    <input type="text" id="searchInput" class="peso-input"
        style="width:260px;"
        placeholder="🔍  Search jobseeker name...">
</div>

@php $jobActivity = $jobActivity ?? collect(); @endphp
@if($jobActivity->isNotEmpty())
{{-- ── WHAT CHANGED ON THIS POSTING ──
     An employer can widen a posting after people have applied — start accepting
     PWD applicants, add slots, move the deadline. Somebody screened out under
     the old wording, and a desk asking why the slots no longer add up, both
     need to be able to read what happened and when. See App\Support\JobChangeLog. --}}
<div class="peso-card p-3 mb-3">
    <div class="d-flex align-items-center justify-content-between" style="cursor:pointer;"
         data-bs-toggle="collapse" data-bs-target="#jobActivityLog">
        <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">
            <i class="ph ph-clock-counter-clockwise me-1" style="color:var(--g-600);"></i>Update History
            <span style="font-weight:400;color:var(--n-500);">({{ $jobActivity->count() }})</span>
        </div>
        <i class="ph ph-caret-down" style="color:var(--n-500);"></i>
    </div>
    <div class="collapse show" id="jobActivityLog">
        <div class="mt-3">
            @foreach($jobActivity as $entry)
            <div class="d-flex gap-2 pb-2 mb-2" style="border-bottom:1px solid var(--n-50);">
                <i class="ph-fill {{ $entry->action === 'external_hires_recorded' ? 'ph-user-plus' : 'ph-pencil-simple' }}"
                   style="color:var(--g-600);margin-top:2px;"></i>
                <div style="min-width:0;">
                    <div style="font-size:12.5px;color:var(--g-700);">{{ $entry->summary }}</div>
                    @foreach($entry->changes ?? [] as $change)
                        <div style="font-size:11px;color:var(--n-500);margin-top:2px;">
                            {{ $change['label'] }}:
                            <span style="text-decoration:line-through;">{{ $change['from'] !== '' ? \Illuminate\Support\Str::limit($change['from'], 60) : 'none' }}</span>
                            &rarr;
                            <span style="color:var(--g-700);font-weight:600;">{{ $change['to'] !== '' ? \Illuminate\Support\Str::limit($change['to'], 60) : 'none' }}</span>
                        </div>
                    @endforeach
                    <div style="font-size:10.5px;color:var(--n-400);margin-top:3px;">
                        {{ $entry->actor_name ?: 'PESO' }} &middot; {{ $entry->created_at->format('F d, Y') }} at {{ $entry->created_at->format('h:i A') }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Job details + requirements. Dinhi mag-hire/reject ang employer, mao
     nga kinahanglan makita niya unsa gyud ang gipangayo sa posting samtang
     naghukom. Naka-collapse aron dili matabunan ang listahan sa jobseeker. ── --}}
@php
    $hiredList = $applicants->where('status', 'hired');
@endphp
<div class="peso-card p-3 mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">
                <i class="ph ph-briefcase me-1" style="color:var(--g-600);"></i>{{ $job->title }}
            </div>
            <div style="font-size:12px;color:var(--n-500);">
                {{-- $groupHired now counts the hires made outside PESO too. A
                     slot is filled whoever filled it, and this line used to
                     disagree with the badge on the listing. --}}
                {{ $groupHired }} of {{ $job->slots }} slot(s) filled
                &middot; Deadline: {{ $job->deadline?->format('M j, Y') ?? 'None' }}
            </div>

            @if(($groupExternal ?? 0) > 0)
            {{-- Where the missing name went. Without this, a slot closes with
                 nobody on the list to account for it. --}}
            <div class="mt-2 p-2 rounded-3" style="background:var(--warn-bg);border:1px solid var(--warn);max-width:520px;">
                <div style="font-size:11.5px;color:var(--n-700);line-height:1.6;">
                    <i class="ph-fill ph-info me-1" style="color:var(--warn);"></i>
                    <strong>{{ $groupExternal }}</strong> of these were hired outside PESO and reported by you,
                    so they are not on the list below.
                    {{ $groupPeso ?? 0 }} came through PESO.
                    Only the PESO hires count as placements on your report.
                </div>
            </div>
            @endif

            {{-- Ang parehas nga position mahimong gi-post sa laing schedule type
                 pud. Managsama silag bakante, mao nga ipakita asa gikan ang mga
                 na-hire — kay dili tanan sila makita sa listahan sa ubos. --}}
            @php $otherChannels = $groupJobs->where('job_qualifications_id', '!=', $job->job_qualifications_id); @endphp
            @if($otherChannels->isNotEmpty())
            @php
                $channelLabels = \App\Models\Job::SCHEDULE_TYPE_LABELS;
            @endphp
            <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                Also posted as:
                @foreach($otherChannels as $sibling)
                    <span style="color:var(--g-700);font-size:10px;font-weight:600;">
                        {{ $channelLabels[$sibling->schedule_type] ?? $sibling->schedule_type }} — {{ $sibling->hired_count }} hired
                    </span>
                @endforeach
                &middot; the vacancy count is shared across them.
            </div>
            @endif
        </div>
        <button class="btn btn-peso-outline btn-sm" type="button"
                data-bs-toggle="collapse" data-bs-target="#jobDetailsPanel">
            <i class="ph ph-list-checks me-1"></i> Job Details &amp; Requirements
        </button>
    </div>

    @if($hiredList->isNotEmpty())
    <div class="mt-2 pt-2" style="border-top:1px solid var(--n-200);">
        <div style="font-size:11px;color:var(--n-500);margin-bottom:4px;">Hired</div>
        <div class="d-flex flex-wrap gap-1">
            @foreach($hiredList as $h)
                <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">
                    <i class="ph-fill ph-check-circle me-1"></i>{{ trim(($h->jobseeker->first_name ?? '') . ' ' . ($h->jobseeker->surname ?? '')) ?: 'None' }}
                </span>
            @endforeach
        </div>
    </div>
    @endif
</div>

<div class="collapse mb-3" id="jobDetailsPanel">
    @include('partials.job-details', ['job' => $job])
</div>

@if(($isInhouse || $isCompanyInterview) && !$actionsUnlocked)
<div class="alert alert-info mb-3" style="font-size:12px;border-radius:10px;">
    <i class="ph-fill ph-info me-1"></i>
    Hire, Reject, and Waiting actions will be available once the
    {{ $isInhouse ? 'in-house' : 'company' }} interview date
    ({{ $job->interview_date ? $job->interview_date->format('M d, Y') : 'N/A' }}) arrives.
</div>
@endif

{{-- TABS --}}
<ul class="nav nav-tabs mb-3" id="qualificationTabs" role="tablist" style="border-bottom:2px solid var(--n-200);">
    {{-- No "All Jobseekers" tab. It held every applicant in one undivided
         list, which is the question the other three tabs already answer, and it
         opened first — so the employer's first sight of a posting was the
         unsorted pile rather than the people who actually fit. Highly Qualified
         opens instead. --}}
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab"
            data-bs-target="#highlyQualifiedPane" type="button" role="tab"
            style="color:var(--g-700);font-size:13px;">
            Highly Qualified Jobseekers
            <span class="badge rounded-pill ms-1" style="background:var(--g-50);color:var(--g-700);">{{ $totalHighly }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
            data-bs-target="#qualifiedPane" type="button" role="tab"
            style="color:var(--n-500);font-size:13px;">
            Qualified Jobseekers
            <span class="badge rounded-pill ms-1" style="background:var(--warn-bg);color:var(--warn);">{{ $totalQualified }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
            data-bs-target="#notQualifiedPane" type="button" role="tab"
            style="color:var(--n-500);font-size:13px;">
            Not Qualified Jobseekers
            <span class="badge rounded-pill ms-1" style="background:var(--danger-bg);color:var(--danger);">{{ $totalNotQualified }}</span>
        </button>
    </li>
</ul>

@php
    
    $renderTable = function ($list, $showActions) use ($actionsUnlocked, $isInhouse) {
        // wala ni magamit — gina-render diretso sa ubos, kini na lang stub para klaro sa purpose
    };
@endphp

<div class="tab-content">
    {{-- ── HIGHLY QUALIFIED TAB ── --}}
    <div class="tab-pane fade show active" id="highlyQualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
            @if($highlyQualified->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="ph ph-user-minus"></i></div>
                    <h6>No highly qualified jobseekers yet</h6>
                    <p>@if($isInhouse) Jobseekers who accepted the in-house interview with 75–100% match will appear here. @else Jobseekers with 75–100% match will appear here. @endif</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table peso-table mb-0 qualified-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Jobseeker</th><th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($highlyQualified as $i => $app)
                            <tr class="qualified-row">
                                <td style="color:var(--n-500);">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--g-600);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? 'None' }}</div>
                                            <div style="font-size:11px;color:var(--n-500);">{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;color:var(--n-700);"><i class="ph ph-phone me-1" style="color:var(--g-600);"></i>{{ $app->jobseeker->contact_number ?? 'None' }}</div>
                                    <div style="font-size:12px;color:var(--n-500);"><i class="ph ph-envelope-simple me-1" style="color:var(--g-600);"></i>{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                </td>
                                <td style="text-align:center;"><span class="fw-bold" style="font-size:15px;color:var(--g-700);">{{ $app->match_percentage }}%</span></td>
                                <td style="text-align:center;"><span class="badge-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                <td style="text-align:center;">
                                    @include('company.jobs.partials.applicant-actions', ['app' => $app])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── QUALIFIED TAB ── --}}
    <div class="tab-pane fade" id="qualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
            @if($qualified->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="ph ph-user-minus"></i></div>
                    <h6>No qualified jobseekers yet</h6>
                    <p>@if($isInhouse) Jobseekers who accepted the in-house interview with 50–74% match will appear here. @else Jobseekers with 50–74% match will appear here. @endif</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table peso-table mb-0 qualified-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Jobseeker</th><th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qualified as $i => $app)
                            <tr class="qualified-row">
                                <td style="color:var(--n-500);">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--g-600);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? 'None' }}</div>
                                            <div style="font-size:11px;color:var(--n-500);">{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;color:var(--n-700);"><i class="ph ph-phone me-1" style="color:var(--g-600);"></i>{{ $app->jobseeker->contact_number ?? 'None' }}</div>
                                    <div style="font-size:12px;color:var(--n-500);"><i class="ph ph-envelope-simple me-1" style="color:var(--g-600);"></i>{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                </td>
                                <td style="text-align:center;"><span class="fw-bold" style="font-size:15px;color:var(--warn);">{{ $app->match_percentage }}%</span></td>
                                <td style="text-align:center;"><span class="badge-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                <td style="text-align:center;">
                                    @include('company.jobs.partials.applicant-actions', ['app' => $app])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── NOT QUALIFIED TAB ── --}}
    <div class="tab-pane fade" id="notQualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
            @if($notQualified->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="ph ph-user-minus"></i></div>
                    <h6>No not-qualified jobseekers yet</h6>
                    <p>@if($isInhouse) Jobseekers who accepted the in-house interview with below 50% match will appear here. @else Jobseekers with below 50% match will appear here. @endif</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table peso-table mb-0 qualified-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Jobseeker</th><th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notQualified as $i => $app)
                            <tr class="qualified-row">
                                <td style="color:var(--n-500);">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--g-600);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:var(--g-700);">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? 'None' }}</div>
                                            <div style="font-size:11px;color:var(--n-500);">{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;color:var(--n-700);"><i class="ph ph-phone me-1" style="color:var(--g-600);"></i>{{ $app->jobseeker->contact_number ?? 'None' }}</div>
                                    <div style="font-size:12px;color:var(--n-500);"><i class="ph ph-envelope-simple me-1" style="color:var(--g-600);"></i>{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                </td>
                                <td style="text-align:center;"><span class="fw-bold" style="font-size:15px;color:var(--danger);">{{ $app->match_percentage }}%</span></td>
                                <td style="text-align:center;"><span class="badge-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                <td style="text-align:center;">
                                    @include('company.jobs.partials.applicant-actions', ['app' => $app])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.qualified-row').forEach(row => {
            const nameCell = row.querySelector('td:nth-child(2)');
            const name = nameCell ? nameCell.innerText.toLowerCase() : '';
            row.style.display = name.includes(q) ? '' : 'none';
        });
    });

    document.querySelectorAll('.confirm-hired').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = btn.closest('form');
            Swal.fire({
                title: 'Mark as Hired?',
                text: 'This jobseeker will be marked as HIRED and will be notified. You can change this later if they do not report for work.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28812F',
                cancelButtonColor: 'var(--n-400)',
                confirmButtonText: 'Yes, mark as hired',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });

    document.querySelectorAll('.confirm-rejected').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = btn.closest('form');
            Swal.fire({
                title: 'Mark as Rejected?',
                text: 'This jobseeker will be marked as REJECTED and will be notified. You can change this later.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C4271B',
                cancelButtonColor: 'var(--n-400)',
                confirmButtonText: 'Yes, mark as rejected',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@endsection