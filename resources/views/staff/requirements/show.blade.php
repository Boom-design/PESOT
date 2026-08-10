@extends('staff.layouts.app')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-file-earmark-check-fill me-2" style="color:#4dd9c0;"></i>
            Employer Requirements
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            {{ $requirement->employer->company_name ?? $requirement->employer->name ?? '—' }}
        </p>
    </div>
    <a href="{{ route('staff.requirements') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;
              background:#fff;border-radius:8px;font-size:13px;">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    {{-- DETAILS --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#2d7a5f;">Company Information</h6>
                <table class="table table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;width:40%;">Company Name</td>
                        <td>{{ $requirement->employer->company_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Email</td>
                        <td>{{ $requirement->employer->employer->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Phone</td>
                        <td>{{ $requirement->employer->mobile_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Status</td>
                        <td>
                            @php
                                $colors = [
                                    'pending'  => '#f59e0b',
                                    'approved' => '#2d7a5f',
                                    'rejected' => '#e05252',
                                ];
                            @endphp
                            <span style="font-size:12px;font-weight:600;color:{{ $colors[$requirement->status] ?? '#888' }}">
                                {{ ucfirst($requirement->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($requirement->remarks)
                    <tr>
                        <td class="fw-semibold" style="color:#e05252;">Remarks</td>
                        <td style="color:#e05252;">{{ $requirement->remarks }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Date Submitted</td>
                        <td>{{ $requirement->created_at->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- DOCUMENTS --}}
        <div class="card border-0 shadow-sm rounded-3 mt-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#2d7a5f;">Submitted Documents</h6>
                @php
                    $docs = [
                        'business_permit'          => 'CDO Business Permit 2026',
                        'sec_dti'                  => 'SEC / DTI',
                        'company_profile'          => 'Company Profile',
                        'no_pending_case_certificate' => 'Certificate of No Pending Case',
                        'vacancy_posting'          => 'Vacancy Posting',
                    ];
                @endphp
                @foreach($docs as $field => $label)
                <div class="d-flex justify-content-between align-items-center py-2"
                    style="border-bottom:1px solid #f0f9f6;">
                    <span style="font-size:13px;color:#2d7a5f;">{{ $label }}</span>
                    @if($requirement->$field)
                        @php $ext = pathinfo($requirement->$field, PATHINFO_EXTENSION); @endphp
                        <button type="button"
                           class="btn btn-sm fw-semibold"
                           style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                  color:#fff;border:none;border-radius:8px;font-size:11px;"
                           onclick="openDocModal('{{ asset('storage/' . $requirement->$field) }}', '{{ $label }}', '{{ $ext }}')">
                            <i class="bi bi-eye me-1"></i>View
                        </button>
                    @else
                        <span style="font-size:12px;color:#aaa;">Not submitted</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── ESTABLISHMENT DETAILS (read-only, gikan sa registration — NSRP Establishment Form I & II) ── --}}
        @php $nsrp = $requirement->employer; @endphp
        <div class="card border-0 shadow-sm rounded-3 mt-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#2d7a5f;">
                    <i class="bi bi-clipboard-data-fill me-2" style="color:#4dd9c0;"></i>
                    Establishment Details <span style="font-weight:400;color:#888;font-size:11px;">(NSRP Form I & II, from registration)</span>
                </h6>
                <table class="table table-borderless mb-0" style="font-size:12.5px;">
                    <tr><td class="fw-semibold" style="color:#2d7a5f;width:45%;">Trade Name</td><td>{{ $nsrp->trade_name ?? '—' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:#2d7a5f;">TIN</td><td>{{ $nsrp->tin ?? '—' }} ({{ $nsrp->tin_type ?? '—' }})</td></tr>
                    <tr><td class="fw-semibold" style="color:#2d7a5f;">Line of Business</td><td>{{ $nsrp->line_of_business ?? '—' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:#2d7a5f;">Total Workforce</td><td>{{ $nsrp->total_workforce ?? '—' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:#2d7a5f;">Establishment Address</td><td>{{ collect([$nsrp->est_barangay ?? null, $nsrp->est_city_municipality ?? null, $nsrp->est_province ?? null])->filter()->implode(', ') ?: '—' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:#2d7a5f;">Contact Person</td><td>{{ $nsrp->contact_person ?? '—' }} ({{ $nsrp->position_title ?? '—' }})</td></tr>
                    <tr><td class="fw-semibold" style="color:#2d7a5f;">Mobile / Telephone</td><td>{{ $nsrp->mobile_number ?? '—' }} / {{ $nsrp->telephone_no ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    @if($requirement->status === 'pending' && $staffRole === 'job_vacancy')
    <div class="col-md-6">
        {{-- APPROVE --}}
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#2d7a5f;">
                    <i class="bi bi-check-circle-fill me-2" style="color:#4dd9c0;"></i>Approve Requirements
                </h6>
                <p style="font-size:13px;color:#888;">
                    Approving will allow this employer to request in-house interviews and post job vacancies.
                </p>
                <form id="approveForm" action="{{ route('staff.requirements.approve', $requirement->employer_requirements_id) }}" method="POST">
                    @csrf
                    <button type="button" class="btn w-100 fw-semibold"
                        style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                               color:#fff;border:none;border-radius:10px;
                               padding:10px;font-size:13px;"
                        onclick="confirmApprove()">
                        <i class="bi bi-check-circle-fill me-2"></i>Approve Requirements
                    </button>
                </form>
            </div>
        </div>

        {{-- REJECT --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#e05252;">
                    <i class="bi bi-x-circle-fill me-2"></i>Reject Requirements
                </h6>
                <form action="{{ route('staff.requirements.reject', $requirement->employer_requirements_id) }}" method="POST" id="rejectDocsForm">
                    @csrf
                    @php
                        $rejectDocs = [
                            'business_permit'             => 'CDO Business Permit',
                            'sec_dti'                     => 'SEC / DTI',
                            'company_profile'             => 'Company Profile',
                            'no_pending_case_certificate' => 'Certificate of No Pending Case',
                            'vacancy_posting'             => 'Vacancy Posting',
                        ];
                    @endphp
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                            Which document(s) are incorrect or missing? <span class="text-danger">*</span>
                        </label>
                        <div class="p-2 rounded-3" style="background:#fff5f5;border:1px solid #ffcdd2;">
                            @foreach($rejectDocs as $field => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="rejected_fields[]" value="{{ $field }}" id="rej_{{ $field }}">
                                <label class="form-check-label" for="rej_{{ $field }}" style="font-size:13px;color:#e05252;">
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                            Reason for Rejection <span class="text-danger">*</span>
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                            style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                            placeholder="e.g. CDO Business Permit is already expired, please submit updated copy."
                            required></textarea>
                    </div>
                    <button type="button" class="btn w-100 fw-semibold btn-danger"
                        style="border-radius:10px;padding:10px;font-size:13px;"
                        onclick="confirmReject()">
                        <i class="bi bi-x-circle-fill me-2"></i>Reject Requirements
                    </button>
                </form>
            </div>
        </div>
    </div>
    @elseif($requirement->status === 'pending')
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4 text-center">
                <i class="bi bi-eye" style="font-size:32px;color:#c0e8dc;"></i>
                <h6 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">View Only</h6>
                <p style="font-size:13px;color:#888;margin-bottom:0;">
                    Only Job Vacancy staff can approve or reject employer requirements.
                    You may view the submitted documents on the left.
                </p>
            </div>
        </div>
    </div>
    @elseif($requirement->status === 'pending')
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4 text-center">
                <i class="bi bi-eye" style="font-size:32px;color:#c0e8dc;"></i>
                <h6 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">View Only</h6>
                <p style="font-size:13px;color:#888;margin-bottom:0;">
                    Only Job Vacancy staff can approve or reject employer requirements.
                    You may view the submitted documents on the left.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function confirmApprove() {
    Swal.fire({
        title: 'Approve Requirements?',
        text: 'This will allow the employer to request in-house interviews and post job vacancies.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2d7a5f',
        cancelButtonColor: '#888',
        confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Yes, Approve!',
        cancelButtonText: 'Cancel',
        borderRadius: '16px',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('approveForm').submit();
        }
    });
}

function confirmReject() {
    const remarks = document.querySelector('textarea[name="remarks"]').value.trim();
    const checkedDocs = document.querySelectorAll('input[name="rejected_fields[]"]:checked');

    if (checkedDocs.length === 0) {
        Swal.fire({
            title: 'Select Document(s)!',
            text: 'Please check at least one document nga sayop/kulang.',
            icon: 'warning',
            confirmButtonColor: '#2d7a5f',
        });
        return;
    }
    if (!remarks) {
        Swal.fire({
            title: 'Reason Required!',
            text: 'Please state the reason for rejection before submitting.',
            icon: 'warning',
            confirmButtonColor: '#2d7a5f',
        });
        return;
    }
    Swal.fire({
        title: 'Reject Requirements?',
        text: 'The employer will be notified to resubmit the checked document(s) only.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e05252',
        cancelButtonColor: '#888',
        confirmButtonText: '<i class="bi bi-x-circle-fill me-1"></i> Yes, Reject!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('rejectDocsForm').submit();
        }
    });
}
</script>
@endpush

{{-- DOCUMENT VIEWER MODAL --}}
<div class="modal fade" id="docModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                <h6 class="modal-title fw-bold text-white" id="docModalTitle">
                    <i class="bi bi-file-earmark me-2"></i>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3" id="docModalBody">
                {{-- Content here --}}
            </div>
            <div class="modal-footer">
                <a id="docModalDownload" href="#" target="_blank"
                   class="btn btn-sm fw-semibold"
                   style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openDocModal(url, label, ext) {
    document.getElementById('docModalTitle').innerHTML = '<i class="bi bi-file-earmark me-2"></i>' + label;
    document.getElementById('docModalDownload').href = url;

    let body = '';
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext.toLowerCase())) {
        body = '<img src="' + url + '" class="img-fluid rounded" style="max-height:500px;" alt="' + label + '">';
    } else if (ext.toLowerCase() === 'pdf') {
        body = '<iframe src="' + url + '" width="100%" height="500px" style="border:none;border-radius:8px;"></iframe>';
    } else {
        body = '<p class="text-muted">Cannot preview this file. Click "Open in New Tab" to view.</p>';
    }

    document.getElementById('docModalBody').innerHTML = body;
    new bootstrap.Modal(document.getElementById('docModal')).show();
}
</script>
@endpush

@endsection