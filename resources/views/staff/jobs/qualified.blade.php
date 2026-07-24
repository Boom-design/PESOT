@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <a href="{{ route('staff.jobs') }}" style="font-size:13px;color:#4dd9c0;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Job Vacancies
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">
        <i class="bi bi-person-check-fill me-2" style="color:#4dd9c0;"></i>
        Qualified Applicants
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        {{ $job->title }} — {{ $job->company->company_name ?? '—' }}
    </p>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $totalHighly }}</div>
            <div class="text-muted small">Highly Qualified (75–100%)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalQualified }}</div>
            <div class="text-muted small">Qualified (50–74%)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalHighly + $totalQualified }}</div>
            <div class="text-muted small">Total Qualified</div>
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        @foreach(['highly' => 'Highly Qualified', 'qualified' => 'Qualified'] as $val => $label)
        <a href="{{ route('staff.jobs.qualified', ['id' => $job->id, 'filter' => $val]) }}"
           class="btn btn-sm fw-semibold"
           style="{{ request('filter','highly') === $val
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
            placeholder="Search name or email..."
            style="border-color:#a8e6cf;font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($applicants->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No qualified applicants found</div>
        <div class="text-muted small mt-1">Applicants with 50% match and above will appear here</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:#2d7a5f;">
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Contact</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Qualification</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicants as $i => $app)
                    @php
                        $match = $app->match_percentage ?? 0;
                        $reg   = $app->jobseeker->registration ?? null;
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">
                            {{ $applicants->firstItem() + $loop->index }}
                        </td>
                        <td style="padding:12px 16px;">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px;height:34px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($app->jobseeker->name ?? 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:#2d7a5f;">
                                        {{ $reg->first_name ?? '' }} {{ $reg->surname ?? $app->jobseeker->name ?? '—' }}
                                    </div>
                                    <div style="font-size:11px;color:#888;">
                                        {{ $app->jobseeker->email ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-size:12px;color:#555;">
                                <i class="bi bi-telephone me-1" style="color:#4dd9c0;"></i>
                                {{ $reg->contact_number ?? $app->jobseeker->phone ?? '—' }}
                            </div>
                            <div style="font-size:12px;color:#888;">
                                <i class="bi bi-envelope me-1" style="color:#4dd9c0;"></i>
                                {{ $app->jobseeker->email ?? '—' }}
                            </div>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <span class="fw-bold" style="font-size:15px;color:{{ $match >= 75 ? '#2d7a5f' : '#f59e0b' }}">
                                {{ $match }}%
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($match >= 75)
                                <span class="badge fw-semibold"
                                    style="background:#2d7a5f;font-size:11px;padding:4px 10px;border-radius:20px;">
                                    Highly Qualified
                                </span>
                            @else
                                <span class="badge fw-semibold"
                                    style="background:#f59e0b;font-size:11px;padding:4px 10px;border-radius:20px;">
                                    Qualified
                                </span>
                            @endif
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
        @if($applicants->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $applicants->firstItem() }}–{{ $applicants->lastItem() }}
                of {{ $applicants->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $applicants->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $applicants->previousPageUrl() }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($applicants->getUrlRange(1, $applicants->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $applicants->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $applicants->currentPage()
                               ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                               : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$applicants->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2"
                           style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $applicants->nextPageUrl() }}">
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