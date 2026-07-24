@extends('company.layouts.app')

@section('page-title', 'In-house Schedules')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-calendar-check-fill me-2" style="color:#4dd9c0;"></i>In-house Schedules
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">Request and track in-house interview schedules</p>
    </div>
    <a href="{{ route('company.inhouse.create') }}"
       class="btn btn-peso btn-sm">
        <i class="bi bi-plus-circle-fill me-1"></i> Request Schedule
    </a>
</div>

@if($schedules->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-calendar-x" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No schedule requests yet</div>
        <div class="text-muted small mt-1">Request an in-house interview schedule from PESO</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Preferred Date</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Preferred Time</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Applicants</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Confirmed Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $i => $schedule)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $schedules->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $schedule->preferred_date->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ \Carbon\Carbon::parse($schedule->preferred_time)->format('h:i A') }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:#555;">
                            {{ $schedule->num_applicants }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            @if($schedule->venue_type === 'custom')
                                {{ $schedule->venue_address }}
                            @else
                                PESO Office
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending'],
                                    'accepted' => ['bg' => '#2d7a5f', 'label' => 'Accepted'],
                                    'rejected' => ['bg' => '#e05252', 'label' => 'Rejected'],
                                ][$schedule->status] ?? ['bg' => '#888', 'label' => ucfirst($schedule->status)];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $badge['bg'] }};font-size:11px;
                                       padding:4px 10px;border-radius:20px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            @if($schedule->status === 'accepted' && $schedule->confirmed_date)
                                {{ $schedule->confirmed_date->format('M d, Y') }}
                                at {{ \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') }}
                            @elseif($schedule->status === 'rejected')
                                <span style="color:#e05252;font-size:12px;">
                                    {{ $schedule->rejection_reason ?? 'Rejected' }}
                                </span>
                            @else
                                <span class="text-muted small">Awaiting response</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection