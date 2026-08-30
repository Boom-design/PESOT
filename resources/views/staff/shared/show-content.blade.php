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

{{-- ── STEP 8: WORK EXPERIENCE ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:var(--g-600);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="ph ph-buildings me-2"></i> VIII. Work Experience
        </span>
    </div>
    <div class="p-3">
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
    </div>
</div>

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