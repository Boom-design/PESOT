@extends('company.layouts.app')

@section('page-title', ' ')

@section('content')

{{-- ── Usa ra ka page para sa tanan nga aktibo nga vacancy (PESO interview,
     2026-08-12). Ang Job Fair wala nay kaugalingon nga nav — tab na siya diri,
     kay parehas ra man nga trabaho: tan-awon kinsa ang mo-apply sa gi-post.
     Ang tab kay link, dili JS toggle ra: kada tab naay kaugalingon nga search
     ug pagination, mao nga kinahanglan mabilin ang gipili nga tab human
     mag-reload. ── --}}
{{-- Ang "Post a Job" naa dinhi, dili sa laing page: dinhi man makita sa
     employer ang iyang gi-post, mao nga dinhi pud siya magsugod. --}}
{{-- Ang teksto naay max-width ug ang gap dako, aron dili mag-abot ang
     paragraph ug ang buton sa tunga-tunga nga gilapdon sa screen. --}}
<div class="d-flex align-items-start justify-content-between gap-4 mb-4 flex-wrap fade-in">
    <div style="max-width:620px;">
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-briefcase me-2" style="color:var(--g-600);"></i>Active Job Postings
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);line-height:1.6;">
            The jobs you are hiring for right now and the people who applied, newest first.
            A job leaves this page when its slots are filled or its last day passes —
            you will find it in Reports.
        </p>
    </div>
    <a href="#" class="btn btn-peso flex-shrink-0 ms-auto" data-bs-toggle="modal" data-bs-target="#requestJobModal">
        <i class="ph ph-plus me-1"></i> Post a Job
    </a>
</div>

@include('company.partials.request-job-modal', ['jobFairId' => session('open_job_fair_modal')])

{{-- ── TABS ── --}}
@php
    $tabs = [
        // Tanan nga buhi nga posting ang naa diri, apil ang Job Fair nga
        // naghulat pa ug event — mao nga dili na "Company Interview & In-house".
        'vacancies'   => ['My Job Vacancies',        'ph-briefcase',     null],
        'invitations' => ['Job Fair Invitations',    'ph-calendar-dots', $pendingInvitationsCount],
        'applicants'  => ['Job Fair Applicants',     'ph-users-three',   null],
    ];
@endphp
<div class="d-flex gap-2 mb-3 flex-wrap">
    @foreach($tabs as $key => [$label, $icon, $badge])
    <a href="{{ route('company.jobseekers', ['tab' => $key]) }}"
       class="btn btn-sm fw-semibold text-decoration-none"
       style="{{ $tab === $key
            ? 'background:var(--g-600);color:#fff;border:none;'
            : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}border-radius:8px;font-size:12px;padding:7px 18px;">
        <i class="{{ $tab === $key ? 'ph-fill' : 'ph' }} {{ $icon }} me-1"></i> {{ $label }}
        @if($badge)
            <span class="badge rounded-pill"
                  style="{{ $tab === $key ? 'background:#fff;color:var(--danger);' : 'background:var(--danger);color:#fff;' }}font-size:10px;margin-left:4px;">{{ $badge }}</span>
        @endif
    </a>
    @endforeach
</div>

@if($tab === 'vacancies')

    <form method="GET" action="{{ route('company.jobseekers') }}" class="mb-3 d-flex justify-content-end">
        <input type="hidden" name="tab" value="vacancies">
        <div class="input-group" style="max-width:360px;">
            <span class="input-group-text" style="background:#fff;border:1px solid var(--peso-border);border-right:none;">
                <i class="ph ph-magnifying-glass" style="color:var(--n-500);"></i>
            </span>
            <input type="text" name="search" class="form-control peso-input" style="border-left:none;"
                placeholder="Search job position..." value="{{ $search }}">
            <button type="submit" class="btn btn-peso">Search</button>
            @if($search)
                <a href="{{ route('company.jobseekers') }}" class="btn btn-peso-outline">Clear</a>
            @endif
        </div>
    </form>

    @if($jobs->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="ph ph-briefcase" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No job vacancies yet</div>
            <div class="text-muted small mt-1">Post a job vacancy to start seeing qualified applicants here.</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Date</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Schedule Type</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Status</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Slots Needed</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Deadline</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $job)
                        @php
                            $scheduleLabel = \App\Models\Job::scheduleTypeLabel($job->schedule_type);
                        @endphp
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;">
                                <div class="fw-semibold" style="color:var(--g-700);">{{ $job->title }}</div>
                                <div style="font-size:11px;color:var(--n-500);"><i class="ph ph-map-pin me-1"></i>{{ $job->location }}</div>
                            </td>
                            <td style="padding:12px 16px;color:var(--n-500);">
                                {{ $job->created_at->format('M d, Y') }}
                            </td>
                            {{-- Plain nga text. Ang kahon sa likod sa text
                                 nagpasabot nga ma-click — ug kini dili. --}}
                            <td style="padding:12px 16px;color:var(--g-700);font-weight:600;">
                                {{ $scheduleLabel }}
                            </td>
                            <td style="padding:12px 16px;">
                                @include('partials.job-status-badge', ['job' => $job])
                            </td>
                            {{-- Ang duha ka rason nga mo-sira ang posting naay kaugalingon
                                 na nga column: ang slots ug ang deadline. --}}
                            <td style="padding:12px 16px;">
                                {{-- Group-wide: kung ang parehas nga position naa pud sa laing
                                     schedule type, usa ra ang bakante nga gi-ambitan nila. --}}
                                <span style="color:var(--g-700);font-weight:600;">
                                    <i class="ph ph-users me-1"></i>{{ $job->group_hired_count }} / {{ $job->slots }} slot(s) filled
                                </span>
                                @if($job->group_external_hires > 0)
                                    <div style="font-size:10.5px;color:var(--n-500);margin-top:3px;">
                                        {{ $job->group_external_hires }} hired outside PESO
                                    </div>
                                @endif
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                @if($job->deadline)
                                    {{ $job->deadline->format('M d, Y') }}
                                @else
                                    <span style="color:var(--n-500);">None</span>
                                @endif
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <div class="d-inline-flex gap-1">
                                    {{-- Ang Job Fair nga naghulat pa ug event walay applicant
                                         nga tan-awon — dili pa siya makita sa jobseeker. --}}
                                    @if($job->lifecycle_status === 'waiting')
                                        <span class="btn btn-sm px-3" style="background:var(--n-100);color:var(--n-400);border:1px solid var(--n-200);border-radius:8px;cursor:not-allowed;"
                                              title="Opens automatically once PESO schedules a job fair event">
                                            <i class="ph ph-hourglass-medium me-1"></i> Waiting
                                        </span>
                                    {{-- Parehas sa Waiting: wala pa siya makita sa jobseeker,
                                         mao nga walay applicant nga tan-awon. Ang tooltip ang
                                         nagsulti kung ngano — o unsay rason sa pagbalibad. --}}
                                    @elseif(in_array($job->lifecycle_status, ['pending', 'rejected'], true))
                                        <span class="btn btn-sm px-3"
                                              style="background:var(--n-100);color:var(--n-400);border:1px solid var(--n-200);border-radius:8px;cursor:not-allowed;"
                                              title="{{ $job->lifecycle_block_reason }}">
                                            <i class="ph {{ $job->lifecycle_status === 'pending' ? 'ph-hourglass-medium' : 'ph-x-circle' }} me-1"></i>
                                            {{ $job->lifecycle_status === 'pending' ? 'For approval' : 'Rejected' }}
                                        </span>
                                    @else
                                        <a href="{{ route('company.jobs.qualified', $job->job_qualifications_id) }}" class="btn btn-peso btn-sm px-3">
                                            <i class="ph ph-eye me-1"></i> View
                                        </a>
                                    @endif
                                    {{-- Ma-edit samtang buhi pa. PESO interview 2026-08-13:
                                         kung walay mo-apply sa "licensed CPA", mahimong hupayan
                                         sa employer ngadto "accounting graduate" — apan samtang
                                         buhi pa ang posting ug wala pa napuno. --}}
                                    <a href="{{ route('company.jobs.edit', $job->job_qualifications_id) }}"
                                       class="btn btn-peso-outline btn-sm px-3" title="Edit qualifications">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                    {{-- Gi-hire nga wala miagi sa PESO — mo-hurot ug slot
                                         apan dili maihap nga PESO placement. Wala pa ni bili
                                         samtang wala pa makita ang posting. --}}
                                    @if(!in_array($job->lifecycle_status, ['pending', 'rejected'], true))
                                    <button type="button" class="btn btn-peso-outline btn-sm px-3"
                                            title="Record someone you hired outside PESO"
                                            data-bs-toggle="modal" data-bs-target="#hiresModal{{ $job->job_qualifications_id }}">
                                        <i class="ph ph-user-plus"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── RECORD OUTSIDE-PESO HIRES ──
             Gawas sa <table> gyud ni gibutang. Ang modal sulod sa usa ka row
             nga naay `d-none` dili gyud mogawas: `display:none` ang ginikanan,
             mao nga walay makita bisan ma-trigger. --}}
        @foreach($jobs as $job)
        <div class="modal fade" id="hiresModal{{ $job->job_qualifications_id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;border:none;">
                    <form method="POST" action="{{ route('company.jobs.recordHires', $job->job_qualifications_id) }}">
                        @csrf
                        <div class="modal-header" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                            <h6 class="modal-title text-white fw-bold">
                                <i class="ph ph-user-plus me-2"></i>Hired outside PESO
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="p-3 mb-3 rounded-3" style="background:var(--n-50);font-size:12.5px;color:var(--n-700);line-height:1.6;">
                                <strong style="color:var(--g-700);">{{ $job->title }}</strong> — {{ $job->slots }} slot(s)<br>
                                Hired through PESO: <strong>{{ $job->group_peso_hires }}</strong>
                            </div>

                            <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">
                                How many did you hire outside PESO?
                            </label>
                            <input type="number" name="external_hires" class="form-control"
                                   min="0" max="{{ max(0, $job->slots - $job->group_peso_hires) }}"
                                   value="{{ $job->group_external_hires }}"
                                   style="font-size:13px;border-radius:8px;border-color:var(--n-200);max-width:140px;">
                            <small style="font-size:11px;color:var(--n-500);display:block;margin-top:6px;line-height:1.6;">
                                People you hired for this position who never applied through PESO —
                                a walk-in or a referral. They take up the slot, so the posting stops
                                advertising it.
                                <br>
                                <strong>They are not counted as PESO placements</strong> in the report
                                sent to the Mayor's Office and DOLE. That figure stays what PESO
                                actually placed.
                            </small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                                style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm fw-semibold"
                                style="background:var(--g-600);color:#fff;border:none;border-radius:8px;">
                                <i class="ph ph-check me-1"></i>Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-center mt-4">
            {{ $jobs->links() }}
        </div>
    @endif

@elseif($tab === 'invitations')

    @include('company.jobfair._invitations')

@else

    @include('company.jobfair._applicants')

@endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('company.partials.request-job-modal-scripts', ['autoOpenRequestJobModal' => session()->has('open_job_fair_modal')])
<script>
    function confirmResponse(id, response) {
        const isConfirm = response === 'confirmed';
        Swal.fire({
            title: isConfirm ? 'Confirm Participation?' : 'Decline Invitation?',
            text: isConfirm
                ? 'All jobseekers who applied to your company will be notified about this job fair.'
                : 'Are you sure you want to decline this job fair invitation?',
            icon: isConfirm ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isConfirm ? 'var(--g-700)' : 'var(--danger)',
            cancelButtonColor: 'var(--n-500)',
            confirmButtonText: isConfirm ? 'Yes, Confirm!' : 'Yes, Decline!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('responseInput' + id).value = response;
                document.getElementById('confirmForm' + id).submit();
            }
        });
    }
</script>
@endsection
