@extends('jobseeker.layouts.app')

@section('page-title', 'Job Detail')

@section('content')

{{-- ── BACK BUTTON ── --}}
<div class="mb-3 fade-in">
    <a href="{{ route('jobseeker.jobs') }}" class="btn btn-peso-outline btn-sm">
        <i class="ph ph-arrow-left me-1"></i> Back to Jobs
    </a>
</div>

<div class="row g-4">

    {{-- ── JOB DETAIL CARD ── --}}
    <div class="col-md-8">
        <div class="peso-card fade-in" style="overflow:hidden;">
            @if($job->poster_image)
            <img src="{{ asset('storage/' . $job->poster_image) }}" alt="{{ $job->title }} hiring poster"
                 style="width:100%;max-height:360px;object-fit:cover;display:block;cursor:pointer;"
                 onclick="document.getElementById('posterFullModal').style.display='flex'">
            @endif
            <div class="peso-card-body">

                {{-- Company + Title --}}
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div style="width:60px; height:60px; background:var(--g-600);
                                border-radius:14px; display:flex; align-items:center;
                                justify-content:center; flex-shrink:0;">
                        <i class="ph ph-buildings" style="color:#fff; font-size:28px;"></i>
                    </div>
                    <div>
                        <h4 style="font-size:20px; font-weight:700; color:var(--g-700); margin-bottom:4px;">
                            {{ $job->title }}
                        </h4>
                        <div style="font-size:13px; color:var(--n-500);">
                            {{ $job->company->company_name ?? 'Company' }}
                        </div>
                    </div>
                </div>

                {{-- Tags ── --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span style="color:var(--g-700);font-size:12px;font-weight:600;">
                        <i class="ph ph-map-pin me-1"></i>{{ $job->location }}
                    </span>
                    <span style="color:var(--g-700);font-size:12px;font-weight:600;">
                        <i class="ph ph-clock me-1"></i>{{ ucfirst(str_replace('_', ' ', $job->type)) }}
                    </span>
                    <span style="color:var(--g-700);font-size:12px;font-weight:600;">
                        <i class="ph ph-users-three me-1"></i>{{ $job->slots }} slot/s
                    </span>
                    @if($job->deadline)
                    <span style="color:var(--warn);font-size:12px;font-weight:600;">
                        <i class="ph ph-calendar-blank me-1"></i>
                        Deadline: {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                    </span>
                    @endif
                    <span class="badge-{{ $job->status }}" style="font-size:12px;font-weight:600;">
                        {{ ucfirst($job->status) }}
                    </span>
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <h6 style="font-size:13px; font-weight:700; color:var(--g-700); margin-bottom:8px;">
                        <i class="ph ph-file-text me-2" style="color:var(--g-600);"></i>Job Description
                    </h6>
                    @include('partials.job-description', ['description' => $job->description])
                </div>

                @if(!$nsrp)
                <div class="p-3 rounded-3 mb-4" style="background:var(--warn-bg); border:1px solid var(--warn);">
                    <p style="font-size:12px; color:var(--warn); margin:0;">
                        <i class="ph-fill ph-lock-simple me-1"></i>
                        Detailed qualification requirements (age, height, sex, education, experience) are hidden
                        until you complete your NSRP Registration Form.
                    </p>
                </div>
                @endif

                {{-- Requirements ── --}}
                @if($nsrp && ($job->skills_required || $job->education_required || $job->experience_required))
                <div class="mb-4">
                    <h6 style="font-size:13px; font-weight:700; color:var(--g-700); margin-bottom:12px;">
                        <i class="ph ph-list-checks me-2" style="color:var(--g-600);"></i>Job Requirements
                    </h6>
                    <div class="row g-2">
                        @if($job->age_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:var(--n-50); border:1px solid var(--n-200);">
                                <i class="ph ph-identification-badge" style="color:var(--g-600);"></i>
                                <span style="font-size:12px; color:var(--g-700);">
                                    Age: {{ $job->age_min }} - {{ $job->age_max }} years old
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->height_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:var(--n-50); border:1px solid var(--n-200);">
                                <i class="ph ph-ruler" style="color:var(--g-600);"></i>
                                <span style="font-size:12px; color:var(--g-700);">
                                    Height: At least {{ $job->height_minimum }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->sex_preference !== 'Any')
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:var(--n-50); border:1px solid var(--n-200);">
                                <i class="ph ph-gender-intersex" style="color:var(--g-600);"></i>
                                <span style="font-size:12px; color:var(--g-700);">
                                    Sex: {{ $job->sex_preference }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->education_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:var(--n-50); border:1px solid var(--n-200);">
                                <i class="ph ph-graduation-cap" style="color:var(--g-600);"></i>
                                <span style="font-size:12px; color:var(--g-700);">
                                    Education: {{ $job->education_required }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->experience_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:var(--n-50); border:1px solid var(--n-200);">
                                <i class="ph ph-briefcase" style="color:var(--g-600);"></i>
                                <span style="font-size:12px; color:var(--g-700);">
                                    Experience: {{ $job->experience_years }} year/s
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->skills_required)
                        @php $skills = is_array($job->skills_required) ? $job->skills_required : json_decode($job->skills_required, true); @endphp
                        @if(!empty($skills))
                        <div class="col-12">
                            <div class="p-2 rounded-2" style="background:var(--n-50); border:1px solid var(--n-200);">
                                <div style="font-size:12px; color:var(--g-700); margin-bottom:6px;">
                                    <i class="ph ph-wrench me-1" style="color:var(--g-600);"></i>
                                    <strong>Skills Required:</strong>
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($skills as $skill)
                                    <span style="color:var(--g-700);font-size:11px;">
                                        {{ $skill }}@if(!$loop->last),@endif
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
                @endif

                {{-- Match Breakdown ── --}}
                @if(!empty($matchCriteria))
                <div class="mb-2">
                    <h6 style="font-size:13px; font-weight:700; color:var(--g-700); margin-bottom:12px;">
                        <i class="ph ph-list-checks me-2" style="color:var(--g-600);"></i>Match Breakdown
                    </h6>
                    <div class="row g-2">
                        @foreach($matchCriteria as $c)
                        @php
                            $iconColor = $c['matched'] === true ? 'var(--g-700)' : ($c['matched'] === 'partial' ? 'var(--warn)' : 'var(--danger)');
                            $icon      = $c['matched'] === true ? 'ph-fill ph-check-circle' : ($c['matched'] === 'partial' ? 'ph-fill ph-minus-circle' : 'ph-fill ph-x-circle');
                        @endphp
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2 p-2 rounded-2 h-100" style="background:var(--n-50); border:1px solid var(--n-200);">
                                <i class="{{ $icon }}" style="color:{{ $iconColor }}; font-size:14px; margin-top:1px;"></i>
                                <div>
                                    <div style="font-size:12px; color:var(--g-700); font-weight:600;">{{ $c['label'] }}</div>
                                    @if($c['note'])
                                    <div style="font-size:11px; color:var(--n-500);">{{ $c['note'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ── APPLY CARD ── --}}
    <div class="col-md-4">
        <div class="peso-card fade-in" style="position:sticky; top:80px;">
            <div class="peso-card-body">

                {{-- Match Percentage ── --}}
                @if($matchPercentage !== null)
                <div class="text-center mb-4 p-3 rounded-3"
                     style="background:{{ $matchPercentage >= 75 ? 'var(--g-50)' : ($matchPercentage >= 50 ? 'var(--warn-bg)' : 'var(--danger-bg)') }};
                            border:1px solid {{ $matchPercentage >= 75 ? 'var(--g-600)' : ($matchPercentage >= 50 ? 'var(--warn)' : 'var(--danger)') }};">
                    <div style="font-size:36px; font-weight:800;
                                color:{{ $matchPercentage >= 75 ? 'var(--g-700)' : ($matchPercentage >= 50 ? 'var(--warn)' : 'var(--danger)') }};">
                        {{ $matchPercentage }}%
                    </div>
                    <div style="font-size:13px; font-weight:700;
                                color:{{ $matchPercentage >= 75 ? 'var(--g-700)' : ($matchPercentage >= 50 ? 'var(--warn)' : 'var(--danger)') }};">
                        @if($matchPercentage >= 75)
                            ✅ Highly Qualified
                        @elseif($matchPercentage >= 50)
                            ⚠️ Qualified
                        @else
                            ❌ May Not Meet Requirements
                        @endif
                    </div>
                    <div style="font-size:11px; color:var(--n-500); margin-top:4px;">Match Percentage</div>

                    {{-- Progress bar --}}
                    <div class="mt-2" style="height:8px; background:var(--n-200); border-radius:4px; overflow:hidden;">
                        <div style="width:{{ $matchPercentage }}%; height:100%;
                                    background:{{ $matchPercentage >= 75 ? 'var(--g-700)' : ($matchPercentage >= 50 ? 'var(--warn)' : 'var(--danger)') }};
                                    border-radius:4px; transition:width 1s ease;"></div>
                    </div>
                </div>

                {{-- Disclaimer --}}
                <div class="p-2 mb-3 rounded-2" style="background:var(--n-100);">
                    <p style="font-size:10px; color:var(--n-500); margin:0; line-height:1.5;">
                        <i class="ph ph-info me-1"></i>
                        Match percentage is based on your NSRP form qualifications.
                        Final hiring decision rests with the employer.
                    </p>
                </div>
                @endif

                {{-- Apply / Status ── --}}
                @if($alreadyApplied)
                    @php
                        // The status already implies the application exists, so it
                        // carries the whole message on its own.
                        [$stIcon, $stFg, $stBg, $stBr] = match($application->status) {
                            'hired'    => ['ph-fill ph-check-circle',  'var(--g-700)',  'var(--g-50)',      'var(--g-200)'],
                            'waiting'  => ['ph-fill ph-hourglass-medium', 'var(--info)', 'var(--info-bg)',  'var(--info-br)'],
                            'rejected' => ['ph-fill ph-x-circle',      'var(--danger)', 'var(--danger-bg)', 'var(--danger-br)'],
                            default    => ['ph-fill ph-clock',         'var(--warn)',   'var(--warn-bg)',   'var(--warn-br)'],
                        };
                    @endphp
                    <div class="text-center p-3 rounded-3 mb-3"
                         style="background:{{ $stBg }}; border:1px solid {{ $stBr }};">
                        <i class="{{ $stIcon }}" style="color:{{ $stFg }}; font-size:30px;"></i>
                        <div style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--n-500); margin-top:8px;">
                            Status
                        </div>
                        <div style="font-size:22px; font-weight:800; letter-spacing:.02em; text-transform:uppercase; color:{{ $stFg }}; line-height:1.2;">
                            {{ $application->status }}
                        </div>
                    </div>

                    {{-- ── NI-DECLINE, APAN PWEDE PA MOBALIK ──
                         Basin namali ra siya ug click, o nakansela ang iyang
                         kalihokan. Ang rekord sa pag-decline magpabilin para sa
                         employer; ang jobseeker makabalik ra samtang buhi pa
                         ang posting (PESO pilot testing 2026-08-13). --}}
                    @php
                        $isInhouseJob = $job->schedule_type === 'inhouse';
                        $isCompanyInterview  = $job->schedule_type === 'company_interview';
                        $participation = $isInhouseJob
                            ? $application->inhouse_participation
                            : ($isCompanyInterview ? $application->company_interview_participation : null);
                        $rejoinRoute = $isInhouseJob
                            ? route('jobseeker.applications.inhouseResponse', $application->job_matching_id)
                            : ($isCompanyInterview ? route('jobseeker.applications.companyInterviewResponse', $application->job_matching_id) : null);
                        $stillOpen = in_array($job->lifecycle_status, ['active', 'waiting'], true);
                    @endphp

                    @if($participation === 'declined' && $rejoinRoute)
                        <div class="p-3 rounded-3 mb-3"
                             style="background:var(--warn-bg); border:1px solid var(--warn);">
                            <div style="font-size:12.5px; color:var(--n-700); line-height:1.6;">
                                You declined to join
                                {{ $isInhouseJob ? 'the in-house interview' : 'this job posting' }}.
                            </div>
                            @if($stillOpen)
                                <form method="POST" action="{{ $rejoinRoute }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="response" value="accepted">
                                    <button type="submit" class="btn btn-peso btn-sm w-100">
                                        <i class="ph ph-arrow-counter-clockwise me-1"></i>
                                        Changed your mind? Join anyway
                                    </button>
                                </form>
                            @else
                                <div style="font-size:11.5px; color:var(--n-500); margin-top:6px;">
                                    This posting is no longer accepting participants.
                                </div>
                            @endif
                        </div>
                    @endif
                @elseif(!$nsrp)
                    <div class="text-center p-3 rounded-3 mb-3"
                         style="background:var(--warn-bg); border:1px solid var(--warn);">
                        <i class="ph-fill ph-warning" style="color:var(--warn); font-size:24px;"></i>
                        <div style="font-size:13px; font-weight:700; color:var(--warn); margin-top:6px;">
                            NSRP Form Required
                        </div>
                        <div style="font-size:11px; color:var(--n-500); margin-bottom:10px;">
                            Please submit your NSRP form before applying.
                        </div>
                        <a href="{{ route('jobseeker.nsrp') }}" class="btn btn-peso btn-sm w-100">
                            <i class="ph ph-clipboard-text me-1"></i> Fill Up NSRP Form
                        </a>
                    </div>
                @else
                    <form action="{{ route('jobseeker.jobs.apply', $job->job_qualifications_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-peso w-100 py-3"
                                style="font-size:15px; font-weight:700;">
                            <i class="ph ph-paper-plane-tilt me-2"></i> Apply Now
                        </button>
                    </form>
                @endif

                {{-- Job Info Summary ── --}}
                <hr style="border-color:var(--n-200); margin:16px 0;">
                <div style="font-size:12px; color:var(--n-700);">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--n-500);">Posted by</span>
                        <span style="font-weight:600; color:var(--g-700);">
                            {{ $job->company->company_name ?? 'Company' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--n-500);">Slots</span>
                        <span style="font-weight:600; color:var(--g-700);">{{ $job->slots }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--n-500);">Type</span>
                        <span style="font-weight:600; color:var(--g-700);">
                            {{ ucfirst(str_replace('_', ' ', $job->type)) }}
                        </span>
                    </div>
                    @if($job->deadline)
                    <div class="d-flex justify-content-between">
                        <span style="color:var(--n-500);">Deadline</span>
                        <span style="font-weight:600; color:var(--warn);">
                            {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@if($job->poster_image)
<div id="posterFullModal" onclick="this.style.display='none'"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:9999;
            align-items:center;justify-content:center;padding:20px;cursor:zoom-out;">
    <img src="{{ asset('storage/' . $job->poster_image) }}" alt="{{ $job->title }} hiring poster"
         style="max-width:100%;max-height:90vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.5);">
</div>
@endif

@endsection

@section('scripts')
@if(session('show_inhouse_prompt') || $showInhouseParticipationPrompt ?? false)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'In-house Interview',
            text: 'Are you sure you want to participate in this in-house interview?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28812F',
            cancelButtonColor: 'var(--danger)',
            confirmButtonText: 'Yes, I will participate',
            cancelButtonText: 'No, decline',
            allowOutsideClick: false,
        }).then((result) => {
            const response = result.isConfirmed ? 'accepted' : 'declined';
            fetch('{{ url("/jobseeker/applications") }}/{{ session("show_inhouse_prompt") ?? ($application->job_matching_id ?? "") }}/inhouse-response', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ response: response }),
            }).then(() => location.reload());
        });
    });
</script>
@endif

@if(session('show_company_interview_prompt'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Company Interview',
            text: 'Are you sure you want to participate in {{ addslashes($job->company->company_name ?? "this company") }}, {{ addslashes(trim(($job->company->est_barangay ?? "") . ", " . ($job->company->est_city_municipality ?? "") . ", " . ($job->company->est_province ?? ""), ", ")) }}?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28812F',
            cancelButtonColor: 'var(--danger)',
            confirmButtonText: 'Yes, I will participate',
            cancelButtonText: 'No, decline',
            allowOutsideClick: false,
        }).then((result) => {
            const response = result.isConfirmed ? 'accepted' : 'declined';
            fetch('{{ url("/jobseeker/applications") }}/{{ session("show_company_interview_prompt") }}/company-interview-response', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ response: response }),
            }).then(() => location.reload());
        });
    });
</script>
@endif
@endsection