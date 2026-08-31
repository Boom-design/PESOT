@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-briefcase me-2" style="color:var(--g-600);"></i>Job Fair Postings
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            Accept a vacancy into a fair, or turn it away. An accepted posting goes live
            {{ \App\Support\JobFairPostingWindow::daysBefore() }} days before that fair.
        </p>
    </div>

    {{-- Ang employer nga niduol sa adlaw sa fair, dala ang iyang papel ug ang
         iyang bakante. Naa siya dinhi ug dili sa Employers kay usa ra ang
         gibuhat sa Job Fair desk sa employer: ang pagdala kaniya sa fair. --}}
    <a href="{{ route('staff.employers.walkin') }}"
       class="btn btn-sm fw-semibold"
       style="background:var(--g-600);color:#fff;border:none;border-radius:8px;
              font-size:12px;padding:8px 16px;white-space:nowrap;">
        <i class="ph-fill ph-storefront me-1"></i> Walk-in Employer
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--warn);">{{ $totalClosed }}</div>
            <div class="text-muted small">Closed (Waiting for Event)</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--g-700);">{{ $totalOpen }}</div>
            <div class="text-muted small">Open (Live)</div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2">
        @foreach(['closed' => 'Closed', 'open' => 'Open'] as $val => $label)
        <a href="{{ route('staff.jobfair.postings', array_merge(request()->query(), ['status' => $val, 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('status','closed') === $val
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        {{-- Ang sala nga gigamit sa dili pa mo-post ug tinapok: ang fair nga
             para sa Education modawat sa Education ra, mao nga salaan ang
             listahan hangtod nga ang nahibilin mao na ang i-post. --}}
        <select id="industryFilter" class="form-select form-select-sm"
                style="max-width:220px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
            <option value="">All industries</option>
            @foreach($industries as $group)
                <option value="{{ $group }}" {{ $industry === $group ? 'selected' : '' }}>{{ $group }}</option>
            @endforeach
        </select>

        {{-- Duha ka pill, dili dropdown: ang gipili makita gikan sa layo, ug
             ang pag-klik pag-usab sa aktibo mao ang pagpalong niini. Walay
             "any" nga linya nga labangan, ug walay Clear nga buton. --}}
        @foreach(['yes' => 'Accepts PWD', 'no' => 'Does not accept PWD'] as $val => $label)
        <a href="{{ route('staff.jobfair.postings', array_merge(request()->query(), [
                'pwd'  => $pwd === $val ? null : $val,
                'page' => 1,
           ])) }}"
           class="btn btn-sm fw-semibold"
           title="{{ $pwd === $val ? 'Showing these only — click to show every posting again' : 'Show only these' }}"
           style="{{ $pwd === $val
               ? 'background:var(--g-600);color:#fff;border:1px solid var(--g-600);'
               : 'background:#fff;color:var(--g-700);border:1px solid var(--n-200);' }}
               border-radius:8px;font-size:12px;padding:5px 14px;white-space:nowrap;">
            <i class="ph {{ $pwd === $val ? 'ph-check' : 'ph-wheelchair' }} me-1"></i>{{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Ang search sa tuong ngilit gyud. Ang ms-auto mao ang nagtulak kaniya:
         ang tab ug ang sala magkuyog sa wala, ug ang usa ka kahon nga naglutaw
         sa tuo mas limpyo basahon kay sa kwatro ka butang nga nagsigpit. --}}
    <div class="input-group ms-auto" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search title or company..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

@if($jobs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-briefcase" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No job fair postings found</div>
    </div>
@else
<form method="POST" action="{{ route('staff.jobfair.postings.bulk') }}" id="bulkPostForm">
    @csrf

    {{-- ── ANG TINAPOK NGA PAG-POST ──
         Walay bakante nga mosulod sa fair nga siya ra. Ang desk mosala sa
         listahan, motsek, ug mo-post — usa ka desisyon, usa ka buton.
         Ang fair nga wala modawat sa usa ka posting mopatay sa tsek niini,
         mao nga ang gipadala mao ra gyud ang mahimong modawat. --}}
    @if($status === 'closed' && $events->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label class="fw-semibold mb-0" style="color:var(--g-700);font-size:12.5px;white-space:nowrap;">
                <i class="ph ph-flag-banner me-1" style="color:var(--g-600);"></i>Post the ticked vacancies to
            </label>
            <select name="job_fair_id" id="bulkEventSelect" class="form-select form-select-sm"
                    style="max-width:340px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;" required>
                @foreach($events as $event)
                    <option value="{{ $event->job_fair_events_id }}">
                        {{ $event->title }} — {{ $event->event_date->format('M d, Y') }}{{ $event->pwd_only ? ' · PWD only' : '' }}
                    </option>
                @endforeach
            </select>
            <span id="bulkHint" style="font-size:11.5px;color:var(--n-500);"></span>

            {{-- Ang buton sa tuong ngilit, parehas sa search sa taas. Siya ang
                 kataposang lihok sa linya, mao nga didto siya matapos. --}}
            <button type="submit" id="bulkPostButton" class="btn btn-sm fw-semibold ms-auto" disabled
                style="background:var(--g-700);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;">
                <i class="ph ph-check-circle me-1"></i> Post Selected (<span id="bulkCount">0</span>)
            </button>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        @if($status === 'closed' && $events->isNotEmpty())
                        <th style="border:none;padding:12px 16px;width:40px;">
                            <input type="checkbox" class="form-check-input" id="bulkCheckAll"
                                   title="Select all on this page">
                        </th>
                        @endif
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Slots</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Accepts PWD</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Industry</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Posting Status</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $i => $job)
                    <tr style="font-size:13px;">
                        @if($status === 'closed' && $events->isNotEmpty())
                        <td style="padding:12px 16px;">
                            @if($job->posting_status === 'pending')
                            <input type="checkbox" class="form-check-input bulk-check" name="job_ids[]"
                                   value="{{ $job->job_qualifications_id }}"
                                   data-job-id="{{ $job->job_qualifications_id }}">
                            @endif
                        </td>
                        @endif
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $jobs->firstItem() + $i }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">{{ $job->title }}</td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $job->company->company_name ?? 'None' }}
                            @if($job->company->is_overseas ?? false)
                                <span style="color:var(--info);font-size:10px;font-weight:600;">Overseas</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">{{ $job->slots }}</td>
                        {{-- Ang timailhan nga gigamit sa pagpili: ang fair para sa
                             PWD modawat sa posting nga modawat kanila. Gisulat
                             ang "No" sad, dili lang ang "Yes" — kung ang "No" ra
                             ang gisala, kinahanglan makita nga mao gyud sila. --}}
                        <td style="padding:12px 16px;">
                            @if($job->acceptsPwd())
                                <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">Yes</span>
                                @if($job->disability_types)
                                    <div style="font-size:10.5px;color:var(--n-500);">
                                        {{ implode(', ', $job->disability_types) }}
                                    </div>
                                @endif
                            @else
                                <span class="fw-semibold" style="color:var(--n-400);font-size:11px;">No</span>
                            @endif
                        </td>
                        {{-- Ang industriya sa posting, dili sa employer: usa ka
                             establisemento mahimong naay bakante sa lain-laing
                             industriya, ug ang fair mopili base sa posting. --}}
                        <td style="padding:12px 16px;color:var(--n-700);font-size:12px;">
                            @if($job->industry_group)
                                {{ $job->industry_group }}
                            @else
                                <span style="color:var(--n-400);">Not set</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($job->posting_status === 'pending')
                                <span class="fw-semibold" style="color:var(--warn);font-size:11px;">Pending</span>
                            @elseif($job->posting_status === 'approved')
                                <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">Approved</span>
                            @else
                                <span class="fw-semibold" style="color:var(--danger);font-size:11px;">Rejected</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($job->status === 'open')
                                <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">Open</span>
                            @else
                                <span class="fw-semibold" style="color:var(--warn);font-size:11px;">Closed</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($job->posting_status === 'pending')
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        style="background:var(--g-700);color:#fff;border:none;border-radius:6px;font-size:11px;padding:4px 12px;"
                                        onclick="openAcceptModal({{ $job->job_qualifications_id }}, @js($job->title), {{ $job->requested_job_fair_id ?? 'null' }})">
                                        <i class="ph ph-check me-1"></i>Accept
                                    </button>
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        style="background:var(--danger);color:#fff;border:none;border-radius:6px;font-size:11px;padding:4px 12px;"
                                        onclick="openRejectModal({{ $job->job_qualifications_id }}, '{{ addslashes($job->title) }}')">
                                        <i class="ph ph-x me-1"></i>Reject
                                    </button>
                                </div>
                            @elseif($job->posting_status === 'approved')
                                <span style="font-size:11px;color:var(--n-500);">None</span>
                            @else
                                @if($job->remarks)
                                    <span style="font-size:11px;color:var(--danger);" title="{{ $job->remarks }}">
                                        <i class="ph ph-warning-circle"></i> Rejected
                                    </span>
                                @else
                                    <span style="font-size:11px;color:var(--n-500);">None</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ $jobs->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $jobs->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $jobs->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                    </li>
                    @foreach($jobs->getUrlRange(1, $jobs->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $jobs->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $jobs->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$jobs->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $jobs->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
</form>
@endif

{{-- ── ACCEPT MODAL ── --}}
{{-- Ang pag-dawat sa posting mao ang pagpili sa fair. Wala nay kalainan ang
     duha: ang bakante buhi ra kung naa siya sa usa ka fair, ug ang fair mao
     ang nagsulti kung kanus-a siya makita sa jobseeker. --}}
<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 8px 32px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background:var(--g-600);">
                <h6 class="modal-title fw-bold text-white">
                    <i class="ph ph-check-circle me-2"></i>Accept into a Job Fair
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="acceptForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p style="font-size:13px;color:var(--n-700);margin-bottom:12px;">
                        <strong id="acceptJobTitle" style="color:var(--g-700);"></strong>
                        will be listed at the fair you choose, and the jobseekers it reaches are the ones
                        registered for that fair.
                    </p>

                    @if($events->isEmpty())
                        <div class="p-3 rounded-3" style="background:var(--warn-bg);border:1px solid var(--warn-br);font-size:12.5px;color:var(--n-700);">
                            <i class="ph-fill ph-warning-circle me-1" style="color:var(--warn);"></i>
                            There is no upcoming job fair to accept this into. Create the event first, then come back.
                        </div>
                    @else
                        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Job Fair *</label>
                        <select name="job_fair_id" id="acceptEventSelect" class="form-select" required
                                style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                            @foreach($events as $event)
                                <option value="{{ $event->job_fair_events_id }}">
                                    {{ $event->title }} — {{ $event->event_date->format('M d, Y') }}{{ $event->pwd_only ? ' · PWD only' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div id="acceptRequestedNote" style="font-size:11px;color:var(--g-600);margin-top:6px;display:none;">
                            <i class="ph ph-info me-1"></i>Pre-selected: this is the fair the employer asked for.
                        </div>
                        {{-- Ang fair nga dili modawat niining bakante gitago sa
                             listahan, ug ang hinungdan gisulat dinhi aron dili
                             magtuo ang staff nga nawala lang siya. --}}
                        <div id="acceptBlockedNote" style="font-size:11px;color:var(--n-500);margin-top:6px;display:none;"></div>
                    @endif
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--n-50);">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if($events->isNotEmpty())
                    <button type="submit" id="acceptSubmit" class="btn btn-sm fw-semibold"
                        style="background:var(--g-700);color:#fff;border:none;border-radius:8px;padding:8px 20px;">
                        <i class="ph ph-check-circle me-1"></i> Accept
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── REJECT MODAL ── --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 8px 32px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background:var(--danger-bg);border-bottom:1px solid var(--danger-br);">
                <h6 class="modal-title fw-bold text-white">
                    <i class="ph ph-x-circle me-2"></i>Reject Job Posting
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p style="font-size:13px;color:var(--n-700);margin-bottom:12px;">
                        You are about to reject <strong id="rejectJobTitle" style="color:var(--g-700);"></strong> for job fair use.
                    </p>
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Reason for Rejection *</label>
                    <textarea name="remarks" class="form-control" rows="3" required
                        placeholder="Enter the reason for rejecting this job posting..."
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;"></textarea>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--n-50);">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:var(--danger);color:#fff;border:none;border-radius:8px;padding:8px 20px;">
                        <i class="ph ph-x-circle me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Kada posting: ang fair id ngadto sa hinungdan nga dili siya modawat, o
    // null kung modawat siya. Ang server ang nagkwenta, mao nga ang gitago
    // dinhi ug ang gisalikway sa pag-save parehas gyud.
    const EVENT_FIT = @js($eventFit);

    let searchTimer;
    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            goToFilter('search', this.value.trim());
        }, 500);
    });

    // Ang sala nagpabilin sa URL, mao nga ang pager ug ang tab magdala niini.
    function goToFilter(key, value) {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    document.getElementById('industryFilter')?.addEventListener('change', function () {
        goToFilter('industry', this.value);
    });

    // ── ANG TINAPOK NGA PAG-POST ──
    // Ang tsek sa posting nga dili dawaton sa piniling fair gipatay, ug ang
    // hinungdan gisulat sa tapad. Gipugngan sad kini sa server: ang usa ka
    // posting nga wala mohaom ginganlan sa balik ug wala gi-post.
    (function () {
        const form   = document.getElementById('bulkPostForm');
        const select = document.getElementById('bulkEventSelect');
        if (!form || !select) return;

        const all    = document.getElementById('bulkCheckAll');
        const button = document.getElementById('bulkPostButton');
        const count  = document.getElementById('bulkCount');
        const hint   = document.getElementById('bulkHint');
        const boxes  = Array.prototype.slice.call(form.querySelectorAll('.bulk-check'));

        function refresh() {
            let   blocked = 0;
            let   ticked  = 0;

            boxes.forEach(function (box) {
                const why = (EVENT_FIT[box.dataset.jobId] || {})[select.value] || null;
                box.disabled = !!why;
                box.title    = why || '';
                if (why) {
                    box.checked = false;
                    blocked++;
                }
                if (box.checked) ticked++;
            });

            if (count)  count.textContent = ticked;
            if (button) button.disabled = ticked === 0;
            if (all) {
                const usable = boxes.filter(function (b) { return !b.disabled; });
                all.disabled = usable.length === 0;
                all.checked  = usable.length > 0 && usable.every(function (b) { return b.checked; });
            }
            if (hint) {
                hint.textContent = blocked
                    ? blocked + ' on this page cannot join this fair — hover a greyed box for the reason.'
                    : '';
            }
        }

        boxes.forEach(function (box) { box.addEventListener('change', refresh); });
        select.addEventListener('change', refresh);

        if (all) {
            all.addEventListener('change', function () {
                const on = this.checked;
                boxes.forEach(function (box) {
                    if (!box.disabled) box.checked = on;
                });
                refresh();
            });
        }

        refresh();
    })();

    function openAcceptModal(jobId, jobTitle, requestedFairId) {
        document.getElementById('acceptJobTitle').textContent = '"' + jobTitle + '"';
        document.getElementById('acceptForm').action = '{{ url("staff/jobfair/postings") }}/' + jobId + '/approve';

        const select  = document.getElementById('acceptEventSelect');
        const note    = document.getElementById('acceptRequestedNote');
        const blocked = document.getElementById('acceptBlockedNote');
        const submit  = document.getElementById('acceptSubmit');
        if (!select) {
            new bootstrap.Modal(document.getElementById('acceptModal')).show();
            return;
        }

        const fit     = EVENT_FIT[jobId] || {};
        const reasons = [];

        // Ang fair nga mosalikway niining bakante gitago, dili gi-disable ra:
        // ang gi-disable nga option makita gihapon ug basahon isip "pilia ni
        // ug e-ayo", nga dili man mahimo sa staff.
        Array.prototype.forEach.call(select.options, function (option) {
            const why = fit[option.value] || null;
            option.hidden   = !!why;
            option.disabled = !!why;
            if (why) reasons.push(why);
        });

        const usable = Array.prototype.filter.call(select.options, function (o) { return !o.disabled; });

        if (usable.length) {
            // Ang gipangayo sa employer gipili nang daan, apan mausab \u2014
            // hangyo siya, dili reserbasyon.
            const hasRequested = requestedFairId && usable.some(function (o) {
                return o.value === String(requestedFairId);
            });
            select.value = hasRequested ? String(requestedFairId) : usable[0].value;
            if (note) note.style.display = hasRequested ? '' : 'none';
        } else if (note) {
            note.style.display = 'none';
        }

        select.disabled = usable.length === 0;
        if (submit) submit.disabled = usable.length === 0;

        if (blocked) {
            blocked.innerHTML = '';
            blocked.style.display = reasons.length ? '' : 'none';

            if (reasons.length) {
                const lead = document.createElement('div');
                lead.textContent = usable.length
                    ? reasons.length + ' fair(s) hidden \u2014 they will not take this vacancy:'
                    : 'No upcoming fair will take this vacancy:';
                blocked.appendChild(lead);

                reasons.forEach(function (why) {
                    const line = document.createElement('div');
                    line.textContent = '\u2022 ' + why;
                    blocked.appendChild(line);
                });
            }
        }

        new bootstrap.Modal(document.getElementById('acceptModal')).show();
    }

    function openRejectModal(jobId, jobTitle) {
        document.getElementById('rejectJobTitle').textContent = '"' + jobTitle + '"';
        document.getElementById('rejectForm').action = '{{ url("staff/jobfair/postings") }}/' + jobId + '/reject';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
</script>
@endpush

@endsection
