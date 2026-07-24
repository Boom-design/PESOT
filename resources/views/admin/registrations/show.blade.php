@extends('admin.layouts.app')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.registrations') }}" style="font-size:13px; color:#4dd9c0; text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Jobseekers
    </a>
    <h5 class="fw-bold mt-2 mb-0" style="color:#2d7a5f;">Jobseeker Registration Form</h5>
    <div style="font-size:12px; color:#888;">
        Submitted: {{ $registration->created_at->format('F d, Y h:i A') }}
    </div>
</div>

{{-- ── STEP 1: PERSONAL INFO ── --}}
<div class="reg-step" data-step="1">
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
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->surname ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">First Name</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->first_name ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Middle Name</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->middle_name ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Suffix</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->suffix ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Date of Birth</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->date_of_birth ? \Carbon\Carbon::parse($registration->date_of_birth)->format('M d, Y') : 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Age</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->age ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Sex</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->sex ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Civil Status</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->civil_status ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Religion</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->religion ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">TIN</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->tin ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Height</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->height ?? 'None' }}</div>
            </div>
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Contact Number</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->contact_number ?? 'None' }}</div>
            </div>
            <div class="col-md-6">
                <div style="color:#888;font-size:11px;">Email</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $registration->reg_email ?? 'None' }}</div>
            </div>
            <div class="col-12">
                <div style="color:#888;font-size:11px;">Present Address</div>
                <div style="color:#2d7a5f;font-weight:600;">
                    {{ implode(', ', array_filter([$registration->house_street, $registration->barangay, $registration->municipality_city, $registration->province])) ?: 'None' }}
                </div>
            </div>
            <div class="col-12">
                <div style="color:#888;font-size:11px;">Disability</div>
                <div style="color:#2d7a5f;font-weight:600;">
                    {{ $registration->disabilities ? implode(', ', $registration->disabilities) : 'None' }}
                    {{ $registration->disability_other ? '(' . $registration->disability_other . ')' : '' }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- ── STEP 2: EMPLOYMENT STATUS ── --}}
<div class="reg-step" data-step="2" style="display:none;">
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-briefcase-fill me-2"></i> II. Employment Status
        </span>
    </div>
    <div class="p-3">
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Employment Type</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->employment_type ? ucfirst($nsrp->employment_type) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Sub Type</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->employed_sub_type ?? $nsrp->unemployed_reason ?? 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Months Looking</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->months_looking ? $nsrp->months_looking . ' month(s)' : 'None' }}</div>
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
            @if($nsrp && ($nsrp->is_ofw || $nsrp->is_former_ofw))
            <div class="col-md-3">
                <div style="color:#888;font-size:11px;">Country</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->ofw_country ?? $nsrp->latest_deployment_country ?? 'None' }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>

{{-- ── STEP 3: JOB PREFERENCE ── --}}
<div class="reg-step" data-step="3" style="display:none;">
<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-star-fill me-2"></i> III. Job Preference
        </span>
    </div>
    <div class="p-3">
        <div class="row g-2" style="font-size:13px;">
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Work Type</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->work_type ? ucfirst(str_replace('_', '-', $nsrp->work_type)) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Preferred Occupations</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->preferred_occupations ? implode(', ', array_filter($nsrp->preferred_occupations)) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Local Locations</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->local_locations ? implode(', ', array_filter($nsrp->local_locations)) : 'None' }}</div>
            </div>
            <div class="col-md-4">
                <div style="color:#888;font-size:11px;">Overseas Locations</div>
                <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->overseas_locations ? implode(', ', array_filter($nsrp->overseas_locations)) : 'None' }}</div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- ── STEP 4: LANGUAGE ── --}}
<div class="reg-step" data-step="4" style="display:none;">
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
        <div style="color:#888;font-size:13px;">None</div>
        @endif
    </div>
</div>
</div>

{{-- ── STEP 5: EDUCATION ── --}}
<div class="reg-step" data-step="5" style="display:none;">
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
        <div style="color:#888;font-size:13px;">None</div>
        @endif
    </div>
</div>
</div>

{{-- ── STEP 6: TRAININGS ── --}}
<div class="reg-step" data-step="6" style="display:none;">
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
                        <th style="color:#2d7a5f;">Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->trainings as $idx => $t)
                    @if(!empty($t['course']))
                    <tr>
                        <td>{{ $t['course'] }}</td>
                        <td>{{ $t['hours'] ?? 'None' }}</td>
                        <td>{{ $t['duration'] ?? 'None' }}</td>
                        <td>{{ $t['institution'] ?? 'None' }}</td>
                        <td>
                            @php $cert = $nsrp->training_certificates[$idx] ?? null; @endphp
                            @if($cert)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($cert) }}" target="_blank" style="color:#2d7a5f;">
                                    <i class="bi bi-file-earmark-check me-1"></i>View
                                </a>
                            @else
                                None
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:#888;font-size:13px;">None</div>
        @endif
    </div>
</div>
</div>

{{-- ── STEP 7: ELIGIBILITY + WORK EXPERIENCE + OTHER SKILLS + CERTIFICATION (combined) ── --}}
<div class="reg-step" data-step="7" style="display:none;">

<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-patch-check-fill me-2"></i> VII. Eligibility & License
        </span>
    </div>
    <div class="p-3 row g-3" style="font-size:13px;">
        <div class="col-md-6">
            <div style="color:#888;font-size:11px;margin-bottom:4px;">Civil Service Eligibility</div>
            @if($nsrp && $nsrp->eligibilities && count(array_filter($nsrp->eligibilities, fn($e) => !empty($e['name']))) > 0)
                @foreach($nsrp->eligibilities as $e)
                    @if(!empty($e['name']))
                    <div style="color:#2d7a5f;font-weight:600;">{{ $e['name'] }} — {{ $e['date_taken'] ?? 'None' }}</div>
                    @endif
                @endforeach
            @else
            <div style="color:#888;">None</div>
            @endif
        </div>
        <div class="col-md-6">
            <div style="color:#888;font-size:11px;margin-bottom:4px;">PRC License</div>
            @if($nsrp && $nsrp->licenses && count(array_filter($nsrp->licenses, fn($l) => !empty($l['name']))) > 0)
                @foreach($nsrp->licenses as $l)
                    @if(!empty($l['name']))
                    <div style="color:#2d7a5f;font-weight:600;">{{ $l['name'] }} — Valid until: {{ $l['valid_until'] ?? 'None' }}</div>
                    @endif
                @endforeach
            @else
            <div style="color:#888;">None</div>
            @endif
        </div>
    </div>
</div>

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
                        <th style="color:#2d7a5f;">Address</th>
                        <th style="color:#2d7a5f;">Position</th>
                        <th style="color:#2d7a5f;">Inclusive Dates</th>
                        <th style="color:#2d7a5f;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsrp->workExperiences as $w)
                    <tr>
                        <td style="font-weight:600;color:#2d7a5f;">{{ $w->company_name }}</td>
                        <td>{{ $w->industry ?? 'None' }}</td>
                        <td>{{ $w->position }}</td>
                        <td>{{ ($w->date_from ?? '') . ' – ' . ($w->date_to ?? '') }}</td>
                        <td>{{ $w->employment_status ?? 'None' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="color:#888;font-size:13px;">None</div>
        @endif
    </div>
</div>

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
        <div style="color:#888;font-size:13px;">None</div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-3">
    <div style="background:linear-gradient(90deg,#90d870,#4dd9c0);padding:10px 16px;">
        <span style="color:#fff;font-weight:700;font-size:13px;">
            <i class="bi bi-pen-fill me-2"></i> X. Certification
        </span>
    </div>
    <div class="p-3 row g-2" style="font-size:13px;">
        <div class="col-md-4">
            <div style="color:#888;font-size:11px;">Date Signed</div>
            <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp->certification_date ?? 'None' }}</div>
        </div>
        <div class="col-md-4">
            <div style="color:#888;font-size:11px;">Agreed to Certification</div>
            <div style="color:#2d7a5f;font-weight:600;">{{ $nsrp && $nsrp->certification_agreed ? 'Yes' : 'No' }}</div>
        </div>
    </div>
</div>

</div>

{{-- spacer para dili ma-cover sa fixed nav ang katapusan nga content --}}
<div style="height:90px;"></div>

{{-- ── FIXED BOTTOM-RIGHT NAV (Prev / Step X of 7 / Next) ── --}}
<div class="d-flex align-items-center gap-3"
     style="position:fixed; bottom:24px; right:24px; z-index:500;
            background:#fff; padding:10px 16px; border-radius:14px;
            box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid #e8f5f0;">
    <button type="button" id="regStepPrev" class="btn btn-sm"
        style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;">
        <i class="bi bi-chevron-left"></i>
    </button>
    <span id="regStepInfo" style="font-size:13px;color:#2d7a5f;font-weight:600;white-space:nowrap;">Step 1 of 7</span>
    <button type="button" id="regStepNext" class="btn btn-sm"
        style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;">
        <i class="bi bi-chevron-right"></i>
    </button>
</div>

<script>
(function() {
    const totalSteps = 7;
    let currentStep = 1;

    function showStep(step) {
        document.querySelectorAll('.reg-step').forEach(el => {
            el.style.display = (parseInt(el.dataset.step) === step) ? 'block' : 'none';
        });
        document.getElementById('regStepInfo').textContent = `Step ${step} of ${totalSteps}`;
        document.getElementById('regStepPrev').disabled = step <= 1;
        document.getElementById('regStepNext').disabled = step >= totalSteps;
    }

    document.getElementById('regStepPrev').addEventListener('click', () => {
        if (currentStep > 1) { currentStep--; showStep(currentStep); }
    });
    document.getElementById('regStepNext').addEventListener('click', () => {
        if (currentStep < totalSteps) { currentStep++; showStep(currentStep); }
    });

    showStep(currentStep);
})();
</script>

@endsection