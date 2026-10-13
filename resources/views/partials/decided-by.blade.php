{{--
    Who answered this, under the word that says it was answered.

    PESO, 2026-10-13: a desk can be two people. Two SRAs, two LRAs — the admin's
    Add Staff form has counted staff per role since 2026-08-26 for exactly that
    reason. Every screen said "Approved", "Accepted", "Confirmed", which names
    the desk and not the person, so where two hold one seat neither could tell
    whether the other had already dealt with it.

    One partial, so a decision reads the same wherever it is shown.

    $staff  the Staff model that decided, or null
    $verb   'Approved', 'Accepted', 'Rejected', 'Decided' …
    $at     optional timestamp of the decision
    $muted  true for the small grey line under a badge (the default), false for
            a full line in a detail table
--}}
@php
    $decidedVerb  = $verb  ?? 'Decided';
    $decidedAt    = $at    ?? null;
    $decidedMuted = $muted ?? true;
    $decidedName  = $staff
        ? (trim($staff->full_name ?? '') ?: optional($staff->user)->name)
        : null;
@endphp

@if($decidedMuted)
    <div style="font-size:10.5px;color:var(--n-500);margin-top:2px;">
        {{-- Nothing recorded is said plainly. The decisions made before the
             column existed have no honest answer, and a blank cell reads as a
             page fault rather than as history. --}}
        {{ strtolower($decidedVerb) }} by {{ $decidedName ?: 'PESO staff (not recorded)' }}
        @if($decidedAt)
            <span style="color:var(--n-400);">· {{ $decidedAt->format('M d, Y') }}</span>
        @endif
    </div>
@else
    <tr>
        <td class="fw-semibold" style="color:var(--g-700);">{{ $decidedVerb }} by</td>
        <td>
            {{ $decidedName ?: 'PESO staff (not recorded)' }}
            @if($decidedAt)
                <span style="color:var(--n-500);font-size:11.5px;">on {{ $decidedAt->format('M d, Y') }}</span>
            @endif
        </td>
    </tr>
@endif
