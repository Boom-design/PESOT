@extends('staff.layouts.app')

@section('content')

{{-- One page, not two tabs.

     The desk's day is the list; encoding a walk-in is one action taken from
     it, so it is a button here and not a second place to be. --}}
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0" style="color:var(--g-700);">
            <i class="ph ph-globe me-2" style="color:var(--g-600);"></i>NSRP Registration
        </h5>
        <div style="font-size:12px;color:var(--n-500);">
            {{ $registrations->total() }} overseas jobseeker(s) registered
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('staff.nsrp') }}"
           class="btn btn-sm fw-semibold"
           style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 14px;white-space:nowrap;">
            <i class="ph-fill ph-file-text me-1"></i> Walk-in NSRP
        </a>
        {{-- Ang gi-download mao gyud ang gipakita nga listahan, apil ang
             gipili nga sala. Kung lahi sila, dili masaligan ang file. --}}
        <a href="{{ route('staff.registrations.export', array_filter([
                'search'     => $search,
                'occupation' => $occupation,
                'education'  => $education,
           ])) }}"
           class="btn btn-sm fw-semibold"
           style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;padding:6px 14px;white-space:nowrap;">
            <i class="ph ph-download-simple me-1"></i> Download Excel
        </a>
    </div>
</div>

{{-- SEARCH + THE TWO FILTERS, one row.

     PESO LRA, 2026-08-26: the desk is asked for candidates by trade and by
     schooling. Both answers are already on the NSRP form.

     The Filter button is gone. Typing an occupation or picking a level is
     already the instruction; a list that only moves after a second click reads
     as if nothing happened. --}}
<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-body p-3">
        <div class="row g-2 align-items-end">

            <div class="col-md-4">
                <label class="peso-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"
                        style="border-color:var(--n-200);background:var(--n-50);">
                        <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control peso-input"
                        placeholder="Name or email..." style="font-size:12px;"
                        value="{{ $search }}">
                </div>
            </div>

            {{-- Sulat, dili pilianan. Ang gipangita nga trabaho gisulat sa
                 jobseeker sa iyang kaugalingong pulong, mao nga ang bahin sa
                 pulong kinahanglan makakita — apan ang na-encode na nga
                 trabaho gilista gihapon isip suhestiyon. --}}
            <div class="col-md-4">
                <label class="peso-label">Preferred Occupation</label>
                <input type="text" id="occupationInput" class="form-control peso-input"
                    list="occupationOptionList" placeholder="Type an occupation..."
                    style="font-size:12px;" value="{{ $occupation }}">
                <datalist id="occupationOptionList">
                    @foreach($occupationOptions as $option)
                        <option value="{{ $option }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="col-md-4">
                <label class="peso-label">Educational Attainment</label>
                <select id="educationInput" class="form-select peso-input" style="font-size:12px;">
                    <option value="">Any level</option>
                    @foreach($educationOptions as $label)
                        <option value="{{ $label }}" {{ $education === $label ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($search || $occupation || $education)
        <div class="mt-2">
            <a href="{{ route('staff.registrations') }}"
               class="btn btn-sm fw-semibold"
               style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:11.5px;padding:4px 12px;">
                <i class="ph ph-x me-1"></i> Clear filters
            </a>
        </div>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">#</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Name</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Email</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Phone Number</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">
                            Date Registered<br>
                            <span style="font-size:10px;font-weight:400;opacity:0.85;">(Account)</span>
                        </th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Status</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">View Form</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $i => $reg)
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
                        <td style="font-size:13px;padding:12px 16px;">
                            @php
                                $hasApplications = $reg->user ? $reg->user->applications()->exists() : false;
                            @endphp
                            @if(!$hasApplications)
                                <span style="color:var(--warn);font-weight:600;">Waiting</span>
                            @else
                                <span style="color:var(--g-700);font-weight:600;">Applied</span>
                            @endif
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
                    @empty
                    {{-- Ang lamesa nagpabilin bisan walay laray, aron ang
                         kolum makita gihapon ug ang page dili mag-usab ug
                         porma matag sala. Mubo ni nga laray, dili tibuok
                         panid nga blangko. --}}
                    <tr>
                        <td colspan="7" class="text-center"
                            style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                            <i class="ph ph-user-minus me-1"
                               style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                            {{ $occupation || $education || $search ? 'No jobseeker matches that filter.' : 'No overseas registrations found.' }}
                        </td>
                    </tr>
                    @endforelse
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
                    {{-- Prev --}}
                    <li class="page-item {{ $registrations->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $registrations->previousPageUrl() }}&search={{ request('search') }}">
                            <i class="ph ph-caret-left"></i>
                        </a>
                    </li>

                    {{-- Pages --}}
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

                    {{-- Next --}}
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

@push('scripts')
<script>
    // Ang tulo ka field usa ra ka sala. Ang pagsulat maghulat ug makadiyot
    // aron dili moadto ang server kada letra; ang pagpili sa lebel moadto
    // dayon, kay usa ra siya ka lihok.
    const applyFilters = function () {
        const url = new URL(window.location.href);
        const set = function (key, value) {
            value ? url.searchParams.set(key, value) : url.searchParams.delete(key);
        };
        set('search',     document.getElementById('searchInput').value.trim());
        set('occupation', document.getElementById('occupationInput').value.trim());
        set('education',  document.getElementById('educationInput').value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    };

    let filterTimer;
    const typeAhead = function () {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(applyFilters, 500);
    };

    document.getElementById('searchInput').addEventListener('input', typeAhead);
    document.getElementById('occupationInput').addEventListener('input', typeAhead);
    document.getElementById('occupationInput').addEventListener('change', applyFilters);
    document.getElementById('educationInput').addEventListener('change', applyFilters);
</script>
@endpush

@endsection