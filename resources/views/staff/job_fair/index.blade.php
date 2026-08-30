@extends('company.layouts.app')

@section('page-title', 'Job Fair Invitations')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-calendar-dots me-2" style="color:var(--g-600);"></i>Job Fair Invitations
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        Respond to job fair invitations from PESO
    </p>
</div>

@if($invitations->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-calendar-x" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No invitations yet</div>
        <div class="text-muted small mt-1">PESO will send you job fair invitations here</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Event</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Date</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Venue</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $i => $inv)
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $invitations->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $inv->jobFair->title ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $inv->jobFair->event_date->format('M d, Y') ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $inv->jobFair->venue ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php
                                $badge = [
                                    'pending'  => ['bg' => 'var(--warn)', 'label' => 'Pending'],
                                    'confirmed' => ['bg' => 'var(--g-700)', 'label' => 'Confirmed'],
                                    'declined' => ['bg' => 'var(--danger)', 'label' => 'Declined'],
                                ][$inv->confirmation_status] ?? ['bg' => 'var(--n-500)', 'label' => ucfirst($inv->confirmation_status)];
                            @endphp
                            <span class="fw-semibold" style="color:{{ $badge['bg'] }};font-size:11px;">
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
                                        style="background:var(--g-600);
                                               color:#fff;border:none;border-radius:8px;font-size:12px;">
                                        <i class="ph ph-check-circle me-1"></i>Accept
                                    </button>
                                </form>
                                <form action="{{ route('company.jobfair.respond', $inv->job_fair_participants_id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="response" value="declined">
                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold"
                                        style="border-radius:8px;font-size:12px;"
                                        onclick="return confirm('Decline this invitation?')">
                                        <i class="ph ph-x-circle me-1"></i>Decline
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