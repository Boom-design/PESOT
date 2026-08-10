@extends('jobseeker.layouts.app')

@section('page-title', 'My Schedules')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-calendar-check-fill me-2" style="color:#4dd9c0;"></i>My Schedules
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        View your in-house interview and job fair schedules
    </p>
</div>

{{-- TABS --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('jobseeker.schedules', ['type' => 'inhouse']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('type','inhouse') === 'inhouse'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="bi bi-building me-1"></i> In-house Interviews
        <span class="badge ms-1"
            style="background:rgba(255,255,255,0.3);font-size:10px;">
            {{ $inhouseApplications->count() }}
        </span>
    </a>
    <a href="{{ route('jobseeker.schedules', ['type' => 'jobfair']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('type') === 'jobfair'
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="bi bi-people-fill me-1"></i> Job Fairs
        <span class="badge ms-1"
            style="background:rgba(255,255,255,0.3);font-size:10px;">
            {{ $jobFairSchedules->count() }}
        </span>
    </a>
</div>

{{-- IN-HOUSE TAB --}}
@if(request('type', 'inhouse') === 'inhouse')

    @if($inhouseApplications->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-calendar-x" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No in-house schedules yet</div>
            <div class="text-muted small mt-1">
                In-house interview schedules will appear here once you confirm participation after applying
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Slots Needed</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">In-house Date</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inhouseApplications as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $i + 1 }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                                {{ $app->job->company->company_name ?? '—' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $app->job->title ?? '—' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $app->job->slots ?? '—' }}
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $app->job->preferred_date ? \Carbon\Carbon::parse($app->job->preferred_date)->format('M d, Y') : '—' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <span class="badge fw-semibold"
                                    style="background:#2d7a5f;font-size:11px;
                                           padding:4px 10px;border-radius:20px;">
                                    Confirmed ✅
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

{{-- JOB FAIR TAB --}}
@else

    @if($jobFairSchedules->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="bi bi-calendar-x" style="font-size:48px;color:#c0e8dc;"></i>
            <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job fair schedules yet</div>
            <div class="text-muted small mt-1">
                Job fair events will appear here once at least 3 employers have confirmed participation
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Event Title</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Date</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobFairSchedules as $i => $event)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $i + 1 }}</td>
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
                                @if(in_array($event->job_fair_events_id, $joinedJobFairIds))
                                    <span class="badge fw-semibold"
                                        style="background:#e8f8f3;color:#2d7a5f;font-size:11px;
                                               padding:4px 10px;border-radius:20px;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Joined
                                    </span>
                                @else
                                    <form action="{{ route('jobseeker.jobfair.join', $event->job_fair_events_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                                   color:#fff;border:none;border-radius:8px;font-size:11px;padding:4px 12px;">
                                            <i class="bi bi-hand-index-thumb-fill me-1"></i>Join
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endif

@endsection