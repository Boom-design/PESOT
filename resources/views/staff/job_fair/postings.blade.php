@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-briefcase-fill me-2" style="color:#4dd9c0;"></i>Job Fair Postings
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            Review and manage job postings for job fair use. Approved closed postings go live once a new job fair event is created.
        </p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalClosed }}</div>
            <div class="text-muted small">Closed (Waiting for Event)</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $totalOpen }}</div>
            <div class="text-muted small">Open (Live)</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2">
        @foreach(['closed' => 'Closed', 'open' => 'Open', 'all' => 'All'] as $val => $label)
        <a href="{{ route('staff.jobfair.postings', array_merge(request()->query(), ['status' => $val, 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('status','closed') === $val
               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
               : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search title or company..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

@if($jobs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-briefcase" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job fair postings found</div>
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
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Posting Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $i => $job)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $jobs->firstItem() + $i }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">{{ $job->title }}</td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $job->company->company_name ?? '—' }}
                            @if($job->company->is_overseas ?? false)
                                <span class="badge" style="background:#8b5cf6;font-size:10px;">Overseas</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;color:#555;">{{ $job->slots }}</td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($job->posting_status === 'pending')
                                <span class="badge fw-semibold" style="background:#fff8e1;color:#f59e0b;font-size:11px;padding:4px 10px;border-radius:20px;">Pending</span>
                            @elseif($job->posting_status === 'approved')
                                <span class="badge fw-semibold" style="background:#e8f8f3;color:#2d7a5f;font-size:11px;padding:4px 10px;border-radius:20px;">Approved</span>
                            @else
                                <span class="badge fw-semibold" style="background:#fff5f5;color:#e53935;font-size:11px;padding:4px 10px;border-radius:20px;">Rejected</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($job->status === 'open')
                                <span class="badge fw-semibold" style="background:#2d7a5f;font-size:11px;padding:4px 10px;border-radius:20px;">Open</span>
                            @else
                                <span class="badge fw-semibold" style="background:#f59e0b;font-size:11px;padding:4px 10px;border-radius:20px;">Closed</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($job->posting_status === 'pending')
                                <div class="d-flex gap-1 justify-content-center">
                                    <form action="{{ route('staff.jobfair.postings.approve', $job->job_qualifications_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="background:#2d7a5f;color:#fff;border:none;border-radius:6px;font-size:11px;padding:4px 12px;"
                                            onclick="return confirm('Approve this job posting for job fair use?')">
                                            <i class="bi bi-check-lg me-1"></i>Approve
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        style="background:#e53935;color:#fff;border:none;border-radius:6px;font-size:11px;padding:4px 12px;"
                                        onclick="openRejectModal({{ $job->job_qualifications_id }}, '{{ addslashes($job->title) }}')">
                                        <i class="bi bi-x-lg me-1"></i>Reject
                                    </button>
                                </div>
                            @elseif($job->posting_status === 'approved')
                                <span style="font-size:11px;color:#888;">—</span>
                            @else
                                @if($job->remarks)
                                    <span style="font-size:11px;color:#e53935;" title="{{ $job->remarks }}">
                                        <i class="bi bi-exclamation-circle"></i> Rejected
                                    </span>
                                @else
                                    <span style="font-size:11px;color:#888;">—</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ $jobs->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $jobs->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $jobs->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    @foreach($jobs->getUrlRange(1, $jobs->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $jobs->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $jobs->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$jobs->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $jobs->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

{{-- ── REJECT MODAL ── --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 8px 32px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background:linear-gradient(90deg,#e53935,#ff6b6b);border:none;">
                <h6 class="modal-title fw-bold text-white">
                    <i class="bi bi-x-circle me-2"></i>Reject Job Posting
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p style="font-size:13px;color:#555;margin-bottom:12px;">
                        You are about to reject <strong id="rejectJobTitle" style="color:#2d7a5f;"></strong> for job fair use.
                    </p>
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Reason for Rejection *</label>
                    <textarea name="remarks" class="form-control" rows="3" required
                        placeholder="Enter the reason for rejecting this job posting..."
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f9f6;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:#e53935;color:#fff;border:none;border-radius:8px;padding:8px 20px;">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let searchTimer;
    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });

    function openRejectModal(jobId, jobTitle) {
        document.getElementById('rejectJobTitle').textContent = '"' + jobTitle + '"';
        document.getElementById('rejectForm').action = '{{ url("staff/jobfair/postings") }}/' + jobId + '/reject';
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
</script>
@endpush

@endsection
