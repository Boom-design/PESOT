@extends('company.layouts.app')

@section('page-title', '')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-bar-chart-fill me-2" style="color:#4dd9c0;"></i>List of Reports
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">List of jobseekers hired by your company.</p>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('company.reports') }}" class="mb-3">
    <div class="input-group" style="max-width:360px;">
        <span class="input-group-text" style="background:#fff;border:1.5px solid var(--peso-border);border-right:none;">
            <i class="bi bi-search" style="color:#888;"></i>
        </span>
        <input type="text" name="search" class="form-control peso-input" style="border-left:none;"
            placeholder="Search by name, email, or job..." value="{{ $search }}">
        <button type="submit" class="btn btn-peso">Search</button>
        @if($search)
            <a href="{{ route('company.reports') }}" class="btn btn-peso-outline">Clear</a>
        @endif
    </div>
</form>

@if($hired->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-person-check" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No hired applicants yet</div>
        <div class="text-muted small mt-1">Jobseekers you have hired will appear here.</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hired as $i => $app)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $hired->firstItem() + $i }}</td>
                        <td style="padding:12px 16px;">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($app->jobseeker->first_name ?? $app->jobseeker->name ?? 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:#2d7a5f;">
                                        {{ $app->jobseeker->first_name ?? '' }}
                                        {{ $app->jobseeker->last_name ?? $app->jobseeker->name ?? '—' }}
                                    </div>
                                    <div style="font-size:11px;color:#888;">{{ $app->jobseeker->email ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $app->job->title ?? '—' }}
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

        @if($hired->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $hired->firstItem() }}–{{ $hired->lastItem() }} of {{ $hired->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $hired->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $hired->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    @foreach($hired->getUrlRange(1, $hired->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $hired->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $hired->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$hired->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $hired->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

@endsection