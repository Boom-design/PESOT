@extends('company.layouts.app')

@section('page-title', 'Dashboard')

@section('content')

@php
    $requirement = \App\Models\EmployerRequirement::where('user_id', $company->employerNsrp->employer_nsrp_registrations_id ?? 0)->first();
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

@include('company.partials.request-job-modal')

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

@section('scripts')
@include('company.partials.request-job-modal-scripts', ['autoOpenRequestJobModal' => $showJobPostingPrompt ?? false])
@endsection
