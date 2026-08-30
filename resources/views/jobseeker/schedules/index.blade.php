@extends('jobseeker.layouts.app')

@section('page-title', 'PESO Events')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-calendar-check me-2" style="color:var(--g-600);"></i>PESO Events
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        The in-house interviews and job fairs you have joined
    </p>
</div>

{{-- TABS --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('jobseeker.schedules', ['type' => 'inhouse']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('type','inhouse') === 'inhouse'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="ph ph-buildings me-1"></i> In-house Interviews
        <span class="badge ms-1"
            style="background:rgba(255,255,255,0.3);font-size:10px;">
            {{ $inhouseApplications->count() }}
        </span>
    </a>
    <a href="{{ route('jobseeker.schedules', ['type' => 'jobfair']) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('type') === 'jobfair'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:6px 18px;">
        <i class="ph-fill ph-users-three me-1"></i> Job Fairs
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
            <i class="ph ph-calendar-x" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No in-house schedules yet</div>
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
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company Name</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Slots Needed</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">In-house Date</th>
                            {{-- When the office asked, and what the jobseeker
                                 answered. Every row here is one they accepted,
                                 but the day it arrived is what they forget. --}}
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Invitation Status</th>
                            <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inhouseApplications as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $i + 1 }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ $app->job->company->company_name ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $app->job->title ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $app->job->slots ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $app->job->schedule_type === 'inhouse'
                                    ? $app->job->schedule_window_label
                                    : ($app->job->interview_date ? $app->job->interview_date->format('M d, Y') : 'None') }}
                            </td>
                            <td style="padding:12px 16px;">
                                @php
                                    [$invLabel, $invColor] = match ($app->inhouse_participation) {
                                        'accepted' => ['Accepted', 'var(--g-600)'],
                                        'declined' => ['Declined', 'var(--danger)'],
                                        default    => ['Waiting for your answer', 'var(--warn)'],
                                    };
                                @endphp
                                <span class="fw-semibold" style="color:{{ $invColor }};font-size:11.5px;">{{ $invLabel }}</span>
                                <div style="font-size:10.5px;color:var(--n-500);margin-top:2px;">
                                    {{ $app->inhouse_participation_notified_at
                                        ? 'Invited ' . $app->inhouse_participation_notified_at->format('F d, Y')
                                        : 'Invitation date not recorded' }}
                                </div>
                            </td>
                            {{-- A row only reaches this tab after the jobseeker has
                                 accepted the invitation to take part, so it is always
                                 joined. Same word as the job fair tab, so one thing is
                                 not called two names. --}}
                            <td style="padding:12px 16px;text-align:center;">
                                <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">
                                    <i class="ph-fill ph-check-circle me-1"></i>Joined
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
            <i class="ph ph-calendar-x" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No job fair schedules yet</div>
            <div class="text-muted small mt-1">
                Job fair events will appear here once at least 3 employers have confirmed participation
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:var(--g-600);">
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Event Title</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Event Date</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Venue</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobFairSchedules as $i => $event)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $i + 1 }}</td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ $event->title }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $event->event_date->format('M d, Y') }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $event->venue }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $state = [
                                        'upcoming'  => ['color' => 'var(--g-700)', 'label' => 'Upcoming'],
                                        'ongoing'   => ['color' => 'var(--warn)',  'label' => 'Ongoing'],
                                        'completed' => ['color' => 'var(--n-500)', 'label' => 'Completed'],
                                    ][$event->status] ?? ['color' => 'var(--n-500)', 'label' => ucfirst($event->status)];
                                @endphp
                                <span style="color:{{ $state['color'] }};font-weight:600;">
                                    {{ $state['label'] }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @if(in_array($event->job_fair_events_id, $joinedJobFairIds))
                                    <span class="fw-semibold" style="color:var(--g-700);font-size:11px;">
                                        <i class="ph-fill ph-check-circle me-1"></i>Joined
                                    </span>
                                @else
                                    <form action="{{ route('jobseeker.jobfair.join', $event->job_fair_events_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="background:var(--g-600);
                                                   color:#fff;border:none;border-radius:8px;font-size:11px;padding:4px 12px;">
                                            <i class="ph-fill ph-hand-pointing me-1"></i>Join
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