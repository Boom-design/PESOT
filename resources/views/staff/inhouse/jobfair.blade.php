@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('staff.inhouse') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-calendar-check-fill me-1"></i> In-house
    </a>
    <a href="{{ route('staff.jobs') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-briefcase-fill me-1"></i> Job Vacancies
    </a>
    <a href="{{ route('staff.inhouse.jobfair') }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-calendar-event-fill me-1"></i> Job Fair
    </a>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-calendar-event-fill me-2" style="color:#4dd9c0;"></i>
            Job Fair Events
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            Overview of ongoing and upcoming job fair events
        </p>
    </div>
</div>

{{-- TABLE --}}
@if($events->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-calendar-x" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job fair events found</div>
        <div class="text-muted small mt-1">Job fair events will appear here once created by Job Fair staff.</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Event Title</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Date</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $i => $event)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $events->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $event->title }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $event->event_date->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $event->venue }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $colors = [
                                    'upcoming'  => '#4dd9c0',
                                    'ongoing'   => '#f59e0b',
                                    'completed' => '#888',
                                ];
                                $color = $colors[$event->status] ?? '#888';
                            @endphp
                            <span style="color:{{ $color }};font-weight:600;text-transform:capitalize;">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($events->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $events->firstItem() }}–{{ $events->lastItem() }}
                of {{ $events->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $events->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $events->previousPageUrl() }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $events->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $events->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$events->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $events->nextPageUrl() }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

@endsection