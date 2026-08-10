<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PESO — Register as Employer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { min-height: 100vh; font-family: 'Segoe UI', sans-serif; }

        .bg-wrapper {
            position: fixed; inset: 0;
            background: url('{{ asset('images/cityhall.png') }}') center center / cover no-repeat;
            z-index: 0;
        }
        .bg-overlay {
            position: fixed; inset: 0;
            background: linear-gradient(180deg, rgba(13,31,24,0.75) 0%, rgba(13,31,24,0.88) 100%);
            z-index: 1;
        }

        .page {
            position: relative; z-index: 2;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 12px;
        }

        .card-register {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 20px;
            padding: 26px 36px; width: 100%; max-width: 880px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.5);
            margin: auto;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            animation: fadeInUp 0.5s ease forwards;
        }

        @media (max-width: 767px) {
            .page { padding: 16px 10px; }
            .card-register { padding: 20px 18px; border-radius: 16px; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Override inline light-mode colours to dark theme ── */
        .card-register [style*="color:#2d7a5f"]        { color: #eafaf0 !important; }
        .card-register [style*="background:#f8fdfc"]   { background: rgba(255,255,255,0.06) !important; border-color: rgba(255,255,255,0.18) !important; }
        .card-register [style*="background:#f0f9f6"]   { background: rgba(77,217,192,0.08) !important; border-color: rgba(77,217,192,0.3) !important; }
        .card-register [style*="border:1.5px solid #a8e6cf"]  { border-color: rgba(255,255,255,0.2) !important; }
        .card-register [style*="border:2px solid #e8f5f0"]    { border-color: rgba(255,255,255,0.12) !important; }
        .card-register [style*="border:1px solid #a8e6cf"]    { border-color: rgba(77,217,192,0.3) !important; }
        .card-register [style*="color:#888"]           { color: rgba(255,255,255,0.5) !important; }
        .card-register [style*="border-top:2px solid #e8f5f0"] { border-top-color: rgba(255,255,255,0.12) !important; }
        .card-register [style*="border-top:1px dashed #a8e6cf"] { border-top-color: rgba(77,217,192,0.3) !important; }

        /* ── Form controls ── */
        .peso-label { font-size: 11.5px; font-weight: 600; color: #90d870; margin-bottom: 4px; display: block; }
        .peso-input {
            width: 100%; border: 1.5px solid rgba(255,255,255,0.22); border-radius: 9px;
            font-size: 13px; padding: 9px 13px; color: #fff;
            transition: border-color 0.2s, background 0.2s; outline: none;
            background: rgba(255,255,255,0.07);
        }
        .peso-input::placeholder { color: rgba(255,255,255,0.38); }
        .peso-input:focus { border-color: #4dd9c0; box-shadow: 0 0 0 3px rgba(77,217,192,0.18); background: rgba(255,255,255,0.11); }

        /* ── Select / Dropdown fix ── */
        select.peso-input { appearance: none; -webkit-appearance: none; cursor: pointer; }
        select.peso-input option {
            background: #0f2e24;
            color: #e8f8f3;
        }
        select.peso-input option:hover,
        select.peso-input option:focus,
        select.peso-input option:checked {
            background: #1a4a38;
            color: #90d870;
        }

        /* ── Custom Address Dropdown (Province/City/Barangay) — dili native <select>, full control ── */
        .addr-field { position: relative; }
        .custom-dropdown-list {
            position: absolute;
            top: calc(100% + 4px); left: 0; right: 0;
            z-index: 60;
            max-height: 200px;
            overflow-y: auto;
            background: #12261f;
            border: 1.5px solid rgba(77,217,192,0.45);
            border-radius: 10px;
            box-shadow: 0 10px 28px rgba(0,0,0,0.5);
            display: none;
        }
        .custom-dropdown-list.show { display: block; }
        .custom-dropdown-item {
            padding: 8px 13px;
            font-size: 13px;
            color: #eafaf0;
            cursor: pointer;
        }
        .custom-dropdown-item:hover {
            background: rgba(77,217,192,0.2);
            color: #90d870;
        }
        .custom-dropdown-empty {
            padding: 9px 13px;
            font-size: 12px;
            color: rgba(255,255,255,0.4);
        }

        /* ── Checkbox / Radio labels on dark bg ── */
        .form-check-label { color: rgba(255,255,255,0.88) !important; }
        .form-check-input {
            background-color: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.3);
        }
        .form-check-input:checked {
            background-color: #4dd9c0;
            border-color: #4dd9c0;
        }

        /* ── Compact radio group box ── */
        .radio-box {
            border: 1.5px solid rgba(77,217,192,0.25);
            border-radius: 9px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.05);
        }
        .radio-box .form-check { margin-bottom: 0; }
        .radio-box .form-check-label { font-size: 11.5px; }
        .radio-group-title { font-size: 11px; font-weight: 700; color: #4dd9c0; min-width: 52px; }

        .input-wrap { position: relative; }
        .input-wrap .peso-input { padding-right: 40px; }
        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.5);
            cursor: pointer; font-size: 15px; padding: 0;
        }
        .toggle-pw:hover { color: #4dd9c0; }

        /* ── Section heading divider ── */
        .section-heading { font-size: 14px; font-weight: 800; color: #eafaf0; margin-bottom: 10px; }
        .section-heading i { color: #4dd9c0; }

        /* ── Error/warning boxes ── */
        .alert-error { background: rgba(198,40,40,0.15) !important; color: #ff8080 !important; border: 1px solid rgba(198,40,40,0.3); border-radius: 10px; padding: 9px 13px; font-size: 12px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .alert-warn  { background: rgba(249,168,37,0.12) !important; color: #f9a825 !important; border-radius: 10px; padding: 8px 12px; font-size: 11px; margin-top: 4px; }

        /* ── Certification box ── */
        .cert-box { background: rgba(77,217,192,0.07) !important; border: 1px solid rgba(77,217,192,0.25) !important; border-radius: 12px; padding: 13px; }
        .cert-box p, .cert-box strong { color: rgba(255,255,255,0.82) !important; font-size: 11.5px; line-height: 1.6; }

        /* ══════════════════════════════════════════ */
        /* WIZARD — 3 steps, one visible at a time    */
        /* ══════════════════════════════════════════ */
        .progress-track {
            display: flex; gap: 6px; margin-bottom: 16px;
        }
        .progress-seg {
            flex: 1; height: 4px; border-radius: 99px;
            background: rgba(255,255,255,0.14);
            transition: background 0.25s;
        }
        .progress-seg.active { background: linear-gradient(90deg, #90d870, #4dd9c0); }

        .wizard-nav {
            display: flex; align-items: center; gap: 12px;
            margin-top: 18px; padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }
        .step-info {
            font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,0.6);
            white-space: nowrap; margin-right: auto;
        }
        .btn-step {
            border: 1.5px solid rgba(77,217,192,0.45);
            background: rgba(77,217,192,0.1);
            color: #4dd9c0; font-weight: 600; border-radius: 9px;
            padding: 9px 20px; font-size: 13px; cursor: pointer;
            transition: background 0.2s, opacity 0.2s;
        }
        .btn-step:hover:not(:disabled) { background: rgba(77,217,192,0.25); }
        .btn-step:disabled { opacity: 0.35; cursor: not-allowed; }

        .btn-register {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            border: none; color: #0f2e24; font-weight: 700; border-radius: 9px;
            padding: 10px 24px; font-size: 13.5px; cursor: pointer;
            transition: opacity 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(77,217,192,0.35);
        }
        .btn-register:hover { opacity: 0.9; box-shadow: 0 6px 24px rgba(77,217,192,0.5); }
    </style>
</head>
<body>
    <div class="bg-wrapper"></div>
    <div class="bg-overlay"></div>
    <div class="page">
    <div class="card-register">
        <div class="text-center mb-3">
            <div style="width:42px;height:42px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                        border-radius:50%;display:flex;align-items:center;justify-content:center;
                        margin:0 auto 8px;font-size:19px;color:#fff;">
                <i class="bi bi-building-fill"></i>
            </div>
            <div style="font-size:18px;font-weight:800;color:#eafaf0;">Employer Registration</div>
            <div style="font-size:12.5px;color:rgba(255,255,255,0.55);margin-top:2px;">Create your PESO company account</div>
        </div>

        @php
            // Map each field to the step it lives on, so a validation bounce reopens
            // the step that actually failed instead of resetting to step 1.
            $stepFields = [
                1 => ['company_name', 'trade_name', 'employer_type', 'tin', 'tin_type',
                      'total_workforce', 'line_of_business', 'industry_group',
                      'est_province', 'est_city_municipality', 'est_barangay'],
                2 => ['contact_title', 'contact_person', 'position_title',
                      'telephone_no', 'mobile_number', 'fax_no', 'email'],
                3 => ['business_permit', 'sec_dti', 'company_profile',
                      'no_pending_case_certificate', 'vacancy_posting',
                      'certification_agreed', 'certification_date', 'password'],
            ];

            $errorStep = 1;
            foreach ($stepFields as $step => $fields) {
                if ($errors->hasAny($fields)) { $errorStep = $step; break; }
            }
        @endphp

        @if($errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}
            </div>
        @endif

        <div class="progress-track">
            <div class="progress-seg" data-seg="1"></div>
            <div class="progress-seg" data-seg="2"></div>
            <div class="progress-seg" data-seg="3"></div>
        </div>

        <form id="registerForm" method="POST" action="{{ route('register.company.post') }}" enctype="multipart/form-data">
            @csrf

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 1 — I. ESTABLISHMENT DETAILS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="reg-step" data-step="1">
                <div class="section-heading">
                    <i class="bi bi-building me-2"></i>I. Establishment Details
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="peso-label">Establishment / Company Name *</label>
                        <input type="text" name="company_name" id="companyNameInput" class="peso-input"
                            placeholder="ABC Company" value="{{ old('company_name') }}" required>
                        <div id="companyNameWarn" class="alert-warn" style="display:none;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            A company with this name is already registered. If this is a different branch or entity, you may proceed.
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Trade Name</label>
                        <input type="text" name="trade_name" class="peso-input" placeholder="If different from Company Name" value="{{ old('trade_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Tax Identification Number (TIN) *</label>
                        <input type="text" name="tin" class="peso-input" placeholder="e.g. 123-456-789-000" value="{{ old('tin') }}" required>
                    </div>

                    <div class="col-12">
                        <label class="peso-label">Employer Type * <span style="font-weight:400;color:rgba(255,255,255,0.45);">(check only 1)</span></label>
                        <div class="radio-box">
                            <div class="d-flex align-items-center flex-wrap gap-3 mb-1">
                                <span class="radio-group-title">Public</span>
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
                                    <label class="form-check-label" for="empType_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                </div>
                                @endforeach
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <span class="radio-group-title">Private</span>
                                @foreach([
                                    'Direct Hire',
                                    'Local Recruitment Agency',
                                    'Overseas Recruitment Agency',
                                ] as $opt)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="employer_type"
                                        value="{{ $opt }}" id="empType_{{ Str::slug($opt) }}"
                                        {{ old('employer_type') == $opt ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="empType_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="peso-label">TIN Type</label>
                        <div class="radio-box d-flex align-items-center gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tin_type" value="main" id="tinMain" {{ old('tin_type')=='main'?'checked':'' }}>
                                <label class="form-check-label" for="tinMain">Main</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tin_type" value="branch" id="tinBranch" {{ old('tin_type')=='branch'?'checked':'' }}>
                                <label class="form-check-label" for="tinBranch">Branch</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="peso-label">Total Work Force</label>
                        <div class="radio-box d-flex align-items-center flex-wrap gap-3">
                            @foreach([
                                'micro'  => 'Micro (1-9)',
                                'small'  => 'Small (10-99)',
                                'medium' => 'Medium (100-199)',
                                'large'  => 'Large (200 and up)',
                            ] as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="total_workforce"
                                    value="{{ $val }}" id="workforce_{{ $val }}"
                                    {{ old('total_workforce') == $val ? 'checked' : '' }}>
                                <label class="form-check-label" for="workforce_{{ $val }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="peso-label">Line of Business/Industry <span style="font-weight:400;color:#888;">(check BIR 2303)</span></label>
                        <input type="text" name="line_of_business" class="peso-input" placeholder="e.g. BPO / Retail / Manufacturing" value="{{ old('line_of_business') }}">
                    </div>
                    <div class="col-md-7">
                        <label class="peso-label">Major Industry Group *</label>
                        <select name="industry_group" class="peso-input" required>
                            <option value="" disabled {{ old('industry_group') ? '' : 'selected' }}>Select industry group</option>
                            <option value="Agriculture, Hunting and Forestry, Fishing" {{ old('industry_group') === 'Agriculture, Hunting and Forestry, Fishing' ? 'selected' : '' }}>Agriculture, Hunting and Forestry, Fishing</option>
                            <option value="Mining and Quarrying" {{ old('industry_group') === 'Mining and Quarrying' ? 'selected' : '' }}>Mining and Quarrying</option>
                            <option value="Manufacturing" {{ old('industry_group') === 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                            <option value="Construction" {{ old('industry_group') === 'Construction' ? 'selected' : '' }}>Construction</option>
                            <option value="Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods" {{ old('industry_group') === 'Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods' ? 'selected' : '' }}>Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods</option>
                            <option value="Hotel and Restaurants" {{ old('industry_group') === 'Hotel and Restaurants' ? 'selected' : '' }}>Hotel and Restaurants</option>
                            <option value="Transport, Storage and Communications" {{ old('industry_group') === 'Transport, Storage and Communications' ? 'selected' : '' }}>Transport, Storage and Communications</option>
                            <option value="Financial Intermediation" {{ old('industry_group') === 'Financial Intermediation' ? 'selected' : '' }}>Financial Intermediation</option>
                            <option value="Real Estate, Renting and Business Activities" {{ old('industry_group') === 'Real Estate, Renting and Business Activities' ? 'selected' : '' }}>Real Estate, Renting and Business Activities</option>
                            <option value="Public Administration and Defense, Compulsory Social Security" {{ old('industry_group') === 'Public Administration and Defense, Compulsory Social Security' ? 'selected' : '' }}>Public Administration and Defense, Compulsory Social Security</option>
                            <option value="Education" {{ old('industry_group') === 'Education' ? 'selected' : '' }}>Education</option>
                            <option value="Health and Social Work" {{ old('industry_group') === 'Health and Social Work' ? 'selected' : '' }}>Health and Social Work</option>
                            <option value="Other Community, Social and Personal Activities" {{ old('industry_group') === 'Other Community, Social and Personal Activities' ? 'selected' : '' }}>Other Community, Social and Personal Activities</option>
                            <option value="Extra-territorial Organization and Bodies" {{ old('industry_group') === 'Extra-territorial Organization and Bodies' ? 'selected' : '' }}>Extra-territorial Organization and Bodies</option>
                            <option value="Overseas Manpower Services" {{ old('industry_group') === 'Overseas Manpower Services' ? 'selected' : '' }}>Overseas Manpower Services</option>
                        </select>
                    </div>

                    <div class="col-md-4 addr-field">
                        <label class="peso-label">Province</label>
                        <input type="text" id="provinceInput" class="peso-input" placeholder="Type or click to select..." autocomplete="off">
                        <input type="hidden" name="est_province" id="provinceValue" value="{{ old('est_province') }}">
                        <div id="provinceDropdown" class="custom-dropdown-list"></div>
                    </div>
                    <div class="col-md-4 addr-field">
                        <label class="peso-label">City/Municipality</label>
                        <input type="text" id="cityInput" class="peso-input" placeholder="Select province first" autocomplete="off" disabled>
                        <input type="hidden" name="est_city_municipality" id="cityValue" value="{{ old('est_city_municipality') }}">
                        <div id="cityDropdown" class="custom-dropdown-list"></div>
                    </div>
                    <div class="col-md-4 addr-field">
                        <label class="peso-label">Barangay</label>
                        <input type="text" id="barangayInput" class="peso-input" placeholder="Select city first" autocomplete="off" disabled>
                        <input type="hidden" name="est_barangay" id="barangayValue" value="{{ old('est_barangay') }}">
                        <div id="barangayDropdown" class="custom-dropdown-list"></div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 2 — II. ESTABLISHMENT CONTACT DETAILS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="reg-step" data-step="2" style="display:none;">
                <div class="section-heading">
                    <i class="bi bi-person-lines-fill me-2"></i>II. Establishment Contact Details
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="peso-label">Title</label>
                        <select name="contact_title" id="contactTitleSelect" class="peso-input">
                            <option value="">Select</option>
                            <option value="Mr." {{ old('contact_title')=='Mr.'?'selected':'' }}>Mr.</option>
                            <option value="Ms." {{ old('contact_title')=='Ms.'?'selected':'' }}>Ms.</option>
                            <option value="Miss" {{ old('contact_title')=='Miss'?'selected':'' }}>Miss</option>
                            <option value="Others" {{ old('contact_title')=='Others' || (old('contact_title') && !in_array(old('contact_title'), ['Mr.','Ms.','Miss'])) ? 'selected' : '' }}>Others (please specify)</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="contactTitleOtherWrap" style="display:none;">
                        <label class="peso-label">Please specify</label>
                        <input type="text" name="contact_title_other" id="contactTitleOther" class="peso-input"
                            placeholder="e.g. Dr." value="{{ old('contact_title') && !in_array(old('contact_title'), ['Mr.','Ms.','Miss','Others']) ? old('contact_title') : '' }}">
                    </div>
                    <div class="col-md-5">
                        <label class="peso-label">Contact Person (Full name) *</label>
                        <input type="text" name="contact_person" class="peso-input"
                            placeholder="Juan Dela Cruz" value="{{ old('contact_person') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Position / Title *</label>
                        <input type="text" name="position_title" class="peso-input"
                            placeholder="HR Manager" value="{{ old('position_title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Telephone No.</label>
                        <input type="text" name="telephone_no" class="peso-input" placeholder="Optional" value="{{ old('telephone_no') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Mobile Number *</label>
                        <input type="text" name="mobile_number" class="peso-input"
                            placeholder="09123456789" value="{{ old('mobile_number') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Fax No.</label>
                        <input type="text" name="fax_no" class="peso-input" placeholder="Optional" value="{{ old('fax_no') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="peso-label">Email Address *</label>
                        <input type="email" name="email" id="emailInput" class="peso-input"
                            placeholder="company@email.com" value="{{ old('email') }}" required>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- STEP 3 — REQUIREMENTS (OPTIONAL) + CERTIFICATION + PASSWORD --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="reg-step" data-step="3" style="display:none;">
                <div class="section-heading" style="margin-bottom:4px;">
                    <i class="bi bi-clipboard-check me-2"></i>Company Requirements
                    <span style="font-size:11.5px;font-weight:400;color:rgba(255,255,255,0.5);">(optional)</span>
                </div>
                <div class="d-flex align-items-start gap-2 p-2 mb-2 rounded-3"
                     style="background:rgba(77,217,192,0.07);border:1px solid rgba(77,217,192,0.25);">
                    <i class="bi bi-info-circle-fill" style="color:#4dd9c0;font-size:15px;margin-top:1px;"></i>
                    <div style="font-size:11.5px;color:rgba(255,255,255,0.8);line-height:1.5;">
                        PESO needs to verify <strong style="color:#4dd9c0;">5 documents</strong> before your postings go public.
                        Attach them now, or skip and submit them later from the <strong style="color:#4dd9c0;">Requirements</strong> page.
                    </div>
                </div>

                <div class="row g-2">
                    @php
                    $regDocs = [
                        ['field' => 'business_permit',             'label' => 'CDO Business Permit 2026'],
                        ['field' => 'sec_dti',                     'label' => 'SEC / DTI'],
                        ['field' => 'company_profile',             'label' => 'Company Profile'],
                        ['field' => 'no_pending_case_certificate', 'label' => 'Certificate of No Pending Case'],
                        ['field' => 'vacancy_posting',             'label' => 'Vacancy Posting'],
                    ];
                    @endphp
                    @foreach($regDocs as $i => $doc)
                    <div class="col-md-6">
                        <label class="peso-label">{{ $i + 1 }}. {{ $doc['label'] }} <span style="font-weight:400;color:#888;">(optional)</span></label>
                        <input type="file" name="{{ $doc['field'] }}" class="peso-input" accept=".jpg,.jpeg,.png,.pdf" style="padding:6px 12px;">
                    </div>
                    @endforeach
                </div>

                {{-- CERTIFICATION / AUTHORIZATION --}}
                <div class="cert-box mt-3">
                    <div style="font-size:11.5px;color:rgba(255,255,255,0.78);line-height:1.6;">
                        <strong style="color:#4dd9c0;">Certification/Authorization:</strong> This is to certify that all data/information
                        that I have provided in this form are true to the best of my knowledge. This is also to
                        authorize the PESO and DOLE to include the establishment profile in the PESO Employment
                        Information System (PEIS). It is understood that the establishment profile and contact
                        details shall be made available to the jobseekers, PESOs, DOLE Regional Offices and Filed
                        Offices, Bureau of Local Employment and others who have access to the PEIS. I am also
                        aware that DOLE is not obliged to seek applicants on our behalf.
                    </div>
                    <div class="row g-2 mt-1 align-items-end">
                        <div class="col-md-3">
                            <label class="peso-label">Date Signed</label>
                            <input type="text" class="peso-input" value="{{ now()->format('m/d/Y') }}" readonly style="background:rgba(77,217,192,0.08);color:rgba(255,255,255,0.6);">
                            <input type="hidden" name="certification_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-9">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="certification_agreed" value="1" id="certAgree" {{ old('certification_agreed') ? 'checked' : '' }} required>
                                <label class="form-check-label" for="certAgree" style="font-size:11.5px;font-weight:600;">
                                    I agree to the certification and authorization above *
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACCOUNT SECURITY --}}
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <label class="peso-label">Password *</label>
                        <div class="input-wrap">
                            <input type="password" name="password" id="pw1" class="peso-input"
                                placeholder="Minimum 6 characters" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('pw1','icon1')">
                                <i class="bi bi-eye" id="icon1"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="peso-label">Confirm Password *</label>
                        <div class="input-wrap">
                            <input type="password" name="password_confirmation" id="pw2" class="peso-input"
                                placeholder="Repeat password" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('pw2','icon2')">
                                <i class="bi bi-eye" id="icon2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── STEP NAVIGATION ── --}}
            <div class="wizard-nav">
                <span class="step-info" id="regStepInfo">Step 1 of 3</span>
                <button type="button" class="btn-step" id="regStepPrev">
                    <i class="bi bi-chevron-left me-1"></i>Back
                </button>
                <button type="button" class="btn-step" id="regStepNext">
                    Next<i class="bi bi-chevron-right ms-1"></i>
                </button>
                <button type="submit" class="btn-register" id="regSubmitBtn" style="display:none;">
                    <i class="bi bi-building-fill me-2"></i>Create Company Account
                </button>
            </div>
        </form>

        <div class="text-center mt-3" style="font-size:12.5px;color:rgba(255,255,255,0.5);">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#4dd9c0;font-weight:700;">Sign in</a>
        </div>
    </div>
    </div>

    <script>
        // ══════════════════════════════════════════
        // STEP NAVIGATION
        // ══════════════════════════════════════════
        const regTotalSteps = 3;
        let regCurrentStep = {{ $errorStep }};

        const registerForm = document.getElementById('registerForm');
        const regStepPrev  = document.getElementById('regStepPrev');
        const regStepNext  = document.getElementById('regStepNext');
        const regSubmitBtn = document.getElementById('regSubmitBtn');
        const regStepInfo  = document.getElementById('regStepInfo');

        function showRegStep(step) {
            document.querySelectorAll('.reg-step').forEach(el => {
                el.style.display = (parseInt(el.dataset.step) === step) ? 'block' : 'none';
            });
            document.querySelectorAll('.progress-seg').forEach(el => {
                el.classList.toggle('active', parseInt(el.dataset.seg) <= step);
            });

            regStepInfo.textContent = `Step ${step} of ${regTotalSteps}`;
            regStepPrev.disabled = step <= 1;

            const isLast = step >= regTotalSteps;
            regStepNext.style.display  = isLast ? 'none' : 'inline-flex';
            regSubmitBtn.style.display = isLast ? 'inline-flex' : 'none';
        }

        // Only checks the fields on the step being left, so hidden steps never
        // block navigation.
        function validateRegStep(step) {
            const stepEl = document.querySelector(`.reg-step[data-step="${step}"]`);
            for (const field of stepEl.querySelectorAll('[required]')) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return false;
                }
            }
            return true;
        }

        regStepPrev.addEventListener('click', () => {
            if (regCurrentStep > 1) {
                regCurrentStep--;
                showRegStep(regCurrentStep);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        regStepNext.addEventListener('click', () => {
            if (!validateRegStep(regCurrentStep)) return;
            if (regCurrentStep < regTotalSteps) {
                regCurrentStep++;
                showRegStep(regCurrentStep);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        // Safety net: a required field on a hidden step cannot be focused, so the
        // browser would silently refuse to submit. Jump to the offending step first.
        registerForm.addEventListener('submit', function (e) {
            if (registerForm.checkValidity()) return;
            e.preventDefault();

            const bad = registerForm.querySelector(':invalid');
            if (!bad) return;

            const owningStep = bad.closest('.reg-step');
            if (owningStep) {
                regCurrentStep = parseInt(owningStep.dataset.step);
                showRegStep(regCurrentStep);
            }
            bad.reportValidity();
        });

        showRegStep(regCurrentStep);

        // ── Title "Others" toggle ──
        const contactTitleSelect = document.getElementById('contactTitleSelect');
        const contactTitleOtherWrap = document.getElementById('contactTitleOtherWrap');
        const contactTitleOther = document.getElementById('contactTitleOther');

        function toggleContactTitleOther() {
            if (contactTitleSelect.value === 'Others') {
                contactTitleOtherWrap.style.display = 'block';
                contactTitleOther.setAttribute('name', 'contact_title');
                contactTitleSelect.removeAttribute('name');
            } else {
                contactTitleOtherWrap.style.display = 'none';
                contactTitleOther.removeAttribute('name');
                contactTitleSelect.setAttribute('name', 'contact_title');
            }
        }
        contactTitleSelect.addEventListener('change', toggleContactTitleOther);
        toggleContactTitleOther();

        function togglePw(id, iconId) {
            const input = document.getElementById(id);
            const icon  = document.getElementById(iconId);
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        document.getElementById('emailInput').addEventListener('blur', function() {
            const email = this.value.trim();
            if (!email) return;
            fetch(`/check-email?email=${encodeURIComponent(email)}`)
                .then(r => r.json())
                .then(data => {
                    const existing = document.getElementById('emailWarn');
                    if (data.taken) {
                        if (!existing) {
                            const div = document.createElement('div');
                            div.id = 'emailWarn';
                            div.className = 'alert-error';
                            div.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> This email address is already registered. Please sign in instead.';
                            document.querySelector('form').prepend(div);
                        }
                    } else { if (existing) existing.remove(); }
                });
        });

        // ── Duplicate Company Name check (soft warning, dili mag-block) ──
        document.getElementById('companyNameInput').addEventListener('blur', function() {
            const name = this.value.trim();
            const warnDiv = document.getElementById('companyNameWarn');
            if (!name) { warnDiv.style.display = 'none'; return; }
            fetch(`/check-company-name?company_name=${encodeURIComponent(name)}`)
                .then(r => r.json())
                .then(data => {
                    warnDiv.style.display = data.exists ? 'block' : 'none';
                });
        });

        // ── CUSTOM CASCADING ADDRESS DROPDOWN (Province → City → Barangay) — PSGC API direct ──
        const PSGC = 'https://psgc.gitlab.io/api';

        const provinceInput    = document.getElementById('provinceInput');
        const provinceValue    = document.getElementById('provinceValue');
        const provinceDropdown = document.getElementById('provinceDropdown');

        const cityInput    = document.getElementById('cityInput');
        const cityValue     = document.getElementById('cityValue');
        const cityDropdown  = document.getElementById('cityDropdown');

        const barangayInput    = document.getElementById('barangayInput');
        const barangayValue    = document.getElementById('barangayValue');
        const barangayDropdown = document.getElementById('barangayDropdown');

        let allProvinces = [];
        let allCities     = [];
        let allBarangays  = [];
        let selectedProvinceCode = null;
        let selectedCityCode     = null;

        // Repopulate the visible boxes after a validation bounce.
        provinceInput.value = provinceValue.value;
        if (cityValue.value)     { cityInput.value = cityValue.value; }
        if (barangayValue.value) { barangayInput.value = barangayValue.value; }

        function renderDropdown(listEl, items, onPick) {
            listEl.innerHTML = '';
            if (items.length === 0) {
                listEl.innerHTML = '<div class="custom-dropdown-empty">No matches found</div>';
            } else {
                items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'custom-dropdown-item';
                    div.textContent = item.name;
                    div.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        onPick(item);
                    });
                    listEl.appendChild(div);
                });
            }
            listEl.classList.add('show');
        }

        function closeDropdown(listEl) {
            listEl.classList.remove('show');
        }

        function filterItems(items, query) {
            const q = query.trim().toLowerCase();
            if (!q) return items;
            return items.filter(i => i.name.toLowerCase().includes(q));
        }

        // ── PROVINCE ──
        fetch(`${PSGC}/provinces.json`)
            .then(r => r.json())
            .then(provinces => {
                allProvinces = provinces.sort((a, b) => a.name.localeCompare(b.name));
            })
            .catch(() => {
                provinceInput.placeholder = 'Failed to load — please refresh';
            });

        provinceInput.addEventListener('focus', function() {
            renderDropdown(provinceDropdown, filterItems(allProvinces, this.value), pickProvince);
        });
        provinceInput.addEventListener('input', function() {
            renderDropdown(provinceDropdown, filterItems(allProvinces, this.value), pickProvince);
        });

        function pickProvince(item) {
            provinceInput.value = item.name;
            provinceValue.value = item.name;
            selectedProvinceCode = item.code;
            closeDropdown(provinceDropdown);

            cityInput.value = '';
            cityValue.value = '';
            cityInput.placeholder = 'Loading cities/municipalities...';
            cityInput.disabled = true;
            barangayInput.value = '';
            barangayValue.value = '';
            barangayInput.placeholder = 'Select city first';
            barangayInput.disabled = true;
            allCities = [];
            allBarangays = [];
            selectedCityCode = null;

            fetch(`${PSGC}/provinces/${item.code}/cities-municipalities.json`)
                .then(r => r.json())
                .then(cities => {
                    allCities = cities.sort((a, b) => a.name.localeCompare(b.name));
                    cityInput.placeholder = 'Type or click to select...';
                    cityInput.disabled = false;
                })
                .catch(() => {
                    cityInput.placeholder = 'Failed to load — please retry';
                });
        }

        // ── CITY / MUNICIPALITY ──
        cityInput.addEventListener('focus', function() {
            if (this.disabled) return;
            renderDropdown(cityDropdown, filterItems(allCities, this.value), pickCity);
        });
        cityInput.addEventListener('input', function() {
            if (this.disabled) return;
            renderDropdown(cityDropdown, filterItems(allCities, this.value), pickCity);
        });

        function pickCity(item) {
            cityInput.value = item.name;
            cityValue.value = item.name;
            selectedCityCode = item.code;
            closeDropdown(cityDropdown);

            barangayInput.value = '';
            barangayValue.value = '';
            barangayInput.placeholder = 'Loading barangays...';
            barangayInput.disabled = true;
            allBarangays = [];

            fetch(`${PSGC}/cities-municipalities/${item.code}/barangays.json`)
                .then(r => r.json())
                .then(barangays => {
                    allBarangays = barangays.sort((a, b) => a.name.localeCompare(b.name));
                    barangayInput.placeholder = 'Type or click to select...';
                    barangayInput.disabled = false;
                })
                .catch(() => {
                    barangayInput.placeholder = 'Failed to load — please retry';
                });
        }

        // ── BARANGAY ──
        barangayInput.addEventListener('focus', function() {
            if (this.disabled) return;
            renderDropdown(barangayDropdown, filterItems(allBarangays, this.value), pickBarangay);
        });
        barangayInput.addEventListener('input', function() {
            if (this.disabled) return;
            renderDropdown(barangayDropdown, filterItems(allBarangays, this.value), pickBarangay);
        });

        function pickBarangay(item) {
            barangayInput.value = item.name;
            barangayValue.value = item.name;
            closeDropdown(barangayDropdown);
        }

        // ── Close dropdowns kung mo-click sa gawas ──
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#provinceInput') && !e.target.closest('#provinceDropdown')) {
                closeDropdown(provinceDropdown);
            }
            if (!e.target.closest('#cityInput') && !e.target.closest('#cityDropdown')) {
                closeDropdown(cityDropdown);
            }
            if (!e.target.closest('#barangayInput') && !e.target.closest('#barangayDropdown')) {
                closeDropdown(barangayDropdown);
            }
        });
    </script>
</body>
</html>
