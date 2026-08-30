@extends('staff.layouts.app')

@section('content')

@include('partials.jobseeker-tabs')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h5 class="fw-bold mb-0">
        <i class="ph-fill ph-user-list me-2" style="color:var(--g-600);"></i>List of Local Jobseekers
    </h5>
    <div class="d-flex align-items-center gap-2 flex-wrap" style="width:100%;">
        <span class="fw-semibold" style="color:var(--g-600);font-size:12px;">
            {{ $registrations->total() }} Total
        </span>
        <div class="input-group" style="max-width:260px;flex:1;">
            <span class="input-group-text"
                style="border-color:var(--n-200);background:var(--n-50);">
                <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
            </span>
            <input type="text" id="searchInput" class="form-control"
                placeholder="Search name or email..."
                style="border-color:var(--n-200);font-size:13px;"
                value="{{ request('search') }}">
        </div>
    </div>
</div>

{{-- FILTER + DOWNLOAD --}}
{{-- PESO LRA, 2026-08-26: the desk is asked for candidates by trade and by
     schooling. Both answers are already on the NSRP form; they were only never
     searchable, and there was no way to hand the list to anyone. --}}
<form method="GET" action="{{ route('staff.registrations') }}"
      class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <div class="row g-2 align-items-end">

            <input type="hidden" name="search" value="{{ $search }}">

            <div class="col-md-4">
                <label class="peso-label">Preferred Occupation</label>
                <select name="occupation" class="form-select peso-input" style="font-size:12px;">
                    <option value="">All occupations</option>
                    @foreach($occupationOptions as $option)
                        <option value="{{ $option }}" {{ $occupation === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="peso-label">Educational Attainment</label>
                <select name="education" class="form-select peso-input" style="font-size:12px;">
                    <option value="">Any level</option>
                    @foreach($educationOptions as $label)
                        <option value="{{ $label }}" {{ $education === $label ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-peso" style="font-size:12px;">
                    <i class="ph ph-funnel me-1"></i> Filter
                </button>

                @if($occupation || $education)
                    <a href="{{ route('staff.registrations', array_filter(['search' => $search])) }}"
                       class="btn btn-sm fw-semibold"
                       style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;padding:6px 14px;">
                        <i class="ph ph-x me-1"></i> Clear
                    </a>
                @endif

                {{-- Ang gi-download mao gyud ang gipakita nga listahan, apil ang
                     gipili nga sala. Kung lahi sila, dili masaligan ang file. --}}
                <a href="{{ route('staff.registrations.export', array_filter([
                        'search'     => $search,
                        'occupation' => $occupation,
                        'education'  => $education,
                   ])) }}"
                   class="btn btn-sm fw-semibold ms-auto"
                   style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 14px;white-space:nowrap;">
                    <i class="ph ph-download-simple me-1"></i> Download Excel
                </a>
            </div>
        </div>
    </div>
</form>

@if($registrations->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-user-minus" style="font-size:48px;color:var(--n-200);"></i>
        <p class="text-muted mt-3 mb-0">{{ $occupation || $education || $search ? 'No jobseeker matches that filter.' : 'No local registrations found.' }}</p>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Name</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Email</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Phone Number</th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">
                            Date Registered<br>
                            <span style="font-size:10px;font-weight:400;opacity:0.85;">(Account)</span>
                        </th>
                        <th style="color:var(--g-700);font-size:12px;padding:12px 16px;">Status</th>
<th style="color:var(--g-700);font-size:12px;padding:12px 16px;">View Form</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $i => $reg)
                    <tr>
                        <td style="font-size:13px;padding:12px 16px;">
                            {{ ($registrations->currentPage() - 1) * $registrations->perPage() + $i + 1 }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;font-weight:600;">
                            {{ $reg->user->name ?? trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? '')) ?: 'None' }}
                            @if($reg->is_walk_in)
                                <span style="color:var(--warn);font-size:9px;font-weight:700;margin-left:4px;">WALK-IN</span>
                            @endif
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                            {{ $reg->user->email ?? $reg->reg_email ?? 'None' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-700);">
                            {{ $reg->user->phone ?? $reg->contact_number ?? 'None' }}
                        </td>
                        <td style="font-size:13px;padding:12px 16px;color:var(--n-500);">
                            {{ optional($reg->user)->created_at?->format('M d, Y') ?? $reg->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;">
                            @php
                                $hasApp = $reg->user ? $reg->user->applications->count() > 0 : false;
                                $statusLabel = $hasApp ? 'Qualified' : 'Waiting';
                                $statusColor = $hasApp ? 'var(--g-600)' : 'var(--warn)';
                            @endphp
                            <span class="fw-semibold" style="color:{{ $statusColor }};font-size:13px;">
                                {{ $statusLabel }}
                            </span> 
                        </td>
                        <td style="padding:12px 16px;">
                            <a href="{{ route('staff.registrations.view', $reg->jobseeker_registrations_id) }}"
                               class="btn btn-sm fw-semibold"
                               style="background:var(--g-600);
                                      color:#fff;border:none;border-radius:8px;font-size:12px;">
                                <i class="ph ph-eye"></i> View
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
            style="border-top:1px solid var(--n-50);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }}
                of {{ $registrations->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $registrations->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $registrations->previousPageUrl() }}&search={{ request('search') }}">
                            <i class="ph ph-caret-left"></i>
                        </a>
                    </li>
                    @foreach($registrations->getUrlRange(1, $registrations->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $registrations->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $registrations->currentPage()
                                ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                : 'border-color:var(--n-200);color:var(--g-700);' }}"
                           href="{{ $url }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$registrations->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $registrations->nextPageUrl() }}&search={{ request('search') }}">
                            <i class="ph ph-caret-right"></i>
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