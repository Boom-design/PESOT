@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('staff.registrations') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-person-lines-fill me-1"></i> Registrations
    </a>
    <a href="{{ route('staff.jobseekers') }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-people-fill me-1"></i> Applications
    </a>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-people-fill me-2" style="color:#4dd9c0;"></i>
        Jobseeker Applications
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        List of jobseekers who applied for jobs
    </p>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalQualified }}</div>
            <div class="text-muted small">Qualified</div>
        </div>
    </div>
</div>

{{-- FILTER + SEARCH --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('staff.jobseekers') }}"
           class="btn btn-sm fw-semibold"
           style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;
                  border-radius:8px;font-size:12px;padding:5px 16px;">
            Qualified
        </a>
    </div>
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search name or email..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($applications->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No applicants found</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Job Applied</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $i => $app)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">
                            {{ $applications->firstItem() + $loop->index }}
                        </td>
                        <td style="padding:12px 16px;">
                            <div class="fw-semibold" style="color:#2d7a5f;">
                                {{ $app->jobseeker->name ?? '—' }}
                            </div>
                            <div style="font-size:11px;color:#888;">
                                {{ $app->jobseeker->email ?? '—' }}
                            </div>
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $app->job->title ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $app->job->company->company_name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php $match = $app->match_percentage ?? 0; @endphp
                            <span class="fw-semibold"
                                style="color:{{ $match >= 75 ? '#2d7a5f' : ($match >= 50 ? '#f59e0b' : '#e05252') }}">
                                {{ $match }}%
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'hired'     => ['bg' => '#2d7a5f', 'label' => 'Hired'],
                                    'qualified' => ['bg' => '#f59e0b', 'label' => 'Qualified'],
                                    'pending'   => ['bg' => '#888',    'label' => 'Waiting'],
                                    'rejected'  => ['bg' => '#e05252', 'label' => 'Rejected'],
                                ][$app->status] ?? ['bg' => '#888', 'label' => ucfirst($app->status)];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $badge['bg'] }};font-size:11px;padding:4px 10px;border-radius:20px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;color:#888;text-align:center;">
                            {{ $app->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($applications->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }}
                of {{ $applications->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $applications->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $applications->previousPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($applications->getUrlRange(1, $applications->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $applications->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $applications->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}&status={{ request('status','all') }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$applications->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $applications->nextPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}">
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