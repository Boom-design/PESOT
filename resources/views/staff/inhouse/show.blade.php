@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-calendar-check me-2" style="color:var(--g-600);"></i>
            In-house Schedule Request
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">Review and respond to this request</p>
    </div>
    <a href="{{ route('staff.inhouse') }}"
       class="btn btn-sm fw-semibold"
       style="border:1px solid var(--n-200);color:var(--g-700);
              background:#fff;border-radius:8px;font-size:13px;">
        <i class="ph ph-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    {{-- DETAILS --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--g-700);">Request Details</h6>

                @include('partials.overseas-inhouse-notice', [
                    'noticeOverseas' => (bool) ($schedule->employer->is_overseas ?? false),
                    'noticeAudience' => 'staff',
                ])

                <table class="table table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);width:40%;">Company</td>
                        <td>{{ $schedule->employer->company_name ?? $schedule->employer->name ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Email</td>
                        <td>{{ $schedule->employer->employer->email ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Available Window</td>
                        <td>{{ $schedule->schedule_window_label }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Preferred Time</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->preferred_time)->format('h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">No. of Applicants</td>
                        <td>{{ $schedule->num_applicants }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Venue</td>
                        <td>
                            @if($schedule->venue_type === 'custom')
                                {{ $schedule->venue_address }}
                                <span class="ms-1" style="color:var(--g-700);font-size:10px;font-weight:600;">Custom Venue</span>
                            @else
                                PESO Office
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Job Positions</td>
                        <td>
                            @if($schedule->job_positions && count($schedule->job_positions) > 0)
                                @foreach($schedule->job_positions as $pos)
                                    <span class="me-1 mb-1" style="color:var(--g-700);font-size:11px;font-weight:600;">
                                        {{ $pos }}@if(!$loop->last),@endif
                                    </span>
                                @endforeach
                            @else
                                <span style="color:var(--n-500);">None</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Notes</td>
                        <td>{{ $schedule->notes ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Status</td>
                        <td>
                            @php
                                $badge = [
                                    'pending'  => ['bg' => 'var(--warn)', 'label' => 'Pending'],
                                    'accepted' => ['bg' => 'var(--g-700)', 'label' => 'Accepted'],
                                    'rejected' => ['bg' => 'var(--danger)', 'label' => 'Rejected'],
                                ][$schedule->status];
                            @endphp
                            <span class="fw-semibold" style="color:{{ $badge['bg'] }};font-size:11px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                    </tr>
                    @if($schedule->status === 'accepted')
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Confirmed Date</td>
                        <td>{{ $schedule->confirmed_date?->format('M d, Y') ?? 'None' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:var(--g-700);">Confirmed Time</td>
                        <td>{{ $schedule->confirmed_time ? \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') : 'None' }}</td>
                    </tr>
                    @endif
                    @if($schedule->status === 'rejected')
                    <tr>
                        <td class="fw-semibold" style="color:var(--danger);">Rejection Reason</td>
                        <td style="color:var(--danger);">{{ $schedule->rejection_reason ?? 'None' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ACTION --}}
    @if($schedule->status === 'pending')
    <div class="col-md-5">
        {{-- ACCEPT --}}
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--g-700);">
                    <i class="ph-fill ph-check-circle me-2" style="color:var(--g-600);"></i>Accept Request
                </h6>
                <form action="{{ route('staff.inhouse.accept', $schedule->inhouse_schedules_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Confirmed Date
                        </label>
                        {{-- Walay date picker: ang gi-accept kay ang TIBUOK range.
                             Ang employer na ang mag-desisyon kung asa gyud didto
                             siya mag-interview. Kung dili mahimo ang range, i-reject
                             ang request — dili siya usbon. --}}
                        <input type="text" class="form-control"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;background:var(--n-50);"
                            value="{{ $schedule->schedule_window_label }}" readonly>
                        <small style="font-size:11px;color:var(--n-500);">
                            @if($schedule->preferred_date_last->isSameDay($schedule->preferred_date))
                                Locked to the employer's requested date. If there's a scheduling conflict, please reject this request instead.
                            @else
                                Accepting reserves all of these dates for this employer — no one else can book them,
                                and the employer picks which one to interview on. If the office cannot give up the
                                whole range, reject the request instead.
                            @endif
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Confirmed Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="confirmed_time" class="form-control"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                            value="{{ $schedule->preferred_time }}" required>
                    </div>
                    <button type="submit" class="btn w-100 fw-semibold"
                        style="background:var(--g-600);
                               color:#fff;border:none;border-radius:10px;
                               padding:10px;font-size:13px;">
                        <i class="ph-fill ph-check-circle me-2"></i>Accept Schedule
                    </button>
                </form>
            </div>
        </div>

        {{-- REJECT --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--danger);">
                    <i class="ph-fill ph-x-circle me-2"></i>Reject Request
                </h6>
                <form action="{{ route('staff.inhouse.reject', $schedule->inhouse_schedules_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:var(--g-700);">
                            Reason for Rejection <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" class="form-control" rows="3"
                            style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                            placeholder="State the reason for rejection..."
                            required></textarea>
                    </div>
                    <button type="submit" class="btn w-100 fw-semibold btn-danger"
                        style="border-radius:10px;padding:10px;font-size:13px;"
                        onclick="return confirm('Reject this request?')">
                        <i class="ph-fill ph-x-circle me-2"></i>Reject Schedule
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection