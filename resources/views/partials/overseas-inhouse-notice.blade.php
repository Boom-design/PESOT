{{--
    An overseas agency must bring the hard copies for an in-house interview.

    PESO Project Manager, 2026-08-26: an overseas agency that wants to hold an
    in-house interview — at the PESO office or at its own venue — has to appear
    in person with the original requirements. Everything may already have been
    uploaded; the online copies are not what approves the schedule. The rule
    holds even when the whole registration was done online.

    Written once and included wherever the request is made or reviewed, so the
    employer reading it and the SRA staff enforcing it are told the same thing.

    Pass `noticeOverseas` to say whether this employer is overseas, and
    `noticeAudience` => 'staff' for the desk's wording. Left alone, the notice
    works out the employer from the signed-in user and speaks to them.
--}}
@php
    $noticeIsOverseas = $noticeOverseas
        ?? (bool) (auth()->user()?->employerNsrp?->is_overseas ?? false);
    $noticeForStaff   = ($noticeAudience ?? 'employer') === 'staff';
@endphp

@if($noticeIsOverseas)
<div class="p-3 rounded-3 mb-3"
     style="background:var(--warn-bg);border:1px solid var(--warn-br);">
    <div class="d-flex gap-2">
        <i class="ph-fill ph-files flex-shrink-0" style="color:var(--warn);font-size:18px;margin-top:1px;"></i>
        <div style="font-size:12.5px;color:var(--n-700);line-height:1.6;">
            @if($noticeForStaff)
                <strong style="color:var(--warn);">Overseas agency — hard copies required.</strong><br>
                Accept this schedule only once the agency has presented the original
                requirements at the PESO office in person. An overseas in-house interview
                is not approved on the uploaded copies alone, even when the agency
                registered and submitted everything online.
            @else
                <strong style="color:var(--warn);">Bring the hard copies to the PESO office.</strong><br>
                Go to the PESO office and present the original copies of your requirements.
                An overseas agency's in-house schedule is approved only after that visit —
                the copies you uploaded are not enough on their own, even if you registered
                and submitted everything online. This applies whether the interview is held
                at the PESO office or at your own venue.
            @endif
        </div>
    </div>
</div>
@endif
