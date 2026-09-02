@extends('staff.layouts.app')

@section('content')

@include('partials.staff-activity-tabs')

@php
    // Gigamit sa ubos sa Take down: ang LRA walay take down. Kaniadto siya
    // gitakda sa tab row nga naa dinhi; ang row usa na ka partial, mao nga
    // kinahanglan niya ang kaugalingon niyang linya.
    $staffRoleForTabs = optional(Auth::user()->staff)->staff_role ?? 'staff';
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-calendar-check me-2" style="color:var(--g-600);"></i>
            Pending In-house Schedules
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            In-house interview requests waiting to be accepted or rejected
        </p>
    </div>
</div>

<div class="mb-3 p-2 px-3 rounded-3" style="background:var(--n-50);font-size:12px;color:var(--g-700);">
    <i class="ph-fill ph-info me-1"></i>
    An in-house request waits for you. The dates the employer picked are held while you decide,
    and the vacancy stays hidden from jobseekers until you accept — so a rejection stops the
    interview before anyone has applied to it. Once you accept, the request leaves this page and
    the vacancy is listed under In-house Job Vacancy. Company-interview and job fair postings are
    not reviewed here; they go live on submit.
</div>

{{-- SEARCH --}}
<div class="d-flex justify-content-end mb-3 flex-wrap gap-2">
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search company..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($schedules->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-calendar-x" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">Nothing waiting for a decision</div>
        <div class="text-muted small mt-1">
            Accepted requests are listed under In-house Job Vacancy.
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Type</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">In-house Date</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">In-house Time</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Vacancies Offered</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $i => $item)
                    @if($item->source === 'schedule')
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $schedules->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $item->employer->company_name ?? $item->employer->name ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span style="color:var(--g-700);font-weight:600;font-size:12px;">Schedule Only</span>
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $item->schedule_window_label }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ \Carbon\Carbon::parse($item->preferred_time)->format('h:i A') }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:var(--n-500);">
                            {{-- Schedule-only: walay job posting, mao nga walay
                                 bakante nga maihap. Ang gipangayo ra niya kay
                                 lugar ug petsa. --}}
                            None
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            @if($item->venue_type === 'custom')
                                {{ $item->venue_address }}
                            @else
                                PESO Office
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'pending'  => ['color' => 'var(--warn)', 'label' => 'Pending'],
                                    'accepted' => ['color' => 'var(--g-700)', 'label' => 'Accepted'],
                                    'rejected' => ['color' => 'var(--danger)', 'label' => 'Rejected'],
                                ][$item->status] ?? ['color' => 'var(--n-500)', 'label' => ucfirst($item->status)];
                            @endphp
                            <span style="color:{{ $badge['color'] }};font-weight:600;font-size:12px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <a href="{{ route('staff.inhouse.view', $item->inhouse_schedules_id) }}"
                               class="btn btn-sm fw-semibold"
                               style="background:var(--g-600);
                                      color:#fff;border:none;border-radius:8px;font-size:12px;">
                                <i class="ph-fill ph-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    @else
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $schedules->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $item->company->company_name ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span style="color:var(--warn);font-weight:600;font-size:12px;">Job Posting</span>
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $item->schedule_window_label }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">8:00 AM – 5:00 PM</td>
                        <td style="padding:12px 16px;text-align:center;color:var(--g-700);font-weight:600;">
                            {{ $item->slots }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $item->venue_type === 'other' ? $item->venue_address : 'PESO Office' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $jbadge = [
                                    'pending'  => ['color' => 'var(--warn)', 'label' => 'Pending'],
                                    'approved' => ['color' => 'var(--g-700)', 'label' => 'Approved'],
                                    'rejected' => ['color' => 'var(--danger)', 'label' => 'Rejected'],
                                ][$item->posting_status] ?? ['color' => 'var(--warn)', 'label' => 'Pending'];
                            @endphp
                            <span style="color:{{ $jbadge['color'] }};font-weight:600;font-size:12px;">
                                {{ $jbadge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <button type="button" class="btn btn-sm fw-semibold"
                                style="background:{{ $item->posting_status === 'pending' ? 'var(--warn)' : 'var(--g-600)' }};color:#fff;border:none;border-radius:8px;font-size:12px;"
                                data-bs-toggle="modal" data-bs-target="#jobInhouseModal{{ $item->job_qualifications_id }}">
                                <i class="ph-fill {{ $item->posting_status === 'pending' ? 'ph-gavel' : 'ph-eye' }} me-1"></i>{{ $item->posting_status === 'pending' ? 'Decide' : 'View' }}
                            </button>
                        </td>
                    </tr>

                    {{-- JOB-BASED IN-HOUSE DETAIL MODAL --}}
                    <div class="modal fade" id="jobInhouseModal{{ $item->job_qualifications_id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header" style="background:var(--g-600);">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="ph-fill ph-briefcase me-2"></i>{{ $item->title }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-2 mb-3" style="font-size:13px;">
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Company</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $item->company->company_name ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Position</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $item->title }}</div>
                                        </div>
                                        {{-- Kini ang numero nga gibasehan sa desisyon: usa ka
                                             tibuok adlaw sa PESO Office para sa duha ka bakante
                                             mao gyud ang ehemplo nga gihatag sa LRA. --}}
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Vacancies Offered</div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                {{ $item->slots }} slot(s)
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">
                                                {{ $item->confirmed_date ? 'Interview Date' : 'Available Window' }}
                                            </div>
                                            <div style="color:var(--g-700);font-weight:600;">
                                                {{ $item->confirmed_date ? $item->confirmed_date->format('M d, Y') : $item->schedule_window_label }}
                                                (8:00 AM – 5:00 PM)
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:var(--n-500);font-size:11px;">Venue</div>
                                            <div style="color:var(--g-700);font-weight:600;">{{ $item->venue_type === 'other' ? $item->venue_address : 'PESO Office' }}</div>
                                        </div>
                                    </div>

                                    @if($item->venue_type === 'peso_office' && $item->preferred_date)
                                    <div class="p-2 mb-3 rounded-3" style="background:{{ $item->scheduled_count >= (int) config('peso.schedule.inhouse_daily_companies') ? 'var(--danger-bg)' : 'var(--n-50)' }};font-size:12.5px;">
                                        <i class="ph-fill ph-users-three me-1"></i>
                                        <strong>{{ $item->scheduled_count }}/{{ config('peso.schedule.inhouse_daily_companies') }}</strong> other companies already approved for PESO Office on {{ $item->preferred_date->format('M d, Y') }}
                                    </div>
                                    @endif

                                    @if($item->posting_status === 'rejected')
                                    <div class="p-2 mb-3 rounded-3" style="background:var(--danger-bg);font-size:12.5px;color:var(--danger);">
                                        <strong>Rejection Reason:</strong> {{ $item->remarks ?? 'None' }}
                                    </div>
                                    @endif

                                    {{-- The posting in full, qualifications included.
                                         LRA is deciding whether the PESO Office gives this
                                         employer a day; the summary above says when and
                                         where, and this says what they are actually
                                         hiring for and who they will accept. Same partial
                                         the employer and the reports pages use, so one
                                         posting reads the same everywhere. --}}
                                    <div class="mt-3">
                                        @include('partials.job-details', ['job' => $item])
                                    </div>

                                    {{-- LRA staff, 2026-08-23: ang in-house mo-agi na usab sa
                                         approval. Ang PESO Office dili modawat sa tanan nga
                                         kompanya, ug ang pagsalikway kinahanglan makapugong sa
                                         interview sa dili pa siya mahitabo — mao nga ang porma
                                         magbag-o sumala sa kahimtang: Accept/Reject samtang
                                         pending, Take Down kung buhi na. --}}
                                    @if($item->posting_status === 'pending')
                                    <div class="mt-3 p-3 rounded-3" style="background:var(--warn-bg);border:1px solid var(--warn-br);">
                                        <div style="font-size:12.5px;color:var(--n-700);" class="mb-3">
                                            <i class="ph-fill ph-clock-countdown me-1" style="color:var(--warn);"></i>
                                            <strong style="color:var(--warn);">Waiting for your decision.</strong>
                                            @if($item->preferred_date)
                                                <strong>{{ $item->schedule_window_label }}</strong>
                                                @if(!$item->preferred_date_last->isSameDay($item->preferred_date))
                                                    are held for this employer while you decide.
                                                @else
                                                    is held for this employer while you decide.
                                                @endif
                                            @endif
                                            Jobseekers cannot see this vacancy yet.
                                        </div>

                                        <form action="{{ route('staff.jobs.approve', $item->job_qualifications_id) }}"
                                              method="POST" class="mb-3">
                                            @csrf
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="ph-fill ph-check-circle me-1"></i> Accept — hold the dates and publish the vacancy
                                            </button>
                                        </form>

                                        <form action="{{ route('staff.jobs.reject', $item->job_qualifications_id) }}" method="POST">
                                            @csrf
                                            <label class="form-label fw-semibold small" style="color:var(--danger);">
                                                Reject — reason (sent to the employer)
                                            </label>
                                            <textarea name="remarks" rows="2" required
                                                class="form-control mb-2"
                                                style="border:1px solid var(--n-200);border-radius:8px;font-size:13px;"
                                                placeholder="e.g. only two vacancies offered for a full office day..."></textarea>
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:var(--danger);color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="ph-fill ph-x-circle me-1"></i> Reject — release the dates
                                            </button>
                                        </form>
                                    </div>
                                    @elseif($item->posting_status !== 'rejected')
                                    <div class="mt-3 p-3 rounded-3" style="background:var(--n-50);border:1px solid var(--n-200);">
                                        <div style="font-size:12.5px;color:var(--n-700);" class="mb-3">
                                            <i class="ph-fill ph-check-circle me-1" style="color:var(--g-600);"></i>
                                            This posting is live.
                                            @if($item->preferred_date)
                                                <strong>{{ $item->schedule_window_label }}</strong>
                                                @if(!$item->preferred_date_last->isSameDay($item->preferred_date))
                                                    are reserved for this employer — no other employer can book
                                                    those dates, and the employer chooses which of them to
                                                    interview on.
                                                @else
                                                    is reserved for this employer.
                                                @endif
                                            @endif
                                        </div>

                                        {{-- PESO, 2026-08-26: ang LRA walay take down. Ang
                                             iyang desisyon mao ang petsa — dawaton o
                                             balibaran ang adlaw sa PESO Office samtang
                                             naghulat pa. Kung buhi na ang posting, ang
                                             employer ra ang makatangtang niini. Ang samang
                                             lagda gipatuman usab sa
                                             StaffWebController::rejectJob, aron dili
                                             makalusot ang direktang POST. --}}
                                        @if($staffRoleForTabs !== 'lra')
                                        <form action="{{ route('staff.jobs.reject', $item->job_qualifications_id) }}" method="POST"
                                              onsubmit="return confirm('Take this posting down? It will stop showing to jobseekers and the dates will be released.');">
                                            @csrf
                                            <label class="form-label fw-semibold small" style="color:var(--danger);">
                                                Take down — reason (sent to the employer)
                                            </label>
                                            <textarea name="remarks" rows="2" required
                                                class="form-control mb-2"
                                                style="border:1px solid var(--n-200);border-radius:8px;font-size:13px;"
                                                placeholder="Why this posting is being removed..."></textarea>
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:var(--danger);color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="ph-fill ph-x-circle me-1"></i> Take Down Posting
                                            </button>
                                        </form>
                                        @else
                                        <div style="font-size:12px;color:var(--n-500);">
                                            <i class="ph ph-info me-1"></i>
                                            Your decision on this booking is made. Only the employer can remove
                                            the posting itself.
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer" style="border-top:1px solid var(--n-200);">
                                    <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid var(--n-50);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }}
                of {{ $schedules->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $schedules->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $schedules->previousPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            <i class="ph ph-caret-left"></i>
                        </a>
                    </li>
                    @foreach($schedules->getUrlRange(1, $schedules->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $schedules->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $schedules->currentPage()
                                ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                : 'border-color:var(--n-200);color:var(--g-700);' }}"
                           href="{{ $url }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$schedules->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $schedules->nextPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            <i class="ph ph-caret-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

@push('scripts')
<script>
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