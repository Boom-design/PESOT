@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-calendar-check-fill me-2" style="color:#4dd9c0;"></i>
            In-house Schedule Request
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">Review and respond to this request</p>
    </div>
    <a href="{{ route('staff.inhouse') }}"
       class="btn btn-sm fw-semibold"
       style="border:1.5px solid #a8e6cf;color:#2d7a5f;
              background:#fff;border-radius:8px;font-size:13px;">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    {{-- DETAILS --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#2d7a5f;">Request Details</h6>

                <table class="table table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;width:40%;">Company</td>
                        <td>{{ $schedule->employer->company_name ?? $schedule->employer->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Email</td>
                        <td>{{ $schedule->employer->employer->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Preferred Date</td>
                        <td>{{ $schedule->preferred_date->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Preferred Time</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->preferred_time)->format('h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">No. of Applicants</td>
                        <td>{{ $schedule->num_applicants }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Venue</td>
                        <td>
                            @if($schedule->venue_type === 'custom')
                                {{ $schedule->venue_address }}
                                <span class="badge ms-1" style="background:#e8f8f3;color:#2d7a5f;font-size:10px;">Custom Venue</span>
                            @else
                                PESO Office
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Job Positions</td>
                        <td>
                            @if($schedule->job_positions && count($schedule->job_positions) > 0)
                                @foreach($schedule->job_positions as $pos)
                                    <span class="badge me-1 mb-1"
                                        style="background:#e8f8f3;color:#2d7a5f;font-size:11px;padding:4px 10px;">
                                        {{ $pos }}
                                    </span>
                                @endforeach
                            @else
                                <span style="color:#888;">None</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Notes</td>
                        <td>{{ $schedule->notes ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Status</td>
                        <td>
                            @php
                                $badge = [
                                    'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending'],
                                    'accepted' => ['bg' => '#2d7a5f', 'label' => 'Accepted'],
                                    'rejected' => ['bg' => '#e05252', 'label' => 'Rejected'],
                                ][$schedule->status];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $badge['bg'] }};font-size:11px;
                                       padding:4px 10px;border-radius:20px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                    </tr>
                    @if($schedule->status === 'accepted')
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Confirmed Date</td>
                        <td>{{ $schedule->confirmed_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold" style="color:#2d7a5f;">Confirmed Time</td>
                        <td>{{ $schedule->confirmed_time ? \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') : '—' }}</td>
                    </tr>
                    @endif
                    @if($schedule->status === 'rejected')
                    <tr>
                        <td class="fw-semibold" style="color:#e05252;">Rejection Reason</td>
                        <td style="color:#e05252;">{{ $schedule->rejection_reason ?? '—' }}</td>
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
                <h6 class="fw-bold mb-3" style="color:#2d7a5f;">
                    <i class="bi bi-check-circle-fill me-2" style="color:#4dd9c0;"></i>Accept Request
                </h6>
                <form action="{{ route('staff.inhouse.accept', $schedule->inhouse_schedules_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                            Confirmed Date
                        </label>
                        <input type="text" class="form-control"
                            style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;background:#f0f9f6;"
                            value="{{ $schedule->preferred_date->format('M d, Y') }}" readonly>
                        <input type="hidden" name="confirmed_date" value="{{ $schedule->preferred_date->format('Y-m-d') }}">
                        <small style="font-size:11px;color:#888;">
                            Locked to the employer's preferred date. If there's a scheduling conflict, please reject this request instead.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                            Confirmed Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="confirmed_time" class="form-control"
                            style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                            value="{{ $schedule->preferred_time }}" required>
                    </div>
                    <button type="submit" class="btn w-100 fw-semibold"
                        style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                               color:#fff;border:none;border-radius:10px;
                               padding:10px;font-size:13px;">
                        <i class="bi bi-check-circle-fill me-2"></i>Accept Schedule
                    </button>
                </form>
            </div>
        </div>

        {{-- REJECT --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#e05252;">
                    <i class="bi bi-x-circle-fill me-2"></i>Reject Request
                </h6>
                <form action="{{ route('staff.inhouse.reject', $schedule->inhouse_schedules_id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                            Reason for Rejection <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" class="form-control" rows="3"
                            style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                            placeholder="State the reason for rejection..."
                            required></textarea>
                    </div>
                    <button type="submit" class="btn w-100 fw-semibold btn-danger"
                        style="border-radius:10px;padding:10px;font-size:13px;"
                        onclick="return confirm('Reject this request?')">
                        <i class="bi bi-x-circle-fill me-2"></i>Reject Schedule
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection