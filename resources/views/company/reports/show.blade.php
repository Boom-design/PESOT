@extends('company.layouts.app')

@section('page-title', '')

@section('content')

<div class="mb-4">
    <a href="{{ route('company.reports') }}" style="font-size:13px;color:#4dd9c0;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">
        <i class="bi bi-person-check-fill me-2" style="color:#4dd9c0;"></i>
        Hired Jobseekers — {{ $job->title }}
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">{{ $job->location ?? 'None' }}</p>
</div>

<form method="GET" action="{{ route('company.reports.show', $job->id) }}" class="mb-3">
    <div class="input-group" style="max-width:360px;">
        <span class="input-group-text" style="background:#fff;border:1.5px solid var(--peso-border);border-right:none;">
            <i class="bi bi-search" style="color:#888;"></i>
        </span>
        <input type="text" name="search" class="form-control peso-input" style="border-left:none;"
            placeholder="Search jobseeker name..." value="{{ $search }}">
        <button type="submit" class="btn btn-peso">Search</button>
        @if($search)
            <a href="{{ route('company.reports.show', $job->id) }}" class="btn btn-peso-outline">Clear</a>
        @endif
    </div>
</form>

@if($hired->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-person-check" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No hired jobseekers yet for this position</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Contact</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Hired</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hired as $i => $app)
                    @php
                        $reg = $app->jobseeker;
                        $fullName = trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? ''));
                        $regEmail = $reg->reg_email ?? ($reg->user->email ?? null);
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $hired->firstItem() + $i }}</td>
                        <td style="padding:12px 16px;">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($fullName ?: 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:#2d7a5f;">{{ $fullName ?: 'None' }}</div>
                                    <div style="font-size:11px;color:#888;">{{ $regEmail ?? 'None' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $reg->contact_number ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php $match = $app->match_percentage ?? 0; @endphp
                            <span class="fw-semibold"
                                style="color:{{ $match >= 75 ? '#2d7a5f' : ($match >= 50 ? '#f59e0b' : '#e05252') }}">
                                {{ $match }}%
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:#555;">
                            {{ $app->updated_at?->format('M d, Y') ?? 'None' }}
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