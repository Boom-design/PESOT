@extends('jobseeker.layouts.app')

@section('page-title', 'Dashboard')

@section('content')

{{-- ── GREETING ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700); font-size:18px;">
            Welcome, {{ $jobseeker->first_name ?? $jobseeker->name ?? 'Jobseeker' }}! 👋
        </h5>
        <p class="mb-0" style="font-size:13px; color:var(--n-500);">
            {{ now()->format('l, F d, Y') }}
        </p>
    </div>
</div>

{{-- ── NSRP STATUS BANNER ── --}}
@if(!$nsrp)
<div class="d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 fade-in"
     style="background:var(--warn-bg); border:1px solid var(--warn);">
    <div class="d-flex align-items-center gap-3">
        <i class="ph ph-clipboard" style="font-size:28px; color:var(--warn);"></i>
        <div>
            <div style="font-size:14px; font-weight:700; color:var(--warn);">NSRP Form Not Yet Submitted</div>
            <div style="font-size:12px; color:var(--n-500);">Complete your NSRP registration form to apply for jobs.</div>
        </div>
    </div>
    <a href="{{ route('jobseeker.nsrp') }}" class="btn btn-peso btn-sm px-3">
        <i class="ph ph-clipboard-text me-1"></i> Fill Up Now
    </a>
</div>

@else
<div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3 fade-in"
     style="background:var(--g-50); border:1px solid var(--g-500);">
    <i class="ph-fill ph-check-circle" style="font-size:28px; color:var(--g-700);"></i>
    <div>
        <div style="font-size:14px; font-weight:700; color:var(--g-700);">NSRP Form Submitted</div>
        <div style="font-size:12px; color:var(--n-500);">Your NSRP registration is on file. You can now browse and apply for job vacancies.</div>
    </div>
</div>
@endif

{{-- ── STAT CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-1">
            <div class="stat-icon"><i class="ph-fill ph-briefcase"></i></div>
            <div class="stat-value">{{ $totalApplications }}</div>
            <div class="stat-label">Total Applications</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-2">
            <div class="stat-icon"><i class="ph ph-hourglass-medium"></i></div>
            <div class="stat-value">{{ $pendingApplications }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card fade-in-3">
            <div class="stat-icon"><i class="ph-fill ph-check-circle"></i></div>
            <div class="stat-value">{{ $hiredApplications }}</div>
            <div class="stat-label">Hired</div>
        </div>
    </div>
</div>

{{-- ── QUICK LINKS ── --}}
<div class="peso-card fade-in mb-4">
    <div class="peso-card-header">
        <h6><i class="ph ph-squares-four me-2" style="color:var(--g-600);"></i>Quick Links</h6>
    </div>
    <div class="peso-card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.nsrp') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:var(--n-50); border:1px solid var(--n-200); transition:all 0.2s;"
                         onmouseover="this.style.background='var(--g-50)'"
                         onmouseout="this.style.background='var(--n-50)'">
                        <i class="ph ph-clipboard-text" style="font-size:28px; color:var(--g-600);"></i>
                        <div style="font-size:13px; font-weight:700; color:var(--g-700); margin-top:8px;">NSRP Form</div>
                        <div style="font-size:11px; color:var(--n-500);">Fill up registration</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.jobs') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:var(--n-50); border:1px solid var(--n-200); transition:all 0.2s;"
                         onmouseover="this.style.background='var(--g-50)'"
                         onmouseout="this.style.background='var(--n-50)'">
                        <i class="ph ph-briefcase" style="font-size:28px; color:var(--g-600);"></i>
                        <div style="font-size:13px; font-weight:700; color:var(--g-700); margin-top:8px;">Job Vacancies</div>
                        <div style="font-size:11px; color:var(--n-500);">Browse available jobs</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.applications') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:var(--n-50); border:1px solid var(--n-200); transition:all 0.2s;"
                         onmouseover="this.style.background='var(--g-50)'"
                         onmouseout="this.style.background='var(--n-50)'">
                        <i class="ph ph-file-text" style="font-size:28px; color:var(--g-600);"></i>
                        <div style="font-size:13px; font-weight:700; color:var(--g-700); margin-top:8px;">My Applications</div>
                        <div style="font-size:11px; color:var(--n-500);">Track your applications</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('jobseeker.profile') }}" class="text-decoration-none">
                    <div class="p-3 rounded-3 text-center h-100"
                         style="background:var(--n-50); border:1px solid var(--n-200); transition:all 0.2s;"
                         onmouseover="this.style.background='var(--g-50)'"
                         onmouseout="this.style.background='var(--n-50)'">
                        <i class="ph ph-user-circle" style="font-size:28px; color:var(--g-600);"></i>
                        <div style="font-size:13px; font-weight:700; color:var(--g-700); margin-top:8px;">My Profile</div>
                        <div style="font-size:11px; color:var(--n-500);">Manage your account</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── UPCOMING EVENTS ── --}}
{{-- The calendar is the whole card. Clicking a day lists what is on it, so a
     separate roll-up underneath would only say the same thing twice.

     Fed by StaffCalendar::forJobseeker(): every job fair, plus in-house days
     LRA has already accepted. Office days are left out — those are internal. --}}
{{-- No wrapper card. The calendar partial is already a card with the green
     header every other portal shows, and wrapping it in a second one both
     doubled the border and made this one page look unlike the rest. --}}
@include('partials.activity-calendar', [
    'calendarRole'      => 'jobseeker',
    'calendarFeed'      => route('jobseeker.calendarData'),
    'calendarTitle'     => 'Upcoming Events',
    'calendarTypes'     => ['job_fair', 'inhouse'],
    'calendarRequested' => false,
    'calendarNote'      => '',
    'calendarNoun'      => ['Events', 'event'],
])

@if($highlyQualifiedMatch)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: 'You\'re Highly Qualified! 🎉',
        html: 'You matched <strong>{{ $highlyQualifiedMatch["percentage"] }}%</strong> with <strong>{{ $highlyQualifiedMatch["job"]->title }}</strong> at {{ $highlyQualifiedMatch["job"]->company->company_name ?? "an employer" }} — matching your preferred occupation and other requirements!',
        icon: 'success',
        confirmButtonText: 'View & Apply',
        confirmButtonColor: '#28812F',
        showCancelButton: true,
        cancelButtonText: 'Maybe Later',
        cancelButtonColor: 'var(--n-500)',
    }).then((result) => {
        if (result.isConfirmed) {
            {{-- job_qualifications_id, dili ->id: ang Job walay column nga
                 "id" sukad sa PK rename, mao nga ang ->id null ug mo-500 ang
                 route(). Nagpakita ra ni kung naa gyuy match nga ≥75%. --}}
            window.location.href = "{{ route('jobseeker.jobs.show', $highlyQualifiedMatch['job']->job_qualifications_id) }}";
        }
    });
});
</script>
@endif

@endsection