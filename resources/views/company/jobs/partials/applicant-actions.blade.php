@php
    $isCompanyInterview = ($job->schedule_type ?? null) === 'company_interview';
    $isDeclined = ($isInhouse && $app->inhouse_participation === 'declined')
        || ($isCompanyInterview && $app->company_interview_participation === 'declined');
    $isPending  = ($isInhouse && $app->inhouse_participation === 'pending')
        || ($isCompanyInterview && $app->company_interview_participation === 'pending');
    $isExpired  = $job->is_expired ?? false;
    $isLocked   = ($isInhouse || $isCompanyInterview) && (!$actionsUnlocked || $isExpired);
@endphp

{{-- A hired or rejected applicant is NOT taken out of these buttons. Someone
     hired can fail to show up, and the employer has to be able to say so. The
     current choice is the one filled in solid; the other two stay clickable. --}}
@if($isDeclined)
    <span style="font-size:11px;color:var(--danger);"><i class="ph ph-x-circle me-1"></i>Declined {{ $isCompanyInterview ? 'job offer' : 'interview' }}</span>
@elseif($isPending)
    <span style="font-size:11px;color:var(--warn);"><i class="ph ph-hourglass-medium me-1"></i>Awaiting participation response</span>
@elseif($isExpired)
    <span style="font-size:11px;color:var(--danger);"><i class="ph ph-calendar-x me-1"></i>Job posting expired</span>
@elseif($isLocked)
    <span style="font-size:11px;color:var(--n-500);"><i class="ph ph-clock me-1"></i>Locked until interview date</span>
@else
{{-- The word is on the button, not only in the tooltip. A tooltip needs a mouse
     and a pause to find, and the office staff using this cannot be asked to
     hover three icons to learn what they do. --}}
<div class="d-flex gap-1 justify-content-center">
    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}" class="status-form">
        @csrf
        <input type="hidden" name="status" value="hired">
        <button type="button" class="btn btn-sm fw-semibold confirm-hired"
            style="font-size:11px;border-radius:8px;padding:4px 10px;white-space:nowrap;
            {{ $app->status === 'hired'
                ? 'background:var(--g-600);color:#fff;border:none;'
                : 'background:var(--g-50);color:var(--g-700);border:1px solid var(--n-200);' }}">
            <i class="ph-fill ph-check-circle me-1"></i>Hired
        </button>
    </form>
    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}">
        @csrf
        <input type="hidden" name="status" value="waiting">
        <button type="submit" class="btn btn-sm fw-semibold"
            style="font-size:11px;border-radius:8px;padding:4px 10px;white-space:nowrap;
            {{ $app->status === 'waiting'
                ? 'background:var(--warn);color:#fff;border:none;'
                : 'background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-br);' }}">
            <i class="ph ph-hourglass-medium me-1"></i>Waiting
        </button>
    </form>
    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}" class="status-form">
        @csrf
        <input type="hidden" name="status" value="rejected">
        <button type="button" class="btn btn-sm fw-semibold confirm-rejected"
            style="font-size:11px;border-radius:8px;padding:4px 10px;white-space:nowrap;
            {{ $app->status === 'rejected'
                ? 'background:var(--danger);color:#fff;border:none;'
                : 'background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);' }}">
            <i class="ph-fill ph-x-circle me-1"></i>Rejected
        </button>
    </form>
</div>
@endif
