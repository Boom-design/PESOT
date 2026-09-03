@extends('company.layouts.app')

@section('page-title', '')

@section('content')

<div class="mb-4">
    <a href="{{ route('company.reports') }}" style="font-size:13px;color:var(--g-600);text-decoration:none;">
        <i class="ph ph-arrow-left me-1"></i> Back to Reports
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-user-list me-2" style="color:var(--g-600);"></i>
        Hired Jobseekers — {{ $job->title }}
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        {{ $job->location ?? 'None' }} &middot;
        <a href="{{ route('company.jobs.details', $job->job_qualifications_id) }}" style="color:var(--g-600);">
            View job details and requirements
        </a>
    </p>
</div>

<form method="GET" action="{{ route('company.reports.show', $job->job_qualifications_id) }}" class="mb-3">
    <div class="input-group" style="max-width:360px;">
        <span class="input-group-text" style="background:#fff;border:1px solid var(--peso-border);border-right:none;">
            <i class="ph ph-magnifying-glass" style="color:var(--n-500);"></i>
        </span>
        <input type="text" name="search" class="form-control peso-input" style="border-left:none;"
            placeholder="Search jobseeker name..." value="{{ $search }}">
        <button type="submit" class="btn btn-peso">Search</button>
        @if($search)
            <a href="{{ route('company.reports.show', $job->job_qualifications_id) }}" class="btn btn-peso-outline">Clear</a>
        @endif
    </div>
</form>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                        <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Contact</th>
                        <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                        <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hired as $i => $app)
                    @php
                        $reg = $app->jobseeker;
                        $fullName = trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? ''));
                        $regEmail = $reg->reg_email ?? ($reg->user->email ?? null);
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $hired->firstItem() + $i }}</td>
                        <td style="padding:12px 16px;">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:var(--g-600);
                                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($fullName ?: 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:var(--g-700);">{{ $fullName ?: 'None' }}</div>
                                    <div style="font-size:11px;color:var(--n-500);">{{ $regEmail ?? 'None' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $reg->contact_number ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php $match = $app->match_percentage ?? 0; @endphp
                            <span class="fw-semibold"
                                style="color:{{ $match >= 75 ? 'var(--g-700)' : ($match >= 50 ? 'var(--warn)' : 'var(--danger)') }}">
                                {{ $match }}%
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                            {{ $app->updated_at?->format('M d, Y') ?? 'None' }}
                        </td>
                    </tr>
                    @empty
                    {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                         gihapon ug ang pahina dili mag-usab ug porma. --}}
                    <tr>
                        <td colspan="5" class="text-center"
                            style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                            <i class="ph ph-user-list me-1"
                               style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                            No hired jobseekers yet for this position
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($hired->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $hired->firstItem() }}–{{ $hired->lastItem() }} of {{ $hired->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $hired->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $hired->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                    </li>
                    @foreach($hired->getUrlRange(1, $hired->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $hired->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $hired->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$hired->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $hired->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>

@endsection