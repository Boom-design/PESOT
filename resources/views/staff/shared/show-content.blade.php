{{-- ── STEP 1: PERSONAL INFO ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-person-fill me-2"></i> I. Personal Information
        </span>
    </div>
    <div class="p-3">
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Surname</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->surname ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">First Name</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->first_name ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Middle Name</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->middle_name ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Suffix</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->suffix ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Date of Birth</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->date_of_birth ? \Carbon\Carbon::parse($registration->date_of_birth)->format('M d, Y') : '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Age</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->age ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Sex</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->sex ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Civil Status</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->civil_status ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Religion</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->religion ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">TIN</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->tin ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Height</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->height ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Contact Number</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->contact_number ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div style="color:#888;font-size:11px;">Email</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->reg_email ?? '—' }}</div>
            </div>
            <div class="col-12">
                <div style="color:#888;font-size:11px;">Present Address</div>
                <div style="color:#2d7a5f;font-weight:600;">
                    {{ implode(', ', array_filter([$registration->house_street, $registration->barangay, $registration->municipality_city, $registration->province])) ?: '—' }}
                </div>
            </div>
            <div class="col-12">
                <div style="color:#888;font-size:11px;">Disability</div>
                <div style="color:#2d7a5f;font-weight:600;">
                    {{ $registration->disabilities ? implode(', ', $registration->disabilities) : '—' }}
                    {{ $registration->disability_other ? '(' . $registration->disability_other . ')' : '' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── STEP 2: EMPLOYMENT STATUS ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-briefcase-fill me-2"></i> II. Employment Status
        </span>
    </div>
    <div class="p-3">
        @if($nsrp)
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Employment Type</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->employment_type ? ucfirst($nsrp->employment_type) : '—' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Sub Type</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->employed_sub_type ?? $nsrp->unemployed_reason ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Months Looking</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->months_looking ? $nsrp->months_looking . ' month(s)' : '—' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">OFW?</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->is_ofw ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Former OFW?</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->is_former_ofw ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">4Ps Beneficiary?</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->is_4ps ? 'Yes — ' . $nsrp->household_id : 'No' }}</div>
            </div>
            @if($nsrp->is_ofw || $nsrp->is_former_ofw)
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Country</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->ofw_country ?? $nsrp->latest_deployment_country ?? '—' }}</div>
            </div>
            @endif
        </div>
        @else
        <div style="color:#888;font-size:13px;">NSRP form not yet submitted.</div>
        @endif
    </div>
</div>

{{-- ── STEP 3: JOB PREFERENCE ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-star-fill me-2"></i> III. Job Preference
        </span>
    </div>
    <div class="p-3">
        @if($nsrp)
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Work Type</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->work_type ? ucfirst(str_replace('_', '-', $nsrp->work_type)) : '—' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Preferred Occupations</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->preferred_occupations ? implode(', ', array_filter($nsrp->preferred_occupations)) : '—' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Local Locations</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->local_locations ? implode(', ', array_filter($nsrp->local_locations)) : '—' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Overseas Locations</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->overseas_locations ? implode(', ', array_filter($nsrp->overseas_locations)) : '—' }}</div>
            </div>
        </div>
        @else
        <div style="color:#888;font-size:13px;">—</div>
        @endif
    </div>
</div>

{{-- ── STEP 4: LANGUAGE ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-translate me-2"></i> IV. Language Proficiency
        </span>
    </div>
    <div class="p-3">
        @if($nsrp && $nsrp->language_proficiency)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:#f0f9f6;">
                        <th style="color:#2d7a5f;">Language</th>
                        <th style="color:#2d7a5f;">Read</th>
                        <th style="color:#2d7a5f;">Write</th>
                        <th style="color:#2d7a5f;">Speak</th>
                        <th style="color:#2d7a5f;">Understand</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->language_proficiency as $lang => $skills)
                    <tr>
                        <td style="color:#2d7a5f;font-weight:600;">{{ $lang }}</td>
                        <td>{{ ($skills['read'] ?? false) ? '✓' : '—' }}</td>
                        <td>{{ ($skills['write'] ?? false) ? '✓' : '—' }}</td>
                        <td>{{ ($skills['speak'] ?? false) ? '✓' : '—' }}</td>
                        <td>{{ ($skills['understand'] ?? false) ? '✓' : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:#888;font-size:13px;">—</div>
        @endif
    </div>
</div>

{{-- ── STEP 5: EDUCATION ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-mortarboard-fill me-2"></i> V. Educational Background
        </span>
    </div>
    <div class="p-3">
        <div style="font-size:12px;color:#888;margin-bottom:8px;">
            Currently in school: <strong style="color:#2d7a5f;">{{ $nsrp && $nsrp->currently_in_school ? 'Yes' : 'No' }}</strong>
        </div>
        @if($nsrp && $nsrp->education)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:#f0f9f6;">
                        <th style="color:#2d7a5f;">Level</th>
                        <th style="color:#2d7a5f;">School Name</th>
                        <th style="color:#2d7a5f;">Course</th>
                        <th style="color:#2d7a5f;">Year Graduated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->education as $level => $info)
                    @if(!empty($info['school_name']))
                    <tr>
                        <td style="color:#2d7a5f;font-weight:600;">{{ $level }}</td>
                        <td>{{ $info['school_name'] ?? '—' }}</td>
                        <td>{{ $info['course'] ?? '—' }}</td>
                        <td>{{ $info['year_graduated'] ?? '—' }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:#888;font-size:13px;">—</div>
        @endif
    </div>
</div>

{{-- ── STEP 6: TRAININGS ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-tools me-2"></i> VI. Technical / Vocational Training
        </span>
    </div>
    <div class="p-3">
        @if($nsrp && $nsrp->trainings && count(array_filter($nsrp->trainings, fn($t) => !empty($t['course']))) > 0)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:#f0f9f6;">
                        <th style="color:#2d7a5f;">Course</th>
                        <th style="color:#2d7a5f;">Hours</th>
                        <th style="color:#2d7a5f;">Duration</th>
                        <th style="color:#2d7a5f;">Institution</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->trainings as $t)
                    @if(!empty($t['course']))
                    <tr>
                        <td>{{ $t['course'] }}</td>
                        <td>{{ $t['hours'] ?? '—' }}</td>
                        <td>{{ $t['duration'] ?? '—' }}</td>
                        <td>{{ $t['institution'] ?? '—' }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:#888;font-size:13px;">—</div>
        @endif
    </div>
</div>

{{-- ── STEP 7: ELIGIBILITY ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-patch-check-fill me-2"></i> VII. Eligibility & License
        </span>
    </div>
    <div class="p-3 row g-3" style="font-size:13px;">
        <div class="col-md-6">
            <div style="color:#888;font-size:11px;margin-bottom:4px;">Civil Service Eligibility</div>
            @if($nsrp && $nsrp->eligibilities)
                @foreach($nsrp->eligibilities as $e)
                    @if(!empty($e['name']))
                    <div style="color:#2d7a5f;font-weight:600;">{{ $e['name'] }} — {{ $e['date_taken'] ?? '—' }}</div>
                    @endif
                @endforeach
            @else
            <div style="color:#888;">—</div>
            @endif
        </div>
        <div class="col-md-6">
            <div style="color:#888;font-size:11px;margin-bottom:4px;">PRC License</div>
            @if($nsrp && $nsrp->licenses)
                @foreach($nsrp->licenses as $l)
                    @if(!empty($l['name']))
                    <div style="color:#2d7a5f;font-weight:600;">{{ $l['name'] }} — Valid until: {{ $l['valid_until'] ?? '—' }}</div>
                    @endif
                @endforeach
            @else
            <div style="color:#888;">—</div>
            @endif
        </div>
    </div>
</div>

{{-- ── STEP 8: WORK EXPERIENCE ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-building me-2"></i> VIII. Work Experience
        </span>
    </div>
    <div class="p-3">
        @if($nsrp && $nsrp->workExperiences && $nsrp->workExperiences->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead>
                    <tr style="background:#f0f9f6;">
                        <th style="color:#2d7a5f;">Company</th>
                        <th style="color:#2d7a5f;">Industry</th>
                        <th style="color:#2d7a5f;">Position</th>
                        <th style="color:#2d7a5f;">Inclusive Dates</th>
                        <th style="color:#2d7a5f;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->workExperiences as $w)
                    <tr>
                        <td style="font-weight:600;color:#2d7a5f;">{{ $w->company_name }}</td>
                        <td>{{ $w->industry ?? '—' }}</td>
                        <td>{{ $w->position ?? '—' }}</td>
                        <td>{{ $w->date_from ?? '—' }} – {{ $w->is_current ? 'Present' : ($w->date_to ?? '—') }}</td>
                        <td>{{ $w->employment_status ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:#888;font-size:13px;">—</div>
        @endif
    </div>
</div>

{{-- ── STEP 9: OTHER SKILLS ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-wrench-adjustable me-2"></i> IX. Other Skills
        </span>
    </div>
    <div class="p-3">
        @if($nsrp && $nsrp->other_skills && count($nsrp->other_skills) > 0)
        <div class="d-flex flex-wrap gap-2">
            @foreach($nsrp->other_skills as $skill)
            <span style="background:#e8f8f3;color:#2d7a5f;font-size:12px;padding:4px 12px;border-radius:20px;font-weight:600;">
                {{ $skill }}
            </span>
            @endforeach
            @if($nsrp->other_skills_specify)
            <span style="background:#e8f8f3;color:#2d7a5f;font-size:12px;padding:4px 12px;border-radius:20px;font-weight:600;">
                {{ $nsrp->other_skills_specify }}
            </span>
            @endif
        </div>
        @else
        <div style="color:#888;font-size:13px;">—</div>
        @endif
    </div>
</div>

{{-- ── STEP 10: CERTIFICATION ── --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-pen-fill me-2"></i> X. Certification
        </span>
    </div>
    <div class="p-3 row g-2" style="font-size:13px;">
        <div class="col-md-4">
            <div style="color:#888;font-size:11px;">Date Signed</div>
            <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->certification_date ?? '—' }}</div>
        </div>
        <div class="col-md-4">
            <div style="color:#888;font-size:11px;">Agreed to Certification</div>
            <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp && $nsrp->certification_agreed ? 'Yes' : 'No' }}</div>
        </div>
    </div>
</div>