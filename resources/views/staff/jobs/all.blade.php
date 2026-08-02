@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <a href="{{ route('staff.jobs.all') }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-list-ul me-1"></i> All Job Postings
    </a>
    <a href="{{ route('staff.jobs') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-briefcase-fill me-1"></i> Office Based
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
            <i class="bi bi-list-ul me-2" style="color:#4dd9c0;"></i>
            All Job Postings
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            All job postings (In-house and Office Based), regardless of status. Manually delete expired postings here.
        </p>
    </div>
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
        @foreach(['all' => 'All', 'open' => 'Open', 'closed' => 'Closed'] as $val => $label)
        <a href="{{ route('staff.jobs.all', array_merge(request()->query(), ['status' => $val, 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('status','all') === $val
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
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job postings found</div>
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
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Schedule Type</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Deadline</th>
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
                        <td style="padding:12px 16px;color:#555;">
                            @php
                                $schedLabels = ['inhouse' => 'In-house', 'office_based' => 'Office Based', 'job_fair' => 'Job Fair'];
                            @endphp
                            <span class="badge" style="background:#f0f9f6;color:#2d7a5f;font-size:11px;">
                                {{ $schedLabels[$job->schedule_type] ?? 'N/A' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : '—' }}
                            @if($job->is_expired)
                                <span class="badge ms-1" style="background:#fff5f5;color:#e05252;font-size:10px;">Expired</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <span class="badge fw-semibold"
                                style="background:{{ $job->status === 'open' ? '#2d7a5f' : '#888' }};font-size:11px;padding:4px 10px;border-radius:20px;">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <button type="button" class="btn btn-sm fw-semibold"
                                style="font-size:11px;background:#fff5f5;color:#e53935;border:1px solid #ffcdd2;border-radius:8px;"
                                onclick="confirmDeleteJob({{ $job->id }}, '{{ addslashes($job->title) }}')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="d-flex justify-content-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
            <div class="d-inline-flex align-items-center gap-2 px-2 py-1"
                style="background:#fff;border:1px solid #e8f5f0;border-radius:30px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">

                @if($jobs->onFirstPage())
                    <span class="d-flex align-items-center justify-content-center"
                        style="width:34px;height:34px;border-radius:50%;border:1.5px solid #e0e0e0;color:#ccc;">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $jobs->appends(request()->query())->previousPageUrl() }}"
                       class="d-flex align-items-center justify-content-center"
                       style="width:34px;height:34px;border-radius:50%;border:1.5px solid #a8e6cf;color:#2d7a5f;text-decoration:none;">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif

                <span class="fw-semibold px-2" style="font-size:13px;color:#2d7a5f;white-space:nowrap;">
                    Step {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}
                </span>

                @if($jobs->hasMorePages())
                    <a href="{{ $jobs->appends(request()->query())->nextPageUrl() }}"
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

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteJobModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-body text-center p-4">
                <div style="width:60px;height:60px;background:#fff5f5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-trash" style="font-size:24px;color:#e53935;"></i>
                </div>
                <h6 class="fw-bold mb-2" style="color:#2d7a5f;">Delete Job Posting?</h6>
                <p style="font-size:13px;color:#888;" id="deleteJobName">Are you sure you want to delete this job posting?</p>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button class="btn px-4" data-bs-dismiss="modal" style="border:1.5px solid #a8e6cf;color:#2d7a5f;border-radius:8px;">Cancel</button>
                    <form id="deleteJobForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm px-4 py-2"
                            style="background:#e53935;color:#fff;border:none;border-radius:10px;font-weight:600;">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDeleteJob(id, title) {
        document.getElementById('deleteJobName').textContent = 'Delete "' + title + '"? This cannot be undone.';
        document.getElementById('deleteJobForm').action = '/staff/jobs/' + id;
        new bootstrap.Modal(document.getElementById('deleteJobModal')).show();
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