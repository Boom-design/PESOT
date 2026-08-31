    @extends('jobseeker.layouts.app')

    @section('page-title', 'NSRP Registration Form')

    @section('content')

    {{-- Resume — only once there is an NSRP on file to build it from. Everything
         on the resume is an answer already given below, re-laid as a document
         they can hand to an employer. --}}
    @if($nsrp)
    <div class="peso-card fade-in mb-3">
        <div class="peso-card-body">
            <table style="width:100%;">
                <tr>
                    <td style="vertical-align:middle;">
                        <div class="fw-bold" style="font-size:13px;color:var(--g-700);">
                            <i class="ph-fill ph-file-text me-2" style="color:var(--g-600);"></i>My Resume
                        </div>
                        <div style="font-size:12px;color:var(--n-500);margin-top:2px;">
                            Built from the answers below — schooling, work history, training and skills.
                            Update this form and the resume follows.
                        </div>
                    </td>
                    <td style="vertical-align:middle;text-align:right;white-space:nowrap;padding-left:14px;">
                        <a href="{{ route('jobseeker.resume') }}" class="btn btn-sm fw-semibold"
                           style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:8px 16px;">
                            <i class="ph-fill ph-download-simple me-1"></i> Download Resume
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <div class="peso-card fade-in">
        <div class="peso-card-body">

            <form id="nsrpForm" action="{{ route('jobseeker.nsrp.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ══════════════════════════════════════════ --}}
                {{-- STEP 1: INTRO — Header, Instructions, OCR Upload --}}
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
                        @if($nsrp)
                            <div style="font-size:11px; color:var(--n-500);margin-top:6px;">Last updated: {{ $nsrp->updated_at->diffForHumans() }}</div>
                        @endif
                    </div>

                    <div class="d-flex align-items-start gap-2 p-3 mb-4 rounded-3"
                        style="background:var(--n-50); border:1px solid var(--n-200);">
                        <i class="ph-fill ph-info" style="color:var(--g-600); font-size:18px; margin-top:1px;"></i>
                        <div style="font-size:12px; color:var(--g-700); line-height:1.6;">
                            <strong>INSTRUCTIONS:</strong> Please fill out the form legibly in block letters. Check appropriate
                            boxes. Please do not leave any items unanswered. Indicate "NA" if not applicable.
                            All fields marked with <strong>*</strong> are required.
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                            <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="ph ph-user" style="color:#fff;font-size:13px;"></i>
                            </div>
                            <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">I. Personal Information</h6>
                        </div>
                        <div class="row g-3">
                            @php
                                $nameParts = explode(' ', $jobseeker->name ?? '', 2);
                                $guessFirstName = $nameParts[0] ?? '';
                                $guessLastName  = $nameParts[1] ?? '';

                                // Locked once answered — see App\Support\NsrpLocks. The
                                // controller refuses the change either way; these boxes
                                // only stop the jobseeker retyping something that will
                                // be thrown away on save.
                                $lockFirstName = \App\Support\NsrpLocks::identityLocked($registration ?? null, 'first_name');
                                $lockSuffix    = \App\Support\NsrpLocks::identityLocked($registration ?? null, 'suffix');
                                $lockBirth     = \App\Support\NsrpLocks::identityLocked($registration ?? null, 'date_of_birth');
                                $lockSex       = \App\Support\NsrpLocks::identityLocked($registration ?? null, 'sex');
                            @endphp
                            <div class="col-md-3">
                                <label class="peso-label">Surname *</label>
                                <input type="text" name="surname" id="surname_input" class="form-control peso-input"
                                    value="{{ old('surname', $registration->surname ?? $guessLastName) }}" required oninput="updateSignature()">
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">First Name *</label>
                                <input type="text" name="first_name" id="first_name_input" class="form-control peso-input"
                                    value="{{ old('first_name', $registration->first_name ?? $guessFirstName) }}" required oninput="updateSignature()"
                                    @if($lockFirstName) readonly style="background:var(--n-50);color:var(--n-500);cursor:not-allowed;" @endif>
                                @if($lockFirstName) <div style="font-size:10.5px;color:var(--n-500);margin-top:3px;"><i class="ph ph-lock-simple me-1"></i>Locked. Ask PESO staff to correct this.</div> @endif
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name_input" class="form-control peso-input"
                                    value="{{ old('middle_name', $registration->middle_name ?? $jobseeker->middle_name ?? '') }}" oninput="updateSignature()">
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Suffix</label>
                                <input type="text" name="suffix" id="suffix_input" class="form-control peso-input"
                                    value="{{ old('suffix', $registration->suffix ?? '') }}" placeholder="Jr., Sr., III" oninput="updateSignature()"
                                    @if($lockSuffix) readonly style="background:var(--n-50);color:var(--n-500);cursor:not-allowed;" @endif>
                                @if($lockSuffix) <div style="font-size:10.5px;color:var(--n-500);margin-top:3px;"><i class="ph ph-lock-simple me-1"></i>Locked. Ask PESO staff to correct this.</div> @endif
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Date of Birth *</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control peso-input"
                                    value="{{ old('date_of_birth', ($registration?->date_of_birth) ? \Carbon\Carbon::parse($registration->date_of_birth)->format('Y-m-d') : '') }}"
                                    max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                    onchange="computeAge()" required
                                    @if($lockBirth) readonly style="background:var(--n-50);color:var(--n-500);cursor:not-allowed;" @endif>
                                @if($lockBirth) <div style="font-size:10.5px;color:var(--n-500);margin-top:3px;"><i class="ph ph-lock-simple me-1"></i>Locked. Ask PESO staff to correct this.</div> @endif
                            </div>
                            <div class="col-md-2">
                                <label class="peso-label">Age *</label>
                                <input type="number" name="age" id="age_field" class="form-control peso-input"
                                    value="{{ old('age', $registration->age ?? '') }}" required min="1" readonly
                                    style="background:var(--n-50);cursor:not-allowed;">
                            </div>
                            <div class="col-md-2">
                                <label class="peso-label">Sex *</label>
                                {{-- A disabled select posts nothing, so a locked one
                                     carries the stored answer in a hidden field and the
                                     required rule still passes. --}}
                                @if($lockSex)
                                    <input type="hidden" name="sex" value="{{ $registration->sex }}">
                                @endif
                                <select @if(!$lockSex) name="sex" @endif class="form-select peso-input" required
                                    @if($lockSex) disabled style="background:var(--n-50);color:var(--n-500);cursor:not-allowed;" @endif>
                                    <option value="">Select</option>
                                    <option value="Male"   {{ old('sex', $registration->sex ?? '') === 'Male'   ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('sex', $registration->sex ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @if($lockSex) <div style="font-size:10.5px;color:var(--n-500);margin-top:3px;"><i class="ph ph-lock-simple me-1"></i>Locked. Ask PESO staff to correct this.</div> @endif
                            </div>
                            <div class="col-md-2">
                                <label class="peso-label">Civil Status *</label>
                                <select name="civil_status" class="form-select peso-input" required>
                                    <option value="">Select</option>
                                    <option value="Single"  {{ old('civil_status', $registration->civil_status ?? '') === 'Single'  ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ old('civil_status', $registration->civil_status ?? '') === 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Widowed" {{ old('civil_status', $registration->civil_status ?? '') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Religion</label>
                                <input type="text" name="religion" class="form-control peso-input"
                                    value="{{ old('religion', $registration->religion ?? '') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="peso-label">TIN</label>
                                <input type="text" name="tin" class="form-control peso-input"
                                    value="{{ old('tin', $registration->tin ?? '') }}" placeholder="Optional">
                            </div>
                            <div class="col-md-2">
                                <label class="peso-label">Height (ft.)</label>
                                <input type="text" name="height" class="form-control peso-input"
                                    value="{{ old('height', $registration->height ?? '') }}" placeholder="e.g. 5.6">
                            </div>
                            <div class="col-md-2">
                                <label class="peso-label">Weight (kg.)</label>
                                <input type="text" name="weight" class="form-control peso-input"
                                    value="{{ old('weight', $registration->weight ?? '') }}" placeholder="e.g. 60">
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Contact Number/s *</label>
                                <input type="text" name="contact_number" class="form-control peso-input"
                                    inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                                    placeholder="09171234567"
                                    value="{{ old('contact_number', $registration->contact_number ?? $jobseeker->phone ?? '') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="peso-label">Email</label>
                                <input type="email" name="reg_email" class="form-control peso-input"
                                    value="{{ old('reg_email', $registration->reg_email ?? $jobseeker->email ?? '') }}">
                            </div>

                            <div class="col-12">
                                <label class="peso-label">Disability (check if applicable)</label>
                                @php
                                    $disabilities = old('disabilities', $registration ? (is_array($registration->disabilities) ? $registration->disabilities : json_decode($registration->disabilities ?? '[]', true)) : []);
                                @endphp
                                <div class="d-flex align-items-center gap-3 flex-wrap mt-1">
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
                                    <input type="text" name="disability_other" class="form-control peso-input"
                                        style="flex:1;min-width:180px;max-width:260px;"
                                        placeholder="If Others, please specify"
                                        value="{{ $registration->disability_other ?? '' }}">
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
                                <input type="text" name="perm_house_street" id="perm_house_street" class="form-control peso-input"
                                    value="{{ $registration->perm_house_street ?? '' }}">
                            </div>

                            <div class="col-12 mt-3">
                                <div style="font-size:12px;font-weight:700;color:var(--g-700);margin-bottom:8px;">
                                    <i class="ph ph-map-pin me-1" style="color:var(--g-600);"></i> Present Address
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="same_as_permanent"
                                        id="same_as_permanent" value="1"
                                        {{ ($registration->same_as_permanent ?? false) ? 'checked' : '' }}
                                        onchange="toggleSameAsPermanent()">
                                    <label class="form-check-label" for="same_as_permanent"
                                        style="font-size:12px;color:var(--g-700);">
                                        Same as Permanent Address
                                    </label>
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
                                <input type="text" name="house_street" id="present_house_street" class="form-control peso-input"
                                    value="{{ $registration->house_street ?? '' }}" required>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ══════════════════════════════════════════ --}}
                {{-- STEP 2: II. JOB PREFERENCE --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="nsrp-step" data-step="2" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-magnifying-glass" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">II. Job Preference</h6>
                    </div>
                    @php
                        $prefOccupations  = $nsrp ? (is_array($nsrp->preferred_occupations)  ? $nsrp->preferred_occupations  : json_decode($nsrp->preferred_occupations  ?? '[]', true)) : [];
                        $localLocations   = $nsrp ? (is_array($nsrp->local_locations)         ? $nsrp->local_locations         : json_decode($nsrp->local_locations         ?? '[]', true)) : [];
                        $overseasLocations= $nsrp ? (is_array($nsrp->overseas_locations)      ? $nsrp->overseas_locations      : json_decode($nsrp->overseas_locations      ?? '[]', true)) : [];
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="peso-label">Work Type *</label>
                            <select name="work_type" class="form-select peso-input" required>
                                <option value="">Select</option>
                                <option value="part_time" {{ ($nsrp->work_type ?? '') === 'part_time' ? 'selected' : '' }}>Part-time</option>
                                <option value="full_time" {{ ($nsrp->work_type ?? '') === 'full_time' ? 'selected' : '' }}>Full-time</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="peso-label">Preferred Occupation *</label>
                            {{-- Three boxes to start with, which is what the paper form
                                 gives. Nobody's list of trades stops at three though, so
                                 the button below adds as many more as they need. Blank
                                 rows are dropped on save. --}}
                            <div class="row g-2 mt-1" id="prefOccupationRows">
                                @php $prefCount = max(3, count($prefOccupations)); @endphp
                                @for($i = 0; $i < $prefCount; $i++)
                                <div class="col-md-4">
                                    <input type="text" name="preferred_occupations[]" class="form-control peso-input"
                                        placeholder="{{ $i + 1 }}."
                                        value="{{ $prefOccupations[$i] ?? '' }}"
                                        {{ $i === 0 ? 'required' : '' }}>
                                </div>
                                @endfor
                            </div>
                            <button type="button" class="btn btn-sm fw-semibold mt-2"
                                style="border:1px solid var(--g-600);color:var(--g-600);background:#fff;border-radius:8px;font-size:12px;padding:5px 14px;"
                                onclick="addPreferredOccupation()">
                                <i class="ph ph-plus me-1"></i> Add another occupation
                            </button>
                        </div>

                        <div class="col-12">
                            <label class="peso-label">Preferred Work Location — Local (specify cities/municipalities)</label>
                            <div class="row g-2 mt-1">
                                @for($i = 0; $i < 3; $i++)
                                <div class="col-md-4">
                                    <input type="text" name="local_locations[]" class="form-control peso-input"
                                        placeholder="{{ $i + 1 }}."
                                        value="{{ $localLocations[$i] ?? '' }}">
                                </div>
                                @endfor
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="peso-label">Preferred Work Location — Overseas (specify countries)</label>
                            <div class="row g-2 mt-1">
                                @for($i = 0; $i < 3; $i++)
                                <div class="col-md-4">
                                    <input type="text" name="overseas_locations[]" class="form-control peso-input"
                                        placeholder="{{ $i + 1 }}."
                                        value="{{ $overseasLocations[$i] ?? '' }}">
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════ --}}
                {{-- STEP 3: III. LANGUAGE / DIALECT PROFICIENCY --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="nsrp-step" data-step="3" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-translate" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">III. Language / Dialect Proficiency</h6>
                    </div>
                    @php
                        $langProf = $nsrp ? (is_array($nsrp->language_proficiency) ? $nsrp->language_proficiency : json_decode($nsrp->language_proficiency ?? '{}', true)) : [];
                        $languages = ['English', 'Filipino', 'Mandarin'];
                        $otherLangs = $nsrp ? (is_array($nsrp->other_language) ? $nsrp->other_language : (json_decode($nsrp->other_language ?? '[]', true) ?: ($nsrp->other_language ? [$nsrp->other_language] : []))) : [];
                        if (empty($otherLangs)) $otherLangs = [''];
                    @endphp
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
                                @php $lData = $langProf[$lang] ?? []; @endphp
                                <tr>
                                    <td style="padding:8px 12px;font-weight:600;color:var(--g-700);">{{ $lang }}</td>
                                    @foreach(['read','write','speak','understand'] as $skill)
                                    <td style="padding:8px 12px;text-align:center;">
                                        <input class="form-check-input" type="checkbox"
                                            name="language_proficiency[{{ $lang }}][{{ $skill }}]"
                                            value="1"
                                            {{ !empty($lData[$skill]) ? 'checked' : '' }}>
                                    </td>
                                    @endforeach
                                    <td></td>
                                </tr>
                                @endforeach
                                @foreach($otherLangs as $idx => $otherLangVal)
                                @php $lData = $langProf['other'][$idx] ?? []; @endphp
                                <tr class="other-lang-row">
                                    <td style="padding:8px 12px;">
                                        <input type="text" name="other_language[]" class="form-control peso-input"
                                            style="font-size:12px;"
                                            placeholder="Others:"
                                            value="{{ $otherLangVal }}">
                                    </td>
                                    @foreach(['read','write','speak','understand'] as $skill)
                                    <td style="padding:8px 12px;text-align:center;">
                                        <input class="form-check-input" type="checkbox"
                                            name="language_proficiency[other][{{ $idx }}][{{ $skill }}]"
                                            value="1"
                                            {{ !empty($lData[$skill]) ? 'checked' : '' }}>
                                    </td>
                                    @endforeach
                                    <td style="padding:8px 12px;">
                                        @if($idx > 0)
                                        <button type="button" class="btn btn-sm text-danger" onclick="this.closest('tr').remove()">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
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
                {{-- STEP 4: IV. EDUCATIONAL BACKGROUND --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="nsrp-step" data-step="4" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-graduation-cap" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">IV. Educational Background</h6>
                    </div>
                    @php
                        $education = $nsrp ? (is_array($nsrp->education) ? $nsrp->education : json_decode($nsrp->education ?? '{}', true)) : [];
                        $eduLevels = ['Elementary','Junior High School','Senior High School','Tertiary / College','Graduate Studies/Post-graduate/Masters'];

                        $shs_strands = [
                            'ABM - Accountancy, Business and Management',
                            'HUMSS - Humanities and Social Sciences',
                            'STEM - Science, Technology, Engineering and Mathematics',
                            'GAS - General Academic Strand',
                            'TVL - Technical-Vocational-Livelihood',
                            'Sports Track',
                            'Arts and Design Track',
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
                                <input class="form-check-input" type="radio" name="currently_in_school"
                                    value="1" id="inschool_yes"
                                    {{ ($nsrp->currently_in_school ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="inschool_yes" style="font-size:12px;color:var(--g-700);">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="currently_in_school"
                                    value="0" id="inschool_no"
                                    {{ !($nsrp->currently_in_school ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="inschool_no" style="font-size:12px;color:var(--g-700);">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="min-width:0;">
                        {{-- School names are the longest thing anyone types on this form
                             ("Cugman National High School"), so the column is given the
                             largest share and the table is allowed to be wider than the
                             card. table-responsive scrolls it on a narrow screen rather
                             than squeezing the box down to a few unreadable letters. --}}
                        <table class="table" style="font-size:12px;min-width:1040px;table-layout:fixed;">
                            <colgroup>
                                <col style="width:16%;">
                                <col style="width:24%;">
                                <col style="width:19%;">
                                <col style="width:18%;">
                                <col style="width:12%;">
                                <col style="width:11%;">
                            </colgroup>
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
                                    $eduData = $education[$level] ?? [];
                                    $isElem    = $level === 'Elementary';
                                    $isJHS     = $level === 'Junior High School';
                                    $isSHS     = $level === 'Senior High School';
                                    $isCollege = $level === 'Tertiary / College';
                                    $isGrad    = $level === 'Graduate Studies/Post-graduate/Masters';

                                    if ($isElem)        $yearGradOptions = $elem_year_grad;
                                    elseif ($isJHS)     $yearGradOptions = $jhs_year_grad;
                                    elseif ($isSHS)     $yearGradOptions = $shs_year_grad;
                                    elseif ($isCollege) $yearGradOptions = $college_year_grad;
                                    else                $yearGradOptions = $grad_year_grad;

                                    // A school already finished cannot be unfinished, so a
                                    // level closes as soon as it holds an answer. In practice
                                    // that leaves the masters row open — somebody who has just
                                    // finished one must be able to add it — until they use it.
                                    // App\Support\NsrpLocks is what actually refuses the write.
                                    $eduLocked = \App\Support\NsrpLocks::educationLocked($lockedEducation ?? [], $level);
                                @endphp
                                <tr>
                                    <td style="padding:8px 12px;font-weight:600;color:var(--g-700);line-height:1.35;">
                                        {{ $level }}
                                        @if($eduLocked)
                                            <div style="font-size:10px;font-weight:500;color:var(--n-500);margin-top:2px;">
                                                <i class="ph ph-lock-simple me-1"></i>Locked
                                            </div>
                                        @endif
                                    </td>

                                    <td style="padding:6px 8px;">
                                        <input type="text" name="education[{{ $level }}][school_name]"
                                            class="form-control peso-input" style="font-size:12px;{{ $eduLocked ? 'background:var(--n-50);color:var(--n-500);cursor:not-allowed;' : '' }}"
                                            placeholder="Full name of school"
                                            title="{{ $eduData['school_name'] ?? '' }}"
                                            value="{{ $eduData['school_name'] ?? '' }}"
                                            @if($eduLocked) readonly @endif>
                                    </td>

                                    <td style="padding:6px 8px;">
                                        @if($isElem || $isJHS)
                                            <input type="hidden" name="education[{{ $level }}][course]" value="N/A">
                                            <input type="text" class="form-control peso-input" style="font-size:12px;background:var(--n-50);color:var(--n-400);cursor:not-allowed;" value="N/A" disabled>
                                        @elseif($isSHS)
                                            <select name="education[{{ $level }}][course]" class="form-select peso-input"
                                                style="font-size:12px;{{ $eduLocked ? 'background:var(--n-50);color:var(--n-500);cursor:not-allowed;' : '' }}"
                                                @if($eduLocked) disabled @endif>
                                                <option value="">Select Strand</option>
                                                @foreach($shs_strands as $strand)
                                                    <option value="{{ $strand }}" {{ ($eduData['course'] ?? '') === $strand ? 'selected' : '' }}>{{ $strand }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($isCollege)
                                            <select name="education[{{ $level }}][course]" id="college_course_select" class="form-select peso-input"
                                                style="font-size:12px;{{ $eduLocked ? 'background:var(--n-50);color:var(--n-500);cursor:not-allowed;' : '' }}"
                                                onchange="toggleOtherCourse(this)" @if($eduLocked) disabled @endif>
                                                <option value="">Select Course</option>
                                                @foreach($college_courses as $course)
                                                    <option value="{{ $course }}" {{ ($eduData['course'] ?? '') === $course ? 'selected' : '' }}>{{ $course }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" id="college_course_other" name="education[{{ $level }}][course_other]"
                                                class="form-control peso-input mt-1" style="font-size:12px;display:none;"
                                                placeholder="Please specify course"
                                                value="{{ $eduData['course_other'] ?? '' }}"
                                                @if($eduLocked) readonly @endif>
                                        @else
                                            <input type="text" name="education[{{ $level }}][course]"
                                                class="form-control peso-input" style="font-size:12px;{{ $eduLocked ? 'background:var(--n-50);color:var(--n-500);cursor:not-allowed;' : '' }}"
                                                placeholder="e.g. Master of Business Administration"
                                                value="{{ $eduData['course'] ?? '' }}"
                                                @if($eduLocked) readonly @endif>
                                        @endif
                                    </td>

                                    <td style="padding:6px 8px;">
                                        <select name="education[{{ $level }}][year_graduated]" class="form-select peso-input"
                                            style="font-size:12px;{{ $eduLocked ? 'background:var(--n-50);color:var(--n-500);cursor:not-allowed;' : '' }}"
                                            @if($eduLocked) disabled @endif>
                                            <option value="">Select</option>
                                            @foreach($yearGradOptions as $opt)
                                                <option value="{{ $opt }}" {{ ($eduData['year_graduated'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td style="padding:6px 8px;">
                                        <input type="text" name="education[{{ $level }}][level_reached]"
                                            class="form-control peso-input" style="font-size:12px;{{ $eduLocked ? 'background:var(--n-50);color:var(--n-500);cursor:not-allowed;' : '' }}"
                                            value="{{ $eduData['level_reached'] ?? '' }}"
                                            @if($eduLocked) readonly @endif>
                                    </td>

                                    <td style="padding:6px 8px;">
                                        <input type="text" name="education[{{ $level }}][year_last_attended]"
                                            class="form-control peso-input" style="font-size:12px;{{ $eduLocked ? 'background:var(--n-50);color:var(--n-500);cursor:not-allowed;' : '' }}"
                                            value="{{ $eduData['year_last_attended'] ?? '' }}"
                                            placeholder="yyyy"
                                            @if($eduLocked) readonly @endif>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════ --}}
                {{-- STEP 5: V. TECHNICAL/VOCATIONAL TRAINING --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="nsrp-step" data-step="5" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-notebook" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">V. Technical/Vocational and Other Training</h6>
                    </div>
                    @php
                        $trainings = $nsrp ? (is_array($nsrp->trainings) ? $nsrp->trainings : json_decode($nsrp->trainings ?? '[]', true)) : [];
                        $trainingCount = max(count($trainings), 3);
                        $certs = $nsrp ? (is_array($nsrp->training_certificates) ? $nsrp->training_certificates : json_decode($nsrp->training_certificates ?? '[]', true)) : [];
                    @endphp
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
                                @for($i = 0; $i < $trainingCount; $i++)
                                @php $t = $trainings[$i] ?? []; @endphp
                                <tr>
                                    <td style="padding:8px 12px;color:var(--n-500);">{{ $i + 1 }}</td>
                                    <td style="padding:6px 8px;">
                                        <input type="text" name="trainings[{{ $i }}][course]"
                                            class="form-control peso-input" style="font-size:12px;"
                                            value="{{ $t['course'] ?? '' }}">
                                    </td>
                                    <td style="padding:6px 8px;">
                                        <input type="text" name="trainings[{{ $i }}][hours]"
                                            class="form-control peso-input" style="font-size:12px;"
                                            placeholder="e.g. 40"
                                            value="{{ $t['hours'] ?? '' }}">
                                    </td>
                                    <td style="padding:6px 8px;">
                                        <input type="text" name="trainings[{{ $i }}][duration]"
                                            class="form-control peso-input" style="font-size:12px;"
                                            placeholder="01/2023 to 03/2023"
                                            value="{{ $t['duration'] ?? '' }}">
                                    </td>
                                    <td style="padding:6px 8px;">
                                        <input type="text" name="trainings[{{ $i }}][institution]"
                                            class="form-control peso-input" style="font-size:12px;"
                                            value="{{ $t['institution'] ?? '' }}">
                                    </td>
                                    <td style="padding:6px 8px;">
                                        @php $existingCert = $certs[$i] ?? null; @endphp
                                        @if($existingCert)
                                            <div class="mb-1">
                                                <a href="{{ route('documents.certificate', [$nsrp->jobseeker_nsrp_registrations_id, $i]) }}"
                                                target="_blank"
                                                style="font-size:11px;color:var(--g-700);">
                                                    <i class="ph ph-file-text me-1"></i>View File
                                                </a>
                                            </div>
                                        @endif
                                        <input type="file" name="training_certificates[{{ $i }}]"
                                            class="form-control peso-input" style="font-size:11px;"
                                            accept=".jpg,.jpeg,.png,.pdf">
                                        <div style="font-size:10px;color:var(--n-500);margin-top:4px;">
                                            JPG, PNG, PDF only
                                        </div>
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
                {{-- STEP 6: VI. ELIGIBILITY / PROFESSIONAL LICENSE --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="nsrp-step" data-step="6" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-medal" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">VI. Eligibility / Professional License</h6>
                    </div>
                    @php
                        $eligibilities = $nsrp ? (is_array($nsrp->eligibilities) ? $nsrp->eligibilities : json_decode($nsrp->eligibilities ?? '[]', true)) : [];
                        $licenses      = $nsrp ? (is_array($nsrp->licenses)      ? $nsrp->licenses      : json_decode($nsrp->licenses      ?? '[]', true)) : [];
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div style="font-size:12px;font-weight:700;color:var(--g-700);margin-bottom:8px;">
                                Eligibility (Civil Service)
                            </div>
                            @for($i = 0; $i < 2; $i++)
                            @php $e = $eligibilities[$i] ?? []; @endphp
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm-7">
                                    <input type="text" name="eligibilities[{{ $i }}][name]"
                                        class="form-control peso-input" style="font-size:12px;"
                                        placeholder="{{ $i + 1 }}. Eligibility"
                                        value="{{ $e['name'] ?? '' }}">
                                </div>
                                <div class="col-12 col-sm-5">
                                    <input type="date" name="eligibilities[{{ $i }}][date_taken]"
                                        class="form-control peso-input" style="font-size:12px;"
                                        value="{{ $e['date_taken'] ?? '' }}">
                                </div>
                            </div>
                            @endfor
                        </div>
                        <div class="col-md-6">
                            <div style="font-size:12px;font-weight:700;color:var(--g-700);margin-bottom:8px;">
                                Professional License (PRC)
                            </div>
                            @for($i = 0; $i < 2; $i++)
                            @php $l = $licenses[$i] ?? []; @endphp
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm-7">
                                    <input type="text" name="licenses[{{ $i }}][name]"
                                        class="form-control peso-input" style="font-size:12px;"
                                        placeholder="{{ $i + 1 }}. License"
                                        value="{{ $l['name'] ?? '' }}">
                                </div>
                                <div class="col-12 col-sm-5">
                                    <input type="date" name="licenses[{{ $i }}][valid_until]"
                                        class="form-control peso-input" style="font-size:12px;"
                                        value="{{ $l['valid_until'] ?? '' }}">
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════ --}}
                {{-- STEP 7: EMPLOYMENT STATUS / TYPE + VII. WORK EXPERIENCE --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="nsrp-step" data-step="7" style="display:none;">

                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                            <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="ph ph-briefcase" style="color:#fff;font-size:13px;"></i>
                            </div>
                            <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">Employment Status / Type</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="peso-label">Classification *</label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="classification_type"
                                            id="type_local" value="local"
                                            {{ ($nsrp->type ?? 'local') === 'local' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="type_local" style="font-size:12px;color:var(--g-700);">Local</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="classification_type"
                                            id="type_overseas" value="overseas"
                                            {{ ($nsrp->type ?? '') === 'overseas' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_overseas" style="font-size:12px;color:var(--g-700);">Overseas</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="classification_type"
                                            id="type_both" value="both"
                                            {{ ($nsrp->type ?? '') === 'both' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_both" style="font-size:12px;color:var(--g-700);">Both (Local & Overseas)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="peso-label">Employment Type *</label>
                                <select name="employment_type" id="employmentType" class="form-select peso-input" required
                                        onchange="toggleEmploymentFields()">
                                    <option value="">Select</option>
                                    <option value="employed"   {{ ($nsrp->employment_type ?? '') === 'employed'   ? 'selected' : '' }}>Employed</option>
                                    <option value="unemployed" {{ ($nsrp->employment_type ?? '') === 'unemployed' ? 'selected' : '' }}>Unemployed</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="monthsLookingField" style="display:none;">
                                <label class="peso-label">How long have you been looking for work? (months)</label>
                                <input type="number" name="months_looking" class="form-control peso-input"
                                    value="{{ $nsrp->months_looking ?? '' }}" min="0">
                            </div>

                            <div class="col-12" id="employedFields" style="display:none;">
                                <label class="peso-label">Type of Employment</label>
                                <div class="d-flex gap-3 flex-wrap mt-1">
                                    @foreach([
                                        'wage_employed'   => 'Wage Employed',
                                        'self_employed'   => 'Self-Employed (please specify)',
                                    ] as $val => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                            name="employed_sub_type" value="{{ $val }}"
                                            id="emp_{{ $val }}"
                                            {{ ($nsrp->employed_sub_type ?? '') === $val ? 'checked' : '' }}
                                            onchange="toggleSelfEmployed()">
                                        <label class="form-check-label" for="emp_{{ $val }}"
                                            style="font-size:12px;color:var(--g-700);">{{ $label }}</label>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="mt-2" id="selfEmployedSpecify" style="display:none;">
                                    <input type="text" name="self_employed_specify" class="form-control peso-input"
                                        style="max-width:400px;"
                                        placeholder="Fisherman/Fisherfolk, Vendor/Retailer, Home-based worker, etc."
                                        value="{{ $nsrp->self_employed_specify ?? '' }}">
                                </div>
                            </div>

                            <div class="col-12" id="unemployedFields" style="display:none;">
                                <div class="row g-3">
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
                                                <input class="form-check-input" type="radio"
                                                    name="unemployed_reason" value="{{ $val }}"
                                                    id="unemp_{{ $val }}"
                                                    {{ ($nsrp->unemployed_reason ?? '') === $val ? 'checked' : '' }}
                                                    onchange="toggleUnemployedOther()">
                                                <label class="form-check-label" for="unemp_{{ $val }}"
                                                    style="font-size:12px;color:var(--g-700);">{{ $label }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="terminatedAbroadCountry" style="display:none;">
                                        <label class="peso-label">Specify country (if terminated abroad)</label>
                                        <input type="text" name="terminated_abroad_country" class="form-control peso-input"
                                            value="{{ $nsrp->terminated_abroad_country ?? '' }}">
                                    </div>
                                    <div class="col-md-4" id="unemployedOtherField" style="display:none;">
                                        <label class="peso-label">Others, please specify</label>
                                        <input type="text" name="unemployed_other" class="form-control peso-input"
                                            value="{{ $nsrp->unemployed_other ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="peso-label">Are you an OFW?</label>
                                <select name="is_ofw" class="form-select peso-input" onchange="toggleOfwFields()">
                                    <option value="0" {{ !($nsrp->is_ofw ?? false) ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ ($nsrp->is_ofw  ?? false) ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="ofwCountryField" style="display:none;">
                                <label class="peso-label">Specify country</label>
                                <input type="text" name="ofw_country" class="form-control peso-input"
                                    value="{{ $nsrp->ofw_country ?? '' }}">
                            </div>

                            <div class="col-md-3">
                                <label class="peso-label">Are you a former OFW?</label>
                                <select name="is_former_ofw" class="form-select peso-input" onchange="toggleFormerOfwFields()">
                                    <option value="0" {{ !($nsrp->is_former_ofw ?? false) ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ ($nsrp->is_former_ofw ?? false) ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-12" id="formerOfwFields" style="display:none;">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="peso-label">Latest country of deployment</label>
                                        <input type="text" name="latest_deployment_country" class="form-control peso-input"
                                            value="{{ $nsrp->latest_deployment_country ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="peso-label">Month and year of return</label>
                                        <input type="text" name="return_month" class="form-control peso-input"
                                            placeholder="e.g. 01/2024"
                                            value="{{ $nsrp->return_month ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="peso-label">4Ps Beneficiary?</label>
                                <select name="is_4ps" class="form-select peso-input">
                                    <option value="0" {{ !($nsrp->is_4ps ?? false) ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ ($nsrp->is_4ps  ?? false) ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="peso-label">Household ID (if 4Ps)</label>
                                <input type="text" name="household_id" class="form-control peso-input"
                                    value="{{ $nsrp->household_id ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                        <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-buildings" style="color:#fff;font-size:13px;"></i>
                        </div>
                        <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">VII. Work Experience</h6>
                    </div>
                    <p style="font-size:11px;color:var(--n-500);">Limit to 10 year period, start with the most recent employment.</p>

                    @php $workExps = $nsrp ? $nsrp->workExperiences : collect([]); @endphp

                    <div id="workExpContainer">
                        @if($workExps->count() > 0)
                            @foreach($workExps as $wi => $exp)
                            <div class="work-exp-row p-3 mb-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-0);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div style="font-size:12px;font-weight:700;color:var(--g-700);">
                                        Work Experience #<span class="exp-num">{{ $wi + 1 }}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        style="border-radius:8px;font-size:11px;"
                                        onclick="removeWorkExp(this)" {{ $wi === 0 ? 'disabled' : '' }}>
                                        <i class="ph-fill ph-trash"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="peso-label">Company Name *</label>
                                        <input type="text" name="work_experiences[{{ $wi }}][company_name]"
                                            class="form-control peso-input" value="{{ $exp->company_name }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="peso-label">Address (City/Municipality)</label>
                                        <input type="text" name="work_experiences[{{ $wi }}][industry]"
                                            class="form-control peso-input" value="{{ $exp->industry ?? '' }}"
                                            placeholder="e.g. Cagayan de Oro City">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="peso-label">Position *</label>
                                        <input type="text" name="work_experiences[{{ $wi }}][position]"
                                            class="form-control peso-input" value="{{ $exp->position }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="peso-label">Date From (mm/yyyy)</label>
                                        <input type="text" name="work_experiences[{{ $wi }}][date_from]"
                                            class="form-control peso-input" placeholder="e.g. 01/2020"
                                            value="{{ $exp->date_from ?? '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="peso-label">Date To (mm/yyyy)</label>
                                        <input type="text" name="work_experiences[{{ $wi }}][date_to]"
                                            class="form-control peso-input" id="dateTo_{{ $wi }}"
                                            placeholder="e.g. 12/2022"
                                            value="{{ $exp->is_current ? 'present' : ($exp->date_to ?? '') }}"
                                            {{ $exp->is_current ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="peso-label">Status</label>
                                        <select name="work_experiences[{{ $wi }}][employment_status]"
                                            class="form-select peso-input">
                                            <option value="">Select</option>
                                            @foreach(['Permanent','Contractual','Part-time','Probationary'] as $es)
                                            <option value="{{ $es }}" {{ ($exp->employment_status ?? '') === $es ? 'selected' : '' }}>
                                                {{ $es }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input current-job-check" type="checkbox"
                                                name="work_experiences[{{ $wi }}][is_current]"
                                                value="1" id="current_{{ $wi }}"
                                                {{ $exp->is_current ? 'checked' : '' }}
                                                onchange="toggleCurrentJob(this, {{ $wi }})">
                                            <label class="form-check-label" for="current_{{ $wi }}"
                                                style="font-size:12px;color:var(--g-700);">Currently working here</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="work-exp-row p-3 mb-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-0);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div style="font-size:12px;font-weight:700;color:var(--g-700);">
                                        Work Experience #<span class="exp-num">1</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        style="border-radius:8px;font-size:11px;" disabled>
                                        <i class="ph-fill ph-trash"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="peso-label">Company Name</label>
                                        <input type="text" name="work_experiences[0][company_name]"
                                            class="form-control peso-input" placeholder="e.g. ABC Company">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="peso-label">Address (City/Municipality)</label>
                                        <input type="text" name="work_experiences[0][industry]"
                                            class="form-control peso-input" placeholder="e.g. Cagayan de Oro City">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="peso-label">Position</label>
                                        <input type="text" name="work_experiences[0][position]"
                                            class="form-control peso-input" placeholder="e.g. Sales Associate">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="peso-label">Date From (mm/yyyy)</label>
                                        <input type="text" name="work_experiences[0][date_from]"
                                            class="form-control peso-input" placeholder="e.g. 01/2020">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="peso-label">Date To (mm/yyyy)</label>
                                        <input type="text" name="work_experiences[0][date_to]"
                                            class="form-control peso-input" id="dateTo_0"
                                            placeholder="e.g. 12/2022">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="peso-label">Status</label>
                                        <select name="work_experiences[0][employment_status]"
                                            class="form-select peso-input">
                                            <option value="">Select</option>
                                            @foreach(['Permanent','Contractual','Part-time','Probationary'] as $es)
                                            <option value="{{ $es }}">{{ $es }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input current-job-check" type="checkbox"
                                                name="work_experiences[0][is_current]"
                                                value="1" id="current_0"
                                                onchange="toggleCurrentJob(this, 0)">
                                            <label class="form-check-label" for="current_0"
                                                style="font-size:12px;color:var(--g-700);">Currently working here</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
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
                {{-- STEP 8: VIII. OTHER SKILLS + CERTIFICATION + SUBMIT --}}
                {{-- ══════════════════════════════════════════ --}}
                <div class="nsrp-step" data-step="8" style="display:none;">

                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid var(--n-200);">
                            <div style="width:28px;height:28px;background:var(--g-600);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="ph ph-wrench" style="color:#fff;font-size:13px;"></i>
                            </div>
                            <h6 style="margin:0;font-size:13px;font-weight:700;color:var(--g-700);">VIII. Other Skills Acquired Without Certificate</h6>
                        </div>
                        @php
                            $skills    = $nsrp ? (is_array($nsrp->other_skills) ? $nsrp->other_skills : json_decode($nsrp->other_skills ?? '[]', true)) : [];
                            $allSkills = ['Auto Mechanic','Beautician','Carpentry Work','Computer Literate','Domestic Chores','Driver','Electrician','Embroidery','Gardening','Masonry','Painter/Artist','Painting Jobs','Photography','Plumbing','Sewing Dresses','Stenography','Tailoring'];
                        @endphp
                        <div class="row g-2">
                            @foreach($allSkills as $skill)
                            <div class="col-md-3 col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="other_skills[]" value="{{ $skill }}"
                                        id="skill_{{ Str::slug($skill) }}"
                                        {{ in_array($skill, $skills) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="skill_{{ Str::slug($skill) }}"
                                        style="font-size:12px;color:var(--g-700);">{{ $skill }}</label>
                                </div>
                            </div>
                            @endforeach
                            <div class="col-md-6 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span style="font-size:12px;color:var(--g-700);font-weight:600;">Others:</span>
                                    <input type="text" name="other_skills_specify" class="form-control peso-input"
                                        style="font-size:12px;"
                                        placeholder="Please specify"
                                        value="{{ $nsrp->other_skills_specify ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 mb-4" style="background:var(--n-50); border:1px solid var(--n-200);">
                        <div style="font-size:12px;color:var(--g-700);line-height:1.6;">
                            <strong>Certification/Authorization:</strong> This is to certify that all data/information
                            that I have provided in this form are true to the best of my knowledge. This is also to
                            authorize DOLE to include my profile in the PESO Employment Information System and use
                            my personal information for employment facilitation. I am also aware that DOLE is not
                            obliged to seek employment on my behalf.
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="peso-label">Signature of Applicant</label>
                                <div id="signature_display" class="form-control peso-input"
                                    style="background:#fff;font-style:italic;color:var(--g-700);font-weight:600;">
                                    &nbsp;
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="peso-label">Date Signed</label>
                                {{-- Read-only, and not posted at all: the server stamps the date
                                     the form was actually submitted. A signature date the signer
                                     can pick is not a signature date. --}}
                                <div class="form-control peso-input"
                                    style="background:#fff;color:var(--g-700);font-weight:600;">
                                    {{ today()->format('F j, Y') }}
                                </div>
                                <div style="font-size:11px;color:var(--n-400);margin-top:4px;">
                                    Filled in automatically on submit.
                                </div>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="certification_agreed"
                                value="1" id="certAgree" required
                                {{ ($nsrp->certification_agreed ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="certAgree"
                                style="font-size:12px;color:var(--g-700);font-weight:600;">
                                I agree to the certification and authorization above *
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-peso px-4" onclick="debugSubmit(event, this)">
                            <i class="ph ph-paper-plane-tilt me-1"></i>
                            {{ $registration ? 'Update NSRP Form' : 'Submit NSRP Form' }}
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
        <span id="nsrpStepInfo" style="font-size:13px;color:var(--g-700);font-weight:600;white-space:nowrap;">Step 1 of 8</span>
        <button type="button" id="nsrpStepNext" class="btn btn-sm fw-semibold"
            style="border:none;border-radius:8px;color:#fff;padding:6px 14px;background:var(--g-600);">
            Next <i class="ph ph-caret-right ms-1"></i>
        </button>
    </div>

    @section('scripts')
    <script>
    let workExpCount = {{ $nsrp && $nsrp->workExperiences->count() > 0 ? $nsrp->workExperiences->count() : 1 }};
    let trainingCount = {{ $trainingCount }};
    let otherLangCount = {{ count($otherLangs) }};

    // ── PREFERRED OCCUPATION — as many as the jobseeker needs. ──
    function addPreferredOccupation() {
        const rows = document.getElementById('prefOccupationRows');
        const col  = document.createElement('div');
        col.className = 'col-md-4';

        const input = document.createElement('input');
        input.type        = 'text';
        input.name        = 'preferred_occupations[]';
        input.className   = 'form-control peso-input';
        input.placeholder = (rows.children.length + 1) + '.';

        col.appendChild(input);
        rows.appendChild(col);
        input.focus();
    }

    // ── STEP NAVIGATION ──
    const nsrpTotalSteps = 8;
    let nsrpCurrentStep = 1;

    function showNsrpStep(step) {
        document.querySelectorAll('.nsrp-step').forEach(el => {
            el.style.display = (parseInt(el.dataset.step) === step) ? 'block' : 'none';
        });
        document.getElementById('nsrpStepInfo').textContent = `Step ${step} of ${nsrpTotalSteps}`;
        document.getElementById('nsrpStepPrev').disabled = step <= 1;

        const nextBtn = document.getElementById('nsrpStepNext');
        if (step >= nsrpTotalSteps) {
            nextBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'inline-flex';
        }
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
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.getElementById('nsrpStepNext').addEventListener('click', () => {
        if (!validateCurrentStep()) return;
        if (nsrpCurrentStep < nsrpTotalSteps) {
            nsrpCurrentStep++;
            showNsrpStep(nsrpCurrentStep);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    showNsrpStep(nsrpCurrentStep);

    // ── Signature Auto-fill ──
    function updateSignature() {
        const surname = document.getElementById('surname_input').value.trim();
        const first    = document.getElementById('first_name_input').value.trim();
        const middle   = document.getElementById('middle_name_input').value.trim();
        const suffix   = document.getElementById('suffix_input').value.trim();

        let fullName = [surname ? surname + ',' : '', first, middle, suffix].filter(Boolean).join(' ').trim();
        document.getElementById('signature_display').textContent = fullName || '\u00A0';
    }

    // ── Language Add Row ──
    function addLanguageRow() {
        const tbody = document.getElementById('languageTableBody');
        const idx = otherLangCount;
        const html = `
        <tr class="other-lang-row">
            <td style="padding:8px 12px;">
                <input type="text" name="other_language[]" class="form-control peso-input"
                    style="font-size:12px;" placeholder="Others:">
            </td>
            <td style="padding:8px 12px;text-align:center;">
                <input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][read]" value="1">
            </td>
            <td style="padding:8px 12px;text-align:center;">
                <input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][write]" value="1">
            </td>
            <td style="padding:8px 12px;text-align:center;">
                <input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][speak]" value="1">
            </td>
            <td style="padding:8px 12px;text-align:center;">
                <input class="form-check-input" type="checkbox" name="language_proficiency[other][${idx}][understand]" value="1">
            </td>
            <td style="padding:8px 12px;">
                <button type="button" class="btn btn-sm text-danger" onclick="this.closest('tr').remove()">
                    <i class="ph ph-trash"></i>
                </button>
            </td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', html);
        otherLangCount++;
    }

    // ── Training Add Row ──
    function addTrainingRow() {
        const tbody = document.getElementById('trainingTableBody');
        const idx = trainingCount;
        const html = `
        <tr>
            <td style="padding:8px 12px;color:var(--n-500);">${idx + 1}</td>
            <td style="padding:6px 8px;">
                <input type="text" name="trainings[${idx}][course]" class="form-control peso-input" style="font-size:12px;">
            </td>
            <td style="padding:6px 8px;">
                <input type="text" name="trainings[${idx}][hours]" class="form-control peso-input" style="font-size:12px;" placeholder="e.g. 40">
            </td>
            <td style="padding:6px 8px;">
                <input type="text" name="trainings[${idx}][duration]" class="form-control peso-input" style="font-size:12px;" placeholder="01/2023 to 03/2023">
            </td>
            <td style="padding:6px 8px;">
                <input type="text" name="trainings[${idx}][institution]" class="form-control peso-input" style="font-size:12px;">
            </td>
            <td style="padding:6px 8px;">
                <input type="file" name="training_certificates[${idx}]" class="form-control peso-input" style="font-size:11px;" accept=".jpg,.jpeg,.png,.pdf">
                <div style="font-size:10px;color:var(--n-500);margin-top:4px;">JPG, PNG, PDF only</div>
            </td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', html);
        trainingCount++;
    }

    // ── Employment Type Toggle ──
    function toggleEmploymentFields() {
        const val = document.getElementById('employmentType').value;
        document.getElementById('employedFields').style.display     = val === 'employed'   ? 'block' : 'none';
        document.getElementById('unemployedFields').style.display   = val === 'unemployed' ? 'block' : 'none';
        document.getElementById('monthsLookingField').style.display = val === 'unemployed' ? 'block' : 'none';
    }

    function toggleSelfEmployed() {
        const checked = document.querySelector('input[name="employed_sub_type"]:checked');
        document.getElementById('selfEmployedSpecify').style.display =
            checked && checked.value === 'self_employed' ? 'block' : 'none';
    }

    function toggleUnemployedOther() {
        const checked = document.querySelector('input[name="unemployed_reason"]:checked');
        document.getElementById('terminatedAbroadCountry').style.display =
            checked && checked.value === 'terminated_abroad' ? 'block' : 'none';
        document.getElementById('unemployedOtherField').style.display =
            checked && checked.value === 'others' ? 'block' : 'none';
    }

    function toggleOfwFields() {
        const val = document.querySelector('select[name="is_ofw"]').value;
        document.getElementById('ofwCountryField').style.display = val === '1' ? 'block' : 'none';
    }

    function toggleFormerOfwFields() {
        const val = document.querySelector('select[name="is_former_ofw"]').value;
        document.getElementById('formerOfwFields').style.display = val === '1' ? 'block' : 'none';
    }

    // ── Work Experience ──
    function addWorkExp() {
        const container = document.getElementById('workExpContainer');
        const idx = workExpCount;
        const html = `
        <div class="work-exp-row p-3 mb-3 rounded-3" style="border:1px solid var(--n-200);background:var(--n-0);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div style="font-size:12px;font-weight:700;color:var(--g-700);">
                    Work Experience #<span class="exp-num">${idx + 1}</span>
                </div>
                <button type="button" class="btn btn-sm btn-danger"
                    style="border-radius:8px;font-size:11px;"
                    onclick="removeWorkExp(this)">
                    <i class="ph-fill ph-trash"></i> Remove
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="peso-label">Company Name</label>
                    <input type="text" name="work_experiences[${idx}][company_name]"
                        class="form-control peso-input" placeholder="e.g. ABC Company">
                </div>
                <div class="col-md-4">
                    <label class="peso-label">Address (City/Municipality)</label>
                    <input type="text" name="work_experiences[${idx}][industry]"
                        class="form-control peso-input" placeholder="e.g. Cagayan de Oro City">
                </div>
                <div class="col-md-4">
                    <label class="peso-label">Position</label>
                    <input type="text" name="work_experiences[${idx}][position]"
                        class="form-control peso-input" placeholder="e.g. Sales Associate">
                </div>
                <div class="col-md-3">
                    <label class="peso-label">Date From (mm/yyyy)</label>
                    <input type="text" name="work_experiences[${idx}][date_from]"
                        class="form-control peso-input" placeholder="e.g. 01/2020">
                </div>
                <div class="col-md-3">
                    <label class="peso-label">Date To (mm/yyyy)</label>
                    <input type="text" name="work_experiences[${idx}][date_to]"
                        class="form-control peso-input" id="dateTo_${idx}" placeholder="e.g. 12/2022">
                </div>
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
                        <input class="form-check-input current-job-check" type="checkbox"
                            name="work_experiences[${idx}][is_current]"
                            value="1" id="current_${idx}"
                            onchange="toggleCurrentJob(this, ${idx})">
                        <label class="form-check-label" for="current_${idx}"
                            style="font-size:12px;color:var(--g-700);">Currently working here</label>
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
        rows.forEach(row => {
            row.querySelector('button').disabled = rows.length === 1;
        });
    }

    function toggleCurrentJob(checkbox, idx) {
        const dateToField = document.getElementById('dateTo_' + idx);
        if (dateToField) {
            if (checkbox.checked) { dateToField.value = 'present'; dateToField.disabled = true; }
            else { dateToField.value = ''; dateToField.disabled = false; }
        }
    }

    // ── College Course "Others" toggle ──
    function toggleOtherCourse(sel) {
        const otherInput = document.getElementById('college_course_other');
        if (!otherInput) return;
        otherInput.style.display = sel.value === 'Others (Please Specify)' ? 'block' : 'none';
        if (sel.value !== 'Others (Please Specify)') otherInput.value = '';
    }

    // ── Auto-compute Age ──
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

    // ── Philippine Address Data ──
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

            const savedPermProv   = "{{ $registration->perm_province ?? '' }}";
    const savedPresProv   = "{{ $registration->province ?? '' }}";
    const savedPermCity   = "{{ $registration->perm_municipality_city ?? '' }}";
    const savedPresCity   = "{{ $registration->municipality_city ?? '' }}";
    const savedPermBrgy   = "{{ $registration->perm_barangay ?? '' }}";
    const savedPresBrgy   = "{{ $registration->barangay ?? '' }}";

            if (savedPermProv) {
                const opt = [...permSel.options].find(o => o.text === savedPermProv || o.value === savedPermProv);
                if (opt) { permSel.value = opt.value; await loadCities('perm', savedPermCity, savedPermBrgy); }
            }
            if (savedPresProv) {
                const opt = [...presSel.options].find(o => o.text === savedPresProv || o.value === savedPresProv);
                if (opt) { presSel.value = opt.value; await loadCities('present', savedPresCity, savedPresBrgy); }
            }
        } catch(e) { console.error('Failed to load provinces', e); }
    }

    async function loadCities(type, restoreCity = null, restoreBrgy = null) {
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

            if (restoreCity) {
                const opt = [...citySel.options].find(o => o.text === restoreCity || o.value === restoreCity);
                if (opt) { citySel.value = opt.value; await loadBarangays(type, restoreBrgy); }
            }
        } catch(e) { console.error('Failed to load cities', e); }
    }

    async function loadBarangays(type, restoreBrgy = null) {
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

            if (restoreBrgy) {
                const opt = [...brgySel.options].find(o => o.text === restoreBrgy || o.value === restoreBrgy);
                if (opt) brgySel.value = opt.value;
            }
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
                loadCities('present', permCityText, permBrgyText);
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

    // ── Init on page load ──
    document.addEventListener('DOMContentLoaded', async function() {
        toggleEmploymentFields();
        toggleOfwFields();
        toggleFormerOfwFields();
        updateSignature();
        const empSubType = document.querySelector('input[name="employed_sub_type"]:checked');
        if (empSubType) toggleSelfEmployed();
        const unempReason = document.querySelector('input[name="unemployed_reason"]:checked');
        if (unempReason) toggleUnemployedOther();

        await loadProvinces();

        if (document.getElementById('same_as_permanent').checked) {
            toggleSameAsPermanent();
        }
    });

    function debugSubmit(e, btn) {
        e.preventDefault();
        const form = btn.form;

        syncPresentAddressIfSame();

        const invalid = [...form.querySelectorAll(':invalid')];
        if (invalid.length > 0) {
            console.log('Invalid fields:', invalid.map(f => f.name + ' = "' + f.value + '"'));
            alert('Invalid fields: ' + invalid.map(f => f.name).join(', '));
        } else {
            form.submit();
        }
    }

    // ── LEAVING WITH THE FORM HALF-FILLED ──
    //
    // The eight steps are one form and one Save, which sits at the bottom of
    // step 8. Somebody who corrects a name on step 1 and then clicks a sidebar
    // link has typed a change that never reached the server, and nothing on the
    // page said so — the correction simply was not there the next time they
    // looked. This catches the click and asks first.
    (function () {
        const form = document.getElementById('nsrpForm');
        if (!form) return;

        let nsrpDirty  = false;
        let nsrpLeavingTo = null;

        form.addEventListener('input',  () => { nsrpDirty = true; });
        form.addEventListener('change', () => { nsrpDirty = true; });

        // Saving is not leaving.
        form.addEventListener('submit', () => { nsrpDirty = false; });

        document.addEventListener('click', function (e) {
            if (!nsrpDirty) return;

            const link = e.target.closest('a[href]');
            if (!link) return;
            if (form.contains(link)) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;

            e.preventDefault();
            nsrpLeavingTo = link.href;
            new bootstrap.Modal(document.getElementById('nsrpLeaveModal')).show();
        }, true);

        document.getElementById('nsrpLeaveSave').addEventListener('click', function () {
            nsrpDirty = false;
            form.requestSubmit ? form.requestSubmit() : form.submit();
        });

        document.getElementById('nsrpLeaveDiscard').addEventListener('click', function () {
            nsrpDirty = false;
            if (nsrpLeavingTo) window.location.href = nsrpLeavingTo;
        });

        // The browser's own guard, for closing the tab or the back button —
        // the wording there belongs to the browser and cannot be styled.
        window.addEventListener('beforeunload', function (e) {
            if (!nsrpDirty) return;
            e.preventDefault();
            e.returnValue = '';
        });
    })();
    </script>

{{-- Asked before the page changes under a typed-but-unsaved answer. Save is the
     first button because saving is almost always what was meant: the eight
     steps are one form, and the Save that ends it is eight screens away from
     the box the jobseeker just corrected. --}}
<div class="modal fade" id="nsrpLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header border-0" style="background:var(--g-600);padding:14px 20px;">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="ph-fill ph-floppy-disk me-2"></i>Save your changes?
                </h6>
            </div>
            <div class="modal-body p-4" style="font-size:13px;color:var(--g-700);">
                You have changed something on this form and have not saved it yet.
                Leaving this page now discards the change.
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex gap-2">
                <button type="button" id="nsrpLeaveSave" class="btn fw-semibold flex-grow-1"
                    style="background:var(--g-600);color:#fff;border:none;border-radius:10px;font-size:13px;padding:9px;">
                    <i class="ph ph-floppy-disk me-1"></i>Save and continue
                </button>
                <button type="button" id="nsrpLeaveDiscard" class="btn fw-semibold"
                    style="border:1px solid var(--n-200);color:var(--danger);background:#fff;border-radius:10px;font-size:13px;padding:9px 16px;">
                    Leave without saving
                </button>
                <button type="button" class="btn fw-semibold" data-bs-dismiss="modal"
                    style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:10px;font-size:13px;padding:9px 16px;">
                    Stay
                </button>
            </div>
        </div>
    </div>
</div>
    @endsection

    
@endsection