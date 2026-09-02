@extends('staff.layouts.app')

@section('content')

{{-- The two tabs are gone. This form is opened from the NSRP Registration
     list by its own button, so the way back is a link, not a second tab that
     is always on screen. --}}
<a href="{{ route('staff.registrations') }}" class="text-decoration-none d-inline-block mb-3"
   style="font-size:12px;color:var(--g-700);">
    <i class="ph ph-arrow-left me-1"></i> Back to NSRP Registration
</a>

<div class="mb-4">
    <h5 class="fw-bold mb-0" style="color:var(--g-700);">Walk-in NSRP Registration</h5>
    <div style="font-size:12px;color:var(--n-500);">Encode NSRP form for a walk-in jobseeker (no account)</div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">

        <form id="nsrpForm" action="{{ route('staff.nsrp.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 1: INTRO --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="1">
                <div style="text-align:center;padding-left:14px;padding-right:14px;margin-bottom:16px;">
                    <div style="font-size:10px;color:var(--n-500);">Republic of the Philippines</div>
                    <div style="font-size:10px;color:var(--n-500);">Department of Labor and Employment</div>
                    <h6 style="margin:6px 0 0;font-size:13px;line-height:1.4;">
                        <i class="ph ph-clipboard-text me-2" style="color:var(--g-600);"></i>
                        NATIONAL SKILLS REGISTRATION PROGRAM — JOBSEEKER REGISTRATION FORM
                    </h6>
                    <div style="font-size:10px;color:var(--n-500);margin-top:2px;">NSRP Form 1 — September 2020</div>
                </div>

                <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
                    style="background:var(--n-50); border:1px solid var(--n-200);">
                    <i class="ph-fill ph-info" style="color:var(--g-600); font-size:18px; margin-top:1px;"></i>
                    <div style="font-size:12px; color:var(--g-700); line-height:1.6;">
                        <strong>INSTRUCTIONS:</strong> Encode the information exactly as written on the walk-in jobseeker's
                        physical NSRP form. Check appropriate boxes. Indicate "NA" if not applicable.
                        All fields marked with <strong>*</strong> are required.
                    </div>
                </div>

                {{-- ══════════════════════════════════════════ --}}
                {{-- OCR AUTO-FILL — Upload Photo of the Physical NSRP Form --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="p-3 mb-4 rounded-3" style="background:var(--warn-bg); border:1px solid var(--warn-br);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ph-fill ph-camera" style="color:var(--warn); font-size:18px;"></i>
                        <div style="font-size:13px;font-weight:700;color:var(--warn);">
                            Optional: Auto-fill Text Fields from a Photo of the Physical Form
                        </div>
                    </div>
                    <div style="font-size:11.5px; color:var(--warn); line-height:1.6; margin-bottom:12px;">
                        <strong>⚠️ Important:</strong> Choose <strong>both sides</strong> of the form — front and back.
                        The order does not matter. Checkboxes are read reliably; handwritten names are often wrong,
                        so confirm them with the applicant. Fields the scanner was unsure about are highlighted.
                        <strong>Always review every field before submitting.</strong>
                        Lay the paper flat, keep all four corners in the photo. JPG or PNG, max 5MB each.
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <input type="file" id="ocrImageInput" accept=".jpg,.jpeg,.png" multiple
                            class="form-control peso-input" style="max-width:320px;">
                        <button type="button" id="ocrScanBtn" class="btn btn-sm fw-semibold"
                                style="background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-br);border-radius:8px;font-size:12px;padding:8px 16px;"
                                onclick="scanNsrpImage()">
                            <i class="ph ph-magnifying-glass me-1"></i> Scan & Auto-fill
                        </button>
                        <span id="ocrStatus" style="font-size:11.5px;color:var(--warn);"></span>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 2: I. PERSONAL INFORMATION + EMPLOYMENT STATUS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="2" style="display:none;">

                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-user" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">I. Personal Information</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="peso-label">Surname *</label>
                            <input type="text" name="surname" id="surname_input" class="form-control peso-input"
                                value="{{ old('surname') }}" required oninput="updateSignature()">
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">First Name *</label>
                            <input type="text" name="first_name" id="first_name_input" class="form-control peso-input"
                                value="{{ old('first_name') }}" required oninput="updateSignature()">
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name_input" class="form-control peso-input"
                                value="{{ old('middle_name') }}" oninput="updateSignature()">
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Suffix</label>
                            <input type="text" name="suffix" id="suffix_input" class="form-control peso-input"
                                value="{{ old('suffix') }}" placeholder="Jr., Sr., III" oninput="updateSignature()">
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Date of Birth *</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control peso-input"
                                value="{{ old('date_of_birth') }}"
                                max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                onchange="computeAge()" required>
                        </div>
                        <div class="col-md-2">
                            <label class="peso-label">Age *</label>
                            <input type="number" name="age" id="age_field" class="form-control peso-input"
                                value="{{ old('age') }}" required min="1" readonly
                                style="background:var(--n-50);cursor:not-allowed;">
                        </div>
                        <div class="col-md-2">
                            <label class="peso-label">Sex *</label>
                            <select name="sex" class="form-select peso-input" required>
                                <option value="">Select</option>
                                <option value="Male"   {{ old('sex') === 'Male'   ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="peso-label">Civil Status *</label>
                            <select name="civil_status" class="form-select peso-input" required>
                                <option value="">Select</option>
                                <option value="Single"  {{ old('civil_status') === 'Single'  ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ old('civil_status') === 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Widowed" {{ old('civil_status') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Religion</label>
                            <input type="text" name="religion" class="form-control peso-input" value="{{ old('religion') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">TIN</label>
                            <input type="text" name="tin" class="form-control peso-input" value="{{ old('tin') }}" placeholder="Optional">
                        </div>
                        <div class="col-md-2">
                            <label class="peso-label">Height (ft.)</label>
                            <input type="text" name="height" class="form-control peso-input" value="{{ old('height') }}" placeholder="e.g. 5.6">
                        </div>
                        <div class="col-md-2">
                            <label class="peso-label">Weight (kg.)</label>
                            <input type="text" name="weight" class="form-control peso-input" value="{{ old('weight') }}" placeholder="e.g. 60">
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Contact Number/s *</label>
                            <input type="text" name="contact_number" class="form-control peso-input"
                                inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                                placeholder="09171234567" value="{{ old('contact_number') }}" required>
                        </div>
                        {{-- Required: kini ang magsumpay niini nga rekord sa account
                             nga himoon sa maong tawo ugma. Kung wala, mapugos siyag
                             sulat pag-usab sa tibuok porma. --}}
                        <div class="col-md-2">
                            <label class="peso-label">Email *</label>
                            <input type="email" name="reg_email" class="form-control peso-input" value="{{ old('reg_email') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="peso-label">Disability (check if applicable)</label>
                            @php $disabilities = old('disabilities', []); @endphp
                            <div class="d-flex gap-3 flex-wrap mt-1">
                                @foreach(['Visual', 'Speech', 'Mental', 'Hearing', 'Physical', 'Others'] as $dis)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="disabilities[]" value="{{ $dis }}"
                                        id="dis_{{ Str::slug($dis) }}"
                                        {{ in_array($dis, $disabilities) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dis_{{ Str::slug($dis) }}"
                                        style="font-size:12px;color:var(--g-700);">{{ $dis }}</label>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <input type="text" name="disability_other" class="form-control peso-input"
                                    style="max-width:300px;" placeholder="If Others, please specify" value="{{ old('disability_other') }}">
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div style="font-size:12px;font-weight:700;color:var(--g-700);margin-bottom:8px;">
                                <i class="ph-fill ph-house me-1" style="color:var(--g-600);"></i> Permanent Address
                                <span style="font-size:11px;font-weight:400;color:var(--n-500);">(Fill this first if different from present address)</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Province</label>
                            <select name="perm_province" id="perm_province" class="form-select peso-input" onchange="loadCities('perm')">
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Municipality / City</label>
                            <select name="perm_municipality_city" id="perm_municipality_city" class="form-select peso-input" onchange="loadBarangays('perm')" disabled>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Barangay</label>
                            <select name="perm_barangay" id="perm_barangay" class="form-select peso-input" disabled>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">House No. / Street / Village</label>
                            <input type="text" name="perm_house_street" id="perm_house_street" class="form-control peso-input" value="{{ old('perm_house_street') }}">
                        </div>

                        <div class="col-12 mt-3">
                            <div style="font-size:12px;font-weight:700;color:var(--g-700);margin-bottom:8px;">
                                <i class="ph ph-map-pin me-1" style="color:var(--g-600);"></i> Present Address
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="same_as_permanent" id="same_as_permanent" value="1" onchange="toggleSameAsPermanent()">
                                <label class="form-check-label" for="same_as_permanent" style="font-size:12px;color:var(--g-700);">Same as Permanent Address</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Province *</label>
                            <select name="province" id="present_province" class="form-select peso-input" onchange="loadCities('present')" required>
                                <option value="">Select Province</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Municipality / City *</label>
                            <select name="municipality_city" id="present_municipality_city" class="form-select peso-input" onchange="loadBarangays('present')" disabled required>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Barangay *</label>
                            <select name="barangay" id="present_barangay" class="form-select peso-input" disabled required>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">House No. / Street / Village *</label>
                            <input type="text" name="house_street" id="present_house_street" class="form-control peso-input" value="{{ old('house_street') }}" required>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-briefcase" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">Employment Status / Type</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="peso-label">Employment Type *</label>
                            <select name="employment_type" id="employmentType" class="form-select peso-input" required onchange="toggleEmploymentFields()">
                                <option value="">Select</option>
                                <option value="employed"   {{ old('employment_type') === 'employed'   ? 'selected' : '' }}>Employed</option>
                                <option value="unemployed" {{ old('employment_type') === 'unemployed' ? 'selected' : '' }}>Unemployed</option>
                            </select>
                        </div>

                        <div class="col-12" id="employedFields" style="display:none;">
                            <label class="peso-label">Type of Employment</label>
                            <div class="d-flex gap-3 flex-wrap mt-1">
                                @foreach(['wage_employed' => 'Wage Employed', 'self_employed' => 'Self-Employed (please specify)'] as $val => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="employed_sub_type" value="{{ $val }}" id="emp_{{ $val }}" onchange="toggleSelfEmployed()">
                                    <label class="form-check-label" for="emp_{{ $val }}" style="font-size:12px;color:var(--g-700);">{{ $label }}</label>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-2" id="selfEmployedSpecify" style="display:none;">
                                <input type="text" name="self_employed_specify" class="form-control peso-input" style="max-width:400px;"
                                    placeholder="Fisherman/Fisherfolk, Vendor/Retailer, Home-based worker, etc." value="{{ old('self_employed_specify') }}">
                            </div>
                        </div>

                        <div class="col-12" id="unemployedFields" style="display:none;">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="peso-label">How long have you been looking for work? (months)</label>
                                    <input type="number" name="months_looking" class="form-control peso-input" value="{{ old('months_looking') }}" min="0">
                                </div>
                                <div class="col-12">
                                    <label class="peso-label">Reason for Unemployment</label>
                                    <div class="d-flex gap-3 flex-wrap mt-1">
                                        @foreach([
                                            'new_entrant'        => 'New Entrant/Fresh Graduate',
                                            'finished_contract'  => 'Finished Contract',
                                            'resigned'           => 'Resigned',
                                            'retired'            => 'Retired',
                                            'terminated_calamity'=> 'Terminated/Laid off due to calamity',
                                            'terminated_local'   => 'Terminated/Laid off (local)',
                                            'terminated_abroad'  => 'Terminated/Laid off (abroad)',
                                            'others'             => 'Others',
                                        ] as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="unemployed_reason" value="{{ $val }}" id="unemp_{{ $val }}" onchange="toggleUnemployedOther()">
                                            <label class="form-check-label" for="unemp_{{ $val }}" style="font-size:12px;color:var(--g-700);">{{ $label }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-4" id="terminatedAbroadCountry" style="display:none;">
                                    <label class="peso-label">Specify country (if terminated abroad)</label>
                                    <input type="text" name="terminated_abroad_country" class="form-control peso-input" value="{{ old('terminated_abroad_country') }}">
                                </div>
                                <div class="col-md-4" id="unemployedOtherField" style="display:none;">
                                    <label class="peso-label">Others, please specify</label>
                                    <input type="text" name="unemployed_other" class="form-control peso-input" value="{{ old('unemployed_other') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="peso-label">Are you an OFW?</label>
                            <select name="is_ofw" class="form-select peso-input" onchange="toggleOfwFields()">
                                <option value="0" selected>No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="ofwCountryField" style="display:none;">
                            <label class="peso-label">Specify country</label>
                            <input type="text" name="ofw_country" class="form-control peso-input" value="{{ old('ofw_country') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="peso-label">Are you a former OFW?</label>
                            <select name="is_former_ofw" class="form-select peso-input" onchange="toggleFormerOfwFields()">
                                <option value="0" selected>No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-12" id="formerOfwFields" style="display:none;">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="peso-label">Latest country of deployment</label>
                                    <input type="text" name="latest_deployment_country" class="form-control peso-input" value="{{ old('latest_deployment_country') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="peso-label">Month and year of return</label>
                                    <input type="text" name="return_month" class="form-control peso-input" placeholder="e.g. 01/2024" value="{{ old('return_month') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="peso-label">4Ps Beneficiary?</label>
                            <select name="is_4ps" class="form-select peso-input">
                                <option value="0" selected>No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="peso-label">Household ID (if 4Ps)</label>
                            <input type="text" name="household_id" class="form-control peso-input" value="{{ old('household_id') }}">
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 3: II. JOB PREFERENCE --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="3" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-magnifying-glass" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">II. Job Preference</h6>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="peso-label">Work Type *</label>
                        <select name="work_type" class="form-select peso-input" required>
                            <option value="">Select</option>
                            <option value="part_time" {{ old('work_type') === 'part_time' ? 'selected' : '' }}>Part-time</option>
                            <option value="full_time" {{ old('work_type') === 'full_time' ? 'selected' : '' }}>Full-time</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="peso-label">Preferred Occupation *</label>
                        <div class="row g-2 mt-1">
                            @for($i = 0; $i < 3; $i++)
                            <div class="col-md-4">
                                <input type="text" name="preferred_occupations[]" class="form-control peso-input"
                                    placeholder="{{ $i + 1 }}." value="{{ old('preferred_occupations.'.$i) }}" {{ $i === 0 ? 'required' : '' }}>
                            </div>
                            @endfor
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="peso-label">Preferred Work Location — Local (specify cities/municipalities)</label>
                        <div class="row g-2 mt-1">
                            @for($i = 0; $i < 3; $i++)
                            <div class="col-md-4">
                                <input type="text" name="local_locations[]" class="form-control peso-input" placeholder="{{ $i + 1 }}." value="{{ old('local_locations.'.$i) }}">
                            </div>
                            @endfor
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="peso-label">Preferred Work Location — Overseas (specify countries)</label>
                        <div class="row g-2 mt-1">
                            @for($i = 0; $i < 3; $i++)
                            <div class="col-md-4">
                                <input type="text" name="overseas_locations[]" class="form-control peso-input" placeholder="{{ $i + 1 }}." value="{{ old('overseas_locations.'.$i) }}">
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 4: III. LANGUAGE / DIALECT PROFICIENCY --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="4" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-translate" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">III. Language / Dialect Proficiency</h6>
                </div>
                @php $languages = ['English', 'Filipino', 'Mandarin']; @endphp
                <div class="table-responsive">
                    <table class="table" style="font-size:12px;min-width:600px;" id="languageTable">
                        <thead>
                            <tr style="background:var(--n-50);">
                                <th style="color:var(--g-700);padding:10px 12px;">Language/Dialect</th>
                                <th style="color:var(--g-700);padding:10px 12px;text-align:center;">Read</th>
                                <th style="color:var(--g-700);padding:10px 12px;text-align:center;">Write</th>
                                <th style="color:var(--g-700);padding:10px 12px;text-align:center;">Speak</th>
                                <th style="color:var(--g-700);padding:10px 12px;text-align:center;">Understand</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="languageTableBody">
                            @foreach($languages as $lang)
                            <tr>
                                <td style="padding:8px 12px;font-weight:600;color:var(--g-700);">{{ $lang }}</td>
                                @foreach(['read','write','speak','understand'] as $skill)
                                <td style="padding:8px 12px;text-align:center;">
                                    <input class="form-check-input" type="checkbox" name="language_proficiency[{{ $lang }}][{{ $skill }}]" value="1">
                                </td>
                                @endforeach
                                <td></td>
                            </tr>
                            @endforeach
                            <tr class="other-lang-row">
                                <td style="padding:8px 12px;">
                                    <input type="text" name="other_language[]" class="form-control peso-input" style="font-size:12px;" placeholder="Others:">
                                </td>
                                @foreach(['read','write','speak','understand'] as $skill)
                                <td style="padding:8px 12px;text-align:center;">
                                    <input class="form-check-input" type="checkbox" name="language_proficiency[other][0][{{ $skill }}]" value="1">
                                </td>
                                @endforeach
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm fw-semibold"
                    style="border:1px dashed var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;"
                    onclick="addLanguageRow()">
                    <i class="ph ph-plus-circle me-1"></i> Add Other Language
                </button>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 5: IV. EDUCATIONAL BACKGROUND --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="5" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-graduation-cap" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">IV. Educational Background</h6>
                </div>
                @php
                    $eduLevels = ['Elementary','Junior High School','Senior High School','Tertiary / College','Graduate Studies/Post-graduate/Masters'];
                    $shs_strands = [
                        'ABM - Accountancy, Business and Management','HUMSS - Humanities and Social Sciences',
                        'STEM - Science, Technology, Engineering and Mathematics','GAS - General Academic Strand',
                        'TVL - Technical-Vocational-Livelihood','Sports Track','Arts and Design Track',
                    ];
                    $college_courses = [
                        'BS Accountancy','BS Architecture','BS Biology','BS Business Administration',
                        'BS Chemical Engineering','BS Chemistry','BS Civil Engineering',
                        'BS Computer Engineering','BS Computer Science','BS Criminology',
                        'BS Education','BS Electrical Engineering','BS Electronics Engineering',
                        'BS Environmental Science','BS Food Technology','BS Forestry',
                        'BS Geodetic Engineering','BS Information Technology','BS Interior Design',
                        'BS Marine Engineering','BS Marine Transportation','BS Mathematics',
                        'BS Mechanical Engineering','BS Medical Laboratory Science','BS Midwifery',
                        'BS Mining Engineering','BS Nursing','BS Nutrition and Dietetics',
                        'BS Pharmacy','BS Physical Therapy','BS Psychology',
                        'BS Radiologic Technology','BS Social Work','BS Statistics',
                        'BS Tourism Management','AB Communication','AB Economics',
                        'AB English','AB Filipino','AB History','AB Mass Communication',
                        'AB Philosophy','AB Political Science','AB Sociology',
                        'Doctor of Medicine','Doctor of Dental Medicine','Juris Doctor',
                        'Others (Please Specify)',
                    ];
                    $elem_year_grad    = array_merge(['Graduated'], array_map(fn($g) => 'Grade ' . $g, range(1, 6)));
                    $jhs_year_grad     = ['Completer / Grade 10','1st Year High School','2nd Year High School','3rd Year High School','4th Year High School'];
                    $shs_year_grad     = ['SHS Graduated','Grade 11','Grade 12'];
                    $college_year_grad = ['Fresh Graduated','1st Year','2nd Year','3rd Year','4th Year','5th Year'];
                    $grad_year_grad    = ['Graduated','1st Year','2nd Year'];
                @endphp

                <div class="mb-3">
                    <label class="peso-label">Currently in school?</label>
                    <div class="d-flex gap-3 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="currently_in_school" value="1" id="inschool_yes">
                            <label class="form-check-label" for="inschool_yes" style="font-size:12px;color:var(--g-700);">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="currently_in_school" value="0" id="inschool_no" checked>
                            <label class="form-check-label" for="inschool_no" style="font-size:12px;color:var(--g-700);">No</label>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="min-width:0;">
                    <table class="table" style="font-size:12px;min-width:800px;">
                        <thead>
                            <tr style="background:var(--n-50);">
                                <th style="color:var(--g-700);padding:10px 12px;">Level</th>
                                <th style="color:var(--g-700);padding:10px 12px;">School Name</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Course / Strand</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Year Graduated / Level Reached</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Level Reached<br><span style="font-weight:400;">(if undergrad)</span></th>
                                <th style="color:var(--g-700);padding:10px 12px;">Year Last Attended<br><span style="font-weight:400;">(if undergrad)</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eduLevels as $level)
                            @php
                                $isElem    = $level === 'Elementary';
                                $isJHS     = $level === 'Junior High School';
                                $isSHS     = $level === 'Senior High School';
                                $isCollege = $level === 'Tertiary / College';
                                if ($isElem)        $yearGradOptions = $elem_year_grad;
                                elseif ($isJHS)     $yearGradOptions = $jhs_year_grad;
                                elseif ($isSHS)     $yearGradOptions = $shs_year_grad;
                                elseif ($isCollege) $yearGradOptions = $college_year_grad;
                                else                $yearGradOptions = $grad_year_grad;
                            @endphp
                            <tr>
                                <td style="padding:8px 12px;font-weight:600;color:var(--g-700);white-space:nowrap;">{{ $level }}</td>
                                <td style="padding:6px 8px;">
                                    <input type="text" name="education[{{ $level }}][school_name]" class="form-control peso-input" style="font-size:12px;">
                                </td>
                                <td style="padding:6px 8px;">
                                    @if($isElem || $isJHS)
                                        <input type="hidden" name="education[{{ $level }}][course]" value="N/A">
                                        <input type="text" class="form-control peso-input" style="font-size:12px;background:var(--n-50);color:var(--n-400);cursor:not-allowed;" value="N/A" disabled>
                                    @elseif($isSHS)
                                        <select name="education[{{ $level }}][course]" class="form-select peso-input" style="font-size:12px;">
                                            <option value="">Select Strand</option>
                                            @foreach($shs_strands as $strand)
                                                <option value="{{ $strand }}">{{ $strand }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($isCollege)
                                        <select name="education[{{ $level }}][course]" id="college_course_select" class="form-select peso-input" style="font-size:12px;" onchange="toggleOtherCourse(this)">
                                            <option value="">Select Course</option>
                                            @foreach($college_courses as $course)
                                                <option value="{{ $course }}">{{ $course }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" id="college_course_other" name="education[{{ $level }}][course_other]"
                                            class="form-control peso-input mt-1" style="font-size:12px;display:none;" placeholder="Please specify course">
                                    @else
                                        <input type="text" name="education[{{ $level }}][course]" class="form-control peso-input" style="font-size:12px;" placeholder="e.g. Master of Business Administration">
                                    @endif
                                </td>
                                <td style="padding:6px 8px;">
                                    <select name="education[{{ $level }}][year_graduated]" class="form-select peso-input" style="font-size:12px;">
                                        <option value="">Select</option>
                                        @foreach($yearGradOptions as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding:6px 8px;">
                                    <input type="text" name="education[{{ $level }}][level_reached]" class="form-control peso-input" style="font-size:12px;">
                                </td>
                                <td style="padding:6px 8px;">
                                    <input type="text" name="education[{{ $level }}][year_last_attended]" class="form-control peso-input" style="font-size:12px;" placeholder="yyyy">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 6: V. TECHNICAL/VOCATIONAL TRAINING --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="6" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-notebook" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">V. Technical/Vocational and Other Training</h6>
                </div>
                <div class="table-responsive">
                    <table class="table" style="font-size:12px;min-width:700px;" id="trainingTable">
                        <thead>
                            <tr style="background:var(--n-50);">
                                <th style="color:var(--g-700);padding:10px 12px;">#</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Training/Vocational Course</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Hours of Training</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Duration (mm/yyyy to mm/yyyy)</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Training Institution</th>
                                <th style="color:var(--g-700);padding:10px 12px;">Certificate Upload</th>
                            </tr>
                        </thead>
                        <tbody id="trainingTableBody">
                            @for($i = 0; $i < 3; $i++)
                            <tr>
                                <td style="padding:8px 12px;color:var(--n-500);">{{ $i + 1 }}</td>
                                <td style="padding:6px 8px;">
                                    <input type="text" name="trainings[{{ $i }}][course]" class="form-control peso-input" style="font-size:12px;">
                                </td>
                                <td style="padding:6px 8px;">
                                    <input type="text" name="trainings[{{ $i }}][hours]" class="form-control peso-input" style="font-size:12px;" placeholder="e.g. 40">
                                </td>
                                <td style="padding:6px 8px;">
                                    <input type="text" name="trainings[{{ $i }}][duration]" class="form-control peso-input" style="font-size:12px;" placeholder="01/2023 to 03/2023">
                                </td>
                                <td style="padding:6px 8px;">
                                    <input type="text" name="trainings[{{ $i }}][institution]" class="form-control peso-input" style="font-size:12px;">
                                </td>
                                <td style="padding:6px 8px;">
                                    <input type="file" name="training_certificates[{{ $i }}]" class="form-control peso-input" style="font-size:11px;" accept=".jpg,.jpeg,.png,.pdf">
                                    <div style="font-size:10px;color:var(--n-500);margin-top:4px;">JPG, PNG, PDF only</div>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm fw-semibold"
                    style="border:1px dashed var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;"
                    onclick="addTrainingRow()">
                    <i class="ph ph-plus-circle me-1"></i> Add Training
                </button>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 7: VI. ELIGIBILITY / PROFESSIONAL LICENSE --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="7" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-medal" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">VI. Eligibility / Professional License</h6>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div style="font-size:12px;font-weight:700;color:var(--g-700);margin-bottom:8px;">Eligibility (Civil Service)</div>
                        @for($i = 0; $i < 2; $i++)
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm-7">
                                <input type="text" name="eligibilities[{{ $i }}][name]" class="form-control peso-input" style="font-size:12px;" placeholder="{{ $i + 1 }}. Eligibility">
                            </div>
                            <div class="col-12 col-sm-5">
                                <input type="date" name="eligibilities[{{ $i }}][date_taken]" class="form-control peso-input" style="font-size:12px;">
                            </div>
                        </div>
                        @endfor
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:12px;font-weight:700;color:var(--g-700);margin-bottom:8px;">Professional License (PRC)</div>
                        @for($i = 0; $i < 2; $i++)
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm-7">
                                <input type="text" name="licenses[{{ $i }}][name]" class="form-control peso-input" style="font-size:12px;" placeholder="{{ $i + 1 }}. License">
                            </div>
                            <div class="col-12 col-sm-5">
                                <input type="date" name="licenses[{{ $i }}][valid_until]" class="form-control peso-input" style="font-size:12px;">
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 8: VII. WORK EXPERIENCE --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="8" style="display:none;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                    <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="ph ph-buildings" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">VII. Work Experience</h6>
                </div>
                <p style="font-size:11px;color:var(--n-500);">Limit to 10 year period, start with the most recent employment.</p>

                <div id="workExpContainer">
                    <div class="work-exp-row p-3 mb-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-0);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div style="font-size:12px;font-weight:700;color:var(--g-700);">
                                Work Experience #<span class="exp-num">1</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" style="border-radius:8px;font-size:11px;" disabled>
                                <i class="ph-fill ph-trash"></i> Remove
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="peso-label">Company Name</label>
                                <input type="text" name="work_experiences[0][company_name]" class="form-control peso-input" placeholder="e.g. ABC Company">
                            </div>
                            <div class="col-md-4">
                                <label class="peso-label">Address (City/Municipality)</label>
                                <input type="text" name="work_experiences[0][industry]" class="form-control peso-input" placeholder="e.g. Cagayan de Oro City">
                            </div>
                            <div class="col-md-4">
                                <label class="peso-label">Position</label>
                                <input type="text" name="work_experiences[0][position]" class="form-control peso-input" placeholder="e.g. Sales Associate">
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Date From (mm/yyyy)</label>
                                <input type="text" name="work_experiences[0][date_from]" class="form-control peso-input" placeholder="e.g. 01/2020">
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Date To (mm/yyyy)</label>
                                <input type="text" name="work_experiences[0][date_to]" class="form-control peso-input" id="dateTo_0" placeholder="e.g. 12/2022">
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Status</label>
                                <select name="work_experiences[0][employment_status]" class="form-select peso-input">
                                    <option value="">Select</option>
                                    @foreach(['Permanent','Contractual','Part-time','Probationary'] as $es)
                                    <option value="{{ $es }}">{{ $es }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input current-job-check" type="checkbox" name="work_experiences[0][is_current]" value="1" id="current_0" onchange="toggleCurrentJob(this, 0)">
                                    <label class="form-check-label" for="current_0" style="font-size:12px;color:var(--g-700);">Currently working here</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-2">
                    <button type="button" class="btn btn-sm fw-semibold"
                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;"
                        onclick="addWorkExp()">
                        <i class="ph ph-plus-circle me-1"></i> Add Work Experience
                    </button>
                    <span style="font-size:11px;color:var(--n-500);">Leave blank if no work experience</span>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 9: VIII. OTHER SKILLS + CERTIFICATION + SUBMIT --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="nsrp-step" data-step="9" style="display:none;">

                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-wrench" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">VIII. Other Skills Acquired Without Certificate</h6>
                    </div>
                    @php
                        $allSkills = ['Auto Mechanic','Beautician','Carpentry Work','Computer Literate','Domestic Chores','Driver','Electrician','Embroidery','Gardening','Masonry','Painter/Artist','Painting Jobs','Photography','Plumbing','Sewing Dresses','Stenography','Tailoring'];
                    @endphp
                    <div class="row g-2">
                        @foreach($allSkills as $skill)
                        <div class="col-md-3 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="other_skills[]" value="{{ $skill }}" id="skill_{{ Str::slug($skill) }}">
                                <label class="form-check-label" for="skill_{{ Str::slug($skill) }}" style="font-size:12px;color:var(--g-700);">{{ $skill }}</label>
                            </div>
                        </div>
                        @endforeach
                        <div class="col-md-6 mt-2">
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size:12px;color:var(--g-700);font-weight:600;">Others:</span>
                                <input type="text" name="other_skills_specify" class="form-control peso-input" style="font-size:12px;" placeholder="Please specify">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-3 mb-4" style="background:var(--n-50); border:1px solid var(--n-200);">
                    <div style="font-size:12px;color:var(--g-700);line-height:1.6;">
                        <strong>Certification/Authorization:</strong> This is to certify that all data/information
                        provided in this form are true to the best of the applicant's knowledge, as encoded from
                        their submitted walk-in NSRP form. This authorizes DOLE to include the profile in the PESO
                        Employment Information System.
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="peso-label">Signature of Applicant (as written)</label>
                            <div id="signature_display" class="form-control peso-input"
                                style="background:#fff;font-style:italic;color:var(--g-700);font-weight:600;">&nbsp;</div>
                        </div>
                        <div class="col-md-4">
                            <label class="peso-label">Date Signed *</label>
                            <input type="date" name="certification_date" class="form-control peso-input" required>
                        </div>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="certification_agreed" value="1" id="certAgree" required>
                        <label class="form-check-label" for="certAgree" style="font-size:12px;color:var(--g-700);font-weight:600;">
                            Applicant agreed to the certification and authorization (as marked on the physical form) *
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-peso px-4" onclick="debugSubmit(event, this)">
                        <i class="ph ph-paper-plane-tilt me-1"></i> Save Walk-in NSRP Registration
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
    <button type="button" id="nsrpStepPrev" class="btn btn-sm"
        style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;">
        <i class="ph ph-caret-left"></i>
    </button>
    <span id="nsrpStepInfo" style="font-size:13px;color:var(--g-700);font-weight:600;white-space:nowrap;">Step 1 of 9</span>
    <button type="button" id="nsrpStepNext" class="btn btn-sm fw-semibold"
        style="border:none;border-radius:8px;color:#fff;padding:6px 14px;background:var(--g-600);">
        Next <i class="ph ph-caret-right ms-1"></i>
    </button>
</div>

@push('scripts')
<script>
let workExpCount = 1;
let trainingCount = 3;
let otherLangCount = 1;

const nsrpTotalSteps = 9;
let nsrpCurrentStep = 1;

function showNsrpStep(step) {
    document.querySelectorAll('.nsrp-step').forEach(el => {
        el.style.display = (parseInt(el.dataset.step) === step) ? 'block' : 'none';
    });
    document.getElementById('nsrpStepInfo').textContent = `Step ${step} of ${nsrpTotalSteps}`;
    document.getElementById('nsrpStepPrev').disabled = step <= 1;
    document.getElementById('nsrpStepNext').style.display = step >= nsrpTotalSteps ? 'none' : 'inline-flex';
}

function validateCurrentStep() {
    const currentStepEl = document.querySelector(`.nsrp-step[data-step="${nsrpCurrentStep}"]`);
    const requiredFields = currentStepEl.querySelectorAll('[required]');
    for (const field of requiredFields) {
        if (!field.checkValidity()) {
            field.reportValidity();
            return false;
        }
    }
    return true;
}

document.getElementById('nsrpStepPrev').addEventListener('click', () => {
    if (nsrpCurrentStep > 1) {
        nsrpCurrentStep--;
        showNsrpStep(nsrpCurrentStep);
    }
});

document.getElementById('nsrpStepNext').addEventListener('click', () => {
    if (!validateCurrentStep()) return;
    if (nsrpCurrentStep < nsrpTotalSteps) {
        nsrpCurrentStep++;
        showNsrpStep(nsrpCurrentStep);
    }
});

showNsrpStep(nsrpCurrentStep);

function updateSignature() {
    const surname = document.getElementById('surname_input').value.trim();
    const first    = document.getElementById('first_name_input').value.trim();
    const middle   = document.getElementById('middle_name_input').value.trim();
    const suffix   = document.getElementById('suffix_input').value.trim();
    let fullName = [surname ? surname + ',' : '', first, middle, suffix].filter(Boolean).join(' ').trim();
    document.getElementById('signature_display').textContent = fullName || '\u00A0';
}

function addLanguageRow() {
    const tbody = document.getElementById('languageTableBody');
    const idx = otherLangCount;
    const html = `
    <tr class="other-lang-row">
        <td style="padding:8px 12px;">
            <input type="text" name="other_language[]" class="form-control peso-input" style="font-size:12px;" placeholder="Others:">
        </td>
        <td style="padding:8px 12px;text-align:center;"><input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][read]" value="1"></td>
        <td style="padding:8px 12px;text-align:center;"><input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][write]" value="1"></td>
        <td style="padding:8px 12px;text-align:center;"><input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][speak]" value="1"></td>
        <td style="padding:8px 12px;text-align:center;"><input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][understand]" value="1"></td>
        <td style="padding:8px 12px;">
            <button type="button" class="btn btn-sm text-danger" onclick="this.closest('tr').remove()"><i class="ph ph-trash"></i></button>
        </td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', html);
    otherLangCount++;
}

function addTrainingRow() {
    const tbody = document.getElementById('trainingTableBody');
    const idx = trainingCount;
    const html = `
    <tr>
        <td style="padding:8px 12px;color:var(--n-500);">${idx + 1}</td>
        <td style="padding:6px 8px;"><input type="text" name="trainings[${idx}][course]" class="form-control peso-input" style="font-size:12px;"></td>
        <td style="padding:6px 8px;"><input type="text" name="trainings[${idx}][hours]" class="form-control peso-input" style="font-size:12px;" placeholder="e.g. 40"></td>
        <td style="padding:6px 8px;"><input type="text" name="trainings[${idx}][duration]" class="form-control peso-input" style="font-size:12px;" placeholder="01/2023 to 03/2023"></td>
        <td style="padding:6px 8px;"><input type="text" name="trainings[${idx}][institution]" class="form-control peso-input" style="font-size:12px;"></td>
        <td style="padding:6px 8px;">
            <input type="file" name="training_certificates[${idx}]" class="form-control peso-input" style="font-size:11px;" accept=".jpg,.jpeg,.png,.pdf">
            <div style="font-size:10px;color:var(--n-500);margin-top:4px;">JPG, PNG, PDF only</div>
        </td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', html);
    trainingCount++;
}

function scanNsrpImage() {
    const fileInput = document.getElementById('ocrImageInput');
    const statusEl  = document.getElementById('ocrStatus');

    if (!fileInput.files || fileInput.files.length === 0) {
        statusEl.textContent = 'Please choose an image first.';
        statusEl.style.color = 'var(--danger)';
        return;
    }

    // Roughly a minute per side on the office machine, so the old "a few
    // seconds" read as a hang. Counting up is the honest version: the staff can
    // see it is still working instead of wondering whether to click again.
    const pageCount = fileInput.files.length;
    const started = Date.now();

    statusEl.style.color = 'var(--warn)';
    statusEl.textContent = `Scanning ${pageCount} page(s)... about ${pageCount} minute(s). Please wait.`;

    const ticker = setInterval(() => {
        const elapsed = Math.round((Date.now() - started) / 1000);
        statusEl.textContent =
            `Scanning ${pageCount} page(s)... ${elapsed}s elapsed. ` +
            `This takes about a minute per page — please do not close the page.`;
    }, 1000);

    const formData = new FormData();
    for (const file of fileInput.files) {
        formData.append('nsrp_images[]', file);
    }

    const button = document.getElementById('ocrScanBtn');
    button.disabled = true;

    fetch('{{ route('staff.nsrp.scan') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            statusEl.textContent = data.error;
            statusEl.style.color = 'var(--danger)';
            return;
        }

        const filled = applyScanResult(data.data || {}, data.confidence || {});

        updateSignature();
        if (typeof toggleEmploymentFields === 'function') toggleEmploymentFields();
        if (typeof toggleSelfEmployed === 'function') toggleSelfEmployed();
        if (typeof toggleUnemployedOther === 'function') toggleUnemployedOther();

        if (filled > 0) {
            const pages = (data.pages || []).join(' and ');
            statusEl.textContent =
                `Filled ${filled} field(s) from page ${pages} in ${data.seconds}s. ` +
                `Highlighted fields were uncertain — confirm them with the applicant.`;
            statusEl.style.color = 'var(--g-700)';
        } else {
            statusEl.textContent = 'Nothing could be read. Please fill out the form manually.';
            statusEl.style.color = 'var(--danger)';
        }
    })
    .catch(() => {
        statusEl.textContent = 'Scan failed. Please fill out the form manually.';
        statusEl.style.color = 'var(--danger)';
    })
    .finally(() => {
        clearInterval(ticker);
        button.disabled = false;
    });
}

// Anything the scanner read below this is shown highlighted. It is not an error
// bar — a low score means "look at this one", and the applicant is standing
// right there to be asked.
const OCR_UNSURE_BELOW = 0.6;

function applyScanResult(values, confidence) {
    let filled = 0;

    for (const [name, value] of Object.entries(values)) {
        if (value === null || value === '' || (Array.isArray(value) && !value.length)) continue;

        // language_proficiency arrives as {English: ['read','write']}, which is
        // the shape the form stores rather than a single input to write into.
        if (name === 'language_proficiency' && typeof value === 'object') {
            for (const [language, skills] of Object.entries(value)) {
                for (const skill of skills) {
                    const cell = document.querySelector(
                        `input[name="language_proficiency[${language}][]"][value="${skill}"]`);
                    if (cell) { cell.checked = true; filled++; }
                }
            }
            continue;
        }

        if (Array.isArray(value)) {
            for (const item of value) {
                const box = document.querySelector(`input[name="${name}[]"][value="${item}"]`);
                if (box) { box.checked = true; filled++; }
            }
            continue;
        }

        if (setField(name, value, confidence[name])) filled++;
    }

    return filled;
}

function setField(name, value, score) {
    const nodes = document.querySelectorAll(`[name="${name}"]`);
    if (!nodes.length) return false;

    const first = nodes[0];

    if (first.type === 'radio') {
        for (const node of nodes) {
            if (node.value === String(value)) { node.checked = true; markUncertain(node, score); return true; }
        }
        return false;
    }

    if (first.type === 'checkbox') {
        first.checked = value === true || value === 'true' || value === 1;
        return true;
    }

    if (first.tagName === 'SELECT') {
        const wanted = String(value).toLowerCase();
        for (const option of first.options) {
            if (option.value.toLowerCase() === wanted) {
                first.value = option.value;
                first.dispatchEvent(new Event('change'));
                markUncertain(first, score);
                return true;
            }
        }
        return false;
    }

    first.value = value;
    markUncertain(first, score);
    return true;
}

function markUncertain(node, score) {
    const target = node.type === 'radio' ? node.closest('.form-check') : node;
    if (!target) return;

    if (score !== undefined && score < OCR_UNSURE_BELOW) {
        target.style.background = 'var(--warn-bg, #fff8e1)';
        target.style.borderColor = 'var(--warn-br, #ffc107)';
        target.title = `Scanner was unsure (${score}). Please confirm.`;
    }
}

function toggleEmploymentFields() {
    const val = document.getElementById('employmentType').value;
    document.getElementById('employedFields').style.display   = val === 'employed'   ? 'block' : 'none';
    document.getElementById('unemployedFields').style.display = val === 'unemployed' ? 'block' : 'none';
}

function toggleSelfEmployed() {
    const checked = document.querySelector('input[name="employed_sub_type"]:checked');
    document.getElementById('selfEmployedSpecify').style.display = checked && checked.value === 'self_employed' ? 'block' : 'none';
}

function toggleUnemployedOther() {
    const checked = document.querySelector('input[name="unemployed_reason"]:checked');
    document.getElementById('terminatedAbroadCountry').style.display = checked && checked.value === 'terminated_abroad' ? 'block' : 'none';
    document.getElementById('unemployedOtherField').style.display = checked && checked.value === 'others' ? 'block' : 'none';
}

function toggleOfwFields() {
    const val = document.querySelector('select[name="is_ofw"]').value;
    document.getElementById('ofwCountryField').style.display = val === '1' ? 'block' : 'none';
}

function toggleFormerOfwFields() {
    const val = document.querySelector('select[name="is_former_ofw"]').value;
    document.getElementById('formerOfwFields').style.display = val === '1' ? 'block' : 'none';
}

function addWorkExp() {
    const container = document.getElementById('workExpContainer');
    const idx = workExpCount;
    const html = `
    <div class="work-exp-row p-3 mb-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-0);">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div style="font-size:12px;font-weight:700;color:var(--g-700);">Work Experience #<span class="exp-num">${idx + 1}</span></div>
            <button type="button" class="btn btn-sm btn-danger" style="border-radius:8px;font-size:11px;" onclick="removeWorkExp(this)">
                <i class="ph-fill ph-trash"></i> Remove
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-4"><label class="peso-label">Company Name</label><input type="text" name="work_experiences[${idx}][company_name]" class="form-control peso-input" placeholder="e.g. ABC Company"></div>
            <div class="col-md-4"><label class="peso-label">Address (City/Municipality)</label><input type="text" name="work_experiences[${idx}][industry]" class="form-control peso-input" placeholder="e.g. Cagayan de Oro City"></div>
            <div class="col-md-4"><label class="peso-label">Position</label><input type="text" name="work_experiences[${idx}][position]" class="form-control peso-input" placeholder="e.g. Sales Associate"></div>
            <div class="col-md-3"><label class="peso-label">Date From (mm/yyyy)</label><input type="text" name="work_experiences[${idx}][date_from]" class="form-control peso-input" placeholder="e.g. 01/2020"></div>
            <div class="col-md-3"><label class="peso-label">Date To (mm/yyyy)</label><input type="text" name="work_experiences[${idx}][date_to]" class="form-control peso-input" id="dateTo_${idx}" placeholder="e.g. 12/2022"></div>
            <div class="col-md-3">
                <label class="peso-label">Status</label>
                <select name="work_experiences[${idx}][employment_status]" class="form-select peso-input">
                    <option value="">Select</option>
                    <option value="Permanent">Permanent</option>
                    <option value="Contractual">Contractual</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Probationary">Probationary</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input current-job-check" type="checkbox" name="work_experiences[${idx}][is_current]" value="1" id="current_${idx}" onchange="toggleCurrentJob(this, ${idx})">
                    <label class="form-check-label" for="current_${idx}" style="font-size:12px;color:var(--g-700);">Currently working here</label>
                </div>
            </div>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    workExpCount++;
    updateRemoveButtons();
}

function removeWorkExp(btn) {
    btn.closest('.work-exp-row').remove();
    updateExpNumbers();
    updateRemoveButtons();
}

function updateExpNumbers() {
    document.querySelectorAll('.exp-num').forEach((el, i) => { el.textContent = i + 1; });
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.work-exp-row');
    rows.forEach(row => { row.querySelector('button').disabled = rows.length === 1; });
}

function toggleCurrentJob(checkbox, idx) {
    const dateToField = document.getElementById('dateTo_' + idx);
    if (dateToField) {
        if (checkbox.checked) { dateToField.value = 'present'; dateToField.disabled = true; }
        else { dateToField.value = ''; dateToField.disabled = false; }
    }
}

function toggleOtherCourse(sel) {
    const otherInput = document.getElementById('college_course_other');
    if (!otherInput) return;
    otherInput.style.display = sel.value === 'Others (Please Specify)' ? 'block' : 'none';
    if (sel.value !== 'Others (Please Specify)') otherInput.value = '';
}

function computeAge() {
    const dob = document.getElementById('date_of_birth').value;
    if (!dob) return;
    const today = new Date();
    const birth = new Date(dob);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    document.getElementById('age_field').value = age;
}

const PSGC_URL = 'https://psgc.gitlab.io/api';

async function loadProvinces() {
    try {
        const res = await fetch(`${PSGC_URL}/provinces.json`);
        const data = await res.json();
        const sorted = data.sort((a,b) => a.name.localeCompare(b.name));
        const permSel = document.getElementById('perm_province');
        const presSel = document.getElementById('present_province');
        sorted.forEach(p => {
            permSel.add(new Option(p.name, p.code));
            presSel.add(new Option(p.name, p.code));
        });
    } catch(e) { console.error('Failed to load provinces', e); }
}

async function loadCities(type) {
    const provSel  = document.getElementById(type === 'perm' ? 'perm_province' : 'present_province');
    const citySel  = document.getElementById(type === 'perm' ? 'perm_municipality_city' : 'present_municipality_city');
    const brgySel  = document.getElementById(type === 'perm' ? 'perm_barangay' : 'present_barangay');
    const provCode = provSel.value;

    citySel.innerHTML = '<option value="">Select City/Municipality</option>';
    brgySel.innerHTML = '<option value="">Select Barangay</option>';
    citySel.disabled  = true;
    brgySel.disabled  = true;

    if (!provCode) return;

    try {
        const res  = await fetch(`${PSGC_URL}/provinces/${provCode}/cities-municipalities.json`);
        const data = await res.json();
        const sorted = data.sort((a,b) => a.name.localeCompare(b.name));
        sorted.forEach(c => citySel.add(new Option(c.name, c.code)));
        citySel.disabled = false;
    } catch(e) { console.error('Failed to load cities', e); }
}

async function loadBarangays(type) {
    const citySel  = document.getElementById(type === 'perm' ? 'perm_municipality_city' : 'present_municipality_city');
    const brgySel  = document.getElementById(type === 'perm' ? 'perm_barangay' : 'present_barangay');
    const cityCode = citySel.value;

    brgySel.innerHTML = '<option value="">Select Barangay</option>';
    brgySel.disabled  = true;

    if (!cityCode) return;

    try {
        const res  = await fetch(`${PSGC_URL}/cities-municipalities/${cityCode}/barangays.json`);
        const data = await res.json();
        const sorted = data.sort((a,b) => a.name.localeCompare(b.name));
        sorted.forEach(b => brgySel.add(new Option(b.name, b.name)));
        brgySel.disabled = false;
    } catch(e) { console.error('Failed to load barangays', e); }
}

function toggleSameAsPermanent() {
    const checked     = document.getElementById('same_as_permanent').checked;
    const presProvSel = document.getElementById('present_province');
    const presCitySel = document.getElementById('present_municipality_city');
    const presBrgySel = document.getElementById('present_barangay');
    const presHouse   = document.getElementById('present_house_street');
    const permProvSel = document.getElementById('perm_province');
    const permCitySel = document.getElementById('perm_municipality_city');
    const permBrgySel = document.getElementById('perm_barangay');
    const permHouse   = document.getElementById('perm_house_street');

    ['_province','_municipality_city','_barangay','_house_street'].forEach(suffix => {
        const old = document.getElementById('hidden_present' + suffix);
        if (old) old.remove();
    });

    if (checked) {
        const permProvText = permProvSel.options[permProvSel.selectedIndex]?.text;
        const permCityText = permCitySel.options[permCitySel.selectedIndex]?.text;
        const permBrgyText = permBrgySel.options[permBrgySel.selectedIndex]?.text;
        const permHouseVal = permHouse.value;

        const permProvOpt = [...presProvSel.options].find(o => o.text === permProvText);
        if (permProvOpt) {
            presProvSel.value = permProvOpt.value;
            loadCities('present');
        }
        presHouse.value = permHouseVal;

        presProvSel.disabled = true;
        presCitySel.disabled = true;
        presBrgySel.disabled = true;
        presHouse.readOnly   = true;

        const form = document.querySelector('form');
        const hiddenFields = [
            { id: 'hidden_present_province',         name: 'province',          value: permProvText },
            { id: 'hidden_present_municipality_city',name: 'municipality_city', value: permCityText },
            { id: 'hidden_present_barangay',         name: 'barangay',          value: permBrgyText },
            { id: 'hidden_present_house_street',     name: 'house_street',      value: permHouseVal },
        ];
        hiddenFields.forEach(f => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.id    = f.id;
            inp.name  = f.name;
            inp.value = f.value ?? '';
            form.appendChild(inp);
        });

    } else {
        presProvSel.disabled = false;
        presCitySel.disabled = false;
        presBrgySel.disabled = false;
        presHouse.readOnly   = false;
    }
}

function syncPresentAddressIfSame() {
    const same = document.getElementById('same_as_permanent');
    if (!same || !same.checked) return;

    const permProvSel = document.getElementById('perm_province');
    const permCitySel = document.getElementById('perm_municipality_city');
    const permBrgySel = document.getElementById('perm_barangay');
    const permHouse   = document.getElementById('perm_house_street');

    const permProvText = permProvSel.options[permProvSel.selectedIndex]?.text || '';
    const permCityText = permCitySel.options[permCitySel.selectedIndex]?.text || '';
    const permBrgyText = permBrgySel.options[permBrgySel.selectedIndex]?.text || '';
    const permHouseVal = permHouse.value || '';

    ['_province','_municipality_city','_barangay','_house_street'].forEach(suffix => {
        const old = document.getElementById('hidden_present' + suffix);
        if (old) old.remove();
    });

    const form = document.getElementById('nsrpForm');
    const hiddenFields = [
        { id: 'hidden_present_province',          name: 'province',          value: permProvText },
        { id: 'hidden_present_municipality_city',  name: 'municipality_city', value: permCityText },
        { id: 'hidden_present_barangay',           name: 'barangay',          value: permBrgyText },
        { id: 'hidden_present_house_street',       name: 'house_street',      value: permHouseVal },
    ];
    hiddenFields.forEach(f => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.id    = f.id;
        inp.name  = f.name;
        inp.value = f.value;
        form.appendChild(inp);
    });
}

document.addEventListener('DOMContentLoaded', async function() {
    toggleEmploymentFields();
    toggleOfwFields();
    toggleFormerOfwFields();
    updateSignature();
    await loadProvinces();
});

function debugSubmit(e, btn) {
    e.preventDefault();
    const form = btn.form;
    syncPresentAddressIfSame();
    const invalid = [...form.querySelectorAll(':invalid')];
    if (invalid.length > 0) {
        alert('Invalid fields: ' + invalid.map(f => f.name).join(', '));
    } else {
        form.submit();
    }
}
</script>
@endpush

@endsection