{{-- ── NSRP FORM — ONE SECTION AT A TIME ──

     The ten sections used to sit on top of each other, so reading section VIII
     meant scrolling past seven others and losing the place. The form is filled
     in as ten steps; it is read the same way.

     Nothing is fetched again when the step changes — every section is already
     on the page and only one of them is shown. The staff can move with the
     numbered pills or with Back/Next, and the numbers say how far along the
     form they are. --}}
<div id="nsrpForm">

    {{-- STEP RAIL --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="p-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                @foreach(['I. Personal Information', 'II. Employment Status', 'III. Job Preference', 'IV. Language Proficiency', 'V. Educational Background', 'VI. Technical / Vocational Training', 'VII. Eligibility & License', 'VIII. Work Experience', 'IX. Other Skills', 'X. Certification'] as $i => $nsrpTitle)
                <button type="button" class="nsrp-pill" data-goto="{{ $i + 1 }}"
                    title="{{ $nsrpTitle }}"
                    style="width:32px;height:32px;border-radius:50%;border:1px solid var(--n-200);
                           background:#fff;color:var(--n-500);font-size:12px;font-weight:700;
                           line-height:1;cursor:pointer;transition:background .15s,color .15s;">
                    {{ $i + 1 }}
                </button>
                @endforeach
            </div>
            <div class="mt-2" style="font-size:12px;color:var(--n-500);">
                <span id="nsrpStepLabel" style="color:var(--g-700);font-weight:700;"></span>
                <span id="nsrpStepCount" class="ms-1"></span>
            </div>
        </div>
    </div>

    <div class="nsrp-step" data-step="1">
{{-- ── STEP 1: PERSONAL INFO ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph-fill ph-user me-2"></i> I. Personal Information
        </span>
    </div>
    <div class="p-3">
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Surname</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->surname ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">First Name</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->first_name ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Middle Name</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->middle_name ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Suffix</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->suffix ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Date of Birth</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->date_of_birth ? \Carbon\Carbon::parse($registration->date_of_birth)->format('M d, Y') : 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Age</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->age ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Sex</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->sex ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Civil Status</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->civil_status ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Religion</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->religion ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">TIN</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->tin ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Height</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->height ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Contact Number</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->contact_number ?? 'None' }}</div>
            </div>
            <div class="col-md-6">
                <div style="color:var(--n-500);font-size:11px;">Email</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $registration->reg_email ?? 'None' }}</div>
            </div>
            <div class="col-12">
                <div style="color:var(--n-500);font-size:11px;">Present Address</div>
                <div style="color:var(--g-700);font-weight:600;">
                    {{ implode(', ', array_filter([$registration->house_street, $registration->barangay, $registration->municipality_city, $registration->province])) ?: 'None' }}
                </div>
            </div>
            <div class="col-12">
                <div style="color:var(--n-500);font-size:11px;">Disability</div>
                <div style="color:var(--g-700);font-weight:600;">
                    {{ $registration->disabilities ? implode(', ', $registration->disabilities) : 'None' }}
                    {{ $registration->disability_other ? '(' . $registration->disability_other . ')' : '' }}
                </div>
            </div>
        </div>
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="2" hidden>
{{-- ── STEP 2: EMPLOYMENT STATUS ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph-fill ph-briefcase me-2"></i> II. Employment Status
        </span>
    </div>
    <div class="p-3">
        @if($nsrp)
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-4">
                <div style="color:var(--n-500);font-size:11px;">Employment Type</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->employment_type ? ucfirst($nsrp->employment_type) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:var(--n-500);font-size:11px;">Sub Type</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->employed_sub_type ?? $nsrp->unemployed_reason ?? 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:var(--n-500);font-size:11px;">Months Looking</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->months_looking ? $nsrp->months_looking . ' month(s)' : 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">OFW?</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->is_ofw ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Former OFW?</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->is_former_ofw ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">4Ps Beneficiary?</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->is_4ps ? 'Yes — ' . $nsrp->household_id : 'No' }}</div>
            </div>
            @if($nsrp->is_ofw || $nsrp->is_former_ofw)
            <div class="col-md-3">
                <div style="color:var(--n-500);font-size:11px;">Country</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->ofw_country ?? $nsrp->latest_deployment_country ?? 'None' }}</div>
            </div>
            @endif
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">NSRP form not yet submitted.</div>
        @endif
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="3" hidden>
{{-- ── STEP 3: JOB PREFERENCE ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph-fill ph-star me-2"></i> III. Job Preference
        </span>
    </div>
    <div class="p-3">
        @if($nsrp)
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-4">
                <div style="color:var(--n-500);font-size:11px;">Work Type</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->work_type ? ucfirst(str_replace('_', '-', $nsrp->work_type)) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:var(--n-500);font-size:11px;">Preferred Occupations</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->preferred_occupations ? implode(', ', array_filter($nsrp->preferred_occupations)) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:var(--n-500);font-size:11px;">Local Locations</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->local_locations ? implode(', ', array_filter($nsrp->local_locations)) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:var(--n-500);font-size:11px;">Overseas Locations</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->overseas_locations ? implode(', ', array_filter($nsrp->overseas_locations)) : 'None' }}</div>
            </div>
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">None</div>
        @endif
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="4" hidden>
{{-- ── STEP 4: LANGUAGE ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph ph-translate me-2"></i> IV. Language Proficiency
        </span>
    </div>
    <div class="p-3">
        @if($nsrp && $nsrp->language_proficiency)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:var(--n-50);">
                        <th style="color:var(--g-700);">Language</th>
                        <th style="color:var(--g-700);">Read</th>
                        <th style="color:var(--g-700);">Write</th>
                        <th style="color:var(--g-700);">Speak</th>
                        <th style="color:var(--g-700);">Understand</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->language_proficiency as $lang => $skills)
                    <tr>
                        <td style="color:var(--g-700);font-weight:600;">{{ $lang }}</td>
                        <td>{{ ($skills['read'] ?? false) ? '✓' : 'None' }}</td>
                        <td>{{ ($skills['write'] ?? false) ? '✓' : 'None' }}</td>
                        <td>{{ ($skills['speak'] ?? false) ? '✓' : 'None' }}</td>
                        <td>{{ ($skills['understand'] ?? false) ? '✓' : 'None' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">None</div>
        @endif
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="5" hidden>
{{-- ── STEP 5: EDUCATION ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph-fill ph-graduation-cap me-2"></i> V. Educational Background
        </span>
    </div>
    <div class="p-3">
        <div style="font-size:12px;color:var(--n-500);margin-bottom:8px;">
            Currently in school: <strong style="color:var(--g-700);">{{ $nsrp && $nsrp->currently_in_school ? 'Yes' : 'No' }}</strong>
        </div>
        @if($nsrp && $nsrp->education)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:var(--n-50);">
                        <th style="color:var(--g-700);">Level</th>
                        <th style="color:var(--g-700);">School Name</th>
                        <th style="color:var(--g-700);">Course</th>
                        <th style="color:var(--g-700);">Year Graduated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->education as $level => $info)
                    @if(!empty($info['school_name']))
                    <tr>
                        <td style="color:var(--g-700);font-weight:600;">{{ $level }}</td>
                        <td>{{ $info['school_name'] ?? 'None' }}</td>
                        <td>{{ $info['course'] ?? 'None' }}</td>
                        <td>{{ $info['year_graduated'] ?? 'None' }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">None</div>
        @endif
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="6" hidden>
{{-- ── STEP 6: TRAININGS ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph ph-wrench me-2"></i> VI. Technical / Vocational Training
        </span>
    </div>
    <div class="p-3">
        @if($nsrp && $nsrp->trainings && count(array_filter($nsrp->trainings, fn($t) => !empty($t['course']))) > 0)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:var(--n-50);">
                        <th style="color:var(--g-700);">Course</th>
                        <th style="color:var(--g-700);">Hours</th>
                        <th style="color:var(--g-700);">Duration</th>
                        <th style="color:var(--g-700);">Institution</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->trainings as $t)
                    @if(!empty($t['course']))
                    <tr>
                        <td>{{ $t['course'] }}</td>
                        <td>{{ $t['hours'] ?? 'None' }}</td>
                        <td>{{ $t['duration'] ?? 'None' }}</td>
                        <td>{{ $t['institution'] ?? 'None' }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">None</div>
        @endif
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="7" hidden>
{{-- ── STEP 7: ELIGIBILITY ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph-fill ph-seal-check me-2"></i> VII. Eligibility & License
        </span>
    </div>
    <div class="p-3 row g-3" style="font-size:13px;">
        <div class="col-md-6">
            <div style="color:var(--n-500);font-size:11px;margin-bottom:4px;">Civil Service Eligibility</div>
            @if($nsrp && $nsrp->eligibilities)
                @foreach($nsrp->eligibilities as $e)
                    @if(!empty($e['name']))
                    <div style="color:var(--g-700);font-weight:600;">{{ $e['name'] }} — {{ $e['date_taken'] ?? 'None' }}</div>
                    @endif
                @endforeach
            @else
            <div style="color:var(--n-500);">None</div>
            @endif
        </div>
        <div class="col-md-6">
            <div style="color:var(--n-500);font-size:11px;margin-bottom:4px;">PRC License</div>
            @if($nsrp && $nsrp->licenses)
                @foreach($nsrp->licenses as $l)
                    @if(!empty($l['name']))
                    <div style="color:var(--g-700);font-weight:600;">{{ $l['name'] }} — Valid until: {{ $l['valid_until'] ?? 'None' }}</div>
                    @endif
                @endforeach
            @else
            <div style="color:var(--n-500);">None</div>
            @endif
        </div>
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="8" hidden>
{{-- ── STEP 8: WORK EXPERIENCE ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph ph-buildings me-2"></i> VIII. Work Experience
        </span>
    </div>
    <div class="p-3">

        {{-- ── VIII-A: DECLARED BY THE JOBSEEKER ──

             What the person wrote on their own NSRP form. None of it passed
             through PESO, so nothing here is evidence of anything — it is their
             account of where they have worked. --}}
        <div style="color:var(--n-500);font-size:11px;font-weight:700;letter-spacing:0.4px;margin-bottom:6px;">
            <i class="ph ph-note-pencil me-1"></i> DECLARED BY THE JOBSEEKER
        </div>

        @if($nsrp && $nsrp->workExperiences && $nsrp->workExperiences->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:var(--n-50);">
                        <th style="color:var(--g-700);">Company</th>
                        <th style="color:var(--g-700);">Industry</th>
                        <th style="color:var(--g-700);">Position</th>
                        <th style="color:var(--g-700);">Inclusive Dates</th>
                        <th style="color:var(--g-700);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->workExperiences as $w)
                    <tr>
                        <td style="font-weight:600;color:var(--g-700);">{{ $w->company_name }}</td>
                        <td>{{ $w->industry ?? 'None' }}</td>
                        <td>{{ $w->position ?? 'None' }}</td>
                        <td>{{ $w->date_from ?? 'None' }} – {{ $w->is_current ? 'Present' : ($w->date_to ?? 'None') }}</td>
                        <td>{{ $w->employment_status ?? 'None' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">None</div>
        @endif

        {{-- ── VIII-B: PLACED THROUGH PESO ──

             A different kind of row, so it is drawn differently rather than
             mixed into the table above. These are not typed by anybody: the
             jobseeker applied here, an employer marked the application hired,
             and the system kept the date. They belong under Work Experience
             because that is what they are, but the desk has to be able to see
             at a glance which half PESO can vouch for. --}}
        <div style="border-top:1px dashed var(--n-200);margin:16px 0 0;"></div>
        <div style="color:var(--g-600);font-size:11px;font-weight:700;letter-spacing:0.4px;margin:12px 0 6px;">
            <i class="ph-fill ph-seal-check me-1"></i> PLACED THROUGH PESO
            <span style="color:var(--n-500);font-weight:500;letter-spacing:0;text-transform:none;">
                — recorded by the system, not entered by hand
            </span>
        </div>

        @if(isset($placements) && $placements->count() > 0)
        <div class="d-flex flex-column gap-2">
            @foreach($placements as $p)
            @php
                // Which door the job came in through. A posting made before the
                // schedule types existed has none, and the row still reads
                // correctly without inventing one.
                $channel = match ($p->job?->schedule_type) {
                    'inhouse'           => 'In-house Interview',
                    'company_interview' => 'Company Interview',
                    'job_fair'          => 'Job Fair',
                    default             => null,
                };
            @endphp
            <div style="border-left:3px solid var(--g-600);background:var(--n-50);
                        border-radius:0 8px 8px 0;padding:10px 14px;">
                <div class="d-flex flex-wrap align-items-baseline gap-2">
                    <span style="color:var(--g-700);font-weight:700;font-size:13px;">
                        {{ $p->job?->title ?? 'None' }}
                    </span>
                    <span style="color:var(--n-500);font-size:12px;">
                        at {{ $p->job?->company?->company_name ?? 'None' }}
                    </span>
                </div>
                <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                    <i class="ph ph-calendar-check me-1"></i>
                    Hired {{ $p->hired_at?->format('M d, Y') ?? 'date not recorded' }}
                    @if($channel)
                        <span class="mx-1">·</span>
                        <i class="ph ph-signpost me-1"></i>{{ $channel }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">None</div>
        @endif
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="9" hidden>
{{-- ── STEP 9: OTHER SKILLS ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph ph-wrench me-2"></i> IX. Other Skills
        </span>
    </div>
    <div class="p-3">
        @if($nsrp && $nsrp->other_skills && count($nsrp->other_skills) > 0)
        @php
            // One list, so the comma between the entries is not the loop's
            // problem: the "other" answer is simply the last skill.
            $skillList = collect($nsrp->other_skills)
                ->push($nsrp->other_skills_specify)
                ->filter()
                ->values();
        @endphp
        <div class="d-flex flex-wrap gap-2">
            @foreach($skillList as $skill)
            <span style="color:var(--g-700);font-size:12px;font-weight:600;">
                {{ $skill }}@if(!$loop->last),@endif
            </span>
            @endforeach
        </div>
        @else
        <div style="color:var(--n-500);font-size:13px;">None</div>
        @endif
    </div>
</div>
    </div>

    <div class="nsrp-step" data-step="10" hidden>
{{-- ── STEP 10: CERTIFICATION ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph-fill ph-pen me-2"></i> X. Certification
        </span>
    </div>
    <div class="p-3 row g-2" style="font-size:13px;">
        <div class="col-md-4">
            <div style="color:var(--n-500);font-size:11px;">Date Signed</div>
            <div style="color:var(--g-700);font-weight:600;">{{ $nsrp->certification_date ?? 'None' }}</div>
        </div>
        <div class="col-md-4">
            <div style="color:var(--n-500);font-size:11px;">Agreed to Certification</div>
            <div style="color:var(--g-700);font-weight:600;">{{ $nsrp && $nsrp->certification_agreed ? 'Yes' : 'No' }}</div>
        </div>
    </div>
</div>
    </div>

    {{-- BACK / NEXT --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <button type="button" id="nsrpBack" class="btn btn-sm fw-semibold"
            style="border:1px solid var(--n-200);background:#fff;color:var(--n-700);
                   border-radius:8px;font-size:12px;padding:8px 18px;">
            <i class="ph ph-arrow-left me-1"></i> Back
        </button>
        <button type="button" id="nsrpNext" class="btn btn-sm fw-semibold"
            style="background:var(--g-600);color:#fff;border:none;
                   border-radius:8px;font-size:12px;padding:8px 18px;">
            Next <i class="ph ph-arrow-right ms-1"></i>
        </button>
    </div>
</div>

<script>
(function () {
    var root = document.getElementById('nsrpForm');
    if (!root) return;

    var steps  = Array.prototype.slice.call(root.querySelectorAll('.nsrp-step'));
    var pills  = Array.prototype.slice.call(root.querySelectorAll('.nsrp-pill'));
    var back   = document.getElementById('nsrpBack');
    var next   = document.getElementById('nsrpNext');
    var label  = document.getElementById('nsrpStepLabel');
    var count  = document.getElementById('nsrpStepCount');
    var total  = steps.length;
    var current = 1;

    function show(n, scroll) {
        current = Math.min(Math.max(n, 1), total);

        steps.forEach(function (s) {
            s.hidden = Number(s.dataset.step) !== current;
        });

        pills.forEach(function (p) {
            var on = Number(p.dataset.goto) === current;
            p.style.background = on ? 'var(--g-600)' : '#fff';
            p.style.color      = on ? '#fff' : 'var(--n-500)';
            p.style.borderColor = on ? 'var(--g-600)' : 'var(--n-200)';
        });

        var active = pills[current - 1];
        label.textContent = active ? active.getAttribute('title') : '';
        count.textContent = 'Step ' + current + ' of ' + total;

        // The first and last step have nowhere to go, and a button that does
        // nothing when pressed is worse than one that says so.
        back.disabled = current === 1;
        next.disabled = current === total;
        back.style.opacity = current === 1 ? '0.45' : '1';
        next.style.opacity = current === total ? '0.45' : '1';
        back.style.cursor  = current === 1 ? 'default' : 'pointer';
        next.style.cursor  = current === total ? 'default' : 'pointer';

        // Back to the top of the rail, so a long section does not open
        // half-way down. Not on the first paint: the page has only just
        // loaded, and pulling it down to the form hides the header above it.
        if (scroll) root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    pills.forEach(function (p) {
        p.addEventListener('click', function () { show(Number(p.dataset.goto), true); });
    });
    back.addEventListener('click', function () { show(current - 1, true); });
    next.addEventListener('click', function () { show(current + 1, true); });

    show(1, false);
})();
</script>
