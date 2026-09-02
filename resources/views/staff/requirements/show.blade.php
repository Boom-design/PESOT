@extends('staff.layouts.app')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-file-text me-2" style="color:var(--g-600);"></i>
            Employer Requirements
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            {{ $requirement->employer->company_name ?? $requirement->employer->name ?? 'None' }}
        </p>
    </div>
    <a href="{{ route('staff.requirements') }}"
       class="btn btn-sm fw-semibold"
       style="border:1px solid var(--n-200);color:var(--g-700);
              background:#fff;border-radius:8px;font-size:13px;">
        <i class="ph ph-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    {{-- DETAILS --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--g-700);">Company Information</h6>
                <table class="table table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);width:40%;">Company Name</td>
                        <td>{{ $requirement->employer->company_name ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Email</td>
                        <td>{{ $requirement->employer->employer->email ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Phone</td>
                        <td>{{ $requirement->employer->mobile_number ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Status</td>
                        <td>
                            @php
                                $colors = [
                                    'pending'  => 'var(--warn)',
                                    'approved' => 'var(--g-700)',
                                    'rejected' => 'var(--danger)',
                                    'expired'  => 'var(--warn)',
                                ];
                            @endphp
                            <span style="font-size:12px;font-weight:600;color:{{ $colors[$requirement->status] ?? 'var(--n-500)' }}">
                                {{ ucfirst($requirement->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($requirement->remarks)
                    <tr>
                        <td class="fw-semibold" style="color:var(--danger);">Remarks</td>
                        <td style="color:var(--danger);">{{ $requirement->remarks }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Date Submitted</td>
                        <td>{{ $requirement->created_at->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- DOCUMENTS --}}
        <div class="card border-0 shadow-sm rounded-3 mt-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color:var(--g-700);">Submitted Documents</h6>

                {{-- Duha ka buton ang gitawag ug Approve sa kini nga pahina: ang
                     usa dinhi para sa usa ka papel, ang usa sa tuo para sa
                     tibuok kompanya. Ang linya sa ubos ang nagsulti kung asa
                     magsugod, aron ang desk dili mag-una sa tuo. --}}
                <p style="font-size:11.5px;color:var(--n-500);margin-bottom:14px;">
                    Review each document below — <strong style="color:var(--g-600);">Approve</strong> or
                    <strong style="color:var(--danger);">Reject</strong> one at a time. The employer moves to
                    Approved Employers only from the button on the right, after all five are done.
                </p>

                {{-- Logo first, and set apart: it is not reviewed, cannot be
                     rejected, and never expires. It is here so the desk can see
                     who they are looking at. --}}
                <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3"
                     style="background:var(--n-50);border:1px solid var(--n-200);">
                    @if($requirement->company_logo)
                        <img src="{{ route('documents.requirement', [$requirement->employer_requirements_id, 'company_logo']) }}"
                             alt="Company logo"
                             style="height:48px;width:48px;object-fit:contain;background:#fff;border:1px solid var(--n-200);border-radius:8px;padding:3px;">
                    @else
                        <div style="height:48px;width:48px;display:flex;align-items:center;justify-content:center;background:#fff;border:1px dashed var(--n-300);border-radius:8px;">
                            <i class="ph ph-image-square" style="color:var(--n-400);font-size:20px;"></i>
                        </div>
                    @endif
                    <div>
                        <div style="font-size:12.5px;font-weight:600;color:var(--g-700);">Company Logo</div>
                        <div style="font-size:10.5px;color:var(--n-500);">
                            {{ $requirement->company_logo ? 'Uploaded by the employer — no expiry.' : 'Not uploaded.' }}
                        </div>
                    </div>
                </div>

                @php
                    $docs = [
                        'business_permit'          => $requirement->businessPermitLabel(),
                        'sec_dti'                  => 'SEC / DTI',
                        'company_profile'          => 'Company Profile',
                        'no_pending_case_certificate' => 'Certificate of No Pending Case',
                        'vacancy_posting'          => 'Vacancy Posting',
                    ];
                @endphp
                @foreach($docs as $field => $label)
                <div style="border-bottom:1px solid var(--n-50);">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div>
                        <span style="font-size:13px;color:var(--g-700);">{{ $label }}</span>
                        @if($requirement->isFieldRejected($field) && $requirement->fieldRejectionNote($field))
                            <div style="font-size:10.5px;color:var(--danger);font-weight:600;">
                                Rejected — {{ $requirement->fieldRejectionNote($field) }}
                            </div>
                        @endif
                        @php $expiresAt = $requirement->{$field.'_expires_at'}; @endphp
                        @if($field === 'business_permit' && $requirement->business_permit_year)
                            {{-- The permit is judged by the year it covers plus the
                                 renewal grace, not by its own 31 December expiry. --}}
                            @if($requirement->isBusinessPermitOverdue())
                                <div style="font-size:10.5px;color:var(--danger);font-weight:600;">
                                    Overdue — the {{ $requirement->business_permit_year + 1 }} permit was due
                                    {{ $requirement->businessPermitGraceEndsAt()->format('M d, Y') }}
                                </div>
                            @elseif($requirement->isBusinessPermitInGrace())
                                <div style="font-size:10.5px;color:var(--warn);font-weight:600;">
                                    Renewal due {{ $requirement->businessPermitGraceEndsAt()->format('M d, Y') }}
                                </div>
                            @else
                                <div style="font-size:10.5px;color:var(--n-500);">
                                    Covers {{ $requirement->business_permit_year }} — carries the account until
                                    {{ $requirement->businessPermitGraceEndsAt()->format('M d, Y') }}
                                </div>
                            @endif
                        @elseif($expiresAt)
                            @if($requirement->isFieldExpired($field))
                                <div style="font-size:10.5px;color:var(--danger);font-weight:600;">Expired {{ $expiresAt->format('M d, Y') }}</div>
                            @elseif($requirement->isFieldExpiringSoon($field))
                                <div style="font-size:10.5px;color:var(--warn);font-weight:600;">Expires soon: {{ $expiresAt->format('M d, Y') }}</div>
                            @else
                                <div style="font-size:10.5px;color:var(--n-500);">Valid until {{ $expiresAt->format('M d, Y') }}</div>
                            @endif
                        @endif
                    </div>
                    @if($requirement->$field)
                        @php $ext = pathinfo($requirement->$field, PATHINFO_EXTENSION); @endphp
                        <div class="d-flex align-items-center gap-2">
                            <button type="button"
                               class="btn btn-sm fw-semibold"
                               style="background:var(--g-600);
                                      color:#fff;border:none;border-radius:8px;font-size:11px;"
                               onclick="openDocModal('{{ route('documents.requirement', [$requirement->employer_requirements_id, $field]) }}', '{{ $label }}', '{{ $ext }}')">
                                <i class="ph ph-eye me-1"></i>View
                            </button>

                            {{-- Usa ka papel, usa ka hukom. Walay pindot dinhi
                                 nga magdala sa kompanya sa Approved Employers ug
                                 walay pindot dinhi nga mopadala ug mensahe sa
                                 employer — ang duha ka buton sa tuo ang mobuhat
                                 niana, human mahukman ang lima. --}}
                            @if($requirement->status === 'pending' && $staffRole === 'job_vacancy')
                                @if($requirement->isFieldAccepted($field))
                                    <span class="fw-semibold" style="color:var(--g-600);font-size:11px;">
                                        <i class="ph-fill ph-check-circle me-1"></i>Approved
                                    </span>
                                    @include('staff.requirements._undo_document', ['field' => $field])
                                @elseif($requirement->isFieldRejected($field))
                                    <span class="fw-semibold" style="color:var(--danger);font-size:11px;">
                                        <i class="ph-fill ph-x-circle me-1"></i>Rejected
                                    </span>
                                    @include('staff.requirements._undo_document', ['field' => $field])
                                @else
                                    <form action="{{ route('staff.requirements.documents.accept', [$requirement->employer_requirements_id, $field]) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="border:1px solid var(--g-600);color:var(--g-600);background:#fff;
                                                   border-radius:8px;font-size:11px;">
                                            <i class="ph ph-check me-1"></i>Approve
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        style="border:1px solid var(--danger-br);color:var(--danger);background:#fff;
                                               border-radius:8px;font-size:11px;"
                                        data-bs-toggle="collapse" data-bs-target="#rejDoc_{{ $field }}">
                                        <i class="ph ph-x me-1"></i>Reject
                                    </button>
                                @endif
                            @elseif($requirement->isFieldAccepted($field))
                                <span class="fw-semibold" style="color:var(--g-600);font-size:11px;">
                                    <i class="ph-fill ph-check-circle me-1"></i>Approved
                                </span>
                            @elseif($requirement->isFieldRejected($field))
                                <span class="fw-semibold" style="color:var(--danger);font-size:11px;">
                                    <i class="ph-fill ph-x-circle me-1"></i>Rejected
                                </span>
                            @endif
                        </div>
                    @else
                        <span style="font-size:12px;color:var(--n-400);">Not submitted</span>
                    @endif
                </div>

                {{-- Ang hinungdan gipangayo dayon. Ang papel nga gibalibaran nga
                     walay hinungdan mobalik sa employer nga wala mahibalo unsay
                     ayohon. --}}
                @if($requirement->$field && $requirement->status === 'pending'
                    && $staffRole === 'job_vacancy' && !$requirement->isFieldDecided($field))
                    <div class="collapse" id="rejDoc_{{ $field }}">
                        <form action="{{ route('staff.requirements.documents.reject', [$requirement->employer_requirements_id, $field]) }}"
                              method="POST" class="d-flex gap-2 pb-2">
                            @csrf
                            <input type="text" name="reason" maxlength="255" required
                                class="form-control form-control-sm"
                                style="border:1px solid var(--danger-br);border-radius:8px;font-size:12px;"
                                placeholder="What is wrong with this document?">
                            <button type="submit" class="btn btn-sm btn-danger fw-semibold"
                                style="border-radius:8px;font-size:11px;white-space:nowrap;">
                                Reject
                            </button>
                        </form>
                    </div>
                @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── ESTABLISHMENT DETAILS (read-only, gikan sa registration — NSRP Establishment Form I & II) ── --}}
        @php $nsrp = $requirement->employer; @endphp
        <div class="card border-0 shadow-sm rounded-3 mt-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--g-700);">
                    <i class="ph-fill ph-clipboard-text me-2" style="color:var(--g-600);"></i>
                    Establishment Details <span style="font-weight:400;color:var(--n-500);font-size:11px;">(NSRP Form I & II, from registration)</span>
                </h6>
                <table class="table table-borderless mb-0" style="font-size:12.5px;">
                    <tr><td class="fw-semibold" style="color:var(--g-700);width:45%;">Trade Name</td><td>{{ $nsrp->trade_name ?? 'None' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:var(--g-700);">TIN</td><td>{{ $nsrp->tin ?? 'None' }} ({{ $nsrp->tin_type ?? 'None' }})</td></tr>
                    <tr><td class="fw-semibold" style="color:var(--g-700);">Line of Business</td><td>{{ $nsrp->line_of_business ?? 'None' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:var(--g-700);">Total Workforce</td><td>{{ $nsrp->total_workforce ?? 'None' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:var(--g-700);">Establishment Address</td><td>{{ collect([$nsrp->est_barangay ?? null, $nsrp->est_city_municipality ?? null, $nsrp->est_province ?? null])->filter()->implode(', ') ?: 'None' }}</td></tr>
                    <tr><td class="fw-semibold" style="color:var(--g-700);">Contact Person</td><td>{{ $nsrp->contact_person ?? 'None' }} ({{ $nsrp->position_title ?? 'None' }})</td></tr>
                    <tr><td class="fw-semibold" style="color:var(--g-700);">Mobile / Telephone</td><td>{{ $nsrp->mobile_number ?? 'None' }} / {{ $nsrp->telephone_no ?? 'None' }}</td></tr>
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
                <h6 class="fw-bold mb-3" style="color:var(--g-700);">
                    <i class="ph-fill ph-check-circle me-2" style="color:var(--g-600);"></i>Final Step
                </h6>
                <p style="font-size:13px;color:var(--n-500);">
                    Moving this employer to Approved Employers lets them request in-house interviews
                    and post job vacancies. Review the five documents on the left first.
                </p>

                {{-- Pila na ang nahukman. Ang buton sirado hangtod mahukman
                     ang lima, ug sirado gihapon kung naay usa nga gibalibaran —
                     ang folder nga naay sayop mobalik sa employer, dili moadto
                     sa Approved Employers. --}}
                @php
                    $docLabels     = \App\Models\EmployerRequirement::DOCUMENT_LABELS;
                    $decidedCount  = $requirement->decidedDocumentCount();
                    $stillToReview = $requirement->documentsNotYetDecided();
                    $rejectedNow   = array_values(array_intersect(
                        \App\Models\EmployerRequirement::REVIEWED_DOCUMENTS,
                        (array) $requirement->rejected_fields
                    ));
                    $hasRejected    = $requirement->hasRejectedDocuments();
                    $readyToApprove = $requirement->allDocumentsDecided() && !$hasRejected;
                @endphp

                <div class="p-3 rounded-3 mb-3"
                     style="background:{{ $hasRejected ? 'var(--danger-bg)' : ($readyToApprove ? 'var(--g-50, #f2f7f3)' : 'var(--warn-bg)') }};
                            border:1px solid {{ $hasRejected ? 'var(--danger-br)' : ($readyToApprove ? 'var(--g-600)' : 'var(--warn-br)') }};">
                    <div class="fw-semibold" style="font-size:12.5px;color:{{ $hasRejected ? 'var(--danger)' : ($readyToApprove ? 'var(--g-700)' : 'var(--warn)') }};">
                        {{ $decidedCount }} of {{ count($docLabels) }} documents reviewed
                    </div>
                    @if($stillToReview)
                        <div style="font-size:11.5px;color:var(--n-500);margin-top:4px;">
                            Still to review:
                            {{ collect($stillToReview)->map(fn($f) => $docLabels[$f] ?? $f)->implode(', ') }}
                        </div>
                    @endif
                    @if($hasRejected)
                        <div style="font-size:11.5px;color:var(--danger);margin-top:4px;">
                            Rejected: {{ collect($rejectedNow)->map(fn($f) => $docLabels[$f] ?? $f)->implode(', ') }}.
                            Send the folder back to the employer below.
                        </div>
                    @endif
                </div>

                <form id="approveForm" action="{{ route('staff.requirements.approve', $requirement->employer_requirements_id) }}" method="POST">
                    @csrf
                    <button type="button" class="btn w-100 fw-semibold"
                        style="background:{{ $readyToApprove ? 'var(--g-600)' : 'var(--n-200)' }};
                               color:{{ $readyToApprove ? '#fff' : 'var(--n-500)' }};border:none;border-radius:10px;
                               padding:10px;font-size:13px;{{ $readyToApprove ? '' : 'cursor:not-allowed;' }}"
                        {{ $readyToApprove ? '' : 'disabled' }}
                        onclick="confirmApprove()">
                        <i class="ph-fill ph-check-circle me-2"></i>Move to Approved Employers
                    </button>
                </form>
            </div>
        </div>

        {{-- REJECT --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--danger);">
                    <i class="ph-fill ph-x-circle me-2"></i>Decline Requirements
                </h6>
                <form action="{{ route('staff.requirements.reject', $requirement->employer_requirements_id) }}" method="POST" id="rejectDocsForm">
                    @csrf
                    @php
                        $rejectDocs = [
                            'business_permit'             => $requirement->businessPermitLabel(),
                            'sec_dti'                     => 'SEC / DTI',
                            'company_profile'             => 'Company Profile',
                            'no_pending_case_certificate' => 'Certificate of No Pending Case',
                            'vacancy_posting'             => 'Vacancy Posting',
                        ];
                    @endphp
                    {{-- Ang gi-check dinhi mao ang gibalibaran na sa taas. Ang
                         desk makadugang o makakuha pa dinhi sa katapusang gutlo
                         — kining porma gihapon ang usa ka mensahe nga makaabot
                         sa employer. --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Which document(s) are incorrect or missing? <span class="text-danger">*</span>
                        </label>
                        <div class="p-2 rounded-3" style="background:var(--danger-bg);border:1px solid var(--danger-br);">
                            @foreach($rejectDocs as $field => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="rejected_fields[]" value="{{ $field }}" id="rej_{{ $field }}"
                                    {{ $requirement->isFieldRejected($field) ? 'checked' : '' }}>
                                <label class="form-check-label" for="rej_{{ $field }}" style="font-size:13px;color:var(--danger);">
                                    {{ $label }}
                                    @if($requirement->fieldRejectionNote($field))
                                        <span style="font-weight:400;">— {{ $requirement->fieldRejectionNote($field) }}</span>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Reason for Decline <span class="text-danger">*</span>
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                            placeholder="e.g. CDO Business Permit is already expired, please submit updated copy."
                            required></textarea>
                    </div>
                    <button type="button" class="btn w-100 fw-semibold btn-danger"
                        style="border-radius:10px;padding:10px;font-size:13px;"
                        onclick="confirmReject()">
                        <i class="ph-fill ph-x-circle me-2"></i>Decline Requirements
                    </button>
                </form>
            </div>
        </div>
    </div>
    @elseif($requirement->status === 'pending')
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4 text-center">
                <i class="ph ph-eye" style="font-size:32px;color:var(--n-300);"></i>
                <h6 class="fw-bold mt-2 mb-1" style="color:var(--g-700);">View Only</h6>
                <p style="font-size:13px;color:var(--n-500);margin-bottom:0;">
                    Only Job Vacancy staff can approve or reject employer requirements.
                    You may view the submitted documents on the left.
                </p>
            </div>
        </div>
    </div>
    @elseif($requirement->status === 'expired')
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4 text-center">
                <i class="ph ph-hourglass-medium" style="font-size:32px;color:var(--warn-br);"></i>
                <h6 class="fw-bold mt-2 mb-1" style="color:var(--warn);">Awaiting Employer Resubmission</h6>
                <p style="font-size:13px;color:var(--n-500);margin-bottom:0;">
                    One or more documents expired and the employer has been notified. No staff action is
                    needed until they resubmit — the request will return to Pending automatically.
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
        confirmButtonColor: 'var(--g-700)',
        cancelButtonColor: 'var(--n-500)',
        confirmButtonText: '<i class="ph-fill ph-check-circle me-1"></i> Yes, Approve!',
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
            confirmButtonColor: 'var(--g-700)',
        });
        return;
    }
    if (!remarks) {
        Swal.fire({
            title: 'Reason Required!',
            text: 'Please state the reason for declining before submitting.',
            icon: 'warning',
            confirmButtonColor: 'var(--g-700)',
        });
        return;
    }
    Swal.fire({
        title: 'Decline Requirements?',
        text: 'The employer will be notified to resubmit the checked document(s) only.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--danger)',
        cancelButtonColor: 'var(--n-500)',
        confirmButtonText: '<i class="ph-fill ph-x-circle me-1"></i> Yes, Decline!',
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
            <div class="modal-header" style="background:var(--g-600);">
                <h6 class="modal-title fw-bold text-white" id="docModalTitle">
                    <i class="ph ph-file me-2"></i>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3" id="docModalBody">
                {{-- Content here --}}
            </div>
            <div class="modal-footer">
                <a id="docModalDownload" href="#" target="_blank"
                   class="btn btn-sm fw-semibold"
                   style="background:var(--g-600);color:#fff;border:none;border-radius:8px;">
                    <i class="ph ph-arrow-square-out me-1"></i> Open in New Tab
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openDocModal(url, label, ext) {
    document.getElementById('docModalTitle').innerHTML = '<i class="ph ph-file me-2"></i>' + label;
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