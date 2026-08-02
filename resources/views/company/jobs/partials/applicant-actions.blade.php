@php
    $isOfficeBased = ($job->schedule_type ?? null) === 'office_based';
    $isDeclined = ($isInhouse && $app->inhouse_participation === 'declined')
        || ($isOfficeBased && $app->office_participation === 'declined');
    $isPending  = ($isInhouse && $app->inhouse_participation === 'pending')
        || ($isOfficeBased && $app->office_participation === 'pending');
    $isExpired  = $job->is_expired ?? false;
    $isLocked   = $isInhouse && (!$actionsUnlocked || $isExpired);
@endphp

@if(in_array($app->status, ['hired', 'rejected']))
    <span style="font-size:11px;color:#888;"><i class="bi bi-lock-fill me-1"></i>Final</span>
@elseif($isDeclined)
    <span style="font-size:11px;color:#e05252;"><i class="bi bi-x-circle me-1"></i>Declined {{ $isOfficeBased ? 'job offer' : 'interview' }}</span>
@elseif($isPending)
    <span style="font-size:11px;color:#f59e0b;"><i class="bi bi-hourglass-split me-1"></i>Awaiting participation response</span>
@elseif($isExpired)
    <span style="font-size:11px;color:#e05252;"><i class="bi bi-calendar-x me-1"></i>Job posting expired</span>
@elseif($isLocked)
    <span style="font-size:11px;color:#888;"><i class="bi bi-clock me-1"></i>Locked until interview date</span>
@else
<div class="d-flex gap-1 justify-content-center">
    <form method="POST" action="{{ route('company.applicants.status', $app->id) }}" class="status-form">
        @csrf
        <input type="hidden" name="status" value="hired">
        <button type="button" class="btn btn-sm fw-semibold confirm-hired"
            style="font-size:10px;border-radius:8px;padding:3px 8px;background:#e8f8f3;color:#2d7a5f;border:1px solid #a8e6cf;"
            title="Mark as Hired">
            <i class="bi bi-check-circle-fill"></i>
        </button>
    </form>
    <form method="POST" action="{{ route('company.applicants.status', $app->id) }}">
        @csrf
        <input type="hidden" name="status" value="waiting">
        <button type="submit" class="btn btn-sm fw-semibold"
            style="font-size:10px;border-radius:8px;padding:3px 8px;
            {{ $app->status === 'waiting'
                ? 'background:#f59e0b;color:#fff;border:none;'
                : 'background:#fff8e1;color:#f59e0b;border:1px solid #fcd34d;' }}"
            title="Mark as Waiting">
            <i class="bi bi-hourglass-split"></i>
        </button>
    </form>
    <form method="POST" action="{{ route('company.applicants.status', $app->id) }}" class="status-form">
        @csrf
        <input type="hidden" name="status" value="rejected">
        <button type="button" class="btn btn-sm fw-semibold confirm-rejected"
            style="font-size:10px;border-radius:8px;padding:3px 8px;background:#fff5f5;color:#e05252;border:1px solid #ffcdd2;"
            title="Mark as Rejected">
            <i class="bi bi-x-circle-fill"></i>
        </button>
    </form>
</div>
@endif