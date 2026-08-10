@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    @if($staffRole === 'job_vacancy')
        <a href="{{ route('staff.jobs.all') }}"
           class="btn btn-sm fw-semibold"
           style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-list-ul me-1"></i> All Job Postings
        </a>
        <a href="{{ route('staff.jobs') }}"
           class="btn btn-sm fw-semibold"
           style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-briefcase-fill me-1"></i> Office Based
        </a>
    @elseif($staffRole === 'sra')
        <a href="{{ route('staff.inhouse') }}"
           class="btn btn-sm fw-semibold"
           style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-calendar-check-fill me-1"></i> In-house Schedule
        </a>
        <a href="{{ route('staff.jobs', ['type' => 'inhouse']) }}"
           class="btn btn-sm fw-semibold"
           style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-briefcase-fill me-1"></i> In-house Job Vacancy
        </a>
        <a href="{{ route('staff.jobs', ['type' => 'office_based']) }}"
           class="btn btn-sm fw-semibold"
           style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-briefcase-fill me-1"></i> Office Based
        </a>
    @else
        <a href="{{ route('staff.inhouse') }}"
           class="btn btn-sm fw-semibold"
           style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-calendar-check-fill me-1"></i> In-house Schedule
        </a>
        <a href="{{ route('staff.jobs') }}"
           class="btn btn-sm fw-semibold"
           style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-briefcase-fill me-1"></i> In-house Job Vacancy
        </a>
    @endif
    <a href="{{ route('staff.inhouse.jobfair') }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-calendar-event-fill me-1"></i> Job Fair
    </a>
</div>

@if($jobs !== null)
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
                <i class="bi bi-briefcase-fill me-2" style="color:#4dd9c0;"></i>
                Job Fair Postings
            </h5>
            <p class="mb-0" style="font-size:13px;color:#888;">
                Review and approve job postings for job fair use. Approved postings stay closed until a new job fair event is created.
            </p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <div class="fs-4 fw-bold" style="color:#f59e0b;">{{ $totalPendingJobs }}</div>
                <div class="text-muted small">Pending</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <div class="fs-4 fw-bold" style="color:#2d7a5f;">{{ $totalApprovedJobs }}</div>
                <div class="text-muted small">Approved</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <div class="fs-4 fw-bold" style="color:#e05252;">{{ $totalRejectedJobs }}</div>
                <div class="text-muted small">Rejected</div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-3">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved'] as $val => $label)
        <a href="{{ route('staff.inhouse.jobfair', ['job_status' => $val, 'job_page' => 1]) }}"
           class="btn btn-sm fw-semibold"
           style="{{ $jobStatus === $val
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($jobs->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 py-5 text-center" style="background:linear-gradient(180deg,#f8fdfc,#fff);">
            <div style="width:72px;height:72px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 6px 18px rgba(77,217,192,0.3);">
                <i class="bi bi-briefcase-fill" style="font-size:28px;color:#fff;"></i>
            </div>
            <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">No job fair postings found</div>
            <div class="text-muted small mt-1">Approved postings for this status will appear here.</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Slots</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs as $i => $job)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $jobs->firstItem() + $loop->index }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">{{ $job->title }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $job->company->company_name ?? '—' }}</td>
                            <td style="padding:12px 16px;color:#555;">{{ $job->slots }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $pBadge = [
                                        'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending'],
                                        'approved' => ['bg' => '#2d7a5f', 'label' => 'Approved'],
                                        'rejected' => ['bg' => '#e05252', 'label' => 'Rejected'],
                                    ][$job->posting_status] ?? ['bg' => '#888', 'label' => ucfirst($job->posting_status)];
                                @endphp
                                <span class="badge fw-semibold" style="background:{{ $pBadge['bg'] }};font-size:11px;padding:4px 10px;border-radius:20px;">
                                    {{ $pBadge['label'] }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <button type="button" class="btn btn-sm fw-semibold"
                                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 12px;"
                                    data-bs-toggle="modal" data-bs-target="#jfJobModal{{ $job->job_qualifications_id }}">
                                    <i class="bi bi-eye-fill me-1"></i>View Details
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="jfJobModal{{ $job->job_qualifications_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
                                <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
                                    <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);flex-shrink:0;">
                                        <h6 class="modal-title text-white fw-bold">
                                            <i class="bi bi-briefcase-fill me-2"></i>{{ $job->title }}
                                        </h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4" style="overflow-y:auto;flex:1;">
                                        <div class="row g-2 mb-3" style="font-size:13px;">
                                            <div class="col-md-6">
                                                <div style="color:#888;font-size:11px;">Company</div>
                                                <div style="color:#2d7a5f;font-weight:600;">{{ $job->company->company_name ?? '—' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="color:#888;font-size:11px;">Nature of Work</div>
                                                <div style="color:#2d7a5f;font-weight:600;">{{ $job->type ? ucfirst(str_replace('_',' ', $job->type)) : 'None' }}</div>
                                            </div>
                                            <div class="col-12">
                                                <div style="color:#888;font-size:11px;">Job Description</div>
                                                <div style="color:#2d7a5f;font-weight:600;">{{ $job->description ?? '—' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="color:#888;font-size:11px;">Place of Work</div>
                                                <div style="color:#2d7a5f;font-weight:600;">{{ $job->location ?? 'None' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="color:#888;font-size:11px;">Vacancy Count</div>
                                                <div style="color:#2d7a5f;font-weight:600;">{{ $job->slots ?? 'None' }}</div>
                                            </div>
                                        </div>

                                        @if($job->posting_status === 'pending')
                                        <div class="mt-3 p-3 rounded-3" style="background:#fff8e1;border:1px solid #f59e0b;">
                                            <div style="font-size:13px;color:#a16207;" class="mb-3 fw-semibold">
                                                <i class="bi bi-exclamation-circle me-1"></i>
                                                Job posting pending — review and approve or reject
                                            </div>
                                            <form action="{{ route('staff.jobs.approve', $job->job_qualifications_id) }}" method="POST" class="mb-3">
                                                @csrf
                                                <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Approve Job Posting
                                                </button>
                                            </form>
                                            <form action="{{ route('staff.jobs.reject', $job->job_qualifications_id) }}" method="POST">
                                                @csrf
                                                <label class="form-label fw-semibold small" style="color:#7c2d12;">Reason for Rejection</label>
                                                <textarea name="remarks" rows="2" required class="form-control mb-2"
                                                    style="border:1.5px solid #f0c674;border-radius:8px;font-size:13px;"
                                                    placeholder="State the reason for rejection..."></textarea>
                                                <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                    style="background:#e05252;color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Reject Job Posting
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer" style="border-top:1px solid #e8f5f0;flex-shrink:0;">
                                        <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                                            style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($jobs->hasPages())
            <div class="d-flex justify-content-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
                <div class="d-inline-flex align-items-center gap-2 px-2 py-1"
                    style="background:#fff;border:1px solid #e8f5f0;border-radius:30px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">
                    @if($jobs->onFirstPage())
                        <span class="d-flex align-items-center justify-content-center" style="width:34px;height:34px;border-radius:50%;border:1.5px solid #e0e0e0;color:#ccc;"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a href="{{ $jobs->appends(['job_status' => $jobStatus])->previousPageUrl() }}" class="d-flex align-items-center justify-content-center" style="width:34px;height:34px;border-radius:50%;border:1.5px solid #a8e6cf;color:#2d7a5f;text-decoration:none;"><i class="bi bi-chevron-left"></i></a>
                    @endif
                    <span class="fw-semibold px-2" style="font-size:13px;color:#2d7a5f;white-space:nowrap;">Step {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}</span>
                    @if($jobs->hasMorePages())
                        <a href="{{ $jobs->appends(['job_status' => $jobStatus])->nextPageUrl() }}" class="d-flex align-items-center gap-1 fw-semibold text-decoration-none" style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border-radius:20px;padding:8px 18px;font-size:13px;">Next <i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="d-flex align-items-center gap-1 fw-semibold" style="background:#e0e0e0;color:#aaa;border-radius:20px;padding:8px 18px;font-size:13px;">Next <i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    @endif
</div>
<div style="height:1px;background:linear-gradient(90deg,transparent,#e8f5f0,transparent);margin:32px 0;"></div>
@endif

@if($staffRole === 'lra')
<div class="mb-3">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-calendar-event-fill me-2" style="color:#4dd9c0;"></i>
        Job Fair Events
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        Overview of ongoing and upcoming job fair events
    </p>
</div>

{{-- TABLE --}}
@if($events->isEmpty())
    <div class="card border-0 shadow-sm rounded-4 py-5 text-center" style="background:linear-gradient(180deg,#f8fdfc,#fff);">
        <div style="width:72px;height:72px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 6px 18px rgba(77,217,192,0.3);">
            <i class="bi bi-calendar-event-fill" style="font-size:28px;color:#fff;"></i>
        </div>
        <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">No job fair events found</div>
        <div class="text-muted small mt-1">Job fair events will appear here once created by Job Fair staff.</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Event Title</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Date</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $i => $event)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $events->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $event->title }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $event->event_date->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $event->venue }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $colors = [
                                    'upcoming'  => '#4dd9c0',
                                    'ongoing'   => '#f59e0b',
                                    'completed' => '#888',
                                ];
                                $color = $colors[$event->status] ?? '#888';
                            @endphp
                            <span style="color:{{ $color }};font-weight:600;text-transform:capitalize;">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($events->hasPages())
        <div class="d-flex justify-content-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
            <div class="d-inline-flex align-items-center gap-2 px-2 py-1"
                style="background:#fff;border:1px solid #e8f5f0;border-radius:30px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">

                @if($events->onFirstPage())
                    <span class="d-flex align-items-center justify-content-center"
                        style="width:34px;height:34px;border-radius:50%;border:1.5px solid #e0e0e0;color:#ccc;">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $events->appends($jobs !== null ? ['job_status' => $jobStatus] : [])->previousPageUrl() }}"
                       class="d-flex align-items-center justify-content-center"
                       style="width:34px;height:34px;border-radius:50%;border:1.5px solid #a8e6cf;color:#2d7a5f;text-decoration:none;">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif

                <span class="fw-semibold px-2" style="font-size:13px;color:#2d7a5f;white-space:nowrap;">
                    Step {{ $events->currentPage() }} of {{ $events->lastPage() }}
                </span>

                @if($events->hasMorePages())
                    <a href="{{ $events->appends($jobs !== null ? ['job_status' => $jobStatus] : [])->nextPageUrl() }}"
                       class="d-flex align-items-center gap-1 fw-semibold text-decoration-none"
                       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border-radius:20px;padding:8px 18px;font-size:13px;">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                @else
                    <span class="d-flex align-items-center gap-1 fw-semibold"
                        style="background:#e0e0e0;color:#aaa;border-radius:20px;padding:8px 18px;font-size:13px;">
                        Next <i class="bi bi-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
@endif
@endif

@endsection