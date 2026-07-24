@extends('jobseeker.layouts.app')

@section('page-title', 'Job Detail')

@section('content')

{{-- ── BACK BUTTON ── --}}
<div class="mb-3 fade-in">
    <a href="{{ route('jobseeker.jobs') }}" class="btn btn-peso-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Jobs
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
                    <div style="width:60px; height:60px; background:linear-gradient(135deg,#90d870,#4dd9c0);
                                border-radius:14px; display:flex; align-items:center;
                                justify-content:center; flex-shrink:0;">
                        <i class="bi bi-building" style="color:#fff; font-size:28px;"></i>
                    </div>
                    <div>
                        <h4 style="font-size:20px; font-weight:700; color:#2d7a5f; margin-bottom:4px;">
                            {{ $job->title }}
                        </h4>
                        <div style="font-size:13px; color:#888;">
                            {{ $job->company->company_name ?? 'Company' }}
                        </div>
                    </div>
                </div>

                {{-- Tags ── --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span style="background:#f0f9f6; color:#2d7a5f; font-size:12px;
                                 padding:6px 14px; border-radius:20px; font-weight:600;">
                        <i class="bi bi-geo-alt me-1"></i>{{ $job->location }}
                    </span>
                    <span style="background:#f0f9f6; color:#2d7a5f; font-size:12px;
                                 padding:6px 14px; border-radius:20px; font-weight:600;">
                        <i class="bi bi-clock me-1"></i>{{ ucfirst(str_replace('_', ' ', $job->type)) }}
                    </span>
                    <span style="background:#f0f9f6; color:#2d7a5f; font-size:12px;
                                 padding:6px 14px; border-radius:20px; font-weight:600;">
                        <i class="bi bi-people me-1"></i>{{ $job->slots }} slot/s
                    </span>
                    @if($job->deadline)
                    <span style="background:#fff8e1; color:#f9a825; font-size:12px;
                                 padding:6px 14px; border-radius:20px; font-weight:600;">
                        <i class="bi bi-calendar me-1"></i>
                        Deadline: {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                    </span>
                    @endif
                    <span class="badge-{{ $job->status }}" style="font-size:12px; padding:6px 14px;">
                        {{ ucfirst($job->status) }}
                    </span>
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <h6 style="font-size:13px; font-weight:700; color:#2d7a5f; margin-bottom:8px;">
                        <i class="bi bi-file-text me-2" style="color:#4dd9c0;"></i>Job Description
                    </h6>
                    <p style="font-size:13px; color:#555; line-height:1.8; white-space:pre-line;">
                        {{ $job->description ?: 'No description provided.' }}
                    </p>
                </div>

                @if(!$nsrp)
                <div class="p-3 rounded-3 mb-4" style="background:#fff8e1; border:1.5px solid #f9a825;">
                    <p style="font-size:12px; color:#a06800; margin:0;">
                        <i class="bi bi-lock-fill me-1"></i>
                        Detailed qualification requirements (age, height, sex, education, experience) are hidden
                        until you complete your NSRP Registration Form.
                    </p>
                </div>
                @endif

                {{-- Requirements ── --}}
                @if($nsrp && ($job->skills_required || $job->education_required || $job->experience_required))
                <div class="mb-4">
                    <h6 style="font-size:13px; font-weight:700; color:#2d7a5f; margin-bottom:12px;">
                        <i class="bi bi-list-check me-2" style="color:#4dd9c0;"></i>Job Requirements
                    </h6>
                    <div class="row g-2">
                        @if($job->age_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:#f0f9f6; border:1px solid #a8e6cf;">
                                <i class="bi bi-person-badge" style="color:#4dd9c0;"></i>
                                <span style="font-size:12px; color:#2d7a5f;">
                                    Age: {{ $job->age_min }} - {{ $job->age_max }} years old
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->height_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:#f0f9f6; border:1px solid #a8e6cf;">
                                <i class="bi bi-rulers" style="color:#4dd9c0;"></i>
                                <span style="font-size:12px; color:#2d7a5f;">
                                    Height: At least {{ $job->height_minimum }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->sex_preference !== 'Any')
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:#f0f9f6; border:1px solid #a8e6cf;">
                                <i class="bi bi-gender-ambiguous" style="color:#4dd9c0;"></i>
                                <span style="font-size:12px; color:#2d7a5f;">
                                    Sex: {{ $job->sex_preference }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->education_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:#f0f9f6; border:1px solid #a8e6cf;">
                                <i class="bi bi-mortarboard" style="color:#4dd9c0;"></i>
                                <span style="font-size:12px; color:#2d7a5f;">
                                    Education: {{ $job->education_required }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->experience_required)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                 style="background:#f0f9f6; border:1px solid #a8e6cf;">
                                <i class="bi bi-briefcase" style="color:#4dd9c0;"></i>
                                <span style="font-size:12px; color:#2d7a5f;">
                                    Experience: {{ $job->experience_years }} year/s
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($job->skills_required)
                        @php $skills = is_array($job->skills_required) ? $job->skills_required : json_decode($job->skills_required, true); @endphp
                        @if(!empty($skills))
                        <div class="col-12">
                            <div class="p-2 rounded-2" style="background:#f0f9f6; border:1px solid #a8e6cf;">
                                <div style="font-size:12px; color:#2d7a5f; margin-bottom:6px;">
                                    <i class="bi bi-tools me-1" style="color:#4dd9c0;"></i>
                                    <strong>Skills Required:</strong>
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($skills as $skill)
                                    <span style="background:#2d7a5f; color:#fff; font-size:11px;
                                                 padding:3px 10px; border-radius:20px;">
                                        {{ $skill }}
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
                     style="background:{{ $matchPercentage >= 75 ? '#e8f8f3' : ($matchPercentage >= 50 ? '#fff8e1' : '#fff5f5') }};
                            border:1.5px solid {{ $matchPercentage >= 75 ? '#4dd9c0' : ($matchPercentage >= 50 ? '#f9a825' : '#e53935') }};">
                    <div style="font-size:36px; font-weight:800;
                                color:{{ $matchPercentage >= 75 ? '#2d7a5f' : ($matchPercentage >= 50 ? '#f9a825' : '#e53935') }};">
                        {{ $matchPercentage }}%
                    </div>
                    <div style="font-size:13px; font-weight:700;
                                color:{{ $matchPercentage >= 75 ? '#2d7a5f' : ($matchPercentage >= 50 ? '#f9a825' : '#e53935') }};">
                        @if($matchPercentage >= 75)
                            ✅ Highly Qualified
                        @elseif($matchPercentage >= 50)
                            ⚠️ Qualified
                        @else
                            ❌ May Not Meet Requirements
                        @endif
                    </div>
                    <div style="font-size:11px; color:#888; margin-top:4px;">Match Percentage</div>

                    {{-- Progress bar --}}
                    <div class="mt-2" style="height:8px; background:#e0e0e0; border-radius:4px; overflow:hidden;">
                        <div style="width:{{ $matchPercentage }}%; height:100%;
                                    background:{{ $matchPercentage >= 75 ? '#2d7a5f' : ($matchPercentage >= 50 ? '#f9a825' : '#e53935') }};
                                    border-radius:4px; transition:width 1s ease;"></div>
                    </div>
                </div>

                {{-- Disclaimer --}}
                <div class="p-2 mb-3 rounded-2" style="background:#f5f5f5;">
                    <p style="font-size:10px; color:#888; margin:0; line-height:1.5;">
                        <i class="bi bi-info-circle me-1"></i>
                        Match percentage is based on your NSRP form qualifications.
                        Final hiring decision rests with the employer.
                    </p>
                </div>
                @endif

                {{-- Apply / Status ── --}}
                @if($alreadyApplied)
                    <div class="text-center p-3 rounded-3 mb-3"
                         style="background:#e8f0fe; border:1.5px solid #3949ab;">
                        <i class="bi bi-check-circle-fill" style="color:#3949ab; font-size:24px;"></i>
                        <div style="font-size:13px; font-weight:700; color:#3949ab; margin-top:6px;">
                            Already Applied
                        </div>
                        <div style="font-size:11px; color:#888;">
                            Status: <strong>{{ ucfirst($application->status) }}</strong>
                        </div>
                    </div>
                @elseif(!$nsrp)
                    <div class="text-center p-3 rounded-3 mb-3"
                         style="background:#fff8e1; border:1.5px solid #f9a825;">
                        <i class="bi bi-exclamation-triangle-fill" style="color:#f9a825; font-size:24px;"></i>
                        <div style="font-size:13px; font-weight:700; color:#f9a825; margin-top:6px;">
                            NSRP Form Required
                        </div>
                        <div style="font-size:11px; color:#888; margin-bottom:10px;">
                            Please submit your NSRP form before applying.
                        </div>
                        <a href="{{ route('jobseeker.nsrp') }}" class="btn btn-peso btn-sm w-100">
                            <i class="bi bi-clipboard-check me-1"></i> Fill Up NSRP Form
                        </a>
                    </div>
                @else
                    <form action="{{ route('jobseeker.jobs.apply', $job->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-peso w-100 py-3"
                                style="font-size:15px; font-weight:700;">
                            <i class="bi bi-send me-2"></i> Apply Now
                        </button>
                    </form>
                @endif

                {{-- Job Info Summary ── --}}
                <hr style="border-color:#e8f5f0; margin:16px 0;">
                <div style="font-size:12px; color:#666;">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#888;">Posted by</span>
                        <span style="font-weight:600; color:#2d7a5f;">
                            {{ $job->company->company_name ?? 'Company' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#888;">Slots</span>
                        <span style="font-weight:600; color:#2d7a5f;">{{ $job->slots }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:#888;">Type</span>
                        <span style="font-weight:600; color:#2d7a5f;">
                            {{ ucfirst(str_replace('_', ' ', $job->type)) }}
                        </span>
                    </div>
                    @if($job->deadline)
                    <div class="d-flex justify-content-between">
                        <span style="color:#888;">Deadline</span>
                        <span style="font-weight:600; color:#f9a825;">
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