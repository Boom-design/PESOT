@extends('admin.layouts.app')

@section('content')

{{-- HEADER + SEARCH --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-person-lines-fill me-2" style="color:#4dd9c0;"></i>
            NSRP Registration Forms
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            List of all submitted jobseeker registration forms
        </p>
    </div>
    <div class="input-group" style="max-width:280px;width:100%;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search name, email, contact..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- MONTHLY/YEARLY BREAKDOWN --}}
<div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h6 class="fw-bold mb-0" style="color:#2d7a5f;font-size:13px;">
            <i class="bi bi-graph-up me-2" style="color:#4dd9c0;"></i>
            Registration Totals — {{ $periodFilter === 'yearly' ? ($periodYear ?: now()->year) : \Carbon\Carbon::parse(($periodMonth ?: now()->format('Y-m')) . '-01')->format('F Y') }}
        </h6>
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="type" value="{{ request('type','local') }}">
            <select name="period_filter" onchange="this.form.submit()" class="form-select form-select-sm" style="font-size:12px;border-color:#a8e6cf;width:auto;">
                <option value="monthly" {{ $periodFilter === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ $periodFilter === 'yearly' ? 'selected' : '' }}>Yearly</option>
            </select>
            @if($periodFilter === 'yearly')
                <select name="period_year" onchange="this.form.submit()" class="form-select form-select-sm" style="font-size:12px;border-color:#a8e6cf;width:auto;">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ ($periodYear ?: now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            @else
                <input type="month" name="period_month" value="{{ $periodMonth ?: now()->format('Y-m') }}" onchange="this.form.submit()" class="form-control form-control-sm" style="font-size:12px;border-color:#a8e6cf;width:auto;">
            @endif
        </form>
    </div>
    <div class="row g-2">
        <div class="col-4">
            <div class="p-2 rounded-3 text-center" style="background:#f0f9f6;border:1px solid #a8e6cf;">
                <div style="font-size:20px;font-weight:700;color:#2d7a5f;">{{ $periodLocal }}</div>
                <div style="font-size:11px;color:#888;">Local (LRA)</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 rounded-3 text-center" style="background:#f0f9f6;border:1px solid #a8e6cf;">
                <div style="font-size:20px;font-weight:700;color:#2d7a5f;">{{ $periodOverseas }}</div>
                <div style="font-size:11px;color:#888;">Overseas (SRA)</div>
            </div>
        </div>
        <div class="col-4">
            <div class="p-2 rounded-3 text-center" style="background:#f0f9f6;border:1px solid #a8e6cf;">
                <div style="font-size:20px;font-weight:700;color:#2d7a5f;">{{ $periodBoth }}</div>
                <div style="font-size:11px;color:#888;">Both</div>
            </div>
        </div>
    </div>
</div>

{{-- LOCAL / OVERSEAS / BOTH TABS --}}
<ul class="nav nav-tabs mb-3" id="regTabs" style="flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <li class="nav-item" style="flex-shrink:0;">
        <a class="nav-link fw-semibold {{ request('type','local') === 'local' ? 'active' : '' }}"
           href="{{ route('admin.registrations', array_merge(request()->query(), ['type' => 'local', 'page' => 1])) }}"
           style="white-space:nowrap;">
            Local
            <span class="badge ms-1" style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                {{ $totalLocal }}
            </span>
        </a>
    </li>
    <li class="nav-item" style="flex-shrink:0;">
        <a class="nav-link fw-semibold {{ request('type') === 'overseas' ? 'active' : '' }}"
           href="{{ route('admin.registrations', array_merge(request()->query(), ['type' => 'overseas', 'page' => 1])) }}"
           style="white-space:nowrap;">
            Overseas
            <span class="badge ms-1" style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                {{ $totalOverseas }}
            </span>
        </a>
    </li>
    <li class="nav-item" style="flex-shrink:0;">
        <a class="nav-link fw-semibold {{ request('type') === 'both' ? 'active' : '' }}"
           href="{{ route('admin.registrations', array_merge(request()->query(), ['type' => 'both', 'page' => 1])) }}"
           style="white-space:nowrap;">
            Both
            <span class="badge ms-1" style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                {{ $totalBoth }}
            </span>
        </a>
    </li>
</ul>

{{-- TABLE --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Full Name</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Email</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Contact Number</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Civil Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Employment</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Date Submitted</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">View Form</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $i => $reg)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;vertical-align:middle;color:#888;">
                            {{ $registrations->firstItem() + $loop->index }}
                        </td>
                        <td style="padding:12px 16px;vertical-align:middle;">
                            <span class="fw-semibold" style="color:#2d7a5f;">
                                {{ trim(($reg->surname ?? '') . ', ' . ($reg->first_name ?? '') . ' ' . ($reg->middle_name ?? '')) ?: ($reg->user->name ?? '—') }}
                            </span>
                        </td>
                        <td style="color:#555;padding:12px 16px;vertical-align:middle;">
                            {{ $reg->reg_email ?? $reg->user->email ?? '—' }}
                        </td>
                        <td style="color:#555;padding:12px 16px;vertical-align:middle;">
                            {{ $reg->contact_number ?? '—' }}
                        </td>
                        <td style="color:#555;padding:12px 16px;vertical-align:middle;">
                            {{ $reg->civil_status ?? '—' }}
                        </td>
                        <td style="color:#555;padding:12px 16px;vertical-align:middle;">
                            {{ $reg->nsrp && $reg->nsrp->employment_type ? ucfirst($reg->nsrp->employment_type) : '—' }}
                        </td>
                        <td style="color:#888;padding:12px 16px;vertical-align:middle;">
                            {{ $reg->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;vertical-align:middle;">
                            <a href="{{ route('admin.registration.view', $reg->id) }}"
                               class="btn btn-sm fw-semibold"
                               style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                      color:#fff;border:none;border-radius:8px;font-size:12px;">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size:40px;color:#c0e8dc;"></i>
                            <div class="mt-2 fw-semibold" style="color:#2d7a5f;font-size:14px;">
                                No registration forms yet
                            </div>
                            <div class="text-muted" style="font-size:12px;">
                                Forms submitted by jobseekers will appear here
                            </div>
                        </td>
                    </tr>
                    @endforelse
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
                           href="{{ $registrations->previousPageUrl() }}&search={{ request('search') }}&type={{ request('type','local') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($registrations->getUrlRange(1, $registrations->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $registrations->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $registrations->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}&search={{ request('search') }}&type={{ request('type','local') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$registrations->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $registrations->nextPageUrl() }}&search={{ request('search') }}&type={{ request('type','local') }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>

<style>
    .nav-tabs .nav-link { color:#2d7a5f;border:none;border-bottom:2px solid transparent; }
    .nav-tabs .nav-link.active {
        color:#2d7a5f;border-bottom:2px solid #4dd9c0;
        background:none;font-weight:700;
    }
    .nav-tabs .nav-link:hover { color:#4dd9c0;background:none; }
    .nav-tabs { border-bottom:1px solid #dee2e6; }
</style>

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