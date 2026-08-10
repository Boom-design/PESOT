@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
@php $staffRoleForTabs = optional(Auth::user()->staff)->staff_role ?? 'staff'; @endphp
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('staff.inhouse') }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-calendar-check-fill me-1"></i> In-house Schedule
    </a>
    @if($staffRoleForTabs === 'sra')
    <a href="{{ route('staff.jobs', ['type' => 'inhouse']) }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-briefcase-fill me-1"></i> In-house Job Vacancy
    </a>
    <a href="{{ route('staff.jobs', ['type' => 'office_based']) }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-briefcase-fill me-1"></i> Office Based
    </a>
    @else
    <a href="{{ route('staff.jobs') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-briefcase-fill me-1"></i> In-house Job Vacancy
    </a>
    @endif
    @if($staffRoleForTabs !== 'lra')
    <a href="{{ route('staff.inhouse.jobfair') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-calendar-event-fill me-1"></i> Job Fair
    </a>
    @endif
</div>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-calendar-check-fill me-2" style="color:#4dd9c0;"></i>
            In-house Interview Schedules
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            Manage employer in-house interview requests
        </p>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalAll }}</div>
            <div class="text-muted small">Total (In-house)</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalPending }}</div>
            <div class="text-muted small">Pending Review</div>
        </div>
    </div>
</div>

<div class="mb-3 p-2 px-3 rounded-3" style="background:#f0f9f6;font-size:12px;color:#2d7a5f;">
    <i class="bi bi-info-circle-fill me-1"></i>
    Once approved, a job posting moves to the <strong>In-house Job Vacancy</strong> tab.
</div>

{{-- SEARCH --}}
<div class="d-flex justify-content-end mb-3 flex-wrap gap-2">
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search company..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($schedules->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-calendar-x" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No schedule requests yet</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Type</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Date</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Time</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Applicants</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $i => $item)
                    @if($item->source === 'schedule')
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $schedules->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $item->employer->company_name ?? $item->employer->name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span class="badge" style="background:#e8f8f3;color:#2d7a5f;font-size:10px;padding:4px 8px;">Schedule Only</span>
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $item->preferred_date->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ \Carbon\Carbon::parse($item->preferred_time)->format('h:i A') }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:#555;">
                            {{ $item->num_applicants }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            @if($item->venue_type === 'custom')
                                {{ $item->venue_address }}
                            @else
                                PESO Office
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending'],
                                    'accepted' => ['bg' => '#2d7a5f', 'label' => 'Accepted'],
                                    'rejected' => ['bg' => '#e05252', 'label' => 'Rejected'],
                                ][$item->status] ?? ['bg' => '#888', 'label' => ucfirst($item->status)];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $badge['bg'] }};font-size:11px;
                                       padding:4px 10px;border-radius:20px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <a href="{{ route('staff.inhouse.view', $item->id) }}"
                               class="btn btn-sm fw-semibold"
                               style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                      color:#fff;border:none;border-radius:8px;font-size:12px;">
                                <i class="bi bi-eye-fill me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    @else
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $schedules->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $item->company->company_name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span class="badge" style="background:#fff3cd;color:#a16207;font-size:10px;padding:4px 8px;">Job Posting</span>
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $item->preferred_date ? $item->preferred_date->format('M d, Y') : '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">8:00 AM – 5:00 PM</td>
                        <td style="padding:12px 16px;text-align:center;color:#555;">—</td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $item->venue_type === 'other' ? $item->venue_address : 'PESO Office' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $jbadge = [
                                    'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending'],
                                    'approved' => ['bg' => '#2d7a5f', 'label' => 'Approved'],
                                    'rejected' => ['bg' => '#e05252', 'label' => 'Rejected'],
                                ][$item->posting_status] ?? ['bg' => '#f59e0b', 'label' => 'Pending'];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $jbadge['bg'] }};font-size:11px;
                                       padding:4px 10px;border-radius:20px;">
                                {{ $jbadge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <button type="button" class="btn btn-sm fw-semibold"
                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;"
                                data-bs-toggle="modal" data-bs-target="#jobInhouseModal{{ $item->job_qualifications_id }}">
                                <i class="bi bi-eye-fill me-1"></i>View
                            </button>
                        </td>
                    </tr>

                    {{-- JOB-BASED IN-HOUSE DETAIL MODAL --}}
                    <div class="modal fade" id="jobInhouseModal{{ $item->job_qualifications_id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="bi bi-briefcase-fill me-2"></i>{{ $item->title }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-2 mb-3" style="font-size:13px;">
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Company</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $item->company->company_name ?? '—' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Position</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $item->title }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Interview Date</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $item->preferred_date ? $item->preferred_date->format('M d, Y') : '—' }} (8:00 AM – 5:00 PM)</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="color:#888;font-size:11px;">Venue</div>
                                            <div style="color:#2d7a5f;font-weight:600;">{{ $item->venue_type === 'other' ? $item->venue_address : 'PESO Office' }}</div>
                                        </div>
                                    </div>

                                    @if($item->venue_type === 'peso_office' && $item->preferred_date)
                                    <div class="p-2 mb-3 rounded-3" style="background:{{ $item->scheduled_count >= 3 ? '#fdecea' : '#f0f9f6' }};font-size:12.5px;">
                                        <i class="bi bi-people-fill me-1"></i>
                                        <strong>{{ $item->scheduled_count }}/3</strong> other companies already approved for PESO Office on {{ $item->preferred_date->format('M d, Y') }}
                                    </div>
                                    @endif

                                    @if($item->posting_status === 'rejected')
                                    <div class="p-2 mb-3 rounded-3" style="background:#fdecea;font-size:12.5px;color:#c0392b;">
                                        <strong>Rejection Reason:</strong> {{ $item->remarks ?? '—' }}
                                    </div>
                                    @endif

                                    @if($item->posting_status === 'pending')
                                    <div class="mt-3 p-3 rounded-3" style="background:#fff8e1;border:1px solid #f59e0b;">
                                        <div style="font-size:13px;color:#a16207;" class="mb-3 fw-semibold">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            Job posting pending — review and approve or reject
                                        </div>

                                        <form action="{{ route('staff.jobs.approve', $item->job_qualifications_id) }}" method="POST" class="mb-3">
                                            @csrf
                                            <button type="submit" class="btn btn-sm w-100 fw-semibold"
                                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Approve Job Posting
                                            </button>
                                        </form>

                                        <form action="{{ route('staff.jobs.reject', $item->job_qualifications_id) }}" method="POST">
                                            @csrf
                                            <label class="form-label fw-semibold small" style="color:#7c2d12;">
                                                Reason for Rejection
                                            </label>
                                            <textarea name="remarks" rows="2" required
                                                class="form-control mb-2"
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
                                <div class="modal-footer" style="border-top:1px solid #e8f5f0;">
                                    <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                                        style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }}
                of {{ $schedules->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $schedules->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $schedules->previousPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($schedules->getUrlRange(1, $schedules->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $schedules->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $schedules->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$schedules->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $schedules->nextPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

@push('scripts')
<script>
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