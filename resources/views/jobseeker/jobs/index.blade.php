@extends('jobseeker.layouts.app')

@section('page-title', '')

@section('content')

{{-- ── HEADER + SEARCH (same row) ── --}}
<div class="d-flex align-items-end justify-content-between mb-3 fade-in flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f; font-size:18px;">
            List of Job Vacancies
        </h5>
        <p class="mb-0" style="font-size:13px; color:#888;">
            Browse available job postings from verified employers.
        </p>
    </div>
    <form method="GET" action="{{ route('jobseeker.jobs') }}" class="d-flex gap-2">
        <input type="hidden" name="job_type" value="{{ $jobType }}">
        <input type="text" name="search" class="form-control peso-input"
               placeholder="Search by job title or location..."
               style="width:260px;"
               value="{{ request('search') }}">
        <button type="submit" class="btn btn-peso">
            <i class="bi bi-search me-1"></i> Search
        </button>
    </form>
</div>

<div class="d-flex gap-2 mb-4 fade-in" style="flex-wrap:wrap;">
    @foreach(['all' => 'All', 'local' => 'Local', 'overseas' => 'Overseas', 'job_fair' => 'Job Fair'] as $val => $label)
    <a href="{{ route('jobseeker.jobs', array_merge(request()->except('page'), ['job_type' => $val])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $jobType === $val
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:20px;font-size:12px;padding:5px 16px;">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- ── JOB LIST ── --}}
@if($jobs->isEmpty())
    <div class="peso-card fade-in">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-briefcase"></i>
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
                        <div style="width:48px; height:48px; background:linear-gradient(135deg,#90d870,#4dd9c0);
                                    border-radius:12px; display:flex; align-items:center;
                                    justify-content:center; flex-shrink:0;">
                            <i class="bi bi-building" style="color:#fff; font-size:22px;"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span style="font-size:15px; font-weight:700; color:#2d7a5f;">
                                    {{ $job->title }}
                                </span>
                                @if(in_array(strtolower($job->title), array_map('strtolower', $preferredOccupations)))
                                <span style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:10px;padding:3px 10px;border-radius:20px;font-weight:700;white-space:nowrap;">
                                    <i class="bi bi-star-fill me-1"></i>Recommended
                                </span>
                                @endif
                            </div>
                            <div style="font-size:12px; color:#888;">
                                {{ $job->company->company_name ?? 'Company' }}
                            </div>
                        </div>
                    </div>

                    {{-- Details ──  --}}
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span style="background:#f0f9f6; color:#2d7a5f; font-size:11px;
                                     padding:4px 10px; border-radius:20px; font-weight:600;">
                            <i class="bi bi-geo-alt me-1"></i>{{ $job->location }}
                        </span>
                        <span style="background:#f0f9f6; color:#2d7a5f; font-size:11px;
                                     padding:4px 10px; border-radius:20px; font-weight:600;">
                            <i class="bi bi-clock me-1"></i>{{ ucfirst(str_replace('_', ' ', $job->type)) }}
                        </span>
                        <span style="background:#f0f9f6; color:#2d7a5f; font-size:11px;
                                     padding:4px 10px; border-radius:20px; font-weight:600;">
                            <i class="bi bi-people me-1"></i>{{ $job->slots }} slot/s
                        </span>
                        @if($job->deadline)
                        <span style="background:#fff8e1; color:#f9a825; font-size:11px;
                                     padding:4px 10px; border-radius:20px; font-weight:600;">
                            <i class="bi bi-calendar me-1"></i>
                            Until {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                        </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($job->description)
                    <p style="font-size:12px; color:#666; line-height:1.5; margin-bottom:12px;"
                       class="text-truncate-3">
                        {{ Str::limit($job->description, 100) }}
                    </p>
                    @endif

                    {{-- Apply Button --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('jobseeker.jobs.show', $job->job_qualifications_id) }}"
                           class="btn btn-peso btn-sm px-3">
                            <i class="bi bi-eye me-1"></i> View & Apply
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
                box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid #e8f5f0;">
        @if($jobs->onFirstPage())
            <button type="button" class="btn btn-sm" disabled
                style="border:1.5px solid #e0e0e0;border-radius:8px;color:#ccc;padding:6px 14px;">
                <i class="bi bi-chevron-left"></i>
            </button>
        @else
            <a href="{{ $jobs->appends(request()->query())->previousPageUrl() }}" class="btn btn-sm"
                style="border:1.5px solid #a8e6cf;border-radius:8px;color:#2d7a5f;padding:6px 14px;">
                <i class="bi bi-chevron-left"></i>
            </a>
        @endif

        <span style="font-size:13px;color:#2d7a5f;font-weight:600;white-space:nowrap;">
            Page {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}
        </span>

        @if($jobs->hasMorePages())
            <a href="{{ $jobs->appends(request()->query())->nextPageUrl() }}" class="btn btn-sm fw-semibold"
                style="border:none;border-radius:8px;color:#fff;padding:6px 14px;background:linear-gradient(90deg,#90d870,#4dd9c0);">
                <i class="bi bi-chevron-right"></i>
            </a>
        @else
            <button type="button" class="btn btn-sm fw-semibold" disabled
                style="border:none;border-radius:8px;color:#fff;padding:6px 14px;background:#ccc;">
                <i class="bi bi-chevron-right"></i>
            </button>
        @endif
    </div>
    @endif
@endif

@endsection