@extends('jobseeker.layouts.app')

@section('page-title', 'Dashboard')

@section('content')

{{-- ── GREETING ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f; font-size:18px;">
            Welcome back, {{ $jobseeker->first_name ?? $jobseeker->name ?? 'Jobseeker' }}! 👋
        </h5>
        <p class="mb-0" style="font-size:13px; color:#888;">
            {{ now()->format('l, F d, Y') }}
        </p>
    </div>
</div>

{{-- ── NSRP STATUS BANNER ── --}}
@if(!$nsrp)
<div class="d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 fade-in"
     style="background:#fff8e1; border:1.5px solid #f9a825;">
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-clipboard-x" style="font-size:28px; color:#f9a825;"></i>
        <div>
            <div style="font-size:14px; font-weight:700; color:#f9a825;">NSRP Form Not Yet Submitted</div>
            <div style="font-size:12px; color:#888;">Complete your NSRP registration form to apply for jobs.</div>
        </div>
    </div>
    <a href="{{ route('jobseeker.nsrp') }}" class="btn btn-peso btn-sm px-3">
        <i class="bi bi-clipboard-check me-1"></i> Fill Up Now
    </a>
</div>

@else
<div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3 fade-in"
     style="background:#e8f8f3; border:1.5px solid #4dd9c0;">
    <i class="bi bi-check-circle-fill" style="font-size:28px; color:#2d7a5f;"></i>
    <div>
        <div style="font-size:14px; font-weight:700; color:#2d7a5f;">NSRP Form Submitted</div>
        <div style="font-size:12px; color:#888;">Your NSRP registration is on file. You can now browse and apply for job vacancies.</div>
    </div>
</div>
@endif

{{-- ── STAT CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-1">
            <div class="stat-icon"><i class="bi bi-briefcase-fill"></i></div>
            <div class="stat-value">{{ $totalApplications }}</div>
            <div class="stat-label">Total Applications</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-2">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value">{{ $pendingApplications }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-3">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-value">{{ $hiredApplications }}</div>
            <div class="stat-label">Hired</div>
        </div>
    </div>
</div>

{{-- ── QUICK LINKS ── --}}
<div class="peso-card fade-in mb-4">
    <div class="peso-card-header">
        <h6><i class="bi bi-grid me-2" style="color:#4dd9c0;"></i>Quick Links</h6>
    </div>
    <div class="peso-card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.nsrp') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:#f0f9f6; border:1.5px solid #a8e6cf; transition:all 0.2s;"
                         onmouseover="this.style.background='#e8f8f3'"
                         onmouseout="this.style.background='#f0f9f6'">
                        <i class="bi bi-clipboard-check" style="font-size:28px; color:#4dd9c0;"></i>
                        <div style="font-size:13px; font-weight:700; color:#2d7a5f; margin-top:8px;">NSRP Form</div>
                        <div style="font-size:11px; color:#888;">Fill up registration</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.jobs') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:#f0f9f6; border:1.5px solid #a8e6cf; transition:all 0.2s;"
                         onmouseover="this.style.background='#e8f8f3'"
                         onmouseout="this.style.background='#f0f9f6'">
                        <i class="bi bi-briefcase" style="font-size:28px; color:#4dd9c0;"></i>
                        <div style="font-size:13px; font-weight:700; color:#2d7a5f; margin-top:8px;">Job Vacancies</div>
                        <div style="font-size:11px; color:#888;">Browse available jobs</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.applications') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:#f0f9f6; border:1.5px solid #a8e6cf; transition:all 0.2s;"
                         onmouseover="this.style.background='#e8f8f3'"
                         onmouseout="this.style.background='#f0f9f6'">
                        <i class="bi bi-file-earmark-text" style="font-size:28px; color:#4dd9c0;"></i>
                        <div style="font-size:13px; font-weight:700; color:#2d7a5f; margin-top:8px;">My Applications</div>
                        <div style="font-size:11px; color:#888;">Track your applications</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.profile') }}" class="text-decoration-none">
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
<div class="peso-card fade-in">
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
                            {{ $schedule->employer->company_name ?? $schedule->employer->name ?? 'N/A' }}
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