@extends('company.layouts.app')

@section('page-title', ' ')

@section('content')

<div class="mb-4 fade-in">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-calendar2-week-fill me-2" style="color:#4dd9c0;"></i>List of Job Vacancy Schedule
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        Qualified jobseekers per job vacancy, latest posting first.
    </p>
</div>

<form method="GET" action="{{ route('company.jobseekers') }}" class="mb-3">
    <div class="input-group" style="max-width:360px;">
        <span class="input-group-text" style="background:#fff;border:1.5px solid var(--peso-border);border-right:none;">
            <i class="bi bi-search" style="color:#888;"></i>
        </span>
        <input type="text" name="search" class="form-control peso-input" style="border-left:none;"
            placeholder="Search jobseeker name..." value="{{ $search }}">
        <button type="submit" class="btn btn-peso">Search</button>
        @if($search)
            <a href="{{ route('company.jobseekers') }}" class="btn btn-peso-outline">Clear</a>
        @endif
    </div>
</form>

@if($jobsData->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-briefcase" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job vacancies yet</div>
        <div class="text-muted small mt-1">Post a job vacancy to start seeing qualified applicants here.</div>
    </div>
@else
    <ul class="nav nav-tabs mb-3" id="jobVacancyTabs" role="tablist" style="border-bottom:2px solid #e8f5f0;">
        @foreach($jobsData as $i => $data)
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold {{ $i === 0 ? 'active' : '' }}"
                id="job-{{ $data['job']->id }}-tab" data-bs-toggle="tab"
                data-bs-target="#job-{{ $data['job']->id }}-pane" type="button" role="tab"
                style="color:{{ $i === 0 ? '#2d7a5f' : '#888' }};font-size:13px;">
                {{ $data['job']->title }}
                <span class="badge rounded-pill ms-1" style="background:#e8f8f3;color:#2d7a5f;">
                    {{ $data['applicants']->count() }}
                </span>
            </button>
        </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($jobsData as $i => $data)
        <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="job-{{ $data['job']->id }}-pane" role="tabpanel">

            <div class="mb-2" style="font-size:12px;color:#888;">
                <i class="bi bi-geo-alt me-1"></i>{{ $data['job']->location }}
                &nbsp;·&nbsp;
                <i class="bi bi-calendar me-1"></i>Posted {{ $data['job']->created_at->format('M d, Y') }}
            </div>

            @if($data['applicants']->isEmpty())
                <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
                    <i class="bi bi-people" style="font-size:48px;color:#c0e8dc;"></i>
                    <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No qualified jobseekers yet</div>
                    <div class="text-muted small mt-1">Jobseekers with a match score of 50% or higher will appear here.</div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                                    <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Qualification</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['applicants'] as $j => $app)
                                <tr style="font-size:13px;">
                                    <td style="padding:12px 16px;color:#888;">{{ $j + 1 }}</td>
                                    <td style="padding:12px 16px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                                        border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                        color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                                {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">
                                                    {{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? '' }}
                                                </div>
                                                <div style="font-size:11px;color:#888;">{{ $app->jobseeker->email ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding:12px 16px;text-align:center;">
                                        <span class="fw-semibold"
                                            style="color:{{ $app->match_percentage >= 75 ? '#2d7a5f' : '#f59e0b' }}">
                                            {{ $app->match_percentage }}%
                                        </span>
                                    </td>
                                    <td style="padding:12px 16px;text-align:center;">
                                        @if($app->match_percentage >= 75)
                                            <span class="fw-semibold" style="font-size:11px;padding:4px 10px;border-radius:20px;background:#e8f8f3;color:#2d7a5f;">
                                                Highly Qualified
                                            </span>
                                        @else
                                            <span class="fw-semibold" style="font-size:11px;padding:4px 10px;border-radius:20px;background:#fff8e1;color:#f59e0b;">
                                                Qualified
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        @endforeach
    </div>
@endif

@endsection