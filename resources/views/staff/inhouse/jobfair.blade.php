@extends('staff.layouts.app')

@section('content')

{{-- Ang Approved nga ihap naa sa tumoy sa tab row, sulod sa parehas nga kahon
     nga gigamit sa Total Jobs sa Company Interview ug In-house. Usa ka numero
     dili kinahanglan ug stat card, ug tapad sa tabs mabasa siya isip ihap sa
     kung unsa ang gipakita sa dagkotan nga tab. --}}
@include('partials.staff-activity-tabs', $jobs !== null ? ['tabsRight' => '
    <div class="d-flex align-items-center gap-2" style="border:1px solid var(--n-200);background:#fff;
         border-radius:8px;padding:5px 14px;white-space:nowrap;">
        <span style="font-size:12px;color:var(--n-500);">Approved</span>
        <span class="fw-bold" style="color:var(--g-600);font-size:15px;">' . $totalApprovedJobs . '</span>
    </div>
'] : [])

{{-- ── SUB-TABS ──
     SRA ra ang naay duha ka panel dinhi. Kung tapadon sila sa usa ka taas nga
     page, ang pag-imbita mahulog sa ubos sa tibuok lamesa sa posting, ug ang
     desk mangita niini kada higayon. Ang gipiling panel naa sa URL, aron ang
     pagpili ug fair sa sulod sa Invite dili mobalik sa Postings. --}}
@if($staffRole === 'sra')
@php
    $panelOn  = 'background:var(--g-600);color:#fff;border:none;';
    $panelOff = 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;';
@endphp
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('staff.inhouse.jobfair', ['panel' => 'postings']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $panel === 'postings' ? $panelOn : $panelOff }}border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="ph-fill ph-briefcase me-1"></i> Job Fair Postings
    </a>
    <a href="{{ route('staff.inhouse.jobfair', ['panel' => 'invite']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $panel === 'invite' ? $panelOn : $panelOff }}border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="ph-fill ph-envelope-simple me-1"></i> Invite Overseas Agencies
    </a>
</div>
@endif

@if($jobs !== null && !($staffRole === 'sra' && $panel === 'invite'))
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1" style="color:var(--g-700);">
                <i class="ph-fill ph-briefcase me-2" style="color:var(--g-600);"></i>
                List of Job Fair Postings
            </h5>
            <p class="mb-0" style="font-size:13px;color:var(--n-500);">
                Approved postings for job fair use. They stay closed and go live
                {{ \App\Support\JobFairPostingWindow::daysBefore() }} days before the event.
            </p>
        </div>
    </div>

    {{-- Ang event ang sala, dili ang posting status. Kining tab kay monitoring:
         ang pag-approve naa sa Job Fair desk, mao nga ang Pending ug Rejected
         nga card nagsulti ug numero nga walay mahimo ang nagbasa niini. --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <select id="jobFairEventFilter" class="form-select form-select-sm"
                style="max-width:340px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
            <option value="">All job fair events</option>
            @foreach($jobFairOptions as $option)
            <option value="{{ $option->job_fair_events_id }}"
                {{ (string) $eventId === (string) $option->job_fair_events_id ? 'selected' : '' }}>
                {{ $option->title }}
                @if($option->event_date)
                    — {{ \Carbon\Carbon::parse($option->event_date)->format('M d, Y') }}
                @endif
            </option>
            @endforeach
        </select>
        @if($eventId)
        <span style="font-size:11.5px;color:var(--n-500);">
            Showing {{ $jobs->total() }} posting(s) brought into this event.
        </span>
        @endif
    </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:var(--g-600);">
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Slots</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($jobs->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center py-5" style="border:none;">
                                <i class="ph ph-briefcase" style="font-size:48px;color:var(--n-300);"></i>
                                <div class="mt-3 fw-semibold" style="color:var(--g-700);">No job fair postings</div>
                                <div class="text-muted small mt-1">
                                    {{ $eventId
                                        ? 'No approved posting has been brought into this event yet.'
                                        : 'Approved job fair postings will appear here.' }}
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($jobs as $i => $job)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $jobs->firstItem() + $loop->index }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">{{ $job->title }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $job->company->company_name ?? 'None' }}</td>
                            <td style="padding:12px 16px;color:var(--n-700);">{{ $job->slots }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $pBadge = [
                                        'pending'  => ['bg' => 'var(--warn)', 'label' => 'Pending'],
                                        'approved' => ['bg' => 'var(--g-700)', 'label' => 'Approved'],
                                        'rejected' => ['bg' => 'var(--danger)', 'label' => 'Rejected'],
                                    ][$job->posting_status] ?? ['bg' => 'var(--n-500)', 'label' => ucfirst($job->posting_status)];
                                @endphp
                                <span class="fw-semibold" style="color:{{ $pBadge['bg'] }};font-size:11px;">
                                    {{ $pBadge['label'] }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <button type="button" class="btn btn-sm fw-semibold"
                                    style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 12px;"
                                    data-bs-toggle="modal" data-bs-target="#jfJobModal{{ $job->job_qualifications_id }}">
                                    <i class="ph-fill ph-eye me-1"></i>View Details
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="jfJobModal{{ $job->job_qualifications_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
                                <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
                                    <div class="modal-header" style="background:var(--g-600);flex-shrink:0;">
                                        <h6 class="modal-title text-white fw-bold">
                                            <i class="ph-fill ph-briefcase me-2"></i>{{ $job->title }}
                                        </h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4" style="overflow-y:auto;flex:1;">
                                        <div class="row g-2 mb-3" style="font-size:13px;">
                                            <div class="col-md-6">
                                                <div style="color:var(--n-500);font-size:11px;">Company</div>
                                                <div style="color:var(--g-700);font-weight:600;">{{ $job->company->company_name ?? 'None' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="color:var(--n-500);font-size:11px;">Nature of Work</div>
                                                <div style="color:var(--g-700);font-weight:600;">{{ $job->type ? ucfirst(str_replace('_',' ', $job->type)) : 'None' }}</div>
                                            </div>
                                            <div class="col-12">
                                                <div style="color:var(--n-500);font-size:11px;">Job Description</div>
                                                <div style="color:var(--g-700);font-weight:600;">{{ $job->description ?? 'None' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="color:var(--n-500);font-size:11px;">Place of Work</div>
                                                <div style="color:var(--g-700);font-weight:600;">{{ $job->location ?? 'None' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="color:var(--n-500);font-size:11px;">Vacancy Count</div>
                                                <div style="color:var(--g-700);font-weight:600;">{{ $job->slots ?? 'None' }}</div>
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
                                                    style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                    <i class="ph-fill ph-check-circle me-1"></i> Approve Job Posting
                                                </button>
                                            </form>
                                            <form action="{{ route('staff.jobs.reject', $job->job_qualifications_id) }}" method="POST">
                                                @csrf
                                                <label class="form-label fw-semibold small" style="color:var(--warn);">Reason for Rejection</label>
                                                <textarea name="remarks" rows="2" required class="form-control mb-2"
                                                    style="border:1px solid var(--warn-br);border-radius:8px;font-size:13px;"
                                                    placeholder="State the reason for rejection..."></textarea>
                                                <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                    style="background:var(--danger);color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                    <i class="ph-fill ph-x-circle me-1"></i> Reject Job Posting
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer" style="border-top:1px solid var(--n-200);flex-shrink:0;">
                                        <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
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
                        <span class="d-flex align-items-center justify-content-center" style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--n-300);"><i class="ph ph-caret-left"></i></span>
                    @else
                        <a href="{{ $jobs->previousPageUrl() }}" class="d-flex align-items-center justify-content-center" style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--g-700);text-decoration:none;"><i class="ph ph-caret-left"></i></a>
                    @endif
                    <span class="fw-semibold px-2" style="font-size:13px;color:var(--g-700);white-space:nowrap;">Step {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}</span>
                    @if($jobs->hasMorePages())
                        <a href="{{ $jobs->nextPageUrl() }}" class="d-flex align-items-center gap-1 fw-semibold text-decoration-none" style="background:var(--g-600);color:#fff;border-radius:20px;padding:8px 18px;font-size:13px;">Next <i class="ph ph-caret-right"></i></a>
                    @else
                        <span class="d-flex align-items-center gap-1 fw-semibold" style="background:var(--n-200);color:var(--n-400);border-radius:20px;padding:8px 18px;font-size:13px;">Next <i class="ph ph-caret-right"></i></span>
                    @endif
                </div>
            </div>
            @endif
        </div>
</div>
@endif

@if($staffRole === 'lra')
<div class="mb-3">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-calendar-dots me-2" style="color:var(--g-600);"></i>
        Job Fair Events
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        Overview of ongoing and upcoming job fair events
    </p>
</div>

{{-- TABLE --}}
@if($events->isEmpty())
    <div class="card border-0 shadow-sm rounded-4 py-5 text-center" style="background:var(--n-0);">
        <div style="width:72px;height:72px;background:var(--g-600);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 6px 18px rgba(77,217,192,0.3);">
            <i class="ph-fill ph-calendar-dots" style="font-size:28px;color:#fff;"></i>
        </div>
        <div class="fw-bold" style="color:var(--g-700);font-size:14px;">No job fair events found</div>
        <div class="text-muted small mt-1">Job fair events will appear here once created by Job Fair staff.</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Event Title</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Date</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $i => $event)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $events->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $event->title }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $event->event_date->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $event->venue }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $colors = [
                                    'upcoming'  => 'var(--g-600)',
                                    'ongoing'   => 'var(--warn)',
                                    'completed' => 'var(--n-500)',
                                ];
                                $color = $colors[$event->status] ?? 'var(--n-500)';
                            @endphp
                            <span style="color:{{ $color }};font-weight:600;text-transform:capitalize;">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($events->hasPages())
        <div class="d-flex justify-content-center px-3 py-3" style="border-top:1px solid var(--n-50);">
            <div class="d-inline-flex align-items-center gap-2 px-2 py-1"
                style="background:#fff;border:1px solid var(--n-200);border-radius:30px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">

                @if($events->onFirstPage())
                    <span class="d-flex align-items-center justify-content-center"
                        style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--n-300);">
                        <i class="ph ph-caret-left"></i>
                    </span>
                @else
                    <a href="{{ $events->appends(request()->except('event_page'))->previousPageUrl() }}"
                       class="d-flex align-items-center justify-content-center"
                       style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--g-700);text-decoration:none;">
                        <i class="ph ph-caret-left"></i>
                    </a>
                @endif

                <span class="fw-semibold px-2" style="font-size:13px;color:var(--g-700);white-space:nowrap;">
                    Step {{ $events->currentPage() }} of {{ $events->lastPage() }}
                </span>

                @if($events->hasMorePages())
                    <a href="{{ $events->appends(request()->except('event_page'))->nextPageUrl() }}"
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
@endif
@endif

{{-- ── INVITE OVERSEAS AGENCIES ──
     SRA ra. Kaniadto naa siyay kaugalingong tab nga "Overseas Line-up"; walay
     nakasabot sa maong ngalan kung para siya sa unsa, mao nga gi-ngalan siya
     sa lihok mismo ug gihimo nga panel dinhi sa Job Fair nga tab. --}}
@if($staffRole === 'sra' && $panel === 'invite')
    @include('partials.overseas-lineup')
@endif

@push('scripts')
<script>
    // Ang pagpili ug fair mo-reload nga dala ang pinili sa URL, aron ang pager
    // ug ang pagbalik sa page magpabilin sa parehas nga event.
    document.getElementById('jobFairEventFilter')?.addEventListener('change', function () {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('event_id', this.value);
        } else {
            url.searchParams.delete('event_id');
        }
        url.searchParams.set('job_page', 1);
        window.location.href = url.toString();
    });
</script>
@endpush

@endsection
