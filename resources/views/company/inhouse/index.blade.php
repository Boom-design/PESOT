@extends('company.layouts.app')

@section('page-title', 'In-house Schedules')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-calendar-check me-2" style="color:var(--g-600);"></i>In-house Schedules
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">Request and track in-house interview schedules</p>
    </div>
    <a href="{{ route('company.inhouse.create') }}"
       class="btn btn-peso btn-sm">
        <i class="ph-fill ph-plus-circle me-1"></i> Request Schedule
    </a>
</div>

@include('partials.overseas-inhouse-notice')

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:var(--g-100);">
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Preferred Date</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Preferred Time</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Applicants</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Confirmed Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $i => $schedule)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $schedules->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $schedule->confirmed_date
                                ? $schedule->confirmed_date->format('M d, Y')
                                : $schedule->schedule_window_label }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ \Carbon\Carbon::parse($schedule->preferred_time)->format('h:i A') }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;color:var(--n-700);">
                            {{ $schedule->num_applicants }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            @if($schedule->venue_type === 'custom')
                                {{ $schedule->venue_address }}
                            @else
                                PESO Office
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'pending'  => ['bg' => 'var(--warn)', 'label' => 'Pending'],
                                    'accepted' => ['bg' => 'var(--g-700)', 'label' => 'Accepted'],
                                    'rejected' => ['bg' => 'var(--danger)', 'label' => 'Rejected'],
                                ][$schedule->status] ?? ['bg' => 'var(--n-500)', 'label' => ucfirst($schedule->status)];
                            @endphp
                            <span class="fw-semibold" style="color:{{ $badge['bg'] }};font-size:11px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            @if($schedule->status === 'accepted' && $schedule->confirmed_date)
                                {{ $schedule->confirmed_date->format('M d, Y') }}
                                at {{ \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') }}
                            @elseif($schedule->status === 'rejected')
                                <span style="color:var(--danger);font-size:12px;">
                                    {{ $schedule->rejection_reason ?? 'Rejected' }}
                                </span>
                            @else
                                <span class="text-muted small">Awaiting response</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                         gihapon ug ang pahina dili mag-usab ug porma. --}}
                    <tr>
                        <td colspan="7" class="text-center"
                            style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                            <i class="ph ph-calendar-x me-1"
                               style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                            Request an in-house interview schedule from PESO
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection