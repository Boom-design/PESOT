@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <a href="{{ route('staff.inhouse') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-calendar-check-fill me-1"></i> In-house
    </a>
    <a href="{{ route('staff.jobs') }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-briefcase-fill me-1"></i> Job Vacancies
    </a>
    <a href="{{ route('staff.inhouse.jobfair') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-calendar-event-fill me-1"></i> Job Fair
    </a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-briefcase-fill me-2" style="color:#4dd9c0;"></i>List of Job Vacancies
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">Manage posted job vacancies</p>
    </div>
    <a href="{{ route('staff.jobs.create') }}"
       class="btn btn-sm fw-semibold w-100 w-md-auto"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);
              color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:13px;">
        <i class="bi bi-plus-circle-fill me-1"></i> Post Job Vacancy
    </a>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalAll }}</div>
            <div class="text-muted small">Total Jobs</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $totalOpen }}</div>
            <div class="text-muted small">Open</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#888;">{{ $totalClosed }}</div>
            <div class="text-muted small">Closed</div>
        </div>
    </div>
</div>

{{-- FILTER + SEARCH --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;">
        @foreach(['open' => 'Open', 'closed' => 'Closed'] as $val => $label)
        <a href="{{ route('staff.jobs', array_merge(request()->query(), ['status' => $val, 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('status','open') === $val
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <div class="input-group" style="max-width:260px;width:100%;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search job or company..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($jobs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-briefcase" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job vacancies yet</div>
        <div class="text-muted small mt-1">Post a new job vacancy to get started</div>
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
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Location</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Type</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Slots</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $i => $job)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $jobs->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $job->title }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $job->company->company_name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $job->location ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ ucfirst(str_replace('_', ' ', $job->type ?? $job->job_type ?? '—')) }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:#555;">
                            {{ $job->slots }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = $job->status === 'open'
                                    ? ['bg' => '#2d7a5f', 'label' => 'Open']
                                    : ['bg' => '#888', 'label' => 'Closed'];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $badge['bg'] }};font-size:11px;
                                       padding:4px 10px;border-radius:20px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <button type="button" class="btn btn-sm fw-semibold me-1"
                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 14px;"
                                data-bs-toggle="modal" data-bs-target="#jobModal{{ $job->id }}">
                                <i class="bi bi-eye-fill me-1"></i>View
                            </button>

                            @if($job->posting_status !== 'pending')
                                <a href="{{ route('staff.jobs.qualified', $job->id) }}"
                                   class="btn btn-sm fw-semibold me-1"
                                   style="background:#2d7a5f;color:#fff;border:none;border-radius:8px;font-size:12px;"
                                   title="Qualified Applicants">
                                    <i class="bi bi-person-check-fill"></i>
                                </a>
                            @endif
                        </td>
                    </tr>

                    {{-- JOB DETAIL MODAL --}}
                    <div class="modal fade" id="jobModal{{ $job->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
                            <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
                                <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);flex-shrink:0;">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="bi bi-briefcase-fill me-2"></i>{{ $job->title }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4" style="overflow-y:auto;flex:1;">

                                    <div class="mb-3 pb-2" style="border-bottom:2px solid #e8f5f0;">
                                        <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                                            <i class="bi bi-briefcase me-1" style="color:#4dd9c0;"></i> III. Vacancy Details
                                        </span>
                                    </div>
                                    <div class="row g-2 mb-3" style="font-size:13px;">
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Company</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->company->company_name ?? '—' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Position Title</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->title }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div style="color:#888;font-size:11px;">Job Description</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->description ?? '—' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Nature of Work</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->type ? ucfirst(str_replace('_',' ', $job->type)) : 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Place of Work</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->location ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Salary</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->salary ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Vacancy Count</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->slots ?? 'None' }}</div>
                                        </div>
                                    </div>

                                    <div class="mb-3 pb-2 mt-4" style="border-bottom:2px solid #e8f5f0;">
                                        <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                                            <i class="bi bi-clipboard-check me-1" style="color:#4dd9c0;"></i> IV. Qualification Requirements
                                        </span>
                                    </div>
                                    <div class="row g-2 mb-3" style="font-size:13px;">
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Work Experience (months)</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->experience_months ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Religion</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->religion ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Sex Preference</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->sex_preference ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Civil Status</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->civil_status ?? 'None' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div style="color:#888;font-size:11px;">Other Qualifications</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->other_qualifications ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Educational Level</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->education_required ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Course / Major</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->course_major ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">License</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->eligibility ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Certification</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->certification ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Language / Dialect</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->language ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Preferred Residence</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $job->preferred_residence ?? 'None' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Accepts Disability?</div>
                                            <div style="color:#2d7a5f;font-weight:600;">
                                                {{ $job->accepts_disability ? 'Yes' : 'No' }}
                                                @if($job->accepts_disability && $job->disability_types)
                                                    — {{ implode(', ', $job->disability_types) }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div style="color:#888;font-size:11px;">Accepts Programs</div>
                                            <div style="color:#2d7a5f;font-weight:600;">
                                                {{ is_array($job->accepts_programs) && count($job->accepts_programs) > 0 ? implode(', ', $job->accepts_programs) : 'None' }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Deadline</div>
                                            <div style="color:#2d7a5f;font-weight:600;">
                                                {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : 'None' }}
                                            </div>
                                        </div>
                                    </div>

                                    @if($job->posting_status === 'pending')
                                    <div class="mt-3 p-3 rounded-3" style="background:#fff8e1;border:1px solid #f59e0b;">
                                        <div style="font-size:13px;color:#a16207;" class="mb-3 fw-semibold">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            Job posting pending — review and approve or reject
                                        </div>

                                        <form action="{{ route('staff.jobs.approve', $job->id) }}" method="POST" class="mb-3">
                                            @csrf
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                                       color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Approve Job Posting
                                            </button>
                                        </form>

                                        <form action="{{ route('staff.jobs.reject', $job->id) }}" method="POST">
                                            @csrf
                                            <label class="form-label fw-semibold small" style="color:#7c2d12;">
                                                Reason for Rejection
                                            </label>
                                            <textarea name="remarks" rows="2" required
                                                class="form-control mb-2"
                                                style="border:1.5px solid #f0c674;border-radius:8px;font-size:13px;"
                                                placeholder="State the reason for rejection..."></textarea>
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:#e05252;color:#fff;border:none;
                                                       border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="bi bi-x-circle-fill me-1"></i> Reject Job Posting
                                            </button>
                                        </form>
                                    </div>
                                    @endif

                                </div>
                                <div class="modal-footer" style="border-top:1px solid #e8f5f0;flex-shrink:0;">
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        data-bs-dismiss="modal"
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
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }}
                of {{ $jobs->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $jobs->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $jobs->previousPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($jobs->getUrlRange(1, $jobs->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $jobs->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $jobs->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$jobs->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $jobs->nextPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

{{-- REJECT MODAL --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#e53935;">
                <h6 class="modal-title text-white fw-bold">
                    <i class="bi bi-x-circle me-2"></i>Reject Job Posting
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="font-size:13px;color:#555;" id="rejectJobTitle"></p>
                    <label class="fw-semibold mb-1" style="font-size:13px;color:#2d7a5f;">Reason for Rejection *</label>
                    <textarea name="remarks" class="form-control" rows="3"
                        placeholder="State the reason for rejection..."
                        style="border-color:#a8e6cf;font-size:13px;" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:#e53935;color:#fff;border:none;border-radius:8px;">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openRejectModal(id, title) {
        document.getElementById('rejectJobTitle').textContent = 'Reject "' + title + '"?';
        document.getElementById('rejectForm').action = '/staff/jobs/' + id + '/reject';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    let searchTimer;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });
</script>
@endpush

@endsection