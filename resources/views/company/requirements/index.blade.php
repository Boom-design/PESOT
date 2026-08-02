@extends('company.layouts.app')

@section('page-title', 'My Company Requirements')

@section('content')

{{-- ── STATUS BANNER ── --}}
@if($requirement)
    @php
        $status = $requirement->status;
        $bannerColor = match($status) {
            'approved' => '#e8f8f3',
            'rejected' => '#fff5f5',
            default    => '#fff8e1',
        };
        $borderColor = match($status) {
            'approved' => '#4dd9c0',
            'rejected' => '#e53935',
            default    => '#f9a825',
        };
        $iconColor = match($status) {
            'approved' => '#2d7a5f',
            'rejected' => '#e53935',
            default    => '#f9a825',
        };
        $icon = match($status) {
            'approved' => 'bi-check-circle-fill',
            'rejected' => 'bi-x-circle-fill',
            default    => 'bi-clock-fill',
        };
        $statusText = match($status) {
            'approved' => 'Your requirements have been approved! You can now request in-house interviews.',
            'rejected' => 'Your requirements were rejected. Please resubmit the correct documents.',
            default    => 'Your requirements are under review. PESO staff will notify you once verified.',
        };
    @endphp
    <div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3 fade-in"
         style="background:{{ $bannerColor }}; border:1.5px solid {{ $borderColor }};">
        <i class="bi {{ $icon }}" style="font-size:28px; color:{{ $iconColor }};"></i>
        <div>
            <div style="font-size:14px; font-weight:700; color:{{ $iconColor }};">
                {{ ucfirst($status) }}
            </div>
            <div style="font-size:12px; color:#666;">{{ $statusText }}</div>
            @if($status === 'rejected' && $requirement->remarks)
                <div class="mt-1" style="font-size:12px; color:#e53935;">
                    <strong>Reason:</strong> {{ $requirement->remarks }}
                </div>
            @endif
        </div>
    </div>
@endif

{{-- ── UPLOAD FORM ── --}}
<div class="peso-card fade-in">
    <div class="peso-card-header">
        <h6>
            <i class="bi bi-upload me-2" style="color:#4dd9c0;"></i>
            {{ $requirement ? 'Resubmit Requirements' : 'Submit Requirements' }}
        </h6>
        @if($requirement && $requirement->status === 'approved')
            <span class="badge" style="background:#e8f8f3; color:#2d7a5f; font-size:11px; padding:6px 12px; border-radius:20px;">
                <i class="bi bi-check-circle me-1"></i> Approved
            </span>
        @endif
    </div>

    <div class="peso-card-body">
        {{-- Info Box --}}
        <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
             style="background:#f0f9f6; border:1px solid #a8e6cf;">
            <i class="bi bi-info-circle-fill" style="color:#4dd9c0; font-size:18px; margin-top:1px;"></i>
            <div style="font-size:12px; color:#2d7a5f; line-height:1.6;">
                Please upload all <strong>5 required documents</strong> to get verified by PESO staff.
                Accepted formats: <strong>JPG, PNG, PDF</strong> (max 5MB each).
            </div>
        </div>

        {{-- ── ESTABLISHMENT DETAILS (read-only, gikan sa registration) ── --}}
        @php $nsrp = $company->employerNsrp; @endphp
        <div class="mb-4 p-3 rounded-3" style="background:#fafffe; border:1.5px solid #e8f5f0;">
            <div style="font-size:12px; font-weight:700; color:#2d7a5f; margin-bottom:12px;">
                <i class="bi bi-clipboard-data me-1" style="color:#4dd9c0;"></i>
                Establishment Details (from your registration)
            </div>
            <div class="row g-2 mb-3" style="font-size:12.5px;">
                <div class="col-md-6"><span style="color:#888;">Company Name:</span> <strong style="color:#2d7a5f;">{{ $nsrp->company_name ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">Trade Name:</span> <strong style="color:#2d7a5f;">{{ $nsrp->trade_name ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">TIN:</span> <strong style="color:#2d7a5f;">{{ $nsrp->tin ?? '—' }} ({{ $nsrp->tin_type ?? '—' }})</strong></div>
                <div class="col-md-6"><span style="color:#888;">Employer Type:</span> <strong style="color:#2d7a5f;">{{ $nsrp->employer_type ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">Line of Business:</span> <strong style="color:#2d7a5f;">{{ $nsrp->line_of_business ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">Total Workforce:</span> <strong style="color:#2d7a5f;">{{ $nsrp->total_workforce ?? '—' }}</strong></div>
                <div class="col-12"><span style="color:#888;">Address:</span> <strong style="color:#2d7a5f;">{{ collect([$nsrp->est_barangay ?? null, $nsrp->est_city_municipality ?? null, $nsrp->est_province ?? null])->filter()->implode(', ') ?: '—' }}</strong></div>
            </div>
            <div style="font-size:11px; font-weight:700; color:#2d7a5f; margin-bottom:8px; border-top:1px dashed #a8e6cf; padding-top:10px;">
                Contact Details
            </div>
            <div class="row g-2" style="font-size:12.5px;">
                <div class="col-md-6"><span style="color:#888;">Contact Person:</span> <strong style="color:#2d7a5f;">{{ $nsrp->contact_person ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">Position / Title:</span> <strong style="color:#2d7a5f;">{{ $nsrp->position_title ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">Mobile Number:</span> <strong style="color:#2d7a5f;">{{ $nsrp->mobile_number ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">Telephone No.:</span> <strong style="color:#2d7a5f;">{{ $nsrp->telephone_no ?? '—' }}</strong></div>
                <div class="col-md-6"><span style="color:#888;">Fax No.:</span> <strong style="color:#2d7a5f;">{{ $nsrp->fax_no ?? '—' }}</strong></div>
            </div>
            <div style="font-size:10.5px;color:#888;margin-top:10px;">
                <i class="bi bi-info-circle me-1"></i>To update these details, please contact PESO staff.
            </div>
        </div>

        <form action="{{ route('company.requirements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @php
            $docs = [
                ['field' => 'business_permit',             'label' => 'CDO Business Permit 2026',                    'icon' => 'bi-building',          'hint' => 'Upload a clear photo or scan of your business permit.'],
                ['field' => 'sec_dti',                     'label' => 'SEC / DTI',                                   'icon' => 'bi-bank',              'hint' => 'SEC for Corporation, DTI for Single Proprietorship.'],
                ['field' => 'company_profile',             'label' => 'Company Profile',                             'icon' => 'bi-file-person',       'hint' => 'Upload your company profile document.'],
                ['field' => 'no_pending_case_certificate', 'label' => 'Certificate of No Pending Case',             'icon' => 'bi-patch-check',       'hint' => 'From DOLE-CDO Field Office. Pwede to-follow.'],
                ['field' => 'vacancy_posting',             'label' => 'Vacancy Posting',                            'icon' => 'bi-file-text',         'hint' => '2 sets, letter size in portrait format with readable fonts.'],
            ];
            @endphp

            @php
                $isPartialReject = $requirement && $requirement->status === 'rejected' && !empty($requirement->rejected_fields);
            @endphp

            <div class="row g-3">
                @foreach($docs as $i => $doc)
                @php
                    $isFlagged = $isPartialReject && in_array($doc['field'], $requirement->rejected_fields);
                    $isLocked  = $isPartialReject && !$isFlagged; // OK na, dili kinahanglan i-touch
                @endphp
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100"
                         style="border:1.5px solid {{ $isFlagged ? '#e05252' : '#e8f5f0' }}; background:{{ $isFlagged ? '#fff5f5' : '#fafffe' }}; transition: border-color 0.2s;">

                        {{-- Header --}}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:32px; height:32px; background:linear-gradient(135deg,#90d870,#4dd9c0);
                                        border-radius:8px; display:flex; align-items:center;
                                        justify-content:center; flex-shrink:0;">
                                <i class="bi {{ $doc['icon'] }}" style="color:#fff; font-size:15px;"></i>
                            </div>
                            <div>
                                <div style="font-size:12px; font-weight:700; color:{{ $isFlagged ? '#e05252' : '#2d7a5f' }};">
                                    {{ $i + 1 }}. {{ $doc['label'] }}
                                    @if($isFlagged)
                                        <span class="badge" style="background:#e05252;font-size:9px;margin-left:4px;">RESUBMIT</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Hint --}}
                        <div style="font-size:11px; color:#888; margin-bottom:10px;">
                            {{ $doc['hint'] }}
                        </div>

                        @if($isLocked)
                            {{-- This document was not flagged by staff, so no resubmission is needed --}}
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:#e8f8f3; border:1px solid #a8e6cf;">
                                <i class="bi bi-check-circle-fill" style="color:#2d7a5f;"></i>
                                <span style="font-size:11px; color:#2d7a5f; font-weight:600;">
                                    No issues found — resubmission not required
                                </span>
                                @if($requirement->{$doc['field']})
                                <button type="button"
                                   onclick="openDocModal('{{ Storage::url($requirement->{$doc['field']}) }}', '{{ $doc['label'] }}', '{{ pathinfo($requirement->{$doc['field']}, PATHINFO_EXTENSION) }}')"
                                   style="font-size:11px;color:#4dd9c0;margin-left:auto;background:none;border:none;cursor:pointer;font-weight:600;">
                                    View <i class="bi bi-eye"></i>
                                </button>
                                @endif
                            </div>
                        @else
                            {{-- Current file (kung naa, ug dili flagged/rejected) --}}
                            @if($requirement && $requirement->{$doc['field']} && !$isFlagged)
                                <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-2"
                                     style="background:#e8f8f3; border:1px solid #a8e6cf;">
                                    <i class="bi bi-file-earmark-check" style="color:#2d7a5f;"></i>
                                    <span style="font-size:11px; color:#2d7a5f; font-weight:600;">
                                        File uploaded
                                    </span>
                                    <button type="button"
                                       onclick="openDocModal('{{ Storage::url($requirement->{$doc['field']}) }}', '{{ $doc['label'] }}', '{{ pathinfo($requirement->{$doc['field']}, PATHINFO_EXTENSION) }}')"
                                       style="font-size:11px;color:#4dd9c0;margin-left:auto;background:none;border:none;cursor:pointer;font-weight:600;">
                                        View <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            @endif

                            {{-- File input --}}
                            <input type="file"
                                   name="{{ $doc['field'] }}"
                                   class="form-control @error($doc['field']) is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   style="font-size:12px; border-radius:8px; border-color:{{ $isFlagged ? '#e05252' : '#a8e6cf' }};">

                            @error($doc['field'])
                                <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-peso px-4">
                    <i class="bi bi-send me-2"></i>
                    {{ $requirement ? 'Resubmit Requirements' : 'Submit Requirements' }}
                </button>
            </div>
        </form>
    </div>
</div>

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
            <div class="modal-body text-center p-3" id="docModalBody"></div>
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

@section('scripts')
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
@endsection

@endsection