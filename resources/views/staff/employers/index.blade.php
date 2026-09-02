@extends('staff.layouts.app')

@section('content')

@include('partials.temp-password-banner')

@php
    // The five requirement documents, named once. Two modals and a decline
    // form read this list, and they no longer open on the same tabs, so a
    // copy defined inside one of them left the others without it.
    $docs = [
        'business_permit'             => 'CDO Business Permit',
        'sec_dti'                     => 'SEC / DTI',
        'company_profile'             => 'Company Profile',
        'no_pending_case_certificate' => 'Certificate of No Pending Case',
        'vacancy_posting'             => 'Vacancy Posting',
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        {{-- The LRA is given the name of the one list it has. The desks that
             decide on employers keep the general title, because their page
             holds three lists and no single one of them names it. --}}
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph ph-buildings me-2" style="color:var(--g-600);"></i>
            {{ $staffRole === 'lra' ? 'List of Employers' : 'Employers' }}
        </h5>
        {{-- No line under the LRA's title: it would only say the title again. --}}
        @if($staffRole !== 'lra')
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            @if($staffRole === 'sra')
                Manage overseas employer accounts
            @else
                Manage local employer accounts and requirements
            @endif
        </p>
        @endif
    </div>

    {{-- Walk-in: ang employer nga miadto sa opisina. Ang Job Vacancy para sa
         lokal ug ang SRA para sa overseas — sila ang naay counter dinhi.
         Ang LRA nagtan-aw ra sa listahan, ug ang Job Fair wala nay page dinhi:
         ang iyang walk-in nagpuyo na sa Job Postings. --}}
    @if(in_array($staffRole, ['job_vacancy', 'sra']))
    <a href="{{ route('staff.employers.walkin') }}"
       class="btn btn-sm fw-semibold"
       style="background:var(--g-600);color:#fff;border:none;border-radius:8px;
              font-size:12px;padding:8px 16px;white-space:nowrap;">
        <i class="ph-fill ph-storefront me-1"></i> Walk-in Registration
    </a>
    @endif
</div>

{{-- The requirement review window is sized to the screen, not to its
     contents. A modal that scrolls is a page with a border round it: the desk
     scrolled down to the buttons and could no longer see the document it was
     judging. Fixing the height and splitting the body in two keeps the paper
     and the decision in one view, and lets the document — the only thing whose
     size is unknown — take whatever room is left. --}}
<style>
    .peso-req-review .modal-dialog { max-width: min(1240px, 96vw); }
    .peso-req-review .modal-content { height: 92vh; overflow: hidden; }
    .peso-req-body {
        display: flex;
        align-items: stretch;
        min-height: 0;   /* without this a flex child refuses to shrink and the
                            document pushes the window past the viewport */
        flex: 1;
        overflow: hidden;
    }
    .peso-req-doc {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .peso-req-view { flex: 1; min-height: 0; overflow: auto; }
    .peso-req-view img { max-width: 100%; }
    .peso-req-view iframe { width: 100%; height: 100%; border: none; }
    .peso-req-side {
        flex: 0 0 300px;
        border-left: 1px solid var(--n-100);
        overflow-y: auto;
    }
    /* Narrow screens have no room for two columns, so they stack and the window
       goes full height — the one case where scrolling is the honest answer. */
    @media (max-width: 991.98px) {
        .peso-req-review .modal-content { height: auto; max-height: 92vh; }
        .peso-req-body { flex-direction: column; overflow-y: auto; }
        .peso-req-view { min-height: 320px; }
        .peso-req-side { flex: 1 1 auto; border-left: none; border-top: 1px solid var(--n-100); }
    }
</style>

{{-- TABS

     The LRA has no tabs. It never had more than one — Pre-Employer and
     Inactive belong to the desks that decide on them — and a single tab is not
     a choice, only a heading repeated under the heading it repeats. --}}
@if($staffRole !== 'lra')
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <a href="{{ route('staff.employers', ['tab' => 'pre']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'pre'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-user-plus me-1"></i>
        Pre-Employer
        <span class="ms-1 fw-bold">({{ $totalPre }})</span>
    </a>
    <a href="{{ route('staff.employers', ['tab' => 'approved']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'approved'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="ph ph-check-circle me-1"></i>
        Approved Employers
        <span class="ms-1 fw-bold">({{ $totalApproved }})</span>
    </a>
    {{-- Ang gipatay nga account, awtomatiko man o gipatay sa staff. Ang LRA
         nagtan-aw ra sa listahan, mao nga wala niya ni — ang Job Vacancy ug ang
         SRA ang mo-abli pag-usab. --}}
    @if(in_array($staffRole, ['job_vacancy', 'sra']))
    <a href="{{ route('staff.employers', ['tab' => 'dormant']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'dormant'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="ph-fill ph-lock-simple me-1"></i>
        Inactive Employers
        <span class="ms-1 fw-bold">({{ $totalDormant }})</span>
    </a>
    @endif
</div>
@endif

{{-- ── SORT AND SEARCH ──
     PESO, 2026-08-26: ang desk kinahanglan makatan-aw sa Manufacturing lang, o
     sa Education lang, ug ma-download kadto nga listahan. Ang sala ug ang
     download nagbasa sa parehas nga query, mao nga ang mogawas nga file mao
     gyud ang naa sa screen — dili mas lapad. --}}
<div class="d-flex align-items-center mb-3 flex-wrap gap-2">
    @if($tab === 'approved')
    <select id="industryFilter" class="form-select form-select-sm"
            style="max-width:240px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
        <option value="">All industries</option>
        @foreach($industries as $group)
            <option value="{{ $group }}" {{ $industry === $group ? 'selected' : '' }}>{{ $group }}</option>
        @endforeach
    </select>

    {{-- Landed here from the dashboard's Nearly Expired Requirements card.
         The list is cut down to those companies, so say so and give the way
         back out — otherwise the desk reads a short Approved list as the whole
         Approved list. --}}
    @if($expiringOnly)
    <span class="d-inline-flex align-items-center gap-1 fw-semibold"
          style="background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn);
                 border-radius:8px;font-size:11.5px;padding:5px 10px;white-space:nowrap;">
        <i class="ph-fill ph-hourglass-medium"></i>
        Nearly expired requirements only ({{ $totalExpiring }})
        <a href="{{ route('staff.employers', array_filter(['tab' => 'approved', 'industry' => $industry, 'search' => request('search')])) }}"
           class="ms-1 text-decoration-none" style="color:var(--warn);" title="Show all approved employers">
            <i class="ph ph-x"></i>
        </a>
    </span>
    @endif

    <a href="{{ route('staff.employers.export', array_filter([
            'industry' => $industry,
            'search'   => request('search'),
       ])) }}"
       class="btn btn-sm fw-semibold"
       style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 14px;white-space:nowrap;">
        <i class="ph-fill ph-microsoft-excel-logo me-1"></i>
        Download Excel
    </a>

    @if($industry)
    <span style="font-size:11.5px;color:var(--n-500);">
        Showing {{ $employers->total() }} {{ $industry }} employer(s).
    </span>
    @endif
    @endif

    <div class="input-group ms-auto" style="max-width:260px;width:100%;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search company or email..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($tab === 'dormant')
    @include('staff.employers._dormant')
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company Name</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company Email</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Employer Type</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">
                            @if($tab === 'pre')
                                Requirements Status
                            @else
                                Total Hired
                            @endif
                        </th>
                        {{-- Approved tab only: Company Status takes this slot.
                             The date an account was made is history — it settles
                             nothing the desk is deciding on this screen — so it
                             moved into the View modal with the rest of the
                             record. What the desk wants at a glance is where the
                             company stands right now: whether its papers have
                             run out, or how long it has been since it posted
                             anything. EmployerNsrpRegistration::companyStatus()
                             decides which of those to say. --}}
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">
                            @if($tab === 'approved')
                                Company Status
                            @else
                                Date Registered
                            @endif
                        </th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if($employers->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center py-5" style="border:none;">
                            <i class="ph ph-x-circle" style="font-size:48px;color:var(--n-300);"></i>
                            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No employers found</div>
                            @if($expiringOnly)
                            <div style="font-size:12px;color:var(--n-500);">
                                No approved employer has a document expiring in the next week.
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @foreach($employers as $employer)
                    @php
                        // ── USA KA ROW KADA KOMPANYA ──
                        // Ang gilista karon kay establisemento, dili account: usa ka
                        // HR mahimong maghawid ug duha, ug ang matag usa naay
                        // kaugalingong papel nga i-approve. Ang $employer nagpabilin
                        // nga ang account aron dili mausab ang tibuok pahina, apan ang
                        // employerNsrp niini gitudlo sa kompanya NIINING laray — dili
                        // sa una nga nakit-an sa database.
                        $companyRow = $employer;
                        $employer   = $companyRow->employer;
                        $employer->setRelation('employerNsrp', $companyRow);
                    @endphp
                    @php
                        // Ang papel ug ang bakante iya sa KOMPANYA, dili sa account.
                        $req        = $companyRow->requirement;
                        $totalHired = $tab === 'approved'
                            ? $companyRow->jobs->flatMap->applications->where('status','hired')->count()
                            : 0;
                    @endphp
                    @php $isHit = ($highlight ?? null) == $companyRow->employer_nsrp_registrations_id; @endphp
                    <tr style="font-size:13px;" @if($isHit) id="employerRow{{ $companyRow->employer_nsrp_registrations_id }}" class="peso-row-hit" @endif>
                        <td style="padding:12px 16px;color:var(--n-500);">
                            {{ $employers->firstItem() + $loop->index }}
                        </td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $employer->employerNsrp->company_name ?? 'None' }}
                            @if($employer->employerNsrp?->is_walk_in)
                                <span style="color:var(--warn);font-size:9px;font-weight:700;margin-left:4px;">WALK-IN</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $employer->email ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
    {{ $employer->employerNsrp->employer_type ?? 'None' }}
</td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($tab === 'pre')
                                @php
                                    $reqStatus = $req ? $req->status : 'not_submitted';
                                    $colors = [
                                        'not_submitted' => 'var(--n-500)',
                                        'pending'       => 'var(--warn)',
                                        'approved'      => 'var(--g-700)',
                                        'rejected'      => 'var(--danger)',
                                    ];
                                    $labels = [
                                        'not_submitted' => 'Not Submitted',
                                        'pending'       => 'Pending',
                                        'approved'      => 'Approved',
                                        'rejected'      => 'Rejected',
                                    ];
                                @endphp
                                <span class="fw-semibold" style="color:{{ $colors[$reqStatus] }};font-size:12px;">
                                    {{ $labels[$reqStatus] }}
                                </span>
                            @else
                                <span class="fw-bold" style="color:var(--g-700);font-size:14px;">
                                    {{ $totalHired }}
                                </span>
                                <span style="font-size:11px;color:var(--n-500);"> hired</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;color:var(--n-500);text-align:center;">
                            @if($tab === 'approved')
                                @php $status = $companyRow->companyStatus(); @endphp
                                <span class="fw-semibold" style="font-size:11.5px;color:{{ $status['color'] }};">
                                    {{ $status['label'] }}
                                </span>
                                @if($status['detail'])
                                <div style="font-size:10px;color:var(--n-500);">{{ $status['detail'] }}</div>
                                @endif
                            @else
                                {{ $companyRow->created_at->format('M d, Y') }}
                            @endif
                        </td>
                        @php
                            // HR handover: ang local employer sa lra/job_vacancy, ang
                            // overseas sa sra — parehas nga bahin sa trabaho nga gigamit
                            // sa controller (PESO interview 2026-08-13).
                            $empIsOverseas = $employer->employerNsrp->is_overseas ?? false;
                            $canTransfer = $tab === 'approved' && $employer->employerNsrp
                                && in_array($staffRole, $empIsOverseas ? ['sra'] : ['lra', 'job_vacancy'], true);

                            // Ang pagpatay sa account kay sa nag-atiman ra sa
                            // maong employer — parehas sa pag-approve sa
                            // requirements ug sa pag-abli sa dormant.
                            $canToggleStatus = $canTransfer
                                && in_array($staffRole, $empIsOverseas ? ['sra'] : ['job_vacancy'], true);
                        @endphp
                        <td style="padding:12px 16px;text-align:center;">
                            {{-- View button: SRA pending requirements, o naa nay submitted requirement, o Approved tab --}}
                            @if(($staffRole === 'sra' && $tab === 'pre' && $req && $req->status === 'pending') || ($tab === 'pre' && $req) || $tab === 'approved')
                                <div class="d-inline-flex gap-1">
                                    <button class="btn btn-sm fw-semibold"
                                        style="background:var(--g-600);
                                               color:#fff;border:none;border-radius:8px;font-size:12px;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#employerModal{{ $companyRow->employer_nsrp_registrations_id }}">
                                        <i class="ph-fill ph-eye me-1"></i>View
                                    </button>
                                    @if($canTransfer)
                                    <button class="btn btn-sm fw-semibold"
                                        style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);
                                               border-radius:8px;font-size:12px;"
                                        title="Change the authorized contact, or switch this account on or off"
                                        data-bs-toggle="modal"
                                        data-bs-target="#recoverModal{{ $employer->users_id }}">
                                        <i class="ph ph-pencil-simple me-1"></i>Update
                                    </button>
                                    @endif
                                </div>
                            @else
                                <span style="font-size:12px;color:var(--n-400);">None</span>
                            @endif
                        </td>
                    </tr>

                    {{-- MODAL --}}
                    @if(($staffRole === 'sra' && $tab === 'pre' && $req && $req->status === 'pending') || ($tab === 'pre' && $req) || $tab === 'approved')
                    <div class="modal fade" id="employerModal{{ $companyRow->employer_nsrp_registrations_id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header"
                                    style="background:var(--g-600);
                                           border-radius:16px 16px 0 0;">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="ph ph-buildings me-2"></i>
                                        {{ $employer->employerNsrp->company_name ?? 'None' }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">

                                    {{-- Why this modal is open at all. It used to sit at the very
                                         bottom, under the whole record, so the desk read the notice
                                         only after scrolling past everything it was a notice about. --}}
                                    @if($tab === 'pre' && $req && $req->status === 'pending')
                                    <div class="mb-3 p-3 rounded-3" style="background:var(--warn-bg);border:1px solid var(--warn);">
                                        <div style="font-size:13px;color:var(--warn);" class="fw-semibold">
                                            <i class="ph ph-warning-circle me-1"></i>
                                            Requirements submitted — pending review
                                        </div>
                                        <div style="font-size:12px;color:var(--n-700);margin-top:6px;">
                                            @if(in_array($staffRole, ['job_vacancy', 'sra'], true))
                                                Open a document below. Approve and Decline are in that
                                                window, beside the document.
                                            @else
                                                View only — {{ $employer->employerNsrp->is_overseas ?? false ? 'SRA' : 'Job Vacancy' }} staff handles approval.
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Employer Info --}}
                                    <div class="mb-3 p-3 rounded-3" style="background:var(--n-50);">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Company Name</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">
                                                    {{ $employer->employerNsrp->company_name ?? 'None' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Email</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">
                                                    {{ $employer->email ?? 'None' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Contact Person</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">
                                                    {{ $employer->employerNsrp->contact_person ?? 'None' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Position / Title</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">
                                                    {{ $employer->employerNsrp->position_title ?? 'None' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Mobile</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">
                                                    {{ $employer->employerNsrp->mobile_number ?? 'None' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
    <div style="font-size:12px;color:var(--n-500);">Employer Type</div>
    <div class="fw-semibold" style="color:var(--g-700);">
        {{ $employer->employerNsrp->employer_type ?? 'None' }}
    </div>
</div>
<div class="col-md-6">
    <div style="font-size:12px;color:var(--n-500);">Classification</div>
    <div class="fw-semibold" style="color:var(--g-700);">
        @if($employer->employerNsrp)
            <span style="color:{{ $employer->employerNsrp->is_overseas ? 'var(--warn)' : 'var(--g-600)' }};font-weight:600;font-size:11px;">
                {{ $employer->employerNsrp->is_overseas ? 'Overseas' : 'Local' }}
            </span>
        @else
            —
        @endif
    </div>
</div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Date Registered</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">
                                                    {{ $companyRow->created_at?->format('M d, Y') ?? 'None' }}
                                                </div>
                                            </div>
                                            @if($tab === 'approved')
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Total Hired</div>
                                                <div class="fw-bold" style="color:var(--g-700);font-size:20px;">
                                                    {{ $totalHired }}
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Submitted Documents --}}
                                    @if($req)
                                    <h6 class="fw-bold mb-2" style="color:var(--g-700);font-size:13px;">
                                        <i class="ph-fill ph-file-text me-1" style="color:var(--g-600);"></i>
                                        Submitted Documents
                                    </h6>
                                    <div class="mb-3">
                                        @foreach($docs as $field => $label)
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 mb-1 rounded-3"
                                            style="background:var(--n-50);">
                                            <span style="font-size:13px;color:var(--g-700);font-weight:500;">
                                                {{ $label }}
                                                @if($req->isFieldAccepted($field))
                                                    <i class="ph-fill ph-check-circle" style="color:var(--g-600);" title="Approved"></i>
                                                @elseif($req->isFieldRejected($field))
                                                    <i class="ph-fill ph-x-circle" style="color:var(--danger);" title="Rejected"></i>
                                                @endif
                                            </span>
                                            @if($req->{$field})
                                                {{-- Opens the review modal on this document. It used to
                                                     be a link with target="_blank": the desk lost the
                                                     employer's page to a new tab, read the file there,
                                                     and had to come back to act on it. The decision now
                                                     sits under the document being decided on. --}}
                                                <button type="button"
                                                    class="btn btn-sm fw-semibold"
                                                    style="background:var(--g-600);
                                                           color:#fff;border:none;border-radius:8px;font-size:11px;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#reqReviewModal{{ $req->employer_requirements_id }}"
                                                    onclick="pesoShowReqDoc({{ $req->employer_requirements_id }}, '{{ $field }}')">
                                                    <i class="ph-fill ph-eye me-1"></i>View
                                                </button>
                                            @else
                                                <span style="font-size:12px;color:var(--n-400);">Not uploaded</span>
                                            @endif
                                        </div>
                                        @endforeach
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 mb-1 rounded-3"
                                            style="background:var(--n-50);">
                                            <span style="font-size:13px;color:var(--g-700);font-weight:500;">NSRP Establishment Form</span>
                                            <button type="button"
                                                class="btn btn-sm fw-semibold"
                                                style="background:var(--g-600);
                                                       color:#fff;border:none;border-radius:8px;font-size:11px;"
                                                data-bs-toggle="modal" data-bs-target="#nsrpEstModal{{ $companyRow->employer_nsrp_registrations_id }}">
                                                <i class="ph-fill ph-eye me-1"></i>View
                                            </button>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 mt-2 rounded-3"
                                            style="background:#fff;border:1px dashed var(--n-200);">
                                            <span style="font-size:12px;color:var(--n-500);">Requirements Status</span>
                                            @php
                                                $reqColors = ['pending' => 'var(--warn)', 'approved' => 'var(--g-700)', 'rejected' => 'var(--danger)'];
                                            @endphp
                                            <span class="fw-semibold" style="color:{{ $reqColors[$req->status] ?? 'var(--n-500)' }};font-size:12px;">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    @endif

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        data-bs-dismiss="modal"
                                        style="border:1px solid var(--n-200);color:var(--g-700);
                                               background:#fff;border-radius:8px;">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Ang pag-ilis sa authorized contact. Ang modal shared sa
                         admin — parehas nga porma, parehas nga audit. --}}
                    @if($canTransfer)
                        @include("partials.employer-recovery-modal", [
                            "employer"        => $employer,
                            "action"          => route("staff.employers.transfer", $employer->users_id),
                            "canToggleStatus" => $canToggleStatus,
                        ])
                    @endif

                    {{-- REQUIREMENT REVIEW — the document and the decision in one window.
                         One modal per company, not one shared modal, so the Approve and
                         Decline forms are rendered by Blade with this employer's own route.
                         A shared modal would have to be rewired by JavaScript on every open,
                         and a mis-wire there posts a decision against the wrong company.

                         Rendered wherever the documents are listed, not on the Pre-Employer
                         tab alone. The View button beside each document is drawn on every
                         tab, but this window was not: on Approved — the only list the LRA
                         has — the button pointed at a modal that was never on the page, so
                         pressing it did nothing and the document could not be seen at all.
                         Approve and Decline stay behind $canDecide, so a decided folder
                         opens read-only. --}}
                    @if($req)
                    @php
                        // Only the documents actually uploaded are offered, and the modal
                        // opens on the first of them.
                        $reqDocs   = collect($docs)->filter(fn($label, $field) => (bool) $req->{$field});
                        $reqId     = $req->employer_requirements_id;
                        $canDecide = $req->status === 'pending' && in_array($staffRole, ['job_vacancy', 'sra'], true);

                        // What the picker needs per document: the label, the file
                        // type the viewer switches on, and the guarded URL.
                        $reqDocPayload = $reqDocs->map(fn($label, $field) => [
                            'label' => $label,
                            'ext'   => strtolower(pathinfo($req->{$field}, PATHINFO_EXTENSION)),
                            'url'   => route('documents.requirement', [$reqId, $field]),
                        ]);
                    @endphp
                    <div class="modal fade peso-req-review" id="reqReviewModal{{ $reqId }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header py-2 px-3" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                                    <h6 class="modal-title text-white fw-bold" style="font-size:13.5px;">
                                        <i class="ph-fill ph-file-text me-2"></i>
                                        Requirements — {{ $companyRow->company_name ?? 'Employer' }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>

                                {{-- Two columns, not one long page.
                                     Stacked, the document and the decision could not both be on
                                     screen, so the desk scrolled down to decide and lost sight of
                                     what it was deciding on — which is the whole reason this window
                                     exists. Side by side, the paper and the buttons are one view,
                                     and the only thing that ever scrolls is the document itself. --}}
                                <div class="modal-body p-0 peso-req-body">

                                    <div class="peso-req-doc p-3">
                                        @if($reqDocs->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="ph ph-file-x" style="font-size:48px;color:var(--n-300);"></i>
                                            <div class="mt-2 fw-semibold" style="color:var(--g-700);">No documents uploaded</div>
                                        </div>
                                        @else
                                        {{-- Switching document must not close the window: the desk
                                             compares one paper against another before deciding. --}}
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            @foreach($reqDocs as $field => $label)
                                            <button type="button"
                                                class="btn btn-sm fw-semibold peso-req-tab-{{ $reqId }}"
                                                data-field="{{ $field }}"
                                                style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;
                                                       border-radius:8px;font-size:11px;padding:4px 10px;white-space:nowrap;"
                                                onclick="pesoShowReqDoc({{ $reqId }}, '{{ $field }}')">
                                                {{ $label }}
                                            </button>
                                            @endforeach
                                        </div>

                                        <div id="reqDocView{{ $reqId }}" class="peso-req-view text-center rounded-3"
                                             style="background:var(--n-50);"></div>

                                        {{-- The URLs and file types the picker needs, written by Blade
                                             rather than assembled in JavaScript. --}}
                                        <script type="application/json" id="reqDocData{{ $reqId }}">@json($reqDocPayload)</script>
                                        @endif
                                    </div>

                                    <div class="peso-req-side p-3">
                                        @if($canDecide)
                                        {{-- Ang hukom sa matag papel una, ang paglihok sa kompanya
                                             ulahi. Ang SRA wala giapil: usa ka pindot ang iyaha sa
                                             tibuok folder, walay per-document nga lakang didto. --}}
                                        @php
                                            $perDoc = $staffRole === 'job_vacancy' && !($companyRow->is_overseas ?? false);
                                            $readyToMove = !$perDoc
                                                || ($req->allDocumentsDecided() && !$req->hasRejectedDocuments());
                                        @endphp

                                        @if($perDoc)
                                            @include('staff.requirements._document_decisions', [
                                                'requirement' => $req,
                                                'perDoc'      => true,
                                            ])
                                        @endif

                                        <form action="{{ route('staff.requirements.approve', $reqId) }}"
                                              method="POST" class="mb-2">
                                            @csrf
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:{{ $readyToMove ? 'var(--g-600)' : 'var(--n-200)' }};
                                                       color:{{ $readyToMove ? '#fff' : 'var(--n-500)' }};border:none;
                                                       border-radius:8px;font-size:12.5px;padding:8px;{{ $readyToMove ? '' : 'cursor:not-allowed;' }}"
                                                {{ $readyToMove ? '' : 'disabled' }}>
                                                <i class="ph-fill ph-check-circle me-1"></i>
                                                {{ $perDoc ? 'Move to Approved Employers' : 'Approve Requirements' }}
                                            </button>
                                        </form>

                                        {{-- Approving needs no form to fill in, so the panel opens
                                             showing two buttons and nothing else. The checkboxes and
                                             the reason are the cost of declining, and they appear
                                             only once the desk has said that is what it is doing. --}}
                                        <button type="button" class="btn btn-sm w-100 fw-semibold"
                                            id="declineOpen{{ $reqId }}"
                                            style="background:#fff;color:var(--danger);border:1px solid var(--danger);
                                                   border-radius:8px;font-size:12.5px;padding:8px;"
                                            onclick="pesoOpenDecline({{ $reqId }})">
                                            <i class="ph ph-x-circle me-1"></i>
                                            Decline Requirements
                                        </button>

                                        <div id="declinePanel{{ $reqId }}" class="p-2 rounded-3"
                                             style="display:none;background:var(--warn-bg);border:1px solid var(--warn);">
                                            <form action="{{ route('staff.requirements.reject', $reqId) }}" method="POST">
                                                @csrf
                                                <div class="fw-semibold mb-1" style="font-size:11.5px;color:var(--warn);">
                                                    Which document(s) are incorrect or missing?
                                                </div>
                                                <div class="p-2 mb-2 rounded-3" style="background:#fff;border:1px solid var(--warn-br);">
                                                    @foreach($docs as $field => $label)
                                                    <div class="form-check" style="margin-bottom:2px;">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="rejected_fields[]" value="{{ $field }}"
                                                            id="dec_{{ $field }}_{{ $reqId }}"
                                                            {{ $req->isFieldRejected($field) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="dec_{{ $field }}_{{ $reqId }}"
                                                            style="font-size:11.5px;color:var(--warn);line-height:1.4;">
                                                            {{ $label }}
                                                            @if($req->fieldRejectionNote($field))
                                                                <span style="color:var(--danger);">— {{ $req->fieldRejectionNote($field) }}</span>
                                                            @endif
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <div class="fw-semibold mb-1" style="font-size:11.5px;color:var(--warn);">
                                                    Reason for Decline
                                                </div>
                                                <textarea name="remarks" rows="3" required
                                                    id="declineReason{{ $reqId }}"
                                                    class="form-control mb-2"
                                                    style="border:1px solid var(--warn-br);border-radius:8px;font-size:12px;"
                                                    placeholder="e.g. CDO Business Permit is already expired..."></textarea>
                                                <button type="submit" class="btn btn-sm w-100 fw-semibold mb-1"
                                                    style="background:var(--danger);color:#fff;border:none;
                                                           border-radius:8px;font-size:12.5px;padding:8px;"
                                                    onclick="return validateDecline(this)">
                                                    <i class="ph-fill ph-x-circle me-1"></i>
                                                    Confirm Decline
                                                </button>
                                                <button type="button" class="btn btn-sm w-100 fw-semibold"
                                                    style="background:#fff;color:var(--n-700);border:1px solid var(--n-200);
                                                           border-radius:8px;font-size:12px;padding:6px;"
                                                    onclick="pesoCancelDecline({{ $reqId }})">
                                                    Cancel
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <div class="p-3 rounded-3 text-center" style="background:var(--n-50);border:1px dashed var(--n-200);">
                                            <i class="ph ph-eye" style="font-size:22px;color:var(--n-300);"></i>
                                            <div style="font-size:12px;color:var(--n-500);margin-top:6px;">
                                                @if($req->status === 'pending')
                                                    View only — {{ $companyRow->is_overseas ? 'SRA' : 'Job Vacancy' }} staff handles approval.
                                                @else
                                                    Already {{ $req->status }} — nothing to decide.
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                        <button type="button" class="btn btn-sm w-100 fw-semibold mt-2"
                                            data-bs-dismiss="modal"
                                            style="border:1px solid var(--n-200);color:var(--g-700);
                                                   background:#fff;border-radius:8px;font-size:12.5px;padding:8px;">
                                            Close
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- NSRP ESTABLISHMENT FORM DETAILS (read-only, gikan sa registration) --}}
                    @if($req)
                    <div class="modal fade" id="nsrpEstModal{{ $companyRow->employer_nsrp_registrations_id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="ph-fill ph-clipboard-text me-2"></i>NSRP Establishment Form
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div style="font-size:11px;font-weight:700;color:var(--g-700);margin-bottom:10px;">
                                        I. Establishment Details
                                    </div>
                                    <div class="row g-2 mb-3" style="font-size:13px;">
                                        <div class="col-md-6"><span style="color:var(--n-500);">Company Name:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->company_name ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Trade Name:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->trade_name ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">TIN:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->tin ?? 'None' }} @if($employer->employerNsrp->tin)({{ $employer->employerNsrp->tin_type ?? 'None' }})@endif</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Employer Type:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->employer_type ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Line of Business:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->line_of_business ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Total Workforce:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->total_workforce ?? 'None' }}</strong></div>
                                        <div class="col-12"><span style="color:var(--n-500);">Establishment Address:</span> <strong style="color:var(--g-700);">{{ collect([$employer->employerNsrp->est_barangay ?? null, $employer->employerNsrp->est_city_municipality ?? null, $employer->employerNsrp->est_province ?? null])->filter()->implode(', ') ?: 'None' }}</strong></div>
                                    </div>

                                    <div style="font-size:11px;font-weight:700;color:var(--g-700);margin-bottom:10px;border-top:1px dashed var(--n-200);padding-top:12px;">
                                        II. Establishment Contact Details
                                    </div>
                                    <div class="row g-2" style="font-size:13px;">
                                        <div class="col-md-6"><span style="color:var(--n-500);">Contact Person:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->contact_person ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Position / Title:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->position_title ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Mobile Number:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->mobile_number ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Telephone No.:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->telephone_no ?? 'None' }}</strong></div>
                                        <div class="col-md-6"><span style="color:var(--n-500);">Fax No.:</span> <strong style="color:var(--g-700);">{{ $employer->employerNsrp->fax_no ?? 'None' }}</strong></div>
                                    </div>

                                    {{-- Editable, unlike everything above it.
                                         PESO aims a job fair invitation at an industry group, so an
                                         employer with none set is never matched to a fair. The field
                                         was on the registration form but was not being saved, so the
                                         employers who signed up before that was fixed have to be
                                         classified here. --}}
                                    <div style="font-size:11px;font-weight:700;color:var(--g-700);margin:16px 0 10px;border-top:1px dashed var(--n-200);padding-top:12px;">
                                        III. Industry Group
                                    </div>
                                    {{-- Ang industriya basahon ra sa LRA. Ang job fair mo-invite
                                         pinaagi niini, mao nga trabaho ni sa Job Fair staff, ug naa
                                         na nila. Kung duha ka lamesa ang makausab, magkalahi ang
                                         tubag ug ang fair mo-invite sa sayop nga employer. --}}
                                    @if($staffRole === 'lra')
                                        <div style="font-size:13px;color:var(--n-700);">
                                            {{ $employer->employerNsrp->industry_group ?? 'Not set' }}
                                        </div>
                                    @else
                                    <form method="POST"
                                          action="{{ route('staff.employers.industry', $companyRow->employer_nsrp_registrations_id) }}"
                                          class="d-flex gap-2 align-items-start flex-wrap">
                                        @csrf
                                        <select name="industry_group" class="form-select form-select-sm"
                                            style="flex:1 1 320px;border:1px solid var(--n-200);border-radius:8px;font-size:13px;"
                                            required>
                                            <option value="" disabled
                                                {{ $employer->employerNsrp->industry_group ? '' : 'selected' }}>
                                                Not set — this employer is skipped by targeted job fair invitations
                                            </option>
                                            @foreach(\App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS as $group)
                                            <option value="{{ $group }}"
                                                {{ $employer->employerNsrp->industry_group === $group ? 'selected' : '' }}>
                                                {{ $group }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:13px;">
                                            <i class="ph ph-floppy-disk me-1"></i>Save
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                <div class="modal-footer">
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

        {{-- PAGINATION --}}
        @if($employers->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid var(--n-50);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $employers->firstItem() }}–{{ $employers->lastItem() }}
                of {{ $employers->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $employers->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $employers->previousPageUrl() }}">
                            <i class="ph ph-caret-left"></i>
                        </a>
                    </li>
                    @foreach($employers->getUrlRange(1, $employers->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $employers->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $employers->currentPage()
                                ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                : 'border-color:var(--n-200);color:var(--g-700);' }}"
                           href="{{ $url }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$employers->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $employers->nextPageUrl() }}">
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
    // Ang sala nagpabilin sa URL, mao nga ang pager ug ang Download Excel
    // nga link magdala niini.
    document.getElementById('industryFilter')?.addEventListener('change', function () {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('industry', this.value);
        } else {
            url.searchParams.delete('industry');
        }
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    });

    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('tab', '{{ $tab }}');
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });

    function validateDecline(btn) {
        const form = btn.closest('form');
        const checked = form.querySelectorAll('input[name="rejected_fields[]"]:checked');
        if (checked.length === 0) {
            alert('Palihug pili og labing menos usa ka document nga sayop/kulang.');
            return false;
        }
        return true;
    }

    // Declining asks for a reason and for which papers are wrong; approving asks
    // for nothing. Showing that form to someone who has not chosen to decline
    // fills the panel with fields most decisions never touch.
    function pesoOpenDecline(reqId) {
        const panel = document.getElementById('declinePanel' + reqId);
        const open  = document.getElementById('declineOpen' + reqId);
        if (!panel || !open) return;
        panel.style.display = 'block';
        open.style.display  = 'none';
        const reason = document.getElementById('declineReason' + reqId);
        if (reason) reason.focus();
    }

    function pesoCancelDecline(reqId) {
        const panel = document.getElementById('declinePanel' + reqId);
        const open  = document.getElementById('declineOpen' + reqId);
        if (!panel || !open) return;
        // Clear what was half-filled. Leaving it would put a stale reason and a
        // stale set of ticks in front of the next employer reviewed.
        panel.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
        const reason = document.getElementById('declineReason' + reqId);
        if (reason) reason.value = '';
        panel.style.display = 'none';
        open.style.display  = 'block';
    }

    // ── The requirement review window ──
    //
    // Shows one of the employer's documents inside the modal. The desk used to
    // lose the page to a new tab for every document; the decision now sits under
    // whichever paper is on screen, and switching papers does not close it.
    function pesoShowReqDoc(reqId, field) {
        const data = document.getElementById('reqDocData' + reqId);
        const view = document.getElementById('reqDocView' + reqId);
        if (!data || !view) return;

        const doc = JSON.parse(data.textContent)[field];
        if (!doc) return;

        // Mark which paper is on screen, or the picker gives no clue once the
        // document itself fills the frame.
        document.querySelectorAll('.peso-req-tab-' + reqId).forEach(function (tab) {
            const on = tab.dataset.field === field;
            tab.style.background = on ? 'var(--g-600)' : '#fff';
            tab.style.color      = on ? '#fff' : 'var(--g-700)';
            tab.style.border     = on ? '1px solid transparent' : '1px solid var(--n-200)';
        });

        // Height comes from the pane, not from a number here — the pane is
        // already sized to whatever is left of the screen.
        const images = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (images.includes(doc.ext)) {
            view.innerHTML = '<img class="rounded-3" alt="">';
            view.querySelector('img').src = doc.url;
        } else if (doc.ext === 'pdf') {
            view.innerHTML = '<iframe style="border-radius:8px;"></iframe>';
            view.querySelector('iframe').src = doc.url;
        } else {
            // Neither an image nor a PDF: there is nothing to render, so offer
            // the file itself rather than an empty grey box.
            const a = document.createElement('a');
            a.href = doc.url;
            a.target = '_blank';
            a.rel = 'noopener';
            a.className = 'btn btn-sm fw-semibold';
            a.style.cssText = 'background:var(--g-600);color:#fff;border:none;border-radius:8px;';
            a.innerHTML = '<i class="ph ph-arrow-square-out me-1"></i>Open file';
            view.innerHTML = '<div class="py-5" style="color:var(--n-500);font-size:13px;">'
                + 'This file cannot be previewed here.</div>';
            view.appendChild(a);
        }
    }

    // Opening the window from the modal header, rather than from a document row,
    // would otherwise leave the frame blank. Land on the first document.
    document.querySelectorAll('[id^="reqReviewModal"]').forEach(function (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            pesoCancelDecline(modal.id.replace('reqReviewModal', ''));
        });
        modal.addEventListener('shown.bs.modal', function () {
            const reqId = modal.id.replace('reqReviewModal', '');
            const view  = document.getElementById('reqDocView' + reqId);
            if (!view || view.innerHTML.trim() !== '') return;
            const first = document.querySelector('.peso-req-tab-' + reqId);
            if (first) pesoShowReqDoc(reqId, first.dataset.field);
        });
    });
</script>
@endpush

{{-- ── Ang laray nga gi-tudlo sa bell ──

     Ang notice sa staff naghisgot ug usa ka kompanya. Ang controller na ang
     nagdala kanimo sa husto nga page; kining bahin mao ang mag-ingon kung
     hain siya sa page. Ang kolor mo-anam ug hanaw human sa pipila ka segundo
     — usa ka pagtudlo, dili usa ka permanenteng marka. --}}
<style>
    tr.peso-row-hit > td {
        background: #fff6d8;
        box-shadow: inset 0 0 0 9999px rgba(255, 193, 7, 0.10);
        animation: pesoRowHit 4s ease-out forwards;
    }
    tr.peso-row-hit > td:first-child { border-left: 3px solid var(--warn); }
    @keyframes pesoRowHit {
        0%, 55% { background: #fff6d8; }
        100%    { background: transparent; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var hit = document.querySelector('tr.peso-row-hit');
        if (hit) {
            hit.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>

@endsection