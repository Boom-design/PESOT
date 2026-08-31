@extends('staff.layouts.app')

@section('content')

@include('partials.staff-activity-tabs')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-users-three me-2" style="color:var(--g-600);"></i>
            Participants
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            Jobseekers who joined an in-house interview or registered for a job fair —
            {{ $staffRole === 'lra' ? 'local' : 'overseas' }} classification, plus those registered for both.
        </p>
    </div>
    <form method="GET" action="{{ route('staff.participants') }}" class="d-flex gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" class="form-control peso-input"
               placeholder="Search by jobseeker name..." style="width:240px;"
               value="{{ $search }}">
        <button type="submit" class="btn btn-peso">
            <i class="ph ph-magnifying-glass me-1"></i> Search
        </button>
    </form>
</div>

{{-- INNER TABS --}}
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('staff.participants', ['tab' => 'inhouse', 'search' => $search]) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'inhouse'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:20px;font-size:12px;padding:5px 16px;">
        <i class="ph-fill ph-buildings me-1"></i> In-house
        <span class="badge ms-1" style="background:rgba(255,255,255,0.3);font-size:10px;">{{ $inhouse->total() }}</span>
    </a>
    <a href="{{ route('staff.participants', ['tab' => 'jobfair', 'search' => $search]) }}"
       class="btn btn-sm fw-semibold"
       style="{{ $tab === 'jobfair'
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:20px;font-size:12px;padding:5px 16px;">
        <i class="ph-fill ph-calendar-dots me-1"></i> Job Fair
        <span class="badge ms-1" style="background:rgba(255,255,255,0.3);font-size:10px;">{{ $jobfair->total() }}</span>
    </a>
</div>

@php
    $th = 'background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;';
    $td = 'padding:12px 16px;color:var(--n-700);font-size:13px;';
@endphp

{{-- ── IN-HOUSE ── --}}
@if($tab === 'inhouse')

    @if($inhouse->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="ph ph-users-three" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No in-house participants yet</div>
            <div class="text-muted small mt-1">
                A jobseeker appears here once they accept the invitation to take part in an in-house interview.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="{{ $th }}">#</th>
                            <th style="{{ $th }}">Jobseeker</th>
                            <th style="{{ $th }}">Contact</th>
                            <th style="{{ $th }}">Employer</th>
                            <th style="{{ $th }}">Job Position</th>
                            <th style="{{ $th }}">In-house Date</th>
                            <th style="{{ $th }}text-align:center;">Match %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inhouse as $i => $app)
                        <tr>
                            <td style="{{ $td }}color:var(--n-500);">{{ $inhouse->firstItem() + $i }}</td>
                            <td style="{{ $td }}font-weight:600;color:var(--g-700);">
                                {{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: 'None' }}
                                <div style="font-size:11px;font-weight:400;color:var(--n-500);text-transform:capitalize;">
                                    {{ $app->jobseeker->nsrp->type ?? 'None' }}
                                </div>
                            </td>
                            <td style="{{ $td }}">{{ $app->jobseeker->contact_number ?? 'None' }}</td>
                            <td style="{{ $td }}">{{ $app->job->company->company_name ?? 'None' }}</td>
                            <td style="{{ $td }}">{{ $app->job->title ?? 'None' }}</td>
                            <td style="{{ $td }}">{{ $app->job->schedule_window_label ?? 'None' }}</td>
                            <td style="{{ $td }}text-align:center;">
                                @php $match = $app->match_percentage ?? 0; @endphp
                                <span class="fw-semibold"
                                    style="color:{{ $match >= 75 ? 'var(--g-700)' : ($match >= 50 ? 'var(--warn)' : 'var(--danger)') }};">
                                    {{ $match }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @include('partials.pager', ['pager' => $inhouse])
    @endif

{{-- ── JOB FAIR ── --}}
@else

    @if($jobfair->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="ph ph-calendar-dots" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No job fair registrations yet</div>
            <div class="text-muted small mt-1">
                A jobseeker appears here once they register for a job fair event.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="{{ $th }}">#</th>
                            <th style="{{ $th }}">Jobseeker</th>
                            <th style="{{ $th }}">Contact</th>
                            <th style="{{ $th }}">Event</th>
                            <th style="{{ $th }}">Event Date</th>
                            <th style="{{ $th }}">Invitation Code</th>
                            <th style="{{ $th }}text-align:center;">Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobfair as $i => $reg)
                        <tr>
                            <td style="{{ $td }}color:var(--n-500);">{{ $jobfair->firstItem() + $i }}</td>
                            <td style="{{ $td }}font-weight:600;color:var(--g-700);">
                                {{ trim(($reg->jobseeker->first_name ?? '') . ' ' . ($reg->jobseeker->surname ?? '')) ?: 'None' }}
                                <div style="font-size:11px;font-weight:400;color:var(--n-500);text-transform:capitalize;">
                                    {{ $reg->jobseeker->nsrp->type ?? 'None' }}
                                </div>
                            </td>
                            <td style="{{ $td }}">{{ $reg->jobseeker->contact_number ?? 'None' }}</td>
                            <td style="{{ $td }}">{{ $reg->jobFair->title ?? 'None' }}</td>
                            <td style="{{ $td }}">
                                {{ optional($reg->jobFair)->event_date?->format('M d, Y') ?? 'None' }}
                            </td>
                            <td style="{{ $td }}">{{ $reg->slip_number ?? 'None' }}</td>
                            <td style="{{ $td }}text-align:center;">
                                @if($reg->is_attended)
                                    <span class="fw-semibold" style="color:var(--g-700);font-size:11.5px;">
                                        <i class="ph-fill ph-check-circle me-1"></i>Attended
                                    </span>
                                @else
                                    <span class="fw-semibold" style="color:var(--n-500);font-size:11.5px;">
                                        <i class="ph ph-clock me-1"></i>Registered
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @include('partials.pager', ['pager' => $jobfair])
    @endif

@endif

@endsection
