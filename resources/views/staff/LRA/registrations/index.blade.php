@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
<div class="d-flex gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">
    <a href="{{ route('staff.registrations') }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-person-lines-fill me-1"></i> Registrations
    </a>
    <a href="{{ route('staff.jobseekers') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;">
        <i class="bi bi-people-fill me-1"></i> Applications
    </a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-person-lines-fill me-2" style="color:#4dd9c0;"></i>List of Local Jobseekers
    </h5>
    <div class="d-flex align-items-center gap-2 flex-wrap" style="width:100%;">
        <span class="badge fw-semibold px-3 py-2"
            style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                   color:#fff;border-radius:20px;font-size:12px;">
            {{ $registrations->total() }} Total
        </span>
        <div class="input-group" style="max-width:260px;flex:1;">
            <span class="input-group-text"
                style="border-color:#a8e6cf;background:#f0fdf9;">
                <i class="bi bi-search" style="color:#4dd9c0;"></i>
            </span>
            <input type="text" id="searchInput" class="form-control"
                placeholder="Search name or email..."
                style="border-color:#a8e6cf;font-size:13px;"
                value="{{ request('search') }}">
        </div>
    </div>
</div>

@if($registrations->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-person-x" style="font-size:48px;color:#a8e6cf;"></i>
        <p class="text-muted mt-3 mb-0">No local registrations found.</p>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;padding:12px 16px;">Name</th>
                        <th style="color:#2d7a5f;font-size:12px;padding:12px 16px;">Email</th>
                        <th style="color:#2d7a5f;font-size:12px;padding:12px 16px;">Phone Number</th>
                        <th style="color:#2d7a5f;font-size:12px;padding:12px 16px;">
                            Date Registered<br>
                            <span style="font-size:10px;font-weight:400;opacity:0.85;">(Account)</span>
                        </th>
                        <th style="color:#2d7a5f;font-size:12px;padding:12px 16px;">Status</th>
<th style="color:#2d7a5f;font-size:12px;padding:12px 16px;">View Form</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $i => $reg)
                    <tr>
                        <td style="font-size:13px;padding:12px 16px;">
                            {{ ($registrations->currentPage() - 1) * $registrations->perPage() + $i + 1 }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;font-weight:600;">
                            {{ $reg->user->name ?? trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? '')) ?: 'N/A' }}
                            @if($reg->is_walk_in)
                                <span class="badge" style="background:#f0d878;color:#5c4600;font-size:9px;font-weight:700;margin-left:4px;">WALK-IN</span>
                            @endif
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:#555;">
                            {{ $reg->user->email ?? $reg->reg_email ?? 'N/A' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:#555;">
                            {{ $reg->user->phone ?? $reg->contact_number ?? 'None' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:#888;">
                            {{ optional($reg->user)->created_at?->format('M d, Y') ?? $reg->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;">
                            @php
                                $hasApp = $reg->user ? $reg->user->applications->count() > 0 : false;
                                $statusLabel = $hasApp ? 'Qualified' : 'Waiting';
                                $statusColor = $hasApp ? '#4dd9c0' : '#f59e0b';
                            @endphp
                            <span class="fw-semibold" style="color:{{ $statusColor }};font-size:13px;">
                                {{ $statusLabel }}
                            </span> 
                        </td>
                        <td style="padding:12px 16px;">
                            <a href="{{ route('staff.registrations.view', $reg->jobseeker_registrations_id) }}"
                               class="btn btn-sm fw-semibold"
                               style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                      color:#fff;border:none;border-radius:8px;font-size:12px;">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($registrations->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }}
                of {{ $registrations->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $registrations->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $registrations->previousPageUrl() }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($registrations->getUrlRange(1, $registrations->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $registrations->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $registrations->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$registrations->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $registrations->nextPageUrl() }}&search={{ request('search') }}">
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