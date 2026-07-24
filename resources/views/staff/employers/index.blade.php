@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-building me-2" style="color:#4dd9c0;"></i>
            Employers
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            @if($staffRole === 'sra')
                Manage overseas employer accounts
            @elseif($staffRole === 'lra')
                View local employer accounts
            @else
                Manage local employer accounts and requirements
            @endif
        </p>
    </div>
</div>

{{-- TABS --}}
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <a href="{{ route('staff.employers', ['tab' => 'pre']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'pre'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-person-plus-fill me-1"></i>
        Pre-Employer
        <span class="ms-1 fw-bold">({{ $totalPre }})</span>
    </a>
    <a href="{{ route('staff.employers', ['tab' => 'approved']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'approved'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-building-check me-1"></i>
        Approved Employers
        <span class="ms-1 fw-bold">({{ $totalApproved }})</span>
    </a>
</div>

{{-- SEARCH --}}
<div class="d-flex justify-content-end mb-3">
    <div class="input-group" style="max-width:260px;width:100%;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search company or email..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($employers->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-building-x" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No employers found</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Email</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Employer Type</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">
                            @if($tab === 'pre')
                                Requirements Status
                            @else
                                Total Hired
                            @endif
                        </th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">
                            Date Registered
                        </th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employers as $employer)
                    @php
                        $req        = $employer->employerRequirement;
                        $totalHired = $tab === 'approved'
                            ? $employer->jobs->flatMap->applications->where('status','hired')->count()
                            : 0;
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">
                            {{ $employers->firstItem() + $loop->index }}
                        </td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $employer->employerNsrp->company_name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $employer->email ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
    {{ $employer->employerNsrp->employer_type ?? '—' }}
</td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($tab === 'pre')
                                @php
                                    $reqStatus = $req ? $req->status : 'not_submitted';
                                    $colors = [
                                        'not_submitted' => '#888',
                                        'pending'       => '#f59e0b',
                                        'approved'      => '#2d7a5f',
                                        'rejected'      => '#e05252',
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
                                <span class="fw-bold" style="color:#2d7a5f;font-size:14px;">
                                    {{ $totalHired }}
                                </span>
                                <span style="font-size:11px;color:#888;"> hired</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;color:#888;text-align:center;">
                            {{ $employer->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            {{-- View button: SRA pending requirements, o naa nay submitted requirement, o Approved tab --}}
                            @if(($staffRole === 'sra' && $tab === 'pre' && $req && $req->status === 'pending') || ($tab === 'pre' && $req) || $tab === 'approved')
                                <button class="btn btn-sm fw-semibold"
                                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                           color:#fff;border:none;border-radius:8px;font-size:12px;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#employerModal{{ $employer->id }}">
                                    <i class="bi bi-eye-fill me-1"></i>View
                                </button>
                            @else
                                <span style="font-size:12px;color:#aaa;">—</span>
                            @endif
                        </td>
                    </tr>

                    {{-- MODAL --}}
                    @if(($staffRole === 'sra' && $tab === 'pre' && $req && $req->status === 'pending') || ($tab === 'pre' && $req) || $tab === 'approved')
                    <div class="modal fade" id="employerModal{{ $employer->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header"
                                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                           border-radius:16px 16px 0 0;">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="bi bi-building me-2"></i>
                                        {{ $employer->employerNsrp->company_name ?? '—' }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">

                                    {{-- Employer Info --}}
                                    <div class="mb-3 p-3 rounded-3" style="background:#f0f9f6;">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Company Name</div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">
                                                    {{ $employer->employerNsrp->company_name ?? '—' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Email</div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">
                                                    {{ $employer->email ?? '—' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Contact Person</div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">
                                                    {{ $employer->employerNsrp->contact_person ?? '—' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Position / Title</div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">
                                                    {{ $employer->employerNsrp->position_title ?? '—' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Mobile</div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">
                                                    {{ $employer->employerNsrp->mobile_number ?? '—' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
    <div style="font-size:12px;color:#888;">Employer Type</div>
    <div class="fw-semibold" style="color:#2d7a5f;">
        {{ $employer->employerNsrp->employer_type ?? '—' }}
    </div>
</div>
<div class="col-md-6">
    <div style="font-size:12px;color:#888;">Classification</div>
    <div class="fw-semibold" style="color:#2d7a5f;">
        @if($employer->employerNsrp)
            <span class="badge" style="background:{{ $employer->employerNsrp->is_overseas ? '#f59e0b' : '#4dd9c0' }};color:#fff;font-weight:600;">
                {{ $employer->employerNsrp->is_overseas ? 'Overseas' : 'Local' }}
            </span>
        @else
            —
        @endif
    </div>
</div>
                                            @if($tab === 'approved')
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Total Hired</div>
                                                <div class="fw-bold" style="color:#2d7a5f;font-size:20px;">
                                                    {{ $totalHired }}
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Submitted Documents --}}
                                    @if($req)
                                    <h6 class="fw-bold mb-2" style="color:#2d7a5f;font-size:13px;">
                                        <i class="bi bi-file-earmark-text-fill me-1" style="color:#4dd9c0;"></i>
                                        Submitted Documents
                                    </h6>
                                    <div class="mb-3">
                                        @php
                                            $docs = [
                                                'business_permit'             => 'CDO Business Permit',
                                                'sec_dti'                     => 'SEC / DTI',
                                                'company_profile'             => 'Company Profile',
                                                'nsrp_establishment_form'     => 'NSRP Establishment Form',
                                                'no_pending_case_certificate' => 'Certificate of No Pending Case',
                                                'vacancy_posting'             => 'Vacancy Posting',
                                            ];
                                        @endphp
                                        @foreach($docs as $field => $label)
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 mb-1 rounded-3"
                                            style="background:#f0f9f6;">
                                            <span style="font-size:13px;color:#2d7a5f;font-weight:500;">{{ $label }}</span>
                                            @if($req->{$field})
                                                <a href="{{ asset('storage/' . $req->{$field}) }}" target="_blank"
                                                    class="btn btn-sm fw-semibold"
                                                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                                           color:#fff;border:none;border-radius:8px;font-size:11px;">
                                                    <i class="bi bi-eye-fill me-1"></i>View
                                                </a>
                                            @else
                                                <span style="font-size:12px;color:#aaa;">Not uploaded</span>
                                            @endif
                                        </div>
                                        @endforeach

                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 mt-2 rounded-3"
                                            style="background:#fff;border:1px dashed #a8e6cf;">
                                            <span style="font-size:12px;color:#888;">Requirements Status</span>
                                            @php
                                                $reqColors = ['pending' => '#f59e0b', 'approved' => '#2d7a5f', 'rejected' => '#e05252'];
                                            @endphp
                                            <span class="fw-semibold" style="color:{{ $reqColors[$req->status] ?? '#888' }};font-size:12px;">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Hired Jobseekers (Approved tab only, LRA/SRA) --}}
                                    @if($tab === 'approved' && in_array($staffRole, ['lra','sra']))
                                    <h6 class="fw-bold mb-2" style="color:#2d7a5f;font-size:13px;">
                                        <i class="bi bi-people-fill me-1" style="color:#4dd9c0;"></i>
                                        Hired Jobseekers
                                    </h6>
                                    @php
                                        $hiredApps = $employer->jobs->flatMap->applications->where('status','hired');
                                    @endphp
                                    @if($hiredApps->isEmpty())
                                        <div class="text-center py-3" style="color:#888;font-size:13px;">
                                            <i class="bi bi-inbox" style="font-size:32px;color:#c0e8dc;"></i>
                                            <div class="mt-2">No hired jobseekers yet</div>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" style="font-size:13px;">
                                                <thead>
                                                    <tr style="background:#f0f9f6;">
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;">#</th>
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;">Name</th>
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;">Job Title</th>
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;text-align:center;">Match %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($hiredApps as $idx => $app)
                                                    <tr>
                                                        <td style="padding:8px 12px;color:#888;">{{ $idx + 1 }}</td>
                                                        <td style="padding:8px 12px;font-weight:600;color:#2d7a5f;">
                                                            {{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: '—' }}
                                                        </td>
                                                        <td style="padding:8px 12px;color:#555;">
                                                            {{ $app->job->title ?? '—' }}
                                                        </td>
                                                        <td style="padding:8px 12px;text-align:center;">
                                                            @php $match = $app->match_percentage ?? 0; @endphp
                                                            <span class="fw-semibold"
                                                                style="color:{{ $match >= 75 ? '#2d7a5f' : ($match >= 50 ? '#f59e0b' : '#e05252') }}">
                                                                {{ $match }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                    @endif

                                    {{-- Approve/Reject for pending requirements — Job Vacancy staff (local) / SRA (overseas) ra --}}
                                    @if($tab === 'pre' && $req && $req->status === 'pending' && in_array($staffRole, ['job_vacancy', 'sra']))
                                    <div class="mt-3 p-3 rounded-3" style="background:#fff8e1;border:1px solid #f59e0b;">
                                        <div style="font-size:13px;color:#a16207;" class="mb-3 fw-semibold">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            Requirements submitted — pending review
                                        </div>

                                        {{-- Approve --}}
                                        <form action="{{ route('staff.requirements.approve', $req->id) }}"
                                              method="POST" class="mb-3">
                                            @csrf
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                                       color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                Approve Requirements
                                            </button>
                                        </form>

                                        {{-- Reject --}}
                                        <form action="{{ route('staff.requirements.reject', $req->id) }}" method="POST" id="rejectForm{{ $req->id }}">
                                            @csrf
                                            @php
                                                $rejectDocs = [
                                                    'business_permit'             => 'CDO Business Permit',
                                                    'sec_dti'                     => 'SEC / DTI',
                                                    'company_profile'             => 'Company Profile',
                                                    'nsrp_establishment_form'     => 'NSRP Establishment Form',
                                                    'no_pending_case_certificate' => 'Certificate of No Pending Case',
                                                    'vacancy_posting'             => 'Vacancy Posting',
                                                ];
                                            @endphp
                                            <label class="form-label fw-semibold small" style="color:#7c2d12;">
                                                Which document(s) ang sayop/kulang?
                                            </label>
                                            <div class="p-2 mb-2 rounded-3" style="background:#fff;border:1px solid #f0c674;">
                                                @foreach($rejectDocs as $field => $label)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="rejected_fields[]" value="{{ $field }}" id="rej_{{ $field }}_{{ $req->id }}">
                                                    <label class="form-check-label" for="rej_{{ $field }}_{{ $req->id }}" style="font-size:12px;color:#7c2d12;">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                            <label class="form-label fw-semibold small" style="color:#7c2d12;">
                                                Reason for Rejection
                                            </label>
                                            <textarea name="remarks" rows="2" required
                                                class="form-control mb-2"
                                                style="border:1.5px solid #f0c674;border-radius:8px;font-size:13px;"
                                                placeholder="e.g. CDO Business Permit is already expired..."></textarea>
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:#e05252;color:#fff;border:none;
                                                       border-radius:8px;font-size:13px;padding:8px;"
                                                onclick="return validateReject(this)">
                                                <i class="bi bi-x-circle-fill me-1"></i>
                                                Reject Requirements
                                            </button>
                                        </form>
                                    </div>
                                    @elseif($tab === 'pre' && $req && $req->status === 'pending')
                                    <div class="mt-3 p-3 rounded-3 text-center" style="background:#f0f9f6;border:1px dashed #a8e6cf;">
                                        <i class="bi bi-eye" style="font-size:24px;color:#c0e8dc;"></i>
                                        <div style="font-size:12px;color:#888;margin-top:6px;">
                                            View only — {{ $employer->employerNsrp->is_overseas ?? false ? 'SRA' : 'Job Vacancy' }} staff handles approval.
                                        </div>
                                    </div>
                                    @endif

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        data-bs-dismiss="modal"
                                        style="border:1.5px solid #a8e6cf;color:#2d7a5f;
                                               background:#fff;border-radius:8px;">
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
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $employers->firstItem() }}–{{ $employers->lastItem() }}
                of {{ $employers->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $employers->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $employers->previousPageUrl() }}&tab={{ $tab }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($employers->getUrlRange(1, $employers->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $employers->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $employers->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}&tab={{ $tab }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$employers->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $employers->nextPageUrl() }}&tab={{ $tab }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-right"></i>
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
            url.searchParams.set('tab', '{{ $tab }}');
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });

    function validateReject(btn) {
        const form = btn.closest('form');
        const checked = form.querySelectorAll('input[name="rejected_fields[]"]:checked');
        if (checked.length === 0) {
            alert('Palihug pili og labing menos usa ka document nga sayop/kulang.');
            return false;
        }
        return true;
    }
</script>
@endpush

@endsection