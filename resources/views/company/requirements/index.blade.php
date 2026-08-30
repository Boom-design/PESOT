@extends('company.layouts.app')

@section('page-title', 'My Company Requirements')

@section('content')

{{-- ── STATUS BANNER ── --}}
@if($requirement)
    @php
        $status = $requirement->status;
        $bannerColor = match($status) {
            'approved' => 'var(--g-50)',
            'rejected' => 'var(--danger-bg)',
            'expired'  => 'var(--warn-bg)',
            default    => 'var(--warn-bg)',
        };
        $borderColor = match($status) {
            'approved' => 'var(--g-600)',
            'rejected' => 'var(--danger)',
            'expired'  => 'var(--warn)',
            default    => 'var(--warn)',
        };
        $iconColor = match($status) {
            'approved' => 'var(--g-700)',
            'rejected' => 'var(--danger)',
            'expired'  => 'var(--warn)',
            default    => 'var(--warn)',
        };
        $icon = match($status) {
            'approved' => 'ph-fill ph-check-circle',
            'rejected' => 'ph-fill ph-x-circle',
            'expired'  => 'ph ph-hourglass-medium',
            default    => 'ph-fill ph-clock',
        };
        $statusText = match($status) {
            'approved' => 'Your requirements have been approved!',
            'rejected' => 'Your requirements were rejected. Please resubmit the correct documents.',
            'expired'  => 'One or more of your documents have expired. Please resubmit the flagged document(s) with updated files and new expiry dates.',
            default    => 'Your requirements are under review. PESO staff will notify you once verified.',
        };
    @endphp
    <div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3 fade-in"
         style="background:{{ $bannerColor }}; border:1px solid {{ $borderColor }};">
        <i class="{{ $icon }}" style="font-size:28px; color:{{ $iconColor }};"></i>
        <div>
            <div style="font-size:14px; font-weight:700; color:{{ $iconColor }};">
                {{ ucfirst($status) }}
            </div>
            <div style="font-size:12px; color:var(--n-700);">{{ $statusText }}</div>
            @if(in_array($status, ['rejected', 'expired']) && $requirement->remarks)
                <div class="mt-1" style="font-size:12px; color:var(--danger);">
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
            <i class="ph ph-upload-simple me-2" style="color:var(--g-600);"></i>
            {{ $requirement ? 'Resubmit Requirements' : 'Submit Requirements' }}
        </h6>
        @if($requirement && $requirement->status === 'approved')
            <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                <i class="ph ph-check-circle me-1"></i> Approved
            </span>
        @endif
    </div>

    <div class="peso-card-body">
        {{-- Info Box --}}
        <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
             style="background:var(--n-50); border:1px solid var(--n-200);">
            <i class="ph-fill ph-info" style="color:var(--g-600); font-size:18px; margin-top:1px;"></i>
            <div style="font-size:12px; color:var(--g-700); line-height:1.6;">
                Please upload all <strong>5 required documents</strong> to get verified by PESO staff.
                Accepted formats: <strong>JPG, PNG, PDF</strong> (max 5MB each).
            </div>
        </div>

        {{-- ── ESTABLISHMENT DETAILS (read-only, gikan sa registration) ── --}}
        @php $nsrp = $company->activeCompany(); @endphp
        <div class="mb-4 p-3 rounded-3" style="background:var(--n-0); border:1px solid var(--n-200);">
            <div style="font-size:12px; font-weight:700; color:var(--g-700); margin-bottom:12px;">
                <i class="ph ph-clipboard-text me-1" style="color:var(--g-600);"></i>
                Establishment Details (from your registration)
            </div>
            <div class="row g-2 mb-3" style="font-size:12.5px;">
                <div class="col-md-6"><span style="color:var(--n-500);">Company Name:</span> <strong style="color:var(--g-700);">{{ $nsrp->company_name ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Trade Name:</span> <strong style="color:var(--g-700);">{{ $nsrp->trade_name ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">TIN:</span> <strong style="color:var(--g-700);">{{ $nsrp->tin ?? 'None' }} ({{ $nsrp->tin_type ?? 'None' }})</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Employer Type:</span> <strong style="color:var(--g-700);">{{ $nsrp->employer_type ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Line of Business:</span> <strong style="color:var(--g-700);">{{ $nsrp->line_of_business ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Total Workforce:</span> <strong style="color:var(--g-700);">{{ $nsrp->total_workforce ?? 'None' }}</strong></div>
                <div class="col-12"><span style="color:var(--n-500);">Address:</span> <strong style="color:var(--g-700);">{{ collect([$nsrp->est_barangay ?? null, $nsrp->est_city_municipality ?? null, $nsrp->est_province ?? null])->filter()->implode(', ') ?: 'None' }}</strong></div>
            </div>
            <div style="font-size:11px; font-weight:700; color:var(--g-700); margin-bottom:8px; border-top:1px dashed var(--n-200); padding-top:10px;">
                Contact Details
            </div>
            <div class="row g-2" style="font-size:12.5px;">
                <div class="col-md-6"><span style="color:var(--n-500);">Contact Person:</span> <strong style="color:var(--g-700);">{{ $nsrp->contact_person ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Position / Title:</span> <strong style="color:var(--g-700);">{{ $nsrp->position_title ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Mobile Number:</span> <strong style="color:var(--g-700);">{{ $nsrp->mobile_number ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Telephone No.:</span> <strong style="color:var(--g-700);">{{ $nsrp->telephone_no ?? 'None' }}</strong></div>
                <div class="col-md-6"><span style="color:var(--n-500);">Fax No.:</span> <strong style="color:var(--g-700);">{{ $nsrp->fax_no ?? 'None' }}</strong></div>
            </div>
            <div style="font-size:10.5px;color:var(--n-500);margin-top:10px;">
                <i class="ph ph-info me-1"></i>To update these details, please contact PESO staff.
            </div>
        </div>

        <form action="{{ route('company.requirements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @php
            $docs = [
                ['field' => 'business_permit',             'label' => 'CDO Business Permit', 'year' => true,         'icon' => 'ph ph-buildings',          'hint' => 'Upload a clear photo or scan of your business permit, and say which year it covers.'],
                ['field' => 'sec_dti',                     'label' => 'SEC / DTI',                                   'icon' => 'ph ph-bank',              'hint' => 'SEC for Corporation, DTI for Single Proprietorship.'],
                ['field' => 'company_profile',             'label' => 'Company Profile',                             'icon' => 'ph ph-identification-card',       'hint' => 'Upload your company profile document.'],
                ['field' => 'no_pending_case_certificate', 'label' => 'Certificate of No Pending Case',             'icon' => 'ph ph-seal-check',       'hint' => 'From DOLE-CDO Field Office. Pwede to-follow.'],
                ['field' => 'vacancy_posting',             'label' => 'Vacancy Posting',                            'icon' => 'ph ph-file-text',         'hint' => '2 sets, letter size in portrait format with readable fonts.'],
            ];
            @endphp

            @php
                $isPartialReject = $requirement && in_array($requirement->status, ['rejected', 'expired']) && !empty($requirement->rejected_fields);
            @endphp

            <div class="row g-3">
                @foreach($docs as $i => $doc)
                @php
                    $isFlagged = $isPartialReject && in_array($doc['field'], $requirement->rejected_fields);
                    $isLocked  = $isPartialReject && !$isFlagged; // OK na, dili kinahanglan i-touch
                @endphp
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100"
                         style="border:1px solid {{ $isFlagged ? 'var(--danger)' : 'var(--n-200)' }}; background:{{ $isFlagged ? 'var(--danger-bg)' : 'var(--n-0)' }}; transition: border-color 0.2s;">

                        {{-- Header --}}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:32px; height:32px; background:var(--g-600);
                                        border-radius:8px; display:flex; align-items:center;
                                        justify-content:center; flex-shrink:0;">
                                <i class="{{ $doc['icon'] }}" style="color:#fff; font-size:15px;"></i>
                            </div>
                            <div>
                                <div style="font-size:12px; font-weight:700; color:{{ $isFlagged ? 'var(--danger)' : 'var(--g-700)' }};">
                                    {{ $i + 1 }}. {{ $doc['label'] }}
                                    @if($isFlagged)
                                        <span style="color:var(--danger);font-size:9px;margin-left:4px;font-weight:600;">RESUBMIT</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Hint --}}
                        <div style="font-size:11px; color:var(--n-500); margin-bottom:10px;">
                            {{ $doc['hint'] }}
                        </div>

                        @if($isLocked)
                            {{-- This document was not flagged by staff, so no resubmission is needed --}}
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:var(--g-50); border:1px solid var(--n-200);">
                                <i class="ph-fill ph-check-circle" style="color:var(--g-700);"></i>
                                <span style="font-size:11px; color:var(--g-700); font-weight:600;">
                                    No issues found — resubmission not required
                                </span>
                                @if($requirement->{$doc['field']})
                                <button type="button"
                                   onclick="openDocModal('{{ route('documents.requirement', [$requirement->employer_requirements_id, $doc['field']]) }}', '{{ $doc['label'] }}', '{{ pathinfo($requirement->{$doc['field']}, PATHINFO_EXTENSION) }}')"
                                   style="font-size:11px;color:var(--g-600);margin-left:auto;background:none;border:none;cursor:pointer;font-weight:600;">
                                    View <i class="ph ph-eye"></i>
                                </button>
                                @endif
                            </div>
                            @if($requirement->{$doc['field'].'_expires_at'})
                            <div style="font-size:10.5px;color:var(--n-500);margin-top:4px;">
                                Valid until: {{ $requirement->{$doc['field'].'_expires_at'}->format('F d, Y') }}
                            </div>
                            @endif
                        @else
                            {{-- Current file (kung naa, ug dili flagged/rejected) --}}
                            @if($requirement && $requirement->{$doc['field']} && !$isFlagged)
                                <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-2"
                                     style="background:var(--g-50); border:1px solid var(--n-200);">
                                    <i class="ph ph-file-text" style="color:var(--g-700);"></i>
                                    <span style="font-size:11px; color:var(--g-700); font-weight:600;">
                                        File uploaded
                                    </span>
                                    <button type="button"
                                       onclick="openDocModal('{{ route('documents.requirement', [$requirement->employer_requirements_id, $doc['field']]) }}', '{{ $doc['label'] }}', '{{ pathinfo($requirement->{$doc['field']}, PATHINFO_EXTENSION) }}')"
                                       style="font-size:11px;color:var(--g-600);margin-left:auto;background:none;border:none;cursor:pointer;font-weight:600;">
                                        View <i class="ph ph-eye"></i>
                                    </button>
                                </div>
                                @if($requirement->{$doc['field'].'_expires_at'})
                                <div style="font-size:10.5px;color:var(--n-500);margin-bottom:8px;">
                                    <i class="ph ph-calendar-blank me-1"></i>Valid until: <strong style="color:var(--g-700);">{{ $requirement->{$doc['field'].'_expires_at'}->format('F d, Y') }}</strong>
                                </div>
                                @endif
                            @endif

                            {{-- File input --}}
                            <input type="file"
                                   name="{{ $doc['field'] }}"
                                   class="form-control @error($doc['field']) is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   style="font-size:12px; border-radius:8px; border-color:{{ $isFlagged ? 'var(--danger)' : 'var(--n-200)' }};">

                            @error($doc['field'])
                                <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                            @enderror

                            @if(!empty($doc['year']))
                                {{-- The permit is issued per calendar year, so the year is
                                     what it is asked for — not a date the employer has to
                                     read off the paper and copy. The office allows three
                                     months into the next year to renew. --}}
                                <label class="mt-2 mb-1" style="font-size:10.5px;color:var(--n-500);display:block;">Permit Year</label>
                                <select name="business_permit_year"
                                        class="form-select @error('business_permit_year') is-invalid @enderror"
                                        style="font-size:12px; border-radius:8px; border-color:{{ $isFlagged ? 'var(--danger)' : 'var(--n-200)' }};">
                                    <option value="">Select year</option>
                                    @foreach(range(now()->year + 1, now()->year - 2) as $year)
                                        <option value="{{ $year }}"
                                            {{ (int) old('business_permit_year', $requirement?->business_permit_year ?? now()->year) === $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('business_permit_year')
                                    <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                                @enderror
                                <div style="font-size:10.5px;color:var(--n-500);margin-top:4px;">
                                    A {{ now()->year }} permit carries your account until
                                    <strong style="color:var(--g-700);">31 March {{ now()->year + 1 }}</strong>.
                                </div>
                            @else
                                {{-- Expiry date (required together with a new file) — pre-filled sa naa nang saved date kung naa --}}
                                <label class="mt-2 mb-1" style="font-size:10.5px;color:var(--n-500);display:block;">Expiry Date</label>
                                <input type="date"
                                       name="{{ $doc['field'] }}_expires_at"
                                       class="form-control @error($doc['field'].'_expires_at') is-invalid @enderror"
                                       value="{{ old($doc['field'].'_expires_at', optional($requirement?->{$doc['field'].'_expires_at'})->format('Y-m-d')) }}"
                                       style="font-size:12px; border-radius:8px; border-color:{{ $isFlagged ? 'var(--danger)' : 'var(--n-200)' }};">

                                @error($doc['field'].'_expires_at')
                                    <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                                @enderror
                            @endif
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- 6. COMPANY LOGO --}}
                {{-- Sits in the grid with the five documents because that is where
                     the employer is already uploading, but it is not one of them:
                     it never expires, it is never reviewed, and nothing here can
                     restrict the account over it. --}}
                <div class="col-md-6">
                    <div class="p-3 rounded-3 h-100" style="border:1px solid var(--n-200); background:var(--n-0);">

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:32px; height:32px; background:var(--g-600);
                                        border-radius:8px; display:flex; align-items:center;
                                        justify-content:center; flex-shrink:0;">
                                <i class="ph ph-image-square" style="color:#fff; font-size:15px;"></i>
                            </div>
                            <div>
                                <div style="font-size:12px; font-weight:700; color:var(--g-700);">
                                    6. Company Logo
                                    <span style="color:var(--n-500);font-weight:500;font-size:10px;">— optional</span>
                                </div>
                            </div>
                        </div>

                        {{-- The logo used to appear as a 32px square tucked
                             against the right edge of the heading, with nothing
                             to say it was the one already saved. Employers
                             uploaded a logo and could not find it afterwards.
                             It gets a panel of its own now. --}}
                        @if($requirement && $requirement->company_logo)
                        <div class="d-flex align-items-center gap-3 p-2 mb-2 rounded-3"
                             style="border:1px solid var(--n-200);background:var(--n-50);">
                            <img src="{{ route('documents.requirement', [$requirement->employer_requirements_id, 'company_logo']) }}"
                                 alt="Company logo"
                                 style="height:64px;width:64px;object-fit:contain;background:#fff;
                                        border:1px solid var(--n-200);border-radius:10px;padding:4px;flex-shrink:0;">
                            <div style="min-width:0;">
                                <div style="font-size:11.5px;font-weight:700;color:var(--g-700);">Current logo</div>
                                <div style="font-size:10.5px;color:var(--n-500);line-height:1.5;">
                                    Saved. Uploading another file below replaces it.
                                </div>
                                {{-- Opens here rather than in a new tab: the
                                     employer is halfway through a form, and a
                                     new tab loses the page they were filling. --}}
                                <button type="button"
                                        data-bs-toggle="modal" data-bs-target="#companyLogoModal"
                                        style="border:none;background:none;padding:0;
                                               font-size:10.5px;color:var(--g-600);font-weight:600;cursor:pointer;">
                                    <i class="ph ph-magnifying-glass-plus me-1"></i>Open full size
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="d-flex align-items-center gap-3 p-2 mb-2 rounded-3"
                             style="border:1px dashed var(--n-300);background:var(--n-50);">
                            <div style="height:64px;width:64px;display:flex;align-items:center;justify-content:center;
                                        background:#fff;border:1px dashed var(--n-300);border-radius:10px;flex-shrink:0;">
                                <i class="ph ph-image-square" style="color:var(--n-400);font-size:24px;"></i>
                            </div>
                            <div style="font-size:11.5px;color:var(--n-500);">No logo uploaded yet.</div>
                        </div>
                        @endif

                        <div style="font-size:11px; color:var(--n-500); margin-bottom:10px;">
                            A square JPG or PNG works best. PESO staff see it beside your company name
                            when they open your requirements.
                        </div>

                        <input type="file" name="company_logo"
                               class="form-control @error('company_logo') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png"
                               style="font-size:12px; border-radius:8px; border-color:var(--n-200);">
                        @error('company_logo')
                            <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                        @enderror

                        <div style="font-size:10.5px;color:var(--n-500);margin-top:8px;">
                            <i class="ph ph-info me-1"></i>No expiry date — a logo never needs renewing.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-peso px-4">
                    <i class="ph ph-paper-plane-tilt me-2"></i>
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
            <div class="modal-header" style="background:var(--g-600);">
                <h6 class="modal-title fw-bold text-white" id="docModalTitle">
                    <i class="ph ph-file me-2"></i>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3" id="docModalBody"></div>
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

@section('scripts')
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
@endsection

@if($requirement && $requirement->company_logo)
<div class="modal fade" id="companyLogoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header border-0" style="background:var(--g-600);padding:14px 20px;">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="ph ph-image-square me-2"></i>Company Logo
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center" style="background:var(--n-50);">
                <img src="{{ route('documents.requirement', [$requirement->employer_requirements_id, 'company_logo']) }}"
                     alt="Company logo"
                     style="max-width:100%;max-height:60vh;object-fit:contain;background:#fff;
                            border:1px solid var(--n-200);border-radius:12px;padding:10px;">
            </div>
        </div>
    </div>
</div>
@endif


@endsection