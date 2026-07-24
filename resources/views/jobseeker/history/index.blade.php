@extends('jobseeker.layouts.app')

@section('page-title', 'History')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f; font-size:18px;">
            <i class="bi bi-clock-history me-2" style="color:#4dd9c0;"></i>History
        </h5>
        <p class="mb-0" style="font-size:13px; color:#888;">
            Jobs you have been hired for.
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
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($hired->isEmpty())
    <div class="peso-card fade-in">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <h6>No hiring history yet</h6>
            <p>Jobs you get hired for will appear here.</p>
        </div>
    </div>
@else
    <div class="peso-table fade-in">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Title</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hired as $i => $app)
                    <tr>
                        <td style="padding:12px 16px;color:#888;">{{ $hired->firstItem() + $i }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
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
                        <td style="padding:12px 16px;text-align:center;color:#555;">
                            {{ $app->updated_at?->format('M d, Y') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($hired->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $hired->links() }}
    </div>
    @endif
@endif

@endsection