@extends('company.layouts.app')

@section('page-title', 'Job Fair Invitations')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-calendar-event-fill me-2" style="color:#4dd9c0;"></i>Job Fair Invitations
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        Respond to job fair invitations from PESO
    </p>
</div>

@if($invitations->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-calendar-x" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No invitations yet</div>
        <div class="text-muted small mt-1">PESO will send you job fair invitations here</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Event</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Date</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $i => $inv)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $invitations->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $inv->jobFair->title ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $inv->jobFair->event_date->format('M d, Y') ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $inv->jobFair->venue ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending'],
                                    'confirmed' => ['bg' => '#2d7a5f', 'label' => 'Confirmed'],
                                    'declined' => ['bg' => '#e05252', 'label' => 'Declined'],
                                ][$inv->confirmation_status] ?? ['bg' => '#888', 'label' => ucfirst($inv->confirmation_status)];
                            @endphp
                            <span class="badge fw-semibold"
                                style="background:{{ $badge['bg'] }};font-size:11px;
                                       padding:4px 10px;border-radius:20px;">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if($inv->confirmation_status === 'pending')
                            <div class="d-flex gap-1 justify-content-center">
                                <form action="{{ route('company.jobfair.respond', $inv->job_fair_participants_id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="response" value="confirmed">
                                    <button type="submit" class="btn btn-sm fw-semibold"
                                        style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                               color:#fff;border:none;border-radius:8px;font-size:12px;">
                                        <i class="bi bi-check-circle me-1"></i>Accept
                                    </button>
                                </form>
                                <form action="{{ route('company.jobfair.respond', $inv->job_fair_participants_id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="response" value="declined">
                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold"
                                        style="border-radius:8px;font-size:12px;"
                                        onclick="return confirm('Decline this invitation?')">
                                        <i class="bi bi-x-circle me-1"></i>Decline
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-muted small">Already responded</span>
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