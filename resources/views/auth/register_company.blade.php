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
            display: flex; align-items: flex-start; justify-content: center;
            padding: 40px 12px;
        }

        .card-register {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 24px;
            padding: 40px 48px; width: 100%; max-width: 860px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.5);
            margin: auto;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            animation: fadeInUp 0.5s ease forwards;
        }

        @media (max-width: 767px) {
            .page { padding: 20px 12px; }
            .card-register { padding: 24px 20px; border-radius: 18px; }
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

        /* Position row override */
        .position-row { background: rgba(255,255,255,0.04) !important; border-color: rgba(77,217,192,0.25) !important; }

        /* ── Form controls ── */
        .peso-label { font-size: 12px; font-weight: 600; color: #90d870; margin-bottom: 6px; display: block; }
        .peso-input {
            width: 100%; border: 1.5px solid rgba(255,255,255,0.22); border-radius: 10px;
            font-size: 13px; padding: 11px 14px; color: #fff;
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
            max-height: 230px;
            overflow-y: auto;
            background: #12261f;
            border: 1.5px solid rgba(77,217,192,0.45);
            border-radius: 10px;
            box-shadow: 0 10px 28px rgba(0,0,0,0.5);
            display: none;
        }
        .custom-dropdown-list.show { display: block; }
        .custom-dropdown-item {
            padding: 9px 14px;
            font-size: 13px;
            color: #eafaf0;
            cursor: pointer;
        }
        .custom-dropdown-item:hover {
            background: rgba(77,217,192,0.2);
            color: #90d870;
        }
        .custom-dropdown-empty {
            padding: 10px 14px;
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

        .input-wrap { position: relative; }
        .input-wrap .peso-input { padding-right: 40px; }
        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.5);
            cursor: pointer; font-size: 15px; padding: 0;
        }
        .toggle-pw:hover { color: #4dd9c0; }

        .btn-register {
            width: 100%; background: linear-gradient(90deg, #90d870, #4dd9c0);
            border: none; color: #0f2e24; font-weight: 700; border-radius: 10px;
            padding: 12px; font-size: 14px; cursor: pointer;
            transition: opacity 0.2s, box-shadow 0.2s; margin-top: 8px;
            box-shadow: 0 4px 16px rgba(77,217,192,0.35);
        }
        .btn-register:hover { opacity: 0.9; box-shadow: 0 6px 24px rgba(77,217,192,0.5); }

        /* ── Add Position button ── */
        #addPositionBtn { border-color: rgba(77,217,192,0.5) !important; color: #4dd9c0 !important; background: rgba(77,217,192,0.1) !important; }
        #addPositionBtn:hover { background: rgba(77,217,192,0.25) !important; }

        /* ── Section heading divider ── */
        .section-heading { font-size: 14px; font-weight: 800; color: #eafaf0; margin-bottom: 12px; }
        .section-heading i { color: #4dd9c0; }

        /* ── Error/warning boxes ── */
        .alert-error { background: rgba(198,40,40,0.15) !important; color: #ff8080 !important; border: 1px solid rgba(198,40,40,0.3); border-radius: 10px; padding: 10px 14px; font-size: 12px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .alert-warn  { background: rgba(249,168,37,0.12) !important; color: #f9a825 !important; border-radius: 10px; padding: 10px 14px; font-size: 11px; margin-top: 4px; }

        /* ── Certification box ── */
        .cert-box { background: rgba(77,217,192,0.07) !important; border: 1px solid rgba(77,217,192,0.25) !important; border-radius: 12px; padding: 16px; }
        .cert-box p, .cert-box strong { color: rgba(255,255,255,0.82) !important; font-size: 12px; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="bg-wrapper"></div>
    <div class="bg-overlay"></div>
    <div class="page">
    <div class="card-register">
        <div class="text-center mb-4">
            <div style="width:52px;height:52px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                        border-radius:50%;display:flex;align-items:center;justify-content:center;
                        margin:0 auto 10px;font-size:22px;color:#fff;">
                <i class="bi bi-building-fill"></i>
            </div>
            <div style="font-size:20px;font-weight:800;color:#eafaf0;">Employer Registration</div>
            <div style="font-size:13px;color:rgba(255,255,255,0.55);margin-top:4px;">Create your PESO company account</div>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.company.post') }}" enctype="multipart/form-data">
            @csrf

            {{-- ══════════════════════════════════════════ --}}
            {{-- I. ESTABLISHMENT DETAILS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div>
                <div class="section-heading">
                    <i class="bi bi-building me-2"></i>I. Establishment Details
                </div>
                <div class="row g-3 mb-2">
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
                    <div class="col-12">
                        <label class="peso-label">Employer Type * <span style="font-weight:400;color:rgba(255,255,255,0.45);">(check only 1)</span></label>
                        <div class="row" style="border:1.5px solid rgba(77,217,192,0.25);border-radius:10px;padding:12px 14px;background:rgba(255,255,255,0.05);">
                            <div class="col-md-6">
                                <div style="font-size:12px;font-weight:700;color:#4dd9c0;margin-bottom:6px;">Public</div>
                                @foreach([
                                    'National Government Agency',
                                    'Local Government Unit',
                                    'Government-owned & controlled corp.',
                                    'State/Local University or College',
                                ] as $opt)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="employer_type"
                                        value="{{ $opt }}" id="empType_{{ Str::slug($opt) }}"
                                        {{ old('employer_type') == $opt ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="empType_{{ Str::slug($opt) }}" style="font-size:12px;color:#2d7a5f;">{{ $opt }}</label>
                                </div>
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                <div style="font-size:12px;font-weight:700;color:#4dd9c0;margin-bottom:6px;">Private</div>
                                @foreach([
                                    'Direct Hire',
                                    'Local Recruitment Agency',
                                    'Overseas Recruitment Agency',
                                ] as $opt)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="employer_type"
                                        value="{{ $opt }}" id="empType_{{ Str::slug($opt) }}"
                                        {{ old('employer_type') == $opt ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="empType_{{ Str::slug($opt) }}" style="font-size:12px;color:#2d7a5f;">{{ $opt }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Tax Identification Number (TIN) *</label>
                        <input type="text" name="tin" class="peso-input" placeholder="e.g. 123-456-789-000" value="{{ old('tin') }}" required>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tin_type" value="main" id="tinMain" {{ old('tin_type')=='main'?'checked':'' }}>
                                <label class="form-check-label" for="tinMain" style="font-size:12px;color:#2d7a5f;">Main</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tin_type" value="branch" id="tinBranch" {{ old('tin_type')=='branch'?'checked':'' }}>
                                <label class="form-check-label" for="tinBranch" style="font-size:12px;color:#2d7a5f;">Branch</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="peso-label">Total Work Force</label>
                        <div class="d-flex flex-wrap gap-4" style="border:1.5px solid rgba(77,217,192,0.25);border-radius:10px;padding:12px 14px;background:rgba(255,255,255,0.05);">
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
                                <label class="form-check-label" for="workforce_{{ $val }}" style="font-size:12px;color:#2d7a5f;">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="peso-label">Line of Business/Industry <span style="font-weight:400;color:#888;">(check BIR 2303)</span></label>
                        <input type="text" name="line_of_business" class="peso-input" placeholder="e.g. BPO / Retail / Manufacturing" value="{{ old('line_of_business') }}">
                    </div>
                    <div class="col-12">
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
                        <input type="hidden" name="est_province" id="provinceValue" required>
                        <div id="provinceDropdown" class="custom-dropdown-list"></div>
                    </div>
                    <div class="col-md-4 addr-field">
                        <label class="peso-label">City/Municipality</label>
                        <input type="text" id="cityInput" class="peso-input" placeholder="Select province first" autocomplete="off" disabled>
                        <input type="hidden" name="est_city_municipality" id="cityValue">
                        <div id="cityDropdown" class="custom-dropdown-list"></div>
                    </div>
                    <div class="col-md-4 addr-field">
                        <label class="peso-label">Barangay</label>
                        <input type="text" id="barangayInput" class="peso-input" placeholder="Select city first" autocomplete="off" disabled>
                        <input type="hidden" name="est_barangay" id="barangayValue">
                        <div id="barangayDropdown" class="custom-dropdown-list"></div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- II. ESTABLISHMENT CONTACT DETAILS --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.12);">
                <div class="section-heading">
                    <i class="bi bi-person-lines-fill me-2"></i>II. Establishment Contact Details
                </div>
                <div class="row g-3 mb-2">
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
                    <div class="col-md-2" id="contactTitleOtherWrap" style="display:none;">
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
                    <div class="col-md-4">
                        <label class="peso-label">Email Address *</label>
                        <input type="email" name="email" id="emailInput" class="peso-input"
                            placeholder="company@email.com" value="{{ old('email') }}" required>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- III/IV. VACANCY DETAILS (NSRP Reg Form 2) --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.12);">
                <div class="section-heading" style="margin-bottom:4px;">
                    <i class="bi bi-briefcase-fill me-2"></i>Job Vacancy Details
                </div>
                <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:16px;">
                    List your current job openings. <strong style="color:rgba(255,255,255,0.8);">This section is optional</strong> — you may skip it for now and add job postings later from the Job Requests page once your requirements are approved.
                </div>

                <div id="positionsContainer"></div>

                <button type="button" id="addPositionBtn" class="btn btn-sm fw-semibold mb-3"
                    style="border:1.5px solid #4dd9c0;color:#2d7a5f;background:#e8f8f3;border-radius:8px;font-size:12px;padding:8px 18px;transition:all 0.2s;"
                    onmouseover="this.style.background='#4dd9c0';this.style.color='#fff';"
                    onmouseout="this.style.background='#e8f8f3';this.style.color='#2d7a5f';">
                    <i class="bi bi-plus-circle-fill me-1"></i> Add Position
                </button>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- REQUIREMENTS NOTICE + OPTIONAL ATTACH --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.12);">
                <div class="section-heading" style="margin-bottom:4px;">
                    <i class="bi bi-clipboard-check me-2"></i>Company Requirements
                </div>
                <div class="d-flex align-items-start gap-2 p-3 mb-3 rounded-3"
                     style="background:rgba(77,217,192,0.07);border:1px solid rgba(77,217,192,0.25);">
                    <i class="bi bi-info-circle-fill" style="color:#4dd9c0;font-size:18px;margin-top:1px;"></i>
                    <div style="font-size:12px;color:rgba(255,255,255,0.8);line-height:1.6;">
                        Before your account can post job vacancies, PESO needs to verify <strong style="color:#4dd9c0;">5 required documents</strong>.
                        If you already have them ready, you may attach them now. Otherwise, you can skip this and
                        submit them later from the <strong style="color:#4dd9c0;">Requirements</strong> page after your account is created.
                    </div>  
                </div>

                <div class="row g-3">
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
                        <input type="file" name="{{ $doc['field'] }}" class="peso-input" accept=".jpg,.jpeg,.png,.pdf" style="padding:8px 14px;">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- CERTIFICATION / AUTHORIZATION --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="cert-box mt-3">
                <div style="font-size:12px;color:rgba(255,255,255,0.78);line-height:1.7;">
                    <strong style="color:#4dd9c0;">Certification/Authorization:</strong> This is to certify that all data/information
                    that I have provided in this form are true to the best of my knowledge. This is also to
                    authorize the PESO and DOLE to include the establishment profile in the PESO Employment
                    Information System (PEIS). It is understood that the establishment profile and contact
                    details shall be made available to the jobseekers, PESOs, DOLE Regional Offices and Filed
                    Offices, Bureau of Local Employment and others who have access to the PEIS. I am also
                    aware that DOLE is not obliged to seek applicants on our behalf.
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="peso-label">Date Signed</label>
                        <input type="text" class="peso-input" value="{{ now()->format('m/d/Y') }}" readonly style="background:rgba(77,217,192,0.08);color:rgba(255,255,255,0.6);">
                        <input type="hidden" name="certification_date" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="certification_agreed" value="1" id="certAgree" required>
                    <label class="form-check-label" for="certAgree" style="font-size:12px;font-weight:600;">
                        I agree to the certification and authorization above *
                    </label>
                </div>
            </div>

            {{-- ══════════════════════════════════════════ --}}
            {{-- ACCOUNT SECURITY --}}
            {{-- ══════════════════════════════════════════ --}}
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.12);">
                <div class="section-heading">
                    <i class="bi bi-shield-lock-fill me-2"></i>Account Security
                </div>
                <div class="row g-3 mb-3">
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

            <button type="submit" class="btn-register mt-3">
                <i class="bi bi-building-fill me-2"></i>Create Company Account
            </button>
        </form>

        <div class="text-center mt-3" style="font-size:13px;color:rgba(255,255,255,0.5);">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#4dd9c0;font-weight:700;">Sign in</a>
        </div>
    </div>
    </div>
    </div>

    <script>
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
        document.addEventListener('DOMContentLoaded', toggleContactTitleOther);

        function togglePw(id, iconId) {
            const input = document.getElementById(id);
            const icon  = document.getElementById(iconId);
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        // ── DYNAMIC POSITION ROWS (Vacancy Details) ──
        let positionCount = 0;

        function buildPositionRow(idx) {
            return `
            <div class="position-row mb-3 p-3" style="border:1.5px solid rgba(77,217,192,0.25);border-radius:12px;background:rgba(255,255,255,0.04);position:relative;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:12px;font-weight:700;color:#4dd9c0;">Position #<span class="pos-num">${idx + 1}</span></span>
                    <button type="button" class="btn btn-sm remove-position-btn"
                        style="background:rgba(224,82,82,0.15);color:#ff8080;border:1px solid rgba(224,82,82,0.35);border-radius:8px;font-size:11px;padding:2px 10px;">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="peso-label">Position Title *</label>
                        <input type="text" name="positions[${idx}][title]" class="peso-input" placeholder="e.g. Sales Associate" required>
                    </div>
                    <div class="col-12">
                        <label class="peso-label">Job Image <span style="font-weight:400;color:#888;">(optional — photo of workplace/job)</span></label>
                        <input type="file" name="positions[${idx}][job_image]" class="peso-input job-image-input" accept=".jpg,.jpeg,.png" style="padding:8px 14px;" data-preview="imgPreview${idx}">
                        <div id="imgPreview${idx}" style="margin-top:6px;max-width:180px;display:none;">
                            <img src="" alt="Preview" style="width:100%;border-radius:8px;border:1px solid rgba(77,217,192,0.3);">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="peso-label">Job Description *</label>
                        <textarea name="positions[${idx}][description]" class="peso-input" rows="4"
                            placeholder="Describe the job responsibilities..." required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Nature of Work *</label>
                        <select name="positions[${idx}][type]" class="peso-input" required>
                            <option value="" disabled selected>Select type</option>
                            <option value="permanent">Permanent</option>
                            <option value="contractual">Contractual</option>
                            <option value="project_based">Project-based</option>
                            <option value="internship">Internship / OJT</option>
                            <option value="part_time">Part-time</option>
                            <option value="work_from_home">Work from home / online job</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="peso-label">Major Industry Group *</label>
                        <select name="positions[${idx}][industry_group]" class="peso-input" required>
                            <option value="" disabled selected>Select industry group</option>
                            <option value="Agriculture, Hunting and Forestry, Fishing">Agriculture, Hunting and Forestry, Fishing</option>
                            <option value="Mining and Quarrying">Mining and Quarrying</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Construction">Construction</option>
                            <option value="Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods">Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods</option>
                            <option value="Hotel and Restaurants">Hotel and Restaurants</option>
                            <option value="Transport, Storage and Communications">Transport, Storage and Communications</option>
                            <option value="Financial Intermediation">Financial Intermediation</option>
                            <option value="Real Estate, Renting and Business Activities">Real Estate, Renting and Business Activities</option>
                            <option value="Public Administration and Defense, Compulsory Social Security">Public Administration and Defense, Compulsory Social Security</option>
                            <option value="Education">Education</option>
                            <option value="Health and Social Work">Health and Social Work</option>
                            <option value="Other Community, Social and Personal Activities">Other Community, Social and Personal Activities</option>
                            <option value="Extra-territorial Organization and Bodies">Extra-territorial Organization and Bodies</option>
                            <option value="Overseas Manpower Services">Overseas Manpower Services</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Place of Work *</label>
                        <input type="text" name="positions[${idx}][location]" class="peso-input" placeholder="e.g. Cagayan de Oro City" required>
                    </div>
                    <div class="col-md-4">
                        <label class="peso-label">Salary</label>
                        <input type="text" name="positions[${idx}][salary]" class="peso-input" placeholder="e.g. 15,000 / Negotiable">
                    </div>
                    <div class="col-md-2">
                        <label class="peso-label">Vacancy Count *</label>
                        <input type="number" name="positions[${idx}][slots]" class="peso-input" min="1" placeholder="e.g. 3" required>
                    </div>
                </div>

            <input type="hidden" name="positions[${idx}][deadline]" value="">

            <div class="mt-3 pt-2" style="border-top:1px dashed rgba(77,217,192,0.3);">
                <div style="font-size:11px;font-weight:700;color:#4dd9c0;margin-bottom:8px;">
                    IV. Qualification Requirements
                </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="peso-label">Work Experience (months)</label>
                            <input type="number" name="positions[${idx}][experience_months]" class="peso-input" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Religion</label>
                            <input type="text" name="positions[${idx}][religion]" class="peso-input" placeholder="e.g. Any">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Sex</label>
                            <select name="positions[${idx}][sex_preference]" class="peso-input">
                                <option value="Any" selected>No Preference</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Civil Status</label>
                            <select name="positions[${idx}][civil_status]" class="peso-input">
                                <option value="Any" selected>No Preference</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="peso-label">Other Qualifications</label>
                            <textarea name="positions[${idx}][other_qualifications]" class="peso-input" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Educational Level</label>
                            <select name="positions[${idx}][education_required]" class="peso-input">
                                <option value="">Any</option>
                                <option value="Elementary Level">Elementary Level</option>
                                <option value="Elementary Graduate">Elementary Graduate</option>
                                <option value="Junior High Level">Junior High Level</option>
                                <option value="Junior High Graduate">Junior High Graduate</option>
                                <option value="Senior High Level">Senior High Level</option>
                                <option value="Senior High Graduate">Senior High Graduate</option>
                                <option value="College Level">College Level</option>
                                <option value="College Graduate">College Graduate</option>
                                <option value="Graduate Studies">Graduate Studies</option>
                                <option value="TESDA Graduate">TESDA Graduate</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Course/Major</label>
                            <input type="text" name="positions[${idx}][course_major]" class="peso-input">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">License</label>
                            <input type="text" name="positions[${idx}][license]" class="peso-input">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Eligibility</label>
                            <input type="text" name="positions[${idx}][eligibility]" class="peso-input">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Certification</label>
                            <input type="text" name="positions[${idx}][certification]" class="peso-input">
                        </div>
                        <div class="col-md-6">
                            <label class="peso-label">Language/Dialect Spoken</label>
                            <input type="text" name="positions[${idx}][language]" class="peso-input">
                        </div>
                        <div class="col-12">
                            <label class="peso-label">Preferred Residence</label>
                            <input type="text" name="positions[${idx}][preferred_residence]" class="peso-input">
                        </div>
                        <div class="col-12">
                            <label class="peso-label">Accepts Disability?</label>
                            <div class="d-flex gap-3 mt-1 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input disability-yes" type="radio" name="positions[${idx}][accepts_disability]" value="yes">
                                    <label class="form-check-label" style="font-size:12px;">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input disability-no" type="radio" name="positions[${idx}][accepts_disability]" value="no" checked>
                                    <label class="form-check-label" style="font-size:12px;">No</label>
                                </div>
                            </div>
                            <div class="disability-types-wrap d-none gap-3 flex-wrap">
                                ${['Visual','Hearing','Speech','Physical','Others'].map(t => `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="positions[${idx}][disability_types][]" value="${t}">
                                    <label class="form-check-label" style="font-size:12px;">${t}</label>
                                </div>`).join('')}
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="peso-label">Accepts</label>
                            <div class="d-flex gap-3 flex-wrap mt-1">
                                ${['PESO','SPES','GIP','JobStart Philippines','K-12 AMP','TraBAJO'].map(p => `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="positions[${idx}][accepts_programs][]" value="${p}">
                                    <label class="form-check-label" style="font-size:12px;">${p}</label>
                                </div>`).join('')}
                            </div>
                        </div>
                    </div>
                </div>

                </div>`;
        }
            
        

        function addPosition() {
            const container = document.getElementById('positionsContainer');
            container.insertAdjacentHTML('beforeend', buildPositionRow(positionCount));
            positionCount++;
            updateRemoveButtons();
        }

        // ── Accepts Disability toggle (event delegation, para mo-work sa dynamic rows) ──
        document.getElementById('positionsContainer').addEventListener('change', function(e) {
            if (e.target.classList.contains('disability-yes') || e.target.classList.contains('disability-no')) {
                const wrap = e.target.closest('.position-row').querySelector('.disability-types-wrap');
                if (e.target.classList.contains('disability-yes') && e.target.checked) {
                    wrap.classList.remove('d-none');
                    wrap.classList.add('d-flex');
                } else if (e.target.classList.contains('disability-no') && e.target.checked) {
                    wrap.classList.remove('d-flex');
                    wrap.classList.add('d-none');
                }
            }
            // ── Job Image preview ──
            if (e.target.classList.contains('job-image-input')) {
                const previewId = e.target.getAttribute('data-preview');
                const previewDiv = document.getElementById(previewId);
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        previewDiv.querySelector('img').src = ev.target.result;
                        previewDiv.style.display = 'block';
                    };
                    reader.readAsDataURL(e.target.files[0]);
                } else {
                    previewDiv.style.display = 'none';
                }
            }
        });

        function updateRemoveButtons() {
            // Optional na ang positions — pwede matangtang tanan hangtod mahimong zero
        }

        function renumberPositions() {
            document.querySelectorAll('.pos-num').forEach((el, i) => { el.textContent = i + 1; });
        }

        document.getElementById('addPositionBtn').addEventListener('click', addPosition);

        document.getElementById('positionsContainer').addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-position-btn');
            if (btn && !btn.disabled) {
                btn.closest('.position-row').remove();
                renumberPositions();
                updateRemoveButtons();
            }
        });

        // Optional Job Vacancy Details — no auto add position

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
                            div.style = 'background:#fff5f5;color:#c62828;border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:12px;display:flex;align-items:center;gap:8px;';
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