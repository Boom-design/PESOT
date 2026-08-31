@extends('jobseeker.layouts.app')

@section('page-title', 'My Applications')

@section('content')

{{--
    Ang page nga gi-link sa "My Applications" nga tile sa dashboard.

    Timan-i: kini nga file kaniadto usa ka eksaktong kopya sa Job Vacancies nga
    listahan, mao nga mo-500 kini kanunay — nangita kini ug $jobs samtang ang
    JobseekerWebController::applications() nagpasa ug $applications. Karon ang
    tinuod nga listahan sa gi-apply-an na ang gipakita.
--}}

{{-- ── HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700); font-size:18px;">
            My Applications
        </h5>
        <p class="mb-0" style="font-size:13px; color:var(--n-500);">
            Every job you have applied to, and where each one stands.
        </p>
    </div>
    <a href="{{ route('jobseeker.jobs') }}" class="btn btn-peso btn-sm px-3">
        <i class="ph ph-magnifying-glass me-1"></i> Browse Jobs
    </a>
</div>

@if($applications->isEmpty())
    <div class="peso-card fade-in">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="ph ph-file-text"></i>
            </div>
            <h6>You have not applied to any job yet</h6>
            <p>Browse the open vacancies and submit a request to get started.</p>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach($applications as $application)
        @php
            // Ang bokabularyo sa jobseeker, dili ang raw nga enum.
            $statusMeta = match($application->status) {
                'hired'     => ['Hired',            'background:#e6f7f1;color:#0f7a5f;', 'ph-fill ph-check-circle'],
                'qualified' => ['Qualified',        'background:#e8eefc;color:#1c3f8a;', 'ph-fill ph-seal-check'],
                'waiting'   => ['Waiting',          'background:#fff5e0;color:#8a5c00;', 'ph-fill ph-hourglass-medium'],
                'reviewed'  => ['Reviewed',         'background:#eef0f3;color:#4a5260;', 'ph-fill ph-eye'],
                'rejected'  => ['Not selected',     'background:#fdecea;color:#8a1c13;', 'ph-fill ph-x-circle'],
                default     => ['Pending review',   'background:#fff5e0;color:#8a5c00;', 'ph-fill ph-clock'],
            };
            $job = $application->job;
        @endphp
        <div class="col-md-6 fade-in">
            <div class="peso-card h-100">
                <div class="peso-card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="width:48px; height:48px; background:var(--g-600);
                                    border-radius:12px; display:flex; align-items:center;
                                    justify-content:center; flex-shrink:0;">
                            <i class="ph ph-buildings" style="color:#fff; font-size:22px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-size:15px; font-weight:700; color:var(--g-700);">
                                {{ $job->title ?? 'Job posting removed' }}
                            </div>
                            <div style="font-size:12px; color:var(--n-500);">
                                {{ $job->company->company_name ?? 'Company' }}
                            </div>
                        </div>
                        <span class="d-inline-flex align-items-center gap-1" style="{{ $statusMeta[1] }}font-size:10.5px;font-weight:600;">
                            <i class="{{ $statusMeta[2] }}" style="font-size:12px;"></i>{{ $statusMeta[0] }}
                        </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($job?->location)
                        <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                            <i class="ph ph-map-pin me-1"></i>{{ $job->location }}
                        </span>
                        @endif
                        <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                            <i class="ph ph-calendar-blank me-1"></i>
                            Applied {{ $application->created_at->format('M d, Y') }}
                        </span>
                        @if($application->match_percentage)
                        <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                            <i class="ph ph-target me-1"></i>{{ rtrim(rtrim(number_format($application->match_percentage, 1), '0'), '.') }}% match
                        </span>
                        @endif
                    </div>

                    {{-- Ang schedule kay ang sunod nga buhaton sa jobseeker, mao
                         nga kini ang gipakita, dili ang deadline sa posting. --}}
                    @if($job?->interview_date)
                    <div class="p-2 rounded-3 mb-3" style="background:var(--n-50); font-size:11.5px; color:var(--n-700);">
                        <i class="ph ph-calendar-check me-1" style="color:var(--g-600);"></i>
                        @php
                            $scheduleLabel = match($job->schedule_type) {
                                'inhouse'      => 'In-house interview',
                                'job_fair'     => 'Job fair',
                                default        => 'Company interview',
                            };
                        @endphp
                        {{-- Sa in-house, ang tibuok range ang naka-reserba sa
                             employer. Siya ang mopili kung asa didto siya
                             mag-interview, mao nga ang range ang gipakita ug
                             gisultihan ang jobseeker nga maghulat sa employer. --}}
                        @if($job->schedule_type === 'inhouse')
                            {{ $scheduleLabel }} on {{ $job->schedule_window_label }}
                            @if($job->preferred_date && !$job->preferred_date_last->isSameDay($job->preferred_date))
                                <br><span style="color:var(--n-500);">The employer will tell you which of these days to come in.</span>
                            @endif
                        @else
                            {{ $scheduleLabel }} on {{ $job->interview_date->format('M d, Y') }}
                        @endif
                    </div>
                    @endif

                    @if($job)
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('jobseeker.jobs.show', $job->job_qualifications_id) }}"
                           class="btn btn-peso-outline btn-sm px-3">
                            <i class="ph ph-eye me-1"></i> View posting
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection
