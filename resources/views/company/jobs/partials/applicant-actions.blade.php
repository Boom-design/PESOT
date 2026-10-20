@php
    $isCompanyInterview = ($job->schedule_type ?? null) === 'company_interview';
    $participation = $isInhouse
        ? $app->inhouse_participation
        : ($isCompanyInterview ? $app->company_interview_participation : null);
    $isDeclined = $participation === 'declined';
    // Withdrew after joining. A separate word from Declined, because the two
    // mean different things to an employer planning a venue: one never came
    // in, the other was counted and then left.
    $isCancelled = $participation === \App\Models\Application::CANCELLED;
    $isPending   = $participation === 'pending';
    $isExpired  = $job->is_expired ?? false;
    $isLocked   = ($isInhouse || $isCompanyInterview) && (!$actionsUnlocked || $isExpired);
@endphp

{{-- A hired or rejected applicant is NOT taken out of these buttons. Someone
     hired can fail to show up, and the employer has to be able to say so. The
     current choice is the one filled in solid; the other two stay clickable. --}}
@if($isDeclined)
    <span style="font-size:11px;color:var(--danger);"><i class="ph ph-x-circle me-1"></i>Declined {{ $isCompanyInterview ? 'job offer' : 'interview' }}</span>
@elseif($isCancelled)
    <span style="font-size:11px;color:var(--danger);"><i class="ph ph-sign-out me-1"></i>Withdrew from {{ $isCompanyInterview ? 'company interview' : 'interview' }}</span>
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
{{-- One line, never two.

     Three buttons at min-width:98px need about 300px and the column is
     narrower than that, so flex-wrap dropped Rejected onto a line of its own —
     the three choices stopped reading as three choices. They share the cell
     equally now and the cell is told not to wrap, so the column takes the
     width the row needs. --}}
<div class="d-flex gap-1 flex-nowrap justify-content-center applicant-actions">
    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}" class="status-form">
        @csrf
        <input type="hidden" name="status" value="hired">
        <input type="hidden" name="start_date" value="{{ $app->start_date?->toDateString() }}">
        @include('company.partials.hire-start-date')
        <button type="button" class="btn btn-sm fw-semibold confirm-hired"
            data-today="{{ now()->toDateString() }}"
            style="font-size:11px;border-radius:8px;padding:4px 6px;width:100%;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;
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
            style="font-size:11px;border-radius:8px;padding:4px 6px;width:100%;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;
            {{ $app->status === 'waiting'
                ? 'background:var(--warn);color:#fff;border:none;'
                : 'background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-br);' }}">
            <i class="ph ph-hourglass-medium me-1"></i>On Process
        </button>
    </form>
    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}" class="status-form">
        @csrf
        <input type="hidden" name="status" value="rejected">
        <button type="button" class="btn btn-sm fw-semibold confirm-rejected"
            style="font-size:11px;border-radius:8px;padding:4px 6px;width:100%;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;
            {{ $app->status === 'rejected'
                ? 'background:var(--danger);color:#fff;border:none;'
                : 'background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);' }}">
            <i class="ph-fill ph-x-circle me-1"></i>Rejected
        </button>
    </form>
</div>
@endif

@once
<style>
    /* The three forms are the flex children, not the buttons, so the width has
       to be shared here. Equal basis keeps them the same size whichever one is
       filled in solid. */
    .applicant-actions > form { flex: 1 1 0; min-width: 0; }
    .applicant-actions { min-width: 268px; }
</style>
@endonce
