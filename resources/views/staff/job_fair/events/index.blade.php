@extends('staff.layouts.app')

@section('content')

{{-- TABS --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('staff.jobfair.events', array_merge(request()->query(), ['view' => 'events'])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('view','events') === 'events'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-calendar-event-fill me-1"></i> Events
    </a>
    <a href="{{ route('staff.jobfair.events', array_merge(request()->query(), ['view' => 'participants'])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('view') === 'participants'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-people-fill me-1"></i> Employer Participants
    </a>
    <a href="{{ route('staff.jobfair.events', array_merge(request()->query(), ['view' => 'attendance'])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('view') === 'attendance'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;">
        <i class="bi bi-clipboard-check-fill me-1"></i> Attendance
    </a>
</div>

@if(request('view') === 'participants')

    {{-- EMPLOYER PARTICIPANTS VIEW --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
                <i class="bi bi-people-fill me-2" style="color:#4dd9c0;"></i>Employer Participants
            </h5>
            <p class="mb-0" style="font-size:13px;color:#888;">List of employers per job fair event</p>
        </div>
    </div>

    {{-- EVENT FILTER --}}
    <div class="mb-3">
        <select id="eventFilter" class="form-select form-select-sm"
            style="max-width:300px;border-color:#a8e6cf;font-size:13px;"
            onchange="filterByEvent(this.value)">
            <option value="">— Select Event —</option>
            @foreach($allEvents as $ev)
            <option value="{{ $ev->job_fair_events_id }}"
                {{ request('event_id') == $ev->job_fair_events_id ? 'selected' : '' }}>
                {{ $ev->title }} ({{ $ev->event_date->format('M d, Y') }})
            </option>
            @endforeach
        </select>
    </div>

    @if(request('event_id') && isset($participants))
        {{-- STAT CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $participants->total() }}</div>
                    <div class="text-muted small">Total Invited</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $confirmedCount }}</div>
                    <div class="text-muted small">Confirmed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $pendingCount }}</div>
                    <div class="text-muted small">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#e05252;">{{ $declinedCount }}</div>
                    <div class="text-muted small">Declined</div>
                </div>
            </div>
        </div>

        {{-- AUTO-NOTIFY BUTTON --}}
        <div class="mb-3">
            <form action="{{ route('staff.jobfair.events.checkNotify', request('event_id')) }}"
                method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                           color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;">
                    <i class="bi bi-bell-fill me-1"></i>
                    Check & Send Notifications
                    @if($confirmedCount >= 3)
                        ({{ $confirmedCount }} confirmed ✅)
                    @else
                        ({{ $confirmedCount }}/3 confirmed)
                    @endif
                </button>
            </form>
        </div>

        {{-- PARTICIPANTS TABLE --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Email</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Employer Type</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Job Offers</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $p)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">
                                {{ $participants->firstItem() + $loop->index }}
                            </td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ $p->employer->company_name ?? '—' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $p->employer->email ?? '—' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $p->employer->employer_type ?? '—' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                @php
                                    $offers = $employmentRequests->get($p->employer_id, collect());
                                @endphp
                                @forelse($offers as $req)
                                    <span class="badge me-1 mb-1" style="background:#e8f8f3;color:#2d7a5f;font-size:11px;">
                                        {{ $req->job->title ?? '—' }}
                                    </span>
                                @empty
                                    <span class="text-muted" style="font-size:11px;">No jobs selected yet</span>
                                @endforelse
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $colors = [
                                        'confirmed' => '#2d7a5f',
                                        'pending'   => '#f59e0b',
                                        'declined'  => '#e05252',
                                    ];
                                    $labels = [
                                        'confirmed' => 'Interested ✓',
                                        'pending'   => 'Pending',
                                        'declined'  => 'Not Interested',
                                    ];
                                @endphp
                                <span class="fw-semibold"
                                    style="color:{{ $colors[$p->confirmation_status] ?? '#888' }};font-size:12px;">
                                    {{ $labels[$p->confirmation_status] ?? ucfirst($p->confirmation_status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($participants->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3"
                style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $participants->firstItem() }}–{{ $participants->lastItem() }}
                    of {{ $participants->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $participants->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                               href="{{ $participants->previousPageUrl() }}&view=participants&event_id={{ request('event_id') }}">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        @foreach($participants->getUrlRange(1, $participants->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $participants->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $participants->currentPage()
                                    ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                    : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}&view=participants&event_id={{ request('event_id') }}">
                                {{ $page }}
                            </a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$participants->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                               href="{{ $participants->nextPageUrl() }}&view=participants&event_id={{ request('event_id') }}">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-people" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">Select an event to view participants</div>
        </div>
    @endif

@elseif(request('view') === 'attendance')

    {{-- ATTENDANCE VIEW --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
                <i class="bi bi-clipboard-check-fill me-2" style="color:#4dd9c0;"></i>Job Fair Attendance
            </h5>
            <p class="mb-0" style="font-size:13px;color:#888;">List of jobseekers who joined and their attendance status</p>
        </div>
    </div>

    {{-- EVENT FILTER --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <select id="eventFilterAttendance" class="form-select form-select-sm"
            style="max-width:300px;border-color:#a8e6cf;font-size:13px;"
            onchange="filterAttendanceByEvent(this.value)">
            <option value="">— Select Event —</option>
            @foreach($allEvents as $ev)
            <option value="{{ $ev->job_fair_events_id }}"
                {{ request('event_id') == $ev->job_fair_events_id ? 'selected' : '' }}>
                {{ $ev->title }} ({{ $ev->event_date->format('M d, Y') }})
            </option>
            @endforeach
        </select>

        @if(request('event_id'))
        <select id="attendanceFilterSelect" class="form-select form-select-sm"
            style="max-width:180px;border-color:#a8e6cf;font-size:13px;"
            onchange="filterAttendanceType(this.value)">
            <option value="all" {{ $attendanceFilter === 'all' ? 'selected' : '' }}>All (Local + Overseas)</option>
            <option value="local" {{ $attendanceFilter === 'local' ? 'selected' : '' }}>Local Only</option>
            <option value="overseas" {{ $attendanceFilter === 'overseas' ? 'selected' : '' }}>Overseas Only</option>
        </select>
        @endif
    </div>

    @if(request('event_id') && isset($registrations))
        {{-- STAT CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalRegistered }}</div>
                    <div class="text-muted small">Total Registered</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#2d7a5f;">{{ $totalAttended }}</div>
                    <div class="text-muted small">Total Attended</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalLocal }}</div>
                    <div class="text-muted small">Local</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                    <div class="fs-2 fw-bold" style="color:#8b5cf6;">{{ $totalOverseas }}</div>
                    <div class="text-muted small">Overseas</div>
                </div>
            </div>
        </div>

        {{-- ATTENDANCE TABLE --}}
        <div class="d-flex justify-content-end mb-2">
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
                    <i class="bi bi-search" style="color:#4dd9c0;"></i>
                </span>
                <input type="text" id="attendanceSearchInput" class="form-control"
                    placeholder="Search name or slip no..."
                    style="border-color:#a8e6cf;font-size:13px;"
                    value="{{ $attendanceSearch }}">
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Slip No.</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Jobseeker Name</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Type</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Attendance</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $r)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">
                                {{ $registrations->firstItem() + $loop->index }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $r->slip_number }}
                            </td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ trim(($r->jobseeker->first_name ?? '') . ' ' . ($r->jobseeker->surname ?? '')) ?: '—' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#555;">
                                {{ ucfirst($r->jobseeker->nsrp->type ?? '—') }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @if($r->is_attended)
                                    <span class="badge fw-semibold"
                                        style="background:#2d7a5f;font-size:11px;padding:4px 10px;border-radius:20px;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Attended
                                    </span>
                                    <div style="font-size:10px;color:#888;margin-top:2px;">
                                        {{ $r->attended_at?->format('M d, Y h:i A') }}
                                    </div>
                                @else
                                    <span class="badge fw-semibold"
                                        style="background:#f59e0b;font-size:11px;padding:4px 10px;border-radius:20px;">
                                        Not Yet Attended
                                    </span>
                                @endif
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @if($r->is_attended)
                                    @if(($event->status ?? null) !== 'completed')
                                    <form action="{{ route('staff.jobfair.attendance.unmark', $r->job_fair_registrations_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="border:1.5px solid #e05252;color:#e05252;background:#fff;
                                                   border-radius:8px;font-size:11px;padding:4px 12px;">
                                            <i class="bi bi-x-circle me-1"></i>Unmark
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    <form action="{{ route('staff.jobfair.attendance.mark', $r->job_fair_registrations_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                                   color:#fff;border:none;border-radius:8px;font-size:11px;padding:4px 12px;">
                                            <i class="bi bi-check-circle me-1"></i>Mark Attended
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($registrations->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3"
                style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }}
                    of {{ $registrations->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $registrations->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                               href="{{ $registrations->previousPageUrl() }}&view=attendance&event_id={{ request('event_id') }}&attendance_filter={{ $attendanceFilter }}">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        @foreach($registrations->getUrlRange(1, $registrations->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $registrations->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $registrations->currentPage()
                                    ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                    : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}&view=attendance&event_id={{ request('event_id') }}&attendance_filter={{ $attendanceFilter }}">
                                {{ $page }}
                            </a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$registrations->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                               href="{{ $registrations->nextPageUrl() }}&view=attendance&event_id={{ request('event_id') }}&attendance_filter={{ $attendanceFilter }}">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-clipboard-check" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">Select an event to view attendance</div>
        </div>
    @endif

@else

    {{-- EVENTS VIEW --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
                <i class="bi bi-calendar-event-fill me-2" style="color:#4dd9c0;"></i>Job Fair Events
            </h5>
            <p class="mb-0" style="font-size:13px;color:#888;">Manage job fair schedules and venues</p>
        </div>
        <a href="{{ route('staff.jobfair.events.create') }}"
           class="btn btn-sm fw-semibold"
           style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                  color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:13px;">
            <i class="bi bi-plus-circle-fill me-1"></i> Create Event
        </a>
    </div>

    {{-- STAT CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <div class="fs-2 fw-bold" style="color:#4dd9c0;">{{ $totalUpcoming }}</div>
                <div class="text-muted small">Upcoming</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <div class="fs-2 fw-bold" style="color:#f59e0b;">{{ $totalOngoing }}</div>
                <div class="text-muted small">Ongoing</div>
            </div>
        </div>
    </div>

    {{-- FILTER + SEARCH --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2 flex-wrap">
            @foreach(['upcoming' => 'Upcoming', 'ongoing' => 'Ongoing'] as $val => $label)
            <a href="{{ route('staff.jobfair.events', array_merge(request()->query(), ['status' => $val, 'page' => 1, 'view' => 'events'])) }}"
               class="btn btn-sm fw-semibold"
               style="{{ request('status','all') === $val
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
                placeholder="Search title or venue..."
                style="border-color:#a8e6cf;font-size:13px;"
                value="{{ request('search') }}">
        </div>
    </div>

    {{-- TABLE --}}
    @if($events->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-calendar-x" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No events found</div>
            <div class="text-muted small mt-1">Create a new job fair event to get started</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Title</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Date</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Confirmed</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $i => $event)
                        @php
                            $confirmed = $event->participants->where('confirmation_status','confirmed')->count();
                        @endphp
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">
                                {{ $events->firstItem() + $loop->index }}
                            </td>
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
                                <span class="fw-semibold"
                                    style="color:{{ $confirmed >= 3 ? '#2d7a5f' : '#f59e0b' }}">
                                    {{ $confirmed }}
                                    @if($confirmed >= 3)
                                        ✅
                                    @endif
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $badge = [
                                        'upcoming'  => ['bg' => '#4dd9c0', 'label' => 'Upcoming'],
                                        'ongoing'   => ['bg' => '#f59e0b', 'label' => 'Ongoing'],
                                        'completed' => ['bg' => '#2d7a5f', 'label' => 'Completed'],
                                    ][$event->status] ?? ['bg' => '#888', 'label' => ucfirst($event->status)];
                                @endphp
                                <span class="badge fw-semibold"
                                    style="background:{{ $badge['bg'] }};font-size:11px;
                                           padding:4px 10px;border-radius:20px;">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <a href="{{ route('staff.jobfair.events', ['view' => 'participants', 'event_id' => $event->job_fair_events_id]) }}"
                                   class="btn btn-sm fw-semibold me-1"
                                   style="border:1.5px solid #a8e6cf;color:#2d7a5f;
                                          background:#fff;border-radius:8px;font-size:12px;"
                                   title="View Participants">
                                    <i class="bi bi-people-fill"></i>
                                </a>
                                <a href="{{ route('staff.jobfair.events.edit', $event->job_fair_events_id) }}"
                                   class="btn btn-sm fw-semibold me-1"
                                   style="border:1.5px solid #a8e6cf;color:#2d7a5f;
                                          background:#fff;border-radius:8px;font-size:12px;">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('staff.jobfair.events.delete', $event->job_fair_events_id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this event?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold"
                                        style="border-radius:8px;font-size:12px;">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
                               href="{{ $events->previousPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}&view=events">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        @foreach($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $events->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $events->currentPage()
                                    ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                    : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}&status={{ request('status','all') }}&search={{ request('search') }}&view=events">
                                {{ $page }}
                            </a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$events->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                               href="{{ $events->nextPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}&view=events">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif

@endif

@push('scripts')
<script>
    function filterByEvent(eventId) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', 'participants');
        url.searchParams.set('event_id', eventId);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function filterAttendanceByEvent(eventId) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', 'attendance');
        url.searchParams.set('event_id', eventId);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function filterAttendanceType(type) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', 'attendance');
        url.searchParams.set('attendance_filter', type);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    let attendanceSearchTimer;
    document.getElementById('attendanceSearchInput')?.addEventListener('input', function() {
        clearTimeout(attendanceSearchTimer);
        attendanceSearchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('view', 'attendance');
            url.searchParams.set('attendance_search', this.value.trim());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });

    @if(request('view','events') === 'events')
    let searchTimer;
    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('page', 1);
            url.searchParams.set('view', 'events');
            window.location.href = url.toString();
        }, 500);
    });
    @endif
</script>
@endpush

@endsection