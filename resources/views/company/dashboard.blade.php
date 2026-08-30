@extends('company.layouts.app')

@section('page-title', 'Dashboard')

@section('content')

@php
    $requirement = \App\Models\EmployerRequirement::where('user_id', $company->activeCompany()->employer_nsrp_registrations_id ?? 0)->first();
    $reqStatus = $requirement?->status ?? 'none';
@endphp

{{-- ── GREETING ── --}}
<div class="peso-page-head fade-in">
    <h1 class="peso-page-title">
        Welcome, {{ optional($company->activeCompany())->company_name ?? $company->name ?? 'Company' }}
    </h1>
    <p class="peso-page-sub">{{ now()->format('l, F d, Y') }}</p>
</div>

@include('company.partials.request-job-modal')

{{-- ── REQUIREMENTS STATUS BANNER ── --}}

@if($reqStatus === 'none')
<div class="peso-notice is-warn mb-4 fade-in flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
    <div class="d-flex align-items-start gap-2">
        <i class="ph ph-upload-simple"></i>
        <div>
            <div class="fw-semibold">Requirements Not Yet Submitted</div>
            <div class="t-muted" style="font-size:12.5px;">Submit your requirements to access PESO services.</div>
        </div>
    </div>
    <a href="{{ route('company.requirements') }}" class="btn-peso btn-sm flex-shrink-0">
        <i class="ph ph-upload-simple"></i> Submit Requirements
    </a>
</div>

@elseif($reqStatus === 'pending')
<div class="peso-notice is-warn mb-4 fade-in">
    <i class="ph-fill ph-clock"></i>
    <div>
        <div class="fw-semibold">Requirements Under Review</div>
        <div class="t-muted" style="font-size:12.5px;">PESO staff is currently reviewing your submitted documents.</div>
    </div>
</div>

@elseif($reqStatus === 'approved')
<div class="peso-notice is-success mb-4 fade-in">
    <i class="ph-fill ph-check-circle"></i>
    <div>
        <div class="fw-semibold">Requirements Approved</div>
        <div class="t-muted" style="font-size:12.5px;">You can now request in-house interviews and post job vacancies.</div>
    </div>
</div>

@elseif($reqStatus === 'rejected')
<div class="peso-notice is-danger mb-4 fade-in flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
    <div class="d-flex align-items-start gap-2">
        <i class="ph-fill ph-x-circle"></i>
        <div>
            <div class="fw-semibold">Requirements Rejected</div>
            <div class="t-muted" style="font-size:12.5px;">
                {{ $requirement?->remarks ?? 'Please resubmit the correct documents.' }}
            </div>
        </div>
    </div>
    <a href="{{ route('company.requirements') }}" class="btn-peso-danger btn-sm flex-shrink-0">
        <i class="ph ph-arrows-clockwise"></i> Resubmit
    </a>
</div>

@elseif($reqStatus === 'expired')
@php
    $expiredLabels = collect($requirement->rejected_fields ?? [])
        ->map(fn($f) => \App\Models\EmployerRequirement::DOCUMENT_LABELS[$f] ?? $f)
        ->implode(', ');
@endphp
<div class="peso-notice is-warn mb-4 fade-in flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
    <div class="d-flex align-items-start gap-2">
        <i class="ph ph-hourglass-medium"></i>
        <div>
            <div class="fw-semibold">Document(s) Expired</div>
            <div class="t-muted" style="font-size:12.5px;">
                {{ $expiredLabels ?: 'One or more of your documents' }} expired. Please resubmit with an updated file and new expiry date.
            </div>
        </div>
    </div>
    <a href="{{ route('company.requirements') }}" class="btn-peso btn-sm flex-shrink-0">
        <i class="ph ph-arrows-clockwise"></i> Resubmit
    </a>
</div>
@endif

{{-- ── STAT CARDS, WHICH ARE ALSO THE QUICK LINKS ──
     There used to be a separate Quick Links card underneath saying the same
     three or four words again. A number the employer is already looking at is
     the better door: reading "3 hired" and wanting to know who they are is the
     whole reason anyone opens Reports. So the cards carry the links, and the
     row of duplicates is gone. --}}
<style>
    .stat-link { display:block; text-decoration:none; color:inherit; transition:transform .15s ease, box-shadow .15s ease; }
    .stat-link:hover { transform:translateY(-2px); }
    .stat-link:hover .stat-card { box-shadow:0 6px 18px rgba(0,0,0,.10); }
    .stat-go { font-size:10.5px; color:var(--n-500); margin-top:6px; }
    .stat-link:hover .stat-go { color:var(--g-600); }
</style>
<div class="row g-3 mb-2">
    <div class="col-6 col-md-4">
        <a href="{{ route('company.jobseekers', ['tab' => 'vacancies']) }}" class="stat-link">
            <div class="stat-card fade-in-1">
                <div class="stat-icon"><i class="ph-fill ph-briefcase"></i></div>
                <div class="stat-value">{{ $totalJobs }}</div>
                <div class="stat-label">Active Job Posts</div>
                <div class="stat-go">Open Active Job Postings <i class="ph ph-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        {{-- Applicants are reached one posting at a time, so this lands on the
             list of postings rather than on a page that does not exist. --}}
        <a href="{{ route('company.jobseekers', ['tab' => 'vacancies']) }}" class="stat-link">
            <div class="stat-card fade-in-2">
                <div class="stat-icon is-info"><i class="ph-fill ph-users-three"></i></div>
                <div class="stat-value">{{ $totalApplicants }}</div>
                <div class="stat-label">Total Active Applicants</div>
                <div class="stat-go">See them per posting <i class="ph ph-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('company.reports') }}" class="stat-link">
            <div class="stat-card fade-in-3">
                <div class="stat-icon"><i class="ph-fill ph-check-circle"></i></div>
                <div class="stat-value">{{ $hired }}</div>
                <div class="stat-label">Hired</div>
                <div class="stat-go">Open Reports <i class="ph ph-arrow-right"></i></div>
            </div>
        </a>
    </div>
</div>

<p class="t-sm t-muted mb-4">
    Counting all your active job posts and their applicants. For a breakdown by date, open Reports.
</p>

{{-- ── UPCOMING EVENTS ──
     PESO, 2026-08-26: this was "Today's Activities" — the fairs and interviews
     happening on this one day and nothing else, so an interview four days out
     was invisible until the morning it arrived. The same calendar the jobseeker
     and the office read answers it properly: click a day, see what is on it.

     What the employer sees is theirs alone: the fairs they were invited to and
     their own interview days. PESO office days are left out — the office
     being in a meeting is not theirs to know. --}}
{{-- Same shell as every other portal: the partial brings its own card and
     green header, so there is no wrapper here. --}}
<div class="mt-4">
@include('partials.activity-calendar', [
    'calendarRole'      => 'company',
    'calendarFeed'      => route('company.calendarData'),
    'calendarTitle'     => 'Upcoming Events',
    'calendarTypes'     => ['job_fair', 'inhouse', 'company_interview'],
    'calendarRequested' => false,
    'calendarNote'      => '',
    'calendarNoun'      => ['Events', 'event'],
])
</div>

@endsection

@section('scripts')
@include('company.partials.request-job-modal-scripts', ['autoOpenRequestJobModal' => $showJobPostingPrompt ?? false])
@endsection
