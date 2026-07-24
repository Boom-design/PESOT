@extends('jobseeker.layouts.app')

@section('page-title', 'Job Vacancies')

@section('content')

{{-- ── HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f; font-size:18px;">
            Job Vacancies
        </h5>
        <p class="mb-0" style="font-size:13px; color:#888;">
            Browse available job postings from verified employers.
        </p>
    </div>
</div>

{{-- ── SEARCH ── --}}
<div class="peso-card mb-4 fade-in">
    <div class="peso-card-body">
        <form method="GET" action="{{ route('jobseeker.jobs') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <label class="peso-label">Search Job</label>
                    <input type="text" name="search" class="form-control peso-input"
                           placeholder="Search by job title or location..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-peso w-100">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
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
            <div class="peso-card h-100" style="transition: transform 0.2s, box-shadow 0.2s;"
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(77,217,192,0.15)'"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div class="peso-card-body">
                    {{-- Company + Title --}}
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="width:48px; height:48px; background:linear-gradient(135deg,#90d870,#4dd9c0);
                                    border-radius:12px; display:flex; align-items:center;
                                    justify-content:center; flex-shrink:0;">
                            <i class="bi bi-building" style="color:#fff; font-size:22px;"></i>
                        </div>
                        <div>
                            <div style="font-size:15px; font-weight:700; color:#2d7a5f;">
                                {{ $job->title }}
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
                        <a href="{{ route('jobseeker.jobs.show', $job->id) }}"
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
    <div class="d-flex justify-content-center mt-4">
        {{ $jobs->appends(request()->query())->links() }}
    </div>
@endif

@endsection