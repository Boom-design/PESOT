@extends('jobseeker.layouts.app')

@section('page-title', '')

@section('content')

{{-- ── HEADER + SEARCH (same row) ── --}}
<div class="d-flex align-items-end justify-content-between mb-3 fade-in flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700); font-size:18px;">
            List of Job Vacancies
        </h5>
        <p class="mb-0" style="font-size:13px; color:var(--n-500);">
            Browse available job postings from verified employers.
        </p>
    </div>
    <form method="GET" action="{{ route('jobseeker.jobs') }}" class="d-flex gap-2 align-items-center">
        <input type="hidden" name="job_type" value="{{ $jobType }}">
        <select name="sort" class="form-select peso-input" style="width:180px;font-size:12px;"
                onchange="this.form.submit()">
            <option value="latest"     {{ $sort === 'latest'     ? 'selected' : '' }}>Newest first</option>
            <option value="location"   {{ $sort === 'location'   ? 'selected' : '' }}>Nearest to me</option>
            <option value="background" {{ $sort === 'background' ? 'selected' : '' }}>Fits my background</option>
        </select>
        <input type="text" name="search" class="form-control peso-input"
               placeholder="Search by job title or location..."
               style="width:260px;"
               value="{{ request('search') }}">
        <button type="submit" class="btn btn-peso">
            <i class="ph ph-magnifying-glass me-1"></i> Search
        </button>
    </form>
</div>

<div class="d-flex gap-2 mb-4 fade-in" style="flex-wrap:wrap;">
    @foreach($tabs as $val => $label)
    <a href="{{ route('jobseeker.jobs', array_merge(request()->except('page'), ['job_type' => $val, 'sort' => $sort])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $jobType === $val
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:20px;font-size:12px;padding:5px 16px;">
        {{ $label }}
    </a>
    @endforeach

    {{-- Say why the list is narrowed, and where to widen it. Without this the
         missing vacancies just look like there are none. --}}
    @if(in_array($classification, ['local', 'overseas'], true))
        <span class="d-inline-flex align-items-center" style="font-size:11.5px;color:var(--n-500);">
            <i class="ph ph-info me-1" style="color:var(--g-600);"></i>
            Showing {{ $classification }} vacancies only —
            <a href="{{ route('jobseeker.nsrp') }}" class="ms-1" style="color:var(--g-600);font-weight:600;">
                change your classification
            </a>
        </span>
    @endif
</div>

{{-- ── JOB LIST ── --}}
@if($jobs->isEmpty())
    <div class="peso-card fade-in">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="ph ph-briefcase"></i>
            </div>
            <h6>No job vacancies found</h6>
            <p>Check back later for new opportunities.</p>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach($jobs as $job)
        <div class="col-md-6 fade-in">
            <div class="peso-card h-100" style="transition: transform 0.2s, box-shadow 0.2s;overflow:hidden;"
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(77,217,192,0.15)'"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                @if($job->poster_image)
                <img src="{{ asset('storage/' . $job->poster_image) }}" alt="{{ $job->title }} poster"
                     style="width:100%;height:160px;object-fit:cover;display:block;">
                @endif
                <div class="peso-card-body">
                    {{-- Company + Title --}}
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="width:48px; height:48px; background:var(--g-600);
                                    border-radius:12px; display:flex; align-items:center;
                                    justify-content:center; flex-shrink:0;">
                            <i class="ph ph-buildings" style="color:#fff; font-size:22px;"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span style="font-size:15px; font-weight:700; color:var(--g-700);">
                                    {{ $job->title }}
                                </span>
                                @if(in_array(strtolower($job->title), array_map('strtolower', $preferredOccupations)))
                                <span style="color:var(--g-600);font-size:10px;font-weight:700;white-space:nowrap;">
                                    <i class="ph-fill ph-star me-1"></i>Recommended
                                </span>
                                @endif
                            </div>
                            <div style="font-size:12px; color:var(--n-500);">
                                {{ $job->company->company_name ?? 'Company' }}
                            </div>
                        </div>
                    </div>

                    {{-- Ang parehas nga position mahimong mo-tunga ug duha o tulo
                         ka card — usa kada schedule type. Usa ra kadto ka bakante,
                         apan managlahi ang adlaw, venue ug proseso, ug ang
                         jobseeker maoy mopili (PESO interview: "mahimo siyang
                         mopili og company interview welder, in-house welder").
                         Kung dili ipakita ang schedule type, morag doble ra. --}}
                    @php
                        [$schedLabel, $schedIcon, $schedBg, $schedFg] = match($job->schedule_type) {
                            'inhouse'  => ['In-house Interview', 'ph-calendar-check', '#eef4ff', '#2a4d9b'],
                            'job_fair' => ['Job Fair',           'ph-calendar-dots',  '#e8f7f0', '#0f7a5f'],
                            default    => ['Company Interview',       'ph-buildings',      '#fff5e0', '#8a5c00'],
                        };
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3"
                         style="background:{{ $schedBg }};">
                        <i class="ph-fill {{ $schedIcon }}" style="color:{{ $schedFg }};font-size:16px;"></i>
                        <div style="font-size:11.5px;color:{{ $schedFg }};line-height:1.4;">
                            <strong>{{ $schedLabel }}</strong>
                            @if($job->schedule_type === 'inhouse' && $job->preferred_date)
                                — {{ $job->schedule_window_label }}
                            @elseif($job->interview_date)
                                — {{ $job->interview_date->format('M d, Y') }}
                            @endif
                            @if($job->schedule_type === 'inhouse' && $job->venue_type)
                                <br><span style="opacity:.85;">{{ $job->venue_type === 'peso_office' ? 'At the PESO Office' : ($job->venue_address ?: 'Employer venue') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Details ──  --}}
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                            <i class="ph ph-map-pin me-1"></i>{{ $job->location }}
                        </span>
                        <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                            <i class="ph ph-clock me-1"></i>{{ ucfirst(str_replace('_', ' ', $job->type)) }}
                        </span>
                        <span style="color:var(--g-700);font-size:11px;font-weight:600;">
                            <i class="ph ph-users-three me-1"></i>{{ $job->slots }} slot/s
                        </span>
                        @if($job->deadline)
                        <span style="color:var(--warn);font-size:11px;font-weight:600;">
                            <i class="ph ph-calendar-blank me-1"></i>
                            Until {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                        </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($job->description)
                    <p style="font-size:12px; color:var(--n-700); line-height:1.5; margin-bottom:12px;"
                       class="text-truncate-3">
                        {{ Str::limit($job->description, 100) }}
                    </p>
                    @endif

                    {{-- Apply Button --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('jobseeker.jobs.show', $job->job_qualifications_id) }}"
                           class="btn btn-peso btn-sm px-3">
                            <i class="ph ph-eye me-1"></i> View & Apply
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    {{-- spacer para dili ma-cover sa fixed nav ang katapusang content --}}
    <div style="height:90px;"></div>

    {{-- ── FIXED BOTTOM-RIGHT PAGINATION NAV ── --}}
    @if($jobs->hasPages())
    <div class="d-flex align-items-center gap-3"
        style="position:fixed; bottom:24px; right:24px; z-index:500;
                background:#fff; padding:10px 16px; border-radius:14px;
                box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid var(--n-200);">
        @if($jobs->onFirstPage())
            <button type="button" class="btn btn-sm" disabled
                style="border:1px solid var(--n-200);border-radius:8px;color:var(--n-300);padding:6px 14px;">
                <i class="ph ph-caret-left"></i>
            </button>
        @else
            <a href="{{ $jobs->appends(request()->query())->previousPageUrl() }}" class="btn btn-sm"
                style="border:1px solid var(--n-200);border-radius:8px;color:var(--g-700);padding:6px 14px;">
                <i class="ph ph-caret-left"></i>
            </a>
        @endif

        <span style="font-size:13px;color:var(--g-700);font-weight:600;white-space:nowrap;">
            Page {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}
        </span>

        @if($jobs->hasMorePages())
            <a href="{{ $jobs->appends(request()->query())->nextPageUrl() }}" class="btn btn-sm fw-semibold"
                style="border:none;border-radius:8px;color:#fff;padding:6px 14px;background:var(--g-600);">
                <i class="ph ph-caret-right"></i>
            </a>
        @else
            <button type="button" class="btn btn-sm fw-semibold" disabled
                style="border:none;border-radius:8px;color:#fff;padding:6px 14px;background:var(--n-300);">
                <i class="ph ph-caret-right"></i>
            </button>
        @endif
    </div>
    @endif
@endif

@endsection