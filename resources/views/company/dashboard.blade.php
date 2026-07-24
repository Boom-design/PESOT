@extends('company.layouts.app')

@section('page-title', 'Dashboard')

@section('content')

@php
    $requirement = \App\Models\EmployerRequirement::where('user_id', $company->employerNsrp->id ?? 0)->first();
    $reqStatus = $requirement?->status ?? 'none';
@endphp

{{-- ── GREETING ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color: #2d7a5f; font-size: 18px;">
            Welcome, {{ optional($company->employerNsrp)->company_name ?? $company->name ?? 'Company' }}! 👋
        </h5>
        <p class="mb-0" style="font-size: 13px; color: #888;">
            {{ now()->format('l, F d, Y') }}
        </p>
    </div>
    
</div>

{{-- ── REQUEST JOB MODAL ── --}}
<div class="modal fade" id="requestJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-briefcase me-2"></i>Request Job Posting
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('company.jobs.request') }}" method="POST">
                @csrf
                <div class="modal-body p-4">

                    {{-- SECTION III: VACANCY DETAILS --}}
                    <div class="mb-3 pb-2" style="border-bottom:2px solid #e8f5f0;">
                        <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                            <i class="bi bi-briefcase me-1" style="color:#4dd9c0;"></i> III. Vacancy Details
                        </span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Position Title *</label>
                            <input type="text" name="title" class="form-control" required
                                placeholder="e.g. Sales Associate"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Job Description *</label>
                            <textarea name="description" class="form-control" rows="3" required
                                placeholder="Describe the job responsibilities..."
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                        </div>
                    </div>

                    {{-- Nature of Work --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Nature of Work *</label>
                        <div class="row g-2">
                            @foreach([
                                'permanent'    => 'Permanent',
                                'contractual'  => 'Contractual',
                                'project_based'=> 'Project-based',
                                'internship'   => 'Internship / OJT',
                                'part_time'    => 'Part-time',
                                'work_from_home'=> 'Work from home / online job',
                            ] as $val => $label)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                        name="type" value="{{ $val }}" id="type_{{ $val }}" required>
                                    <label class="form-check-label" for="type_{{ $val }}"
                                        style="font-size:12px;color:#555;">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Place of Work *</label>
                            <input type="text" name="location" class="form-control" required
                                placeholder="e.g. Cagayan de Oro City"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Salary</label>
                            <input type="text" name="salary" class="form-control"
                                placeholder="e.g. 15,000 / Negotiable"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Vacancy Count *</label>
                            <input type="number" name="slots" class="form-control" required min="1"
                                placeholder="e.g. 3"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Valid Until</label>
                            <input type="date" name="deadline" class="form-control"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                    {{-- SECTION IV: QUALIFICATION REQUIREMENTS --}}
                    <div class="mb-3 pb-2 mt-4" style="border-bottom:2px solid #e8f5f0;">
                        <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                            <i class="bi bi-clipboard-check me-1" style="color:#4dd9c0;"></i> IV. Qualification Requirements
                        </span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Work Experience (months)</label>
                            <input type="number" name="experience_months" class="form-control" min="0"
                                placeholder="e.g. 6"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Religion</label>
                            <input type="text" name="religion" class="form-control"
                                placeholder="e.g. Any"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                    {{-- Sex --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Sex</label>
                        <div class="d-flex gap-4">
                            @foreach(['Male' => 'Male', 'Female' => 'Female', 'Any' => 'No Preference'] as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                    name="sex_preference" value="{{ $val }}"
                                    id="sex_{{ $val }}" {{ $val === 'Any' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sex_{{ $val }}"
                                    style="font-size:12px;color:#555;">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Civil Status --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Civil Status</label>
                        <div class="d-flex gap-4">
                            @foreach(['Single' => 'Single', 'Married' => 'Married', 'Any' => 'No Preference'] as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                    name="civil_status" value="{{ $val }}"
                                    id="civil_{{ $val }}" {{ $val === 'Any' ? 'checked' : '' }}>
                                <label class="form-check-label" for="civil_{{ $val }}"
                                    style="font-size:12px;color:#555;">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Other Qualifications</label>
                        <textarea name="other_qualifications" class="form-control" rows="2"
                            placeholder="State any other qualifications..."
                            style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                    </div>

                    {{-- Accepts Disability --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Accepts Disability?</label>
                        <div class="d-flex gap-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                    name="accepts_disability" value="yes" id="disability_yes"
                                    onclick="document.getElementById('disabilityTypes').style.display='flex'">
                                <label class="form-check-label" for="disability_yes" style="font-size:12px;color:#555;">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                    name="accepts_disability" value="no" id="disability_no" checked
                                    onclick="document.getElementById('disabilityTypes').style.display='none'">
                                <label class="form-check-label" for="disability_no" style="font-size:12px;color:#555;">No</label>
                            </div>
                        </div>
                        <div id="disabilityTypes" class="d-none gap-3 flex-wrap">
                            @foreach(['Visual', 'Hearing', 'Speech', 'Physical', 'Others'] as $type)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="disability_types[]" value="{{ $type }}" id="dis_{{ $type }}">
                                <label class="form-check-label" for="dis_{{ $type }}"
                                    style="font-size:12px;color:#555;">{{ $type }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Educational Level</label>
                            <select name="education_required" class="form-select"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                                <option value="">Any</option>
                                <option value="Elementary">Elementary</option>
                                <option value="High School">High School</option>
                                <option value="Senior High">Senior High</option>
                                <option value="Tertiary / College">Tertiary / College</option>
                                <option value="Graduate Studies">Graduate Studies</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Course / Major</label>
                            <input type="text" name="course_major" class="form-control"
                                placeholder="e.g. Business Administration"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">License</label>
                            <input type="text" name="license" class="form-control"
                                placeholder="e.g. Driver's License"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Eligibility</label>
                            <input type="text" name="eligibility" class="form-control"
                                placeholder="e.g. Civil Service Eligible"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Certification</label>
                            <input type="text" name="certification" class="form-control"
                                placeholder="e.g. TESDA NC II"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Language / Dialect Spoken</label>
                            <input type="text" name="language" class="form-control"
                                placeholder="e.g. Filipino, English, Bisaya"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Residence</label>
                            <input type="text" name="preferred_residence" class="form-control"
                                placeholder="e.g. Cagayan de Oro City"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1px solid #e8f5f0;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-peso btn-sm px-4">
                        <i class="bi bi-send me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── REQUIREMENTS STATUS BANNER ── --}}

@if($reqStatus === 'none')
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-3 fade-in"
     style="background:#fff8e1; border:1.5px solid #f9a825;">
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-upload" style="font-size:28px; color:#f9a825;flex-shrink:0;"></i>
        <div>
            <div style="font-size:14px; font-weight:700; color:#f9a825;">Requirements Not Yet Submitted</div>
            <div style="font-size:12px; color:#888;">Submit your requirements to access PESO services.</div>
        </div>
    </div>
    <a href="{{ route('company.requirements') }}" class="btn btn-peso btn-sm px-3 w-100 w-md-auto">
        <i class="bi bi-upload me-1"></i> Submit Requirements
    </a>
</div>

@elseif($reqStatus === 'pending')
<div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3 fade-in"
     style="background:#fff8e1; border:1.5px solid #f9a825;">
    <i class="bi bi-clock-fill" style="font-size:28px; color:#f9a825;"></i>
    <div>
        <div style="font-size:14px; font-weight:700; color:#f9a825;">Requirements Under Review</div>
        <div style="font-size:12px; color:#888;">PESO staff is currently reviewing your submitted documents.</div>
    </div>
</div>

@elseif($reqStatus === 'approved')
<div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3 fade-in"
     style="background:#e8f8f3; border:1.5px solid #4dd9c0;">
    <i class="bi bi-check-circle-fill" style="font-size:28px; color:#2d7a5f;"></i>
    <div>
        <div style="font-size:14px; font-weight:700; color:#2d7a5f;">Requirements Approved ✅</div>
        <div style="font-size:12px; color:#888;">You can now request in-house interviews and post job vacancies.</div>
    </div>
</div>

@elseif($reqStatus === 'rejected')
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-3 fade-in"
     style="background:#fff5f5; border:1.5px solid #e53935;">
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-x-circle-fill" style="font-size:28px; color:#e53935;flex-shrink:0;"></i>
        <div>
            <div style="font-size:14px; font-weight:700; color:#e53935;">Requirements Rejected ❌</div>
            <div style="font-size:12px; color:#888;">
                {{ $requirement?->remarks ?? 'Please resubmit the correct documents.' }}
            </div>
        </div>
    </div>
    <a href="{{ route('company.requirements') }}" class="btn btn-sm px-3 w-100 w-md-auto"
       style="background:#e53935; color:#fff; border-radius:10px; font-size:12px; font-weight:600;">
        <i class="bi bi-arrow-repeat me-1"></i> Resubmit
    </a>
</div>
@endif

{{-- ── STAT CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-1">
            <div class="stat-icon">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <div class="stat-value">{{ $totalJobs }}</div>
            <div class="stat-label">Total Job Posts</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-2">
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-value">{{ $totalApplicants }}</div>
            <div class="stat-label">Total Applicants</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-3">
            <div class="stat-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-value">{{ $hired }}</div>
            <div class="stat-label">Hired</div>
        </div>
    </div>
</div>

{{-- ── QUICK LINKS ── --}}
<div class="peso-card fade-in">
    <div class="peso-card-header">
        <h6><i class="bi bi-grid me-2" style="color:#4dd9c0;"></i>Quick Links</h6>
    </div>
    <div class="peso-card-body">
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <a href="{{ route('company.jobs') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:#f0f9f6; border:1.5px solid #a8e6cf; transition:all 0.2s;"
                         onmouseover="this.style.background='#e8f8f3'"
                         onmouseout="this.style.background='#f0f9f6'">
                        <i class="bi bi-send" style="font-size:28px; color:#4dd9c0;"></i>
                        <div style="font-size:13px; font-weight:700; color:#2d7a5f; margin-top:8px;">Job Requests</div>
                        <div style="font-size:11px; color:#888;">View all job postings</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('company.requirements') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:#f0f9f6; border:1.5px solid #a8e6cf; transition:all 0.2s;"
                         onmouseover="this.style.background='#e8f8f3'"
                         onmouseout="this.style.background='#f0f9f6'">
                        <i class="bi bi-upload" style="font-size:28px; color:#4dd9c0;"></i>
                        <div style="font-size:13px; font-weight:700; color:#2d7a5f; margin-top:8px;">Requirements</div>
                        <div style="font-size:11px; color:#888;">Submit your documents</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4">
                <a href="{{ route('company.profile') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:#f0f9f6; border:1.5px solid #a8e6cf; transition:all 0.2s;"
                         onmouseover="this.style.background='#e8f8f3'"
                         onmouseout="this.style.background='#f0f9f6'">
                        <i class="bi bi-person-circle" style="font-size:28px; color:#4dd9c0;"></i>
                        <div style="font-size:13px; font-weight:700; color:#2d7a5f; margin-top:8px;">My Profile</div>
                        <div style="font-size:11px; color:#888;">Manage your account</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── TODAY'S ACTIVITIES ── --}}
<div class="peso-card fade-in mt-4">
    <div class="peso-card-header">
        <h6><i class="bi bi-calendar-check me-2" style="color:#4dd9c0;"></i>Today's Activities</h6>
    </div>
    <div class="peso-card-body">

        {{-- Job Fair Events Today --}}
        <div class="mb-3">
            <div class="fw-semibold mb-2" style="font-size:13px; color:#2d7a5f;">
                <i class="bi bi-people-fill me-1"></i> Job Fair Events
            </div>
            @if($todayJobFairs->isEmpty())
                <div class="text-muted" style="font-size:13px; padding:10px 0;">
                    No job fair events scheduled today.
                </div>
            @else
                @foreach($todayJobFairs as $event)
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2"
                     style="background:#f0f9f6; border:1px solid #a8e6cf;">
                    <i class="bi bi-calendar-event" style="color:#4dd9c0; font-size:20px;"></i>
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#2d7a5f;">{{ $event->title }}</div>
                        <div style="font-size:12px; color:#888;">
                            <i class="bi bi-geo-alt me-1"></i>{{ $event->venue }}
                            &nbsp;•&nbsp;
                            <span style="color:#4dd9c0; font-weight:600; text-transform:capitalize;">{{ $event->status }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        <hr style="border-color:#e8f8f3; margin:12px 0;">

        {{-- In-house Schedules Today --}}
        <div>
            <div class="fw-semibold mb-2" style="font-size:13px; color:#2d7a5f;">
                <i class="bi bi-building me-1"></i> In-house Interviews
            </div>
            @if($todayInhouse->isEmpty())
                <div class="text-muted" style="font-size:13px; padding:10px 0;">
                    No in-house interviews scheduled today.
                </div>
            @else
                @foreach($todayInhouse as $schedule)
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2"
                     style="background:#f0f9f6; border:1px solid #a8e6cf;">
                    <i class="bi bi-clock" style="color:#4dd9c0; font-size:20px;"></i>
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#2d7a5f;">
                            In-house Interview
                        </div>
                        <div style="font-size:12px; color:#888;">
                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') }}
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

    </div>
</div>

@endsection