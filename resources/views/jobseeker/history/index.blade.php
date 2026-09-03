@extends('jobseeker.layouts.app')

@section('page-title', 'History')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700); font-size:18px;">
            <i class="ph ph-clock-counter-clockwise me-2" style="color:var(--g-600);"></i>History
        </h5>
        <p class="mb-0" style="font-size:13px; color:var(--n-500);">
            Every job you have applied for, and where you stand on each one.
        </p>
    </div>
</div>

{{-- Search --}}
<div class="peso-card mb-4 fade-in">
    <div class="peso-card-body">
        <form method="GET" action="{{ route('jobseeker.history') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <label class="peso-label">Search</label>
                    <input type="text" name="search" class="form-control peso-input"
                           placeholder="Search by job title or company..."
                           value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-peso w-100">
                        <i class="ph ph-magnifying-glass me-1"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

    <div class="peso-table fade-in">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company Name</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Job Match %</th>
                        <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Application Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $i => $app)
                    <tr>
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $history->firstItem() + $i }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $app->job->title ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $app->job->company->company_name ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php $match = $app->match_percentage ?? 0; @endphp
                            <span class="fw-semibold"
                                style="color:{{ $match >= 75 ? 'var(--g-700)' : ($match >= 50 ? 'var(--warn)' : 'var(--danger)') }}">
                                {{ $match }}%
                            </span>
                        </td>
                        {{-- Two states only, and neither is the raw application status.
                             PESO asks one question of this page: is this person still
                             looking for work? Someone who took the job is not, and
                             everyone else is — whatever stage their application sits
                             at. --}}
                        <td style="padding:12px 16px;text-align:center;">
                            @if($app->status === 'hired')
                                <span class="fw-semibold" style="color:var(--g-700);font-size:11.5px;">
                                    <i class="ph-fill ph-check-circle me-1"></i>Employed
                                </span>
                                {{-- The day it happened. It used to be a column of its
                                     own; it belongs to this one row, not to every row. --}}
                                <div style="font-size:11px;color:var(--n-500);margin-top:2px;">
                                    since {{ $app->hired_at?->format('M d, Y') ?? 'None' }}
                                </div>
                            @else
                                <span class="fw-semibold" style="color:var(--warn);font-size:11.5px;">
                                    <i class="ph ph-magnifying-glass me-1"></i>Looking for Work
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                         gihapon ug ang pahina dili mag-usab ug porma. --}}
                    <tr>
                        <td colspan="5" class="text-center"
                            style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                            <i class="ph ph-clock-counter-clockwise me-1"
                               style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                            Jobs you apply for will appear here.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($history->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $history->links() }}
    </div>
    @endif

@endsection