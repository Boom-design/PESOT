@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <a href="{{ route('staff.jobs') }}" style="font-size:13px;color:var(--g-600);text-decoration:none;">
        <i class="ph ph-arrow-left me-1"></i> Back to Job Vacancies
    </a>
    {{-- The title stays where every other page keeps it. Only the vacancy is
         centred, with a gap above it, so the two lines are not read as one. --}}
    <h5 class="fw-bold mt-2 mb-2" style="color:var(--g-700);">
        <i class="ph-fill ph-user-list me-2" style="color:var(--g-600);"></i>
        Qualified Applicants
    </h5>
    <p class="mb-0 text-center" style="font-size:13px;color:var(--n-500);">
        {{ $job->title }} — {{ $job->company->company_name ?? 'None' }}
    </p>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--g-700);">{{ $totalHighly }}</div>
            <div class="text-muted small">Highly Qualified (75–100%)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--warn);">{{ $totalQualified }}</div>
            <div class="text-muted small">Qualified (50–74%)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--danger);">{{ $totalNotQualified }}</div>
            <div class="text-muted small">Not Qualified (below 50%)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $totalHighly + $totalQualified + $totalNotQualified }}</div>
            <div class="text-muted small">Total Applicants</div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        @foreach(['highly' => 'Highly Qualified', 'qualified' => 'Qualified', 'not_qualified' => 'Not Qualified'] as $val => $label)
        <a href="{{ route('staff.jobs.qualified', ['id' => $job->job_qualifications_id, 'filter' => $val]) }}"
           class="btn btn-sm fw-semibold"
           style="{{ $filter === $val
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search name or email..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">#</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Jobseeker</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;">Contact</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;text-align:center;">Match %</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;text-align:center;">Qualification</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;font-weight:700;border:none;padding:12px 16px;text-align:center;">Date Applied</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applicants as $i => $app)
                    @php
                        $match = $app->match_percentage ?? 0;
                        $reg   = $app->jobseeker;
                        $fullName = trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? ''));
                        $regEmail = $reg->reg_email ?? ($reg->user->email ?? null);
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">
                            {{ $applicants->firstItem() + $loop->index }}
                        </td>
                        <td style="padding:12px 16px;">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px;height:34px;background:var(--g-600);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($fullName ?: 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:var(--g-700);">
                                        {{ $fullName ?: 'None' }}
                                    </div>
                                    <div style="font-size:11px;color:var(--n-500);">
                                        {{ $regEmail ?? 'None' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-size:12px;color:var(--n-700);">
                                <i class="ph ph-phone me-1" style="color:var(--g-600);"></i>
                                {{ $reg->contact_number ?? 'None' }}
                            </div>
                            <div style="font-size:12px;color:var(--n-500);">
                                <i class="ph ph-envelope-simple me-1" style="color:var(--g-600);"></i>
                                {{ $regEmail ?? 'None' }}
                            </div>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <span class="fw-bold" style="font-size:15px;color:{{ $match >= 75 ? 'var(--g-700)' : ($match >= 50 ? 'var(--warn)' : 'var(--danger)') }}">
                                {{ $match }}%
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($match >= 75)
                                <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">
                                    Highly Qualified
                                </span>
                            @elseif($match >= 50)
                                <span class="fw-semibold" style="color:var(--warn);font-size:11px;">
                                    Qualified
                                </span>
                            @else
                                <span class="fw-semibold" style="color:var(--danger);font-size:11px;">
                                    Not Qualified
                                </span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;color:var(--n-500);text-align:center;">
                            {{ $app->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="ph ph-tray" style="font-size:40px;color:var(--n-300);"></i>
                        <div class="mt-2 fw-semibold" style="color:var(--g-700);font-size:13px;">No applicants found</div>
                        <div class="text-muted small mt-1">Applicants under this category will appear here</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($applicants->hasPages())
    <div class="d-flex justify-content-center px-3 py-3" style="border-top:1px solid var(--n-50);">
        <div class="d-inline-flex align-items-center gap-2 px-2 py-1"
            style="background:#fff;border:1px solid var(--n-200);border-radius:30px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">

            @if($applicants->onFirstPage())
                <span class="d-flex align-items-center justify-content-center"
                    style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--n-300);">
                    <i class="ph ph-caret-left"></i>
                </span>
            @else
                <a href="{{ $applicants->previousPageUrl() }}"
                   class="d-flex align-items-center justify-content-center"
                   style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--g-700);text-decoration:none;">
                    <i class="ph ph-caret-left"></i>
                </a>
            @endif

            <span class="fw-semibold px-2" style="font-size:13px;color:var(--g-700);white-space:nowrap;">
                Step {{ $applicants->currentPage() }} of {{ $applicants->lastPage() }}
            </span>

            @if($applicants->hasMorePages())
                <a href="{{ $applicants->nextPageUrl() }}"
                   class="d-flex align-items-center gap-1 fw-semibold text-decoration-none"
                   style="background:var(--g-600);color:#fff;border-radius:20px;padding:8px 18px;font-size:13px;">
                    Next <i class="ph ph-caret-right"></i>
                </a>
            @else
                <span class="d-flex align-items-center gap-1 fw-semibold"
                    style="background:var(--n-200);color:var(--n-400);border-radius:20px;padding:8px 18px;font-size:13px;">
                    Next <i class="ph ph-caret-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

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