@extends('company.layouts.app')

@section('page-title', '')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-bar-chart-fill me-2" style="color:#4dd9c0;"></i>List of Reports
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Job postings with hired jobseekers.</p>
</div>

<form method="GET" action="{{ route('company.reports') }}" class="mb-3">
    <div class="input-group" style="max-width:360px;">
        <span class="input-group-text" style="background:#fff;border:1.5px solid var(--peso-border);border-right:none;">
            <i class="bi bi-search" style="color:#888;"></i>
        </span>
        <input type="text" name="search" class="form-control peso-input" style="border-left:none;"
            placeholder="Search job position..." value="{{ $search }}">
        <button type="submit" class="btn btn-peso">Search</button>
        @if($search)
            <a href="{{ route('company.reports') }}" class="btn btn-peso-outline">Clear</a>
        @endif
    </div>
</form>

@if($jobs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-person-check" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No hired applicants yet</div>
        <div class="text-muted small mt-1">Job postings with hired jobseekers will appear here.</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Schedule Type</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Hired</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $job)
                    @php
                        $typeLabel = match($job->schedule_type) {
                            'job_fair' => ['Job Fair', '#2d7a5f', '#e8f8f3'],
                            'inhouse'  => ['In-house', '#e6a817', '#fff8e1'],
                            default    => ['Office Based', '#0ea5e9', '#e0f2fe'],
                        };
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;">
                            <div class="fw-semibold" style="color:#2d7a5f;">{{ $job->title }}</div>
                            <div style="font-size:11px;color:#888;"><i class="bi bi-geo-alt"></i> {{ $job->location ?? 'None' }}</div>
                        </td>
                        <td style="padding:12px 16px;">
                            <span class="badge fw-semibold" style="background:{{ $typeLabel[2] }};color:{{ $typeLabel[1] }};font-size:11px;padding:4px 10px;border-radius:20px;">
                                {{ $typeLabel[0] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:#2d7a5f;font-weight:700;">
                            {{ $job->hired_count }} / {{ $job->slots }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            <a href="{{ route('company.reports.show', $job->id) }}" class="btn btn-sm fw-semibold"
                               style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ $jobs->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $jobs->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $jobs->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    @foreach($jobs->getUrlRange(1, $jobs->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $jobs->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $jobs->currentPage() ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;' : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$jobs->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;" href="{{ $jobs->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

@endsection