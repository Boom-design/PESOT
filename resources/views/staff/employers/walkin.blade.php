@extends('staff.layouts.app')

@section('content')

{{-- Parehas nga marka sa kalendaryo sa nakita sa employer: pula ug linaktan
     ang adlaw nga puno, dalag ang holiday. Ang layout sa staff wala pa niini
     kay dinhi ra siya gigamit. --}}
<style>
    .flatpickr-day.fp-date-booked,
    .flatpickr-day.fp-date-booked:hover {
        background: var(--danger-bg) !important;
        color: var(--danger) !important;
        text-decoration: line-through;
        cursor: not-allowed !important;
    }
    .flatpickr-day.fp-date-holiday,
    .flatpickr-day.fp-date-holiday:hover {
        background: var(--warn-bg) !important;
        color: var(--warn) !important;
        cursor: not-allowed !important;
    }
</style>

{{-- An employer who walks into the office. The staff encode the company, the
     documents they brought with them, and the vacancy they came to give — all
     in one sitting. The account is real; the password is temporary and the
     employer is forced to change it the first time they log in.

     Three desks use this page. Job Vacancy takes local employers and SRA takes
     overseas ones; for those two, $isOverseas comes from the staff role and not
     from anything typed here.

     Job Fair is the exception. An employer who turns up on the day of a fair
     may be either, so that desk chooses the employer type and $isOverseas
     follows it — the same rule the online registration uses. That desk also
     brings only one channel: the fair itself. --}}

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h5 class="fw-bold mb-0" style="color:var(--g-700);">Walk-in Employer Registration</h5>
        <div style="font-size:12px;color:var(--n-500);">
            @if($staffRole === 'job_fair')
                Register an employer who turned up for a job fair, attach their requirements,
                and bring their vacancy to the event
            @else
                Register {{ $isOverseas ? 'an overseas' : 'a local' }} employer at the counter,
                attach their requirements, and post their vacancy
            @endif
        </div>
    </div>
    <a href="{{ route('staff.employers') }}" class="btn btn-sm fw-semibold"
       style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;padding:6px 14px;">
        <i class="ph ph-caret-left me-1"></i> Back to Employers
    </a>
</div>

@if($errors->any())
<div class="alert d-flex align-items-start gap-2 rounded-3 border-0 mb-3"
     style="background:#FEF2F2;color:var(--danger);border:1px solid var(--danger) !important;">
    <i class="ph-fill ph-warning-circle" style="font-size:18px;flex-shrink:0;margin-top:1px;"></i>
    <div style="font-size:12.5px;">
        <div class="fw-bold mb-1">This form was not saved.</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@php
    // Which step owns each field, so a validation bounce reopens the step that
    // failed instead of dropping the staff back on page one.
    $stepFields = [
        1 => ['company_name', 'trade_name', 'tin', 'tin_type', 'employer_type',
              'total_workforce', 'line_of_business', 'industry_group',
              'est_province', 'est_city_municipality', 'est_barangay'],
        2 => ['contact_title', 'contact_person', 'position_title',
              'telephone_no', 'mobile_number', 'fax_no', 'email'],
        3 => ['business_permit', 'sec_dti', 'company_profile',
              'no_pending_case_certificate', 'vacancy_posting',
              'company_logo', 'business_permit_year',
              'business_permit_expires_at', 'sec_dti_expires_at',
              'company_profile_expires_at', 'no_pending_case_certificate_expires_at',
              'vacancy_posting_expires_at'],
        4 => ['title', 'description', 'location', 'type', 'slots', 'salary', 'accepts_disability',
              'deadline', 'certification_agreed', 'schedule_types',
              'company_interview_date', 'inhouse_date', 'inhouse_date_end',
              'venue_type', 'venue_address', 'job_fair_id'],
    ];

    $errorStep = 1;
    foreach ($stepFields as $step => $fields) {
        foreach ($fields as $field) {
            if ($errors->has($field)) { $errorStep = $step; break 2; }
        }
    }

    $walkinDocs = [
        ['field' => 'business_permit',             'label' => 'CDO Business Permit', 'year' => true],
        ['field' => 'sec_dti',                     'label' => 'SEC / DTI'],
        ['field' => 'company_profile',             'label' => 'Company Profile'],
        ['field' => 'no_pending_case_certificate', 'label' => 'Certificate of No Pending Case'],
        ['field' => 'vacancy_posting',             'label' => 'Vacancy Posting'],
    ];
@endphp

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">

        <form id="walkinForm" action="{{ route('staff.employers.walkin.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 1: ESTABLISHMENT DETAILS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="walkin-step" data-step="1">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-buildings" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">I. Establishment Details</h6>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="peso-label">Name of Establishment *</label>
                        <input type="text" name="company_name" id="companyNameInput" class="form-control peso-input"
                            value="{{ old('company_name') }}" required>
                        <div id="companyNameWarn" style="display:none;font-size:11px;color:var(--warn);margin-top:3px;">
                            <i class="ph-fill ph-warning-circle"></i> A company with this name is already registered. Check the Employers list before continuing.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="peso-label">Trade Name</label>
                        <input type="text" name="trade_name" class="form-control peso-input" value="{{ old('trade_name') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Tax Identification Number (TIN) *</label>
                        <input type="text" name="tin" class="form-control peso-input"
                            value="{{ old('tin') }}" placeholder="e.g. 123-456-789-000" required>
                        {{-- Main/Branch belongs to the TIN itself, so it sits directly
                             under the input rather than in a field of its own. --}}
                        <div class="d-flex align-items-center gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tin_type" value="main" id="tinMain"
                                    {{ old('tin_type') == 'main' ? 'checked' : '' }}>
                                <label class="form-check-label" style="font-size:12px;" for="tinMain">Main</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tin_type" value="branch" id="tinBranch"
                                    {{ old('tin_type') == 'branch' ? 'checked' : '' }}>
                                <label class="form-check-label" style="font-size:12px;" for="tinBranch">Branch</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="peso-label">Total Workforce</label>
                        <select name="total_workforce" class="form-select peso-input">
                            <option value="">Select</option>
                            @foreach(['micro' => 'Micro (1-9)', 'small' => 'Small (10-99)', 'medium' => 'Medium (100-199)', 'large' => 'Large (200+)'] as $value => $label)
                                <option value="{{ $value }}" {{ old('total_workforce') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="peso-label">Employer Type * <span style="font-weight:400;color:var(--n-500);">(choose one)</span></label>
                        <div class="p-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-50);">
                            <div class="d-flex align-items-center flex-wrap gap-3 mb-2">
                                <span class="fw-bold" style="font-size:11px;color:var(--g-700);min-width:56px;">Public</span>
                                @foreach([
                                    'National Government Agency',
                                    'Local Government Unit',
                                    'Government-owned & controlled corp.',
                                    'State/Local University or College',
                                ] as $opt)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="employer_type"
                                        value="{{ $opt }}" id="empType_{{ Str::slug($opt) }}"
                                        {{ old('employer_type') == $opt ? 'checked' : '' }} required>
                                    <label class="form-check-label" style="font-size:12px;" for="empType_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                </div>
                                @endforeach
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <span class="fw-bold" style="font-size:11px;color:var(--g-700);min-width:56px;">Private</span>
                                {{-- The recruitment agency on offer follows the desk. An
                                     overseas agency encoded at the local counter — or the
                                     other way round — would be filed under the wrong
                                     office and never appear in that staff's list. --}}
                                @foreach($staffRole === 'job_fair'
                                    ? ['Direct Hire', 'Local Recruitment Agency', 'Overseas Recruitment Agency']
                                    : ['Direct Hire', $isOverseas ? 'Overseas Recruitment Agency' : 'Local Recruitment Agency']
                                as $opt)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="employer_type"
                                        value="{{ $opt }}" id="empType_{{ Str::slug($opt) }}"
                                        {{ old('employer_type') == $opt ? 'checked' : '' }} required>
                                    <label class="form-check-label" style="font-size:12px;" for="empType_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                </div>
                                @endforeach
                            </div>
                            <div style="font-size:11px;color:var(--n-500);margin-top:8px;">
                                @if($staffRole === 'job_fair')
                                    A job fair takes both markets, so the type you pick here decides
                                    which desk the company is filed under — Overseas Recruitment
                                    Agency goes to SRA, everything else to Job Vacancy.
                                @elseif($isOverseas)
                                    This is the overseas desk — the company is filed under SRA.
                                    Local employers are registered by Job Vacancy staff.
                                @else
                                    This is the local desk — the company is filed under Job Vacancy.
                                    Overseas Recruitment Agencies are registered by SRA.
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Line of Business</label>
                        <input type="text" name="line_of_business" class="form-control peso-input" value="{{ old('line_of_business') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="peso-label">Industry Group *</label>
                        <select name="industry_group" class="form-select peso-input" required>
                            <option value="">Select</option>
                            @foreach(\App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS as $group)
                                <option value="{{ $group }}" {{ old('industry_group') === $group ? 'selected' : '' }}>{{ $group }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="peso-label">Province</label>
                        <select id="provinceSelect" name="est_province" class="form-select peso-input"
                                data-old="{{ old('est_province') }}">
                            <option value="">Select province</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">City / Municipality</label>
                        <select id="citySelect" name="est_city_municipality" class="form-select peso-input"
                                data-old="{{ old('est_city_municipality') }}" disabled>
                            <option value="">Select province first</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Barangay</label>
                        <select id="barangaySelect" name="est_barangay" class="form-select peso-input"
                                data-old="{{ old('est_barangay') }}" disabled>
                            <option value="">Select city first</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 2: CONTACT DETAILS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="walkin-step" data-step="2" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-user" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">II. Establishment Contact Details</h6>
                </div>

                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="peso-label">Title</label>
                        <select name="contact_title" class="form-select peso-input">
                            <option value="">—</option>
                            @foreach(['Mr.', 'Ms.', 'Mrs.', 'Engr.', 'Atty.', 'Dr.'] as $title)
                                <option value="{{ $title }}" {{ old('contact_title') === $title ? 'selected' : '' }}>{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="peso-label">Contact Person *</label>
                        <input type="text" name="contact_person" class="form-control peso-input"
                            value="{{ old('contact_person') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="peso-label">Position / Designation *</label>
                        <input type="text" name="position_title" class="form-control peso-input"
                            value="{{ old('position_title') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="peso-label">Telephone No.</label>
                        <input type="text" name="telephone_no" class="form-control peso-input" value="{{ old('telephone_no') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Mobile Number *</label>
                        <input type="text" name="mobile_number" class="form-control peso-input"
                            value="{{ old('mobile_number') }}" placeholder="09XXXXXXXXX" required>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Fax No.</label>
                        <input type="text" name="fax_no" class="form-control peso-input" value="{{ old('fax_no') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Email Address *</label>
                        <input type="email" name="email" id="emailInput" class="form-control peso-input"
                            value="{{ old('email') }}" required>
                        <div id="emailWarn" style="display:none;font-size:11px;color:var(--danger);margin-top:3px;">
                            <i class="ph-fill ph-warning-circle"></i> This email is already registered. Search the Employers list instead.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100 d-flex align-items-center"
                             style="background:#fff5e0;border:1px solid #e0b64d;">
                            <div style="font-size:11.5px;color:#6b4500;line-height:1.6;">
                                <i class="ph-fill ph-key me-1"></i>
                                This email is the employer's login. A <strong>temporary password</strong> is
                                generated on save and shown to you once — read it to them before you leave the
                                page. They are forced to change it at first login.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 3: REQUIREMENTS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="walkin-step" data-step="3" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-clipboard-text" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">III. Company Requirements</h6>
                </div>

                {{-- Required here, unlike the online form. The staff member is
                     holding the papers, so these are saved already approved and
                     nobody has to review them a second time. --}}
                <div class="d-flex align-items-start gap-2 p-2 mb-3 rounded-3"
                     style="background:var(--n-50);border:1px solid var(--n-200);">
                    <i class="ph-fill ph-info" style="color:var(--g-600);font-size:15px;margin-top:1px;"></i>
                    <div style="font-size:11.5px;color:var(--n-700);line-height:1.5;">
                        All <strong>5 documents</strong> are required. Scan or photograph what the employer
                        brought. They are recorded as verified by you, so the vacancy goes live immediately —
                        do not encode a walk-in whose papers you have not seen.
                    </div>
                </div>

                <div class="row g-3">
                    @foreach($walkinDocs as $i => $doc)
                    <div class="col-md-6">
                        <label class="peso-label">{{ $i + 1 }}. {{ $doc['label'] }} *</label>
                        <input type="file" name="{{ $doc['field'] }}" class="form-control peso-input"
                               accept=".jpg,.jpeg,.png,.pdf" required>
                        @if(!empty($doc['year']))
                            {{-- Tinuig ang permit, mao nga tuig ang gipangayo. Ang
                                 petsa (Dis 31) gikalkula sa server gikan sa tuig. --}}
                            <label class="peso-label mt-1" style="font-weight:400;">Permit year *</label>
                            <select name="business_permit_year" class="form-select peso-input" required>
                                @foreach(range(now()->year + 1, now()->year - 2) as $year)
                                    <option value="{{ $year }}" {{ (int) old('business_permit_year', now()->year) === $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                            @error('business_permit_year')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        @else
                            <label class="peso-label mt-1" style="font-weight:400;">Expiry date *</label>
                            <input type="date" name="{{ $doc['field'] }}_expires_at" class="form-control peso-input"
                                   value="{{ old($doc['field'].'_expires_at') }}"
                                   min="{{ now()->addDay()->format('Y-m-d') }}" required>
                            @error($doc['field'].'_expires_at')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        @endif
                        @error($doc['field'])
                            <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                        @enderror
                    </div>
                    @endforeach

                    {{-- Logo — dili siya requirement, walay expiry, ug dili siya
                         required: ang employer nga naa sa counter tingali walay dala. --}}
                    <div class="col-md-6">
                        <label class="peso-label">6. Company Logo <span style="font-weight:400;color:var(--n-500);">(optional, no expiry)</span></label>
                        <input type="file" name="company_logo" class="form-control peso-input" accept=".jpg,.jpeg,.png">
                        @error('company_logo')
                            <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 4: THE VACANCY --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="walkin-step" data-step="4" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-briefcase" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">IV. Job Vacancy</h6>
                </div>

                {{-- One fill-up, as many channels as the employer wants. Each
                     channel is its own job row with its own date, venue and
                     applicant list, but they share one posting_group_id — so
                     five slots stay five slots however many channels carry
                     them. --}}
                @php $pickedTypes = (array) old('schedule_types', ['company_interview']); @endphp

                @if($staffRole === 'job_fair')
                    {{-- Usa ra ang channel niini nga desk: ang fair mismo. Ang
                         in-house iya sa LRA ug ang company interview sa Job
                         Vacancy, ug ang pagtanyag niini dinhi mao ang pagtanyag
                         ug desisyon nga dili iya niining counter. --}}
                    <input type="hidden" name="schedule_types[]" value="job_fair">
                    <div class="p-3 rounded-3 mb-3" style="border:1px solid var(--n-200);background:var(--n-50);">
                        <div class="fw-semibold" style="font-size:12px;color:var(--g-700);">
                            <i class="ph-fill ph-calendar-dots me-1" style="color:var(--g-600);"></i>Job Fair
                        </div>
                        <div style="font-size:11px;color:var(--n-500);margin-top:2px;">
                            This vacancy goes onto the fair you pick below. It is accepted as you save it —
                            you are the desk that decides.
                        </div>
                    </div>
                @else
                <label class="peso-label">Schedule Type * <span style="font-weight:400;color:var(--n-500);">(pick one or more)</span></label>
                <div class="p-3 rounded-3 mb-3" style="border:1px solid var(--n-200);background:var(--n-50);">
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input walkin-channel" type="checkbox" name="schedule_types[]"
                                   value="company_interview" id="chanCompanyInterview" data-panel="panelCompanyInterview"
                                   {{ in_array('company_interview', $pickedTypes) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" style="font-size:12px;" for="chanCompanyInterview">
                                Company Interview
                            </label>
                            <div style="font-size:11px;color:var(--n-500);">At the employer's own office. Goes live on save.</div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input walkin-channel" type="checkbox" name="schedule_types[]"
                                   value="inhouse" id="chanInhouse" data-panel="panelInhouse"
                                   {{ in_array('inhouse', $pickedTypes) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" style="font-size:12px;" for="chanInhouse">
                                In-house
                            </label>
                            <div style="font-size:11px;color:var(--n-500);">Books a day. LRA accepts the date before it goes live.</div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input walkin-channel" type="checkbox" name="schedule_types[]"
                                   value="job_fair" id="chanJobFair" data-panel="panelJobFair"
                                   {{ in_array('job_fair', $pickedTypes) ? 'checked' : '' }}
                                   {{ $jobFairEvents->isEmpty() ? 'disabled' : '' }}>
                            <label class="form-check-label fw-semibold" style="font-size:12px;" for="chanJobFair">
                                Job Fair
                            </label>
                            <div style="font-size:11px;color:var(--n-500);">
                                @if($jobFairEvents->isEmpty())
                                    No upcoming job fair to attach this to.
                                @else
                                    Waits for the Job Fair desk to accept it into an event.
                                @endif
                            </div>
                        </div>
                    </div>
                    @error('schedule_types')
                        <div style="font-size:11px;color:var(--danger);margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                {{-- COMPANY INTERVIEW --}}
                <div id="panelCompanyInterview" class="walkin-panel p-3 rounded-3 mb-3"
                     style="border:1px solid var(--n-200);display:none;">
                    <div class="fw-bold mb-2" style="font-size:12px;color:var(--g-700);">
                        <i class="ph-fill ph-briefcase me-1"></i> Company Interview
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="peso-label">Preferred date *</label>
                            <input type="text" name="company_interview_date" id="walkinCompanyInterviewDate"
                                   class="form-control peso-input walkin-conditional" autocomplete="off"
                                   placeholder="Select a date" value="{{ old('company_interview_date') }}">
                            <div style="font-size:11px;color:var(--n-500);margin-top:3px;">
                                The day applicants come in. PESO is closed on holidays, so those dates
                                cannot be picked; weekends can still be requested.
                            </div>
                            @error('company_interview_date')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- IN-HOUSE --}}
                <div id="panelInhouse" class="walkin-panel p-3 rounded-3 mb-3"
                     style="border:1px solid var(--n-200);display:none;">
                    <div class="fw-bold mb-2" style="font-size:12px;color:var(--g-700);">
                        <i class="ph-fill ph-buildings me-1"></i> In-house Interview
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="peso-label">Available dates *</label>
                            {{-- Ang makita nga input kay pang-display ra; ang gipadala kay ang
                                 duha ka hidden. Range ni: ang employer motanyag kung kanus-a
                                 sila libre, ang LRA mopili sa aktwal nga adlaw. Parehas gyud
                                 nga picker sa naa sa modal sa employer. --}}
                            <input type="text" id="walkinInhouseDate" class="form-control peso-input walkin-conditional"
                                   autocomplete="off" placeholder="Select a date or a range">
                            <input type="hidden" name="inhouse_date"     id="walkinInhouseDateValue"    value="{{ old('inhouse_date') }}">
                            <input type="hidden" name="inhouse_date_end" id="walkinInhouseDateEndValue" value="{{ old('inhouse_date_end') }}">
                            <div style="font-size:11px;color:var(--n-500);margin-top:3px;">
                                Pick one day, or click twice for a range. Every day picked is held —
                                no other company can take it. The PESO Office takes
                                {{ $inhouseDailyLimit }} {{ $inhouseDailyLimit === 1 ? 'company' : 'companies' }}
                                a day; a full day is crossed out on the calendar. Holidays cannot be
                                picked; weekends can. The date is not final until <strong>LRA accepts</strong> it.
                            </div>
                            <div id="walkinDateAvailability" style="font-size:11px;margin-top:4px;"></div>
                            @error('inhouse_date')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                            @error('inhouse_date_end')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Venue *</label>
                            <select name="venue_type" id="walkinVenueType" class="form-select peso-input">
                                <option value="peso_office" {{ old('venue_type', 'peso_office') === 'peso_office' ? 'selected' : '' }}>PESO Office</option>
                                <option value="other" {{ old('venue_type') === 'other' ? 'selected' : '' }}>Other Venue</option>
                            </select>
                            <div style="font-size:11px;color:var(--n-500);margin-top:3px;">
                                Other Venue is the employer's own place — no limit, so a date that is
                                full here is still theirs to use there.
                            </div>
                            @error('venue_type')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12" id="walkinVenueAddressWrap" style="display:none;">
                            <label class="peso-label">Venue address *</label>
                            <input type="text" name="venue_address" id="walkinVenueAddress" class="form-control peso-input"
                                   value="{{ old('venue_address') }}"
                                   placeholder="e.g. SM CDO Downtown Premier, 3rd Floor">
                            @error('venue_address')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- JOB FAIR --}}
                {{-- Sa Job Fair desk walay checkbox nga mo-abli niini, mao nga
                     abli na siya dayon ug required ang event. --}}
                <div id="panelJobFair" class="walkin-panel p-3 rounded-3 mb-3"
                     style="border:1px solid var(--n-200);{{ $staffRole === 'job_fair' ? '' : 'display:none;' }}">
                    <div class="fw-bold mb-2" style="font-size:12px;color:var(--g-700);">
                        <i class="ph-fill ph-tent me-1"></i> Job Fair
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="peso-label">Which job fair *</label>
                            <select name="job_fair_id" class="form-select peso-input walkin-conditional"
                                    {{ $staffRole === 'job_fair' ? 'required' : '' }}>
                                <option value="">Select event</option>
                                @foreach($jobFairEvents as $event)
                                    <option value="{{ $event->job_fair_events_id }}"
                                        {{ (string) old('job_fair_id') === (string) $event->job_fair_events_id ? 'selected' : '' }}>
                                        {{ $event->title }} — {{ $event->event_date->format('M d, Y') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('job_fair_id')
                                <div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="peso-label">Job Title *</label>
                        <input type="text" name="title" class="form-control peso-input" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="peso-label">Employment Type *</label>
                        <select name="type" class="form-select peso-input" required>
                            @foreach(['full_time' => 'Full time', 'part_time' => 'Part time', 'contractual' => 'Contractual', 'casual' => 'Casual'] as $value => $label)
                                <option value="{{ $value }}" {{ old('type', 'full_time') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="peso-label">No. of Vacancies *</label>
                        <input type="number" name="slots" class="form-control peso-input"
                            value="{{ old('slots', 1) }}" min="1" required>
                    </div>

                    <div class="col-md-6">
                        <label class="peso-label">Work Location *</label>
                        <input type="text" name="location" class="form-control peso-input"
                            value="{{ old('location') }}" placeholder="e.g. Carmen, Cagayan de Oro City" required>
                    </div>
                    <div class="col-md-3">
                        <label class="peso-label">Salary</label>
                        <input type="text" name="salary" class="form-control peso-input"
                            value="{{ old('salary') }}" placeholder="e.g. 15,000 - 18,000">
                    </div>
                    <div class="col-md-3">
                        <label class="peso-label">Deadline <span style="font-weight:400;color:var(--n-500);">(optional)</span></label>
                        <input type="date" name="deadline" class="form-control peso-input"
                            value="{{ old('deadline') }}" min="{{ now()->addDay()->format('Y-m-d') }}">
                        <div style="font-size:11px;color:var(--n-500);margin-top:2px;">
                            Blank uses the schedule date.
                        </div>
                    </div>

                    {{-- Gipangutana kay ang Job Fair desk mismo ang mopili kung
                         asa nga fair mosulod kini nga bakante, ug ang fair nga
                         para sa PWD modawat lang sa "Yes". Kung dili masukna
                         dinhi, ang walk-in dili gyud makasulod sa maong fair. --}}
                    <div class="col-12">
                        <label class="peso-label">Accepts Applicants with Disability? *</label>
                        <div class="d-flex gap-4">
                            @foreach(['yes' => 'Yes', 'no' => 'No'] as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input walkin-disability" type="radio"
                                       name="accepts_disability" value="{{ $val }}"
                                       id="walkinDisability{{ $val }}"
                                       {{ old('accepts_disability', 'no') === $val ? 'checked' : '' }} required>
                                <label class="form-check-label" style="font-size:12px;" for="walkinDisability{{ $val }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                        <div id="walkinDisabilityTypes"
                             class="d-flex gap-3 flex-wrap mt-2 {{ old('accepts_disability') === 'yes' ? '' : 'd-none' }}">
                            @foreach(['Visual', 'Hearing', 'Speech', 'Physical', 'Others'] as $t)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="disability_types[]"
                                       value="{{ $t }}" id="walkinDisabilityType{{ $loop->index }}"
                                       {{ in_array($t, (array) old('disability_types', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" style="font-size:12px;" for="walkinDisabilityType{{ $loop->index }}">{{ $t }}</label>
                            </div>
                            @endforeach
                        </div>
                        @error('accepts_disability')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="peso-label">Job Description *</label>
                        <textarea name="description" rows="4" class="form-control peso-input" required>{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- CERTIFICATION --}}
                <div class="mt-4 p-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-50);">
                    <div class="fw-bold mb-2" style="font-size:12px;color:var(--g-700);">Certification / Authorization</div>
                    <div style="font-size:11.5px;color:var(--n-700);line-height:1.6;">
                        The employer certifies that the information given above is true and correct, and
                        authorizes PESO Cagayan de Oro to use it for employment facilitation.
                        Certified on <strong>{{ now()->format('F d, Y') }}</strong>.
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="certification_agreed"
                               value="1" id="certAgreed" {{ old('certification_agreed') ? 'checked' : '' }} required>
                        <label class="form-check-label" style="font-size:12px;" for="certAgreed">
                            The employer agreed to the certification at the counter.
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn fw-semibold"
                        style="background:var(--g-600);color:#fff;border:none;border-radius:10px;font-size:13px;padding:10px 24px;">
                        <i class="ph-fill ph-floppy-disk me-1"></i> Register Employer &amp; Post Vacancy
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- spacer para dili ma-cover sa fixed nav ang katapusang content --}}
<div style="height:90px;"></div>

{{-- ── FIXED BOTTOM-RIGHT STEP NAV ── --}}
<div class="d-flex align-items-center gap-3"
    style="position:fixed; bottom:24px; right:24px; z-index:500;
            background:#fff; padding:10px 16px; border-radius:14px;
            box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid var(--n-200);">
    <button type="button" id="walkinStepPrev" class="btn btn-sm"
        style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;">
        <i class="ph ph-caret-left"></i>
    </button>
    <span id="walkinStepInfo" style="font-size:13px;color:var(--g-700);font-weight:600;white-space:nowrap;">Step 1 of 4</span>
    <button type="button" id="walkinStepNext" class="btn btn-sm fw-semibold"
        style="border:none;border-radius:8px;color:#fff;padding:6px 14px;background:var(--g-600);">
        Next <i class="ph ph-caret-right ms-1"></i>
    </button>
</div>

@push('scripts')
<script>
const walkinTotalSteps = 4;
let walkinCurrentStep = {{ $errorStep }};

function showWalkinStep(step) {
    document.querySelectorAll('.walkin-step').forEach(el => {
        el.style.display = (parseInt(el.dataset.step) === step) ? 'block' : 'none';
    });
    document.getElementById('walkinStepInfo').textContent = `Step ${step} of ${walkinTotalSteps}`;
    document.getElementById('walkinStepPrev').disabled = step <= 1;
    document.getElementById('walkinStepNext').style.display = step >= walkinTotalSteps ? 'none' : 'inline-flex';
}

function validateWalkinStep() {
    const stepEl = document.querySelector(`.walkin-step[data-step="${walkinCurrentStep}"]`);
    for (const field of stepEl.querySelectorAll('[required]')) {
        if (!field.checkValidity()) {
            field.reportValidity();
            return false;
        }
    }
    return true;
}

document.getElementById('walkinStepPrev').addEventListener('click', () => {
    if (walkinCurrentStep > 1) {
        walkinCurrentStep--;
        showWalkinStep(walkinCurrentStep);
    }
});

document.getElementById('walkinStepNext').addEventListener('click', () => {
    if (!validateWalkinStep()) return;
    if (walkinCurrentStep < walkinTotalSteps) {
        walkinCurrentStep++;
        showWalkinStep(walkinCurrentStep);
    }
});

// A required field on a hidden step cannot be reported by the browser, so jump
// to the step that owns it before letting the submit fail silently.
document.getElementById('walkinForm').addEventListener('submit', function (event) {
    for (const field of this.querySelectorAll('[required]')) {
        if (!field.checkValidity()) {
            const owner = field.closest('.walkin-step');
            if (owner) {
                walkinCurrentStep = parseInt(owner.dataset.step);
                showWalkinStep(walkinCurrentStep);
            }
            field.reportValidity();
            event.preventDefault();
            return;
        }
    }
});

showWalkinStep(walkinCurrentStep);

// ── Channel panels. A required field inside a hidden panel can never be
// ── filled, so `required` follows visibility rather than being fixed in the
// ── markup. The server checks the same thing again. ──
function syncWalkinChannels() {
    document.querySelectorAll('.walkin-channel').forEach(box => {
        const panel = document.getElementById(box.dataset.panel);
        if (!panel) return;
        const on = box.checked && !box.disabled;
        panel.style.display = on ? 'block' : 'none';
        panel.querySelectorAll('.walkin-conditional').forEach(field => {
            if (on) { field.setAttribute('required', 'required'); }
            else    { field.removeAttribute('required'); field.setCustomValidity(''); }
        });
    });

    // Ang Job Fair desk walay in-house nga channel, mao nga wala sad ang
    // venue nga kontrol. Ang pagpangita niini nga walay pagsusi mo-undang sa
    // tibuok script, ug lakip na ang calendar ug ang mga lakang sa porma.
    const venue = document.getElementById('walkinVenueType');
    const wrap  = document.getElementById('walkinVenueAddressWrap');
    const input = document.getElementById('walkinVenueAddress');
    const inhouseBox = document.getElementById('chanInhouse');
    if (!venue || !wrap || !input || !inhouseBox) return;

    const showAddress = venue.value === 'other' && inhouseBox.checked;
    wrap.style.display = showAddress ? 'block' : 'none';
    if (showAddress) { input.setAttribute('required', 'required'); }
    else             { input.removeAttribute('required'); }
}

document.querySelectorAll('.walkin-channel').forEach(box =>
    box.addEventListener('change', syncWalkinChannels));

syncWalkinChannels();

// ── The calendar. Same behaviour the employer gets in the Post a Job modal:
// ── a day the PESO Office cannot take is disabled and crossed out, a holiday
// ── is disabled in a different colour, and picking a date asks the server how
// ── full it really is. The server checks all of it again on submit. ──
const WALKIN_HOLIDAYS         = @json(\App\Support\Holidays::aroundNow());
const WALKIN_EARLIEST         = @json($earliestBookable);
const WALKIN_DAILY_COMPANIES  = @json($inhouseDailyLimit);
let   walkinBookedDates       = [];

function walkinIso(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function walkinIsHoliday(date) {
    return Object.prototype.hasOwnProperty.call(WALKIN_HOLIDAYS, walkinIso(date));
}

function walkinDayCreate(dObj, dStr, fp, dayElem) {
    const iso = walkinIso(dayElem.dateObj);
    if (WALKIN_HOLIDAYS[iso]) {
        dayElem.classList.add('fp-date-holiday');
        dayElem.title = WALKIN_HOLIDAYS[iso] + ' — PESO is closed';
        return;
    }
    if (walkinBookedDates.includes(iso)) {
        dayElem.classList.add('fp-date-booked');
        dayElem.title = `PESO Office is full (${WALKIN_DAILY_COMPANIES}/${WALKIN_DAILY_COMPANIES} companies)`
                      + ' — pick Other Venue to use this date';
    }
}

let walkinInhousePicker = null;

function walkinInitPickers() {
    flatpickr(document.getElementById('walkinCompanyInterviewDate'), {
        minDate: WALKIN_EARLIEST,
        dateFormat: 'Y-m-d',
        disable: [walkinIsHoliday],
        onDayCreate: walkinDayCreate,
    });

    const startValue = document.getElementById('walkinInhouseDateValue').value;
    const endValue   = document.getElementById('walkinInhouseDateEndValue').value;

    walkinInhousePicker = flatpickr(document.getElementById('walkinInhouseDate'), {
        minDate: WALKIN_EARLIEST,
        dateFormat: 'Y-m-d',
        mode: 'range',
        // A validation bounce should not lose the dates already picked.
        defaultDate: startValue ? (endValue ? [startValue, endValue] : [startValue]) : null,
        disable: [function (date) {
            // Holiday: PESO is closed whatever the venue.
            if (walkinIsHoliday(date)) return true;
            if (document.getElementById('walkinVenueType').value !== 'peso_office') return false;
            return walkinBookedDates.includes(walkinIso(date));
        }],
        onDayCreate: walkinDayCreate,
        onChange: function (selectedDates) {
            document.getElementById('walkinInhouseDateValue').value =
                selectedDates.length ? walkinIso(selectedDates[0]) : '';
            // One day picked: the end stays blank — that is the shape the
            // server expects for a single-day request.
            document.getElementById('walkinInhouseDateEndValue').value =
                selectedDates.length > 1 ? walkinIso(selectedDates[1]) : '';
            walkinCheckAvailability();
        },
    });
}

function walkinCheckAvailability() {
    const note  = document.getElementById('walkinDateAvailability');
    const start = document.getElementById('walkinInhouseDateValue').value;
    const end   = document.getElementById('walkinInhouseDateEndValue').value;
    const venue = document.getElementById('walkinVenueType').value;

    if (!start) { note.textContent = ''; return; }

    note.textContent = 'Checking availability...';
    note.style.color = 'var(--n-500)';

    fetch(`{{ route('staff.inhouse.checkDate') }}?date=${start}&date_end=${end || start}&venue_type=${venue}`)
        .then(res => res.json())
        .then(data => {
            // A PESO office schedule wins over everything: if the window holds
            // one, the venue does not matter.
            if (data.office_full) {
                note.innerHTML = (data.office_note || 'PESO is not available on those dates.')
                    + ' Please pick dates that do not include it.';
                note.style.color = 'var(--danger)';
                return;
            }

            const officeNote = data.office_note ? ' ' + data.office_note : '';

            if (venue === 'other') {
                note.textContent = 'No limit for other venues.' + officeNote;
                note.style.color = officeNote ? 'var(--warn)' : 'var(--g-700)';
                return;
            }

            const cap = data.limit ?? WALKIN_DAILY_COMPANIES;

            if (data.occupied) {
                note.innerHTML = `PESO Office is full (${cap}/${cap} companies) on <strong>`
                    + (data.full_days || []).join(', ') + '</strong>. '
                    + 'Switch the venue to <strong>Other Venue</strong> to keep these dates — '
                    + 'there is no company limit outside the PESO Office.';
                note.style.color = 'var(--danger)';
            } else {
                note.textContent = `Available at PESO Office (${data.count}/${cap} at the busiest day).` + officeNote;
                note.style.color = officeNote ? 'var(--warn)' : 'var(--g-700)';
            }
        })
        .catch(() => { note.textContent = ''; });
}

document.getElementById('walkinVenueType').addEventListener('change', function () {
    syncWalkinChannels();
    // A full day is only blocked at the PESO Office, so redraw when the venue
    // changes — outside the office that same day is free again.
    if (walkinInhousePicker) walkinInhousePicker.redraw();
    walkinCheckAvailability();
});

fetch(`{{ route('staff.inhouse.bookedDates') }}`)
    .then(res => res.json())
    .then(data => { walkinBookedDates = data.booked_dates || []; walkinInitPickers(); walkinCheckAvailability(); })
    .catch(() => { walkinInitPickers(); });

// ── Duplicate checks — a warning, not a block. The staff decide. ──
document.getElementById('companyNameInput').addEventListener('blur', function () {
    const name = this.value.trim();
    const warn = document.getElementById('companyNameWarn');
    if (!name) { warn.style.display = 'none'; return; }
    fetch(`{{ route('check.companyName') }}?company_name=${encodeURIComponent(name)}`)
        .then(r => r.json())
        .then(data => { warn.style.display = data.exists ? 'block' : 'none'; })
        .catch(() => { warn.style.display = 'none'; });
});

document.getElementById('emailInput').addEventListener('blur', function () {
    const email = this.value.trim();
    const warn  = document.getElementById('emailWarn');
    if (!email) { warn.style.display = 'none'; return; }
    fetch(`{{ route('check.email') }}?email=${encodeURIComponent(email)}`)
        .then(r => r.json())
        .then(data => { warn.style.display = data.taken ? 'block' : 'none'; })
        .catch(() => { warn.style.display = 'none'; });
});

// ── Address cascade — the local JSON in storage/app/ph_address, not an
// ── external API: the office has no budget for one and it must work offline. ──
const provinceSelect = document.getElementById('provinceSelect');
const citySelect     = document.getElementById('citySelect');
const barangaySelect = document.getElementById('barangaySelect');

function fillSelect(select, items, placeholder, keep) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(item => {
        const option = document.createElement('option');
        option.value = item.name ?? item;
        option.textContent = item.name ?? item;
        if (item.code) option.dataset.code = item.code;
        if (keep && option.value === keep) option.selected = true;
        select.appendChild(option);
    });
    select.disabled = items.length === 0;
}

function selectedCode(select) {
    const option = select.selectedOptions[0];
    return option ? option.dataset.code : null;
}

function loadCities(keepCity, keepBarangay) {
    const code = selectedCode(provinceSelect);
    if (!code) {
        fillSelect(citySelect, [], 'Select province first');
        fillSelect(barangaySelect, [], 'Select city first');
        return;
    }
    fetch(`/ph-address/provinces/${encodeURIComponent(code)}/cities`)
        .then(r => r.json())
        .then(cities => {
            fillSelect(citySelect, cities, 'Select city / municipality', keepCity);
            fillSelect(barangaySelect, [], 'Select city first');
            if (keepCity) loadBarangays(keepBarangay);
        });
}

function loadBarangays(keepBarangay) {
    const code = selectedCode(citySelect);
    if (!code) {
        fillSelect(barangaySelect, [], 'Select city first');
        return;
    }
    fetch(`/ph-address/cities/${encodeURIComponent(code)}/barangays`)
        .then(r => r.json())
        .then(barangays => fillSelect(barangaySelect, barangays, 'Select barangay', keepBarangay));
}

provinceSelect.addEventListener('change', () => loadCities(null, null));
citySelect.addEventListener('change', () => loadBarangays(null));

fetch('/ph-address/provinces')
    .then(r => r.json())
    .then(provinces => {
        // Repopulate the whole chain after a validation bounce.
        fillSelect(provinceSelect, provinces, 'Select province', provinceSelect.dataset.old);
        if (provinceSelect.dataset.old) {
            loadCities(citySelect.dataset.old, barangaySelect.dataset.old);
        }
    });

// ── Ang tipo sa disability walay kahulogan kung "No" ang tubag, mao nga gitago
// ── siya hangtod nga "Yes". Gilimpyohan pud ang na-tsek: ang natago nga
// ── checkbox mopadala gihapon kung mabilin siya nga naka-tsek. ──
(function () {
    const wrap  = document.getElementById('walkinDisabilityTypes');
    const radio = document.querySelectorAll('.walkin-disability');
    if (!wrap || !radio.length) return;

    radio.forEach(function (input) {
        input.addEventListener('change', function () {
            const yes = this.value === 'yes' && this.checked;
            wrap.classList.toggle('d-none', !yes);
            if (!yes) {
                wrap.querySelectorAll('input[type=checkbox]').forEach(function (box) {
                    box.checked = false;
                });
            }
        });
    });
})();
</script>
@endpush

@endsection
