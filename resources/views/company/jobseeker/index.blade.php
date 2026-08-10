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
            placeholder="Search job position..." value="{{ $search }}">
        <button type="submit" class="btn btn-peso">Search</button>
        @if($search)
            <a href="{{ route('company.jobseekers') }}" class="btn btn-peso-outline">Clear</a>
        @endif
    </div>
</form>

@if($jobs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-briefcase" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job vacancies yet</div>
        <div class="text-muted small mt-1">Post a job vacancy to start seeing qualified applicants here.</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Date</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Schedule Type</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $job)
                    @php
                        $scheduleLabels = [
                            'inhouse'      => 'In-house',
                            'office_based' => 'Office Based',
                            'job_fair'     => 'Job Fair',
                        ];
                        $scheduleLabel = $scheduleLabels[$job->schedule_type] ?? ucfirst(str_replace('_', ' ', $job->schedule_type ?? 'N/A'));
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;">
                            <div class="fw-semibold" style="color:#2d7a5f;">{{ $job->title }}</div>
                            <div style="font-size:11px;color:#888;"><i class="bi bi-geo-alt me-1"></i>{{ $job->location }}</div>
                        </td>
                        <td style="padding:12px 16px;color:#888;">
                            {{ $job->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span class="fw-semibold" style="font-size:11px;padding:4px 10px;border-radius:20px;background:#f0f9f6;color:#2d7a5f;">
                                {{ $scheduleLabel }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <a href="{{ route('company.jobs.qualified', $job->job_qualifications_id) }}" class="btn btn-peso btn-sm px-3">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $jobs->appends(request()->query())->links() }}
    </div>
@endif

@endsection