{{--
    An overseas recruitment agency's industry group picks itself.

    PESO, 2026-10-20: "Overseas Manpower Services" used to be an entry in the
    Major Industry Group list, and an agency picked it without thinking. The
    list is PSIC Revision 5 now, and the PSA has no such group — the work is
    sub-class 78102, under Division 78 of Section O, Administrative and
    Support Service Activities. Right, and not something an agency arriving at
    the form would guess from the twenty-two section names.

    So the form answers it for them. Choosing Overseas Recruitment Agency as
    the employer type selects Section O and closes the other options; choosing
    any other type opens them again. The select stays enabled — only the
    options are disabled — because a disabled select submits nothing, and the
    field is required.

    It never clears a choice the person made. Switching away from the agency
    type leaves Section O selected if that is where they were; it only stops
    being the only option.

    Expects $selectId: the id on the industry_group select. The employer type
    is read by name, which is employer_type on both forms.
--}}
@php
    $agencyType  = 'Overseas Recruitment Agency';
    $agencyGroup = 'Administrative and Support Service Activities';
@endphp

<script>
(function () {
    const AGENCY_TYPE  = @json($agencyType);
    const AGENCY_GROUP = @json($agencyGroup);

    const select = document.getElementById(@json($selectId));
    const types  = document.querySelectorAll('input[name="employer_type"]');
    if (!select || !types.length) return;

    const note = document.createElement('div');
    note.style.cssText = 'font-size:11px;margin-top:5px;display:none;';
    note.className = 'industry-locked-note';
    note.textContent = 'An overseas recruitment agency is PSIC sub-class 78102, '
        + 'under Section O. Chosen for you.';
    select.parentNode.appendChild(note);

    function apply() {
        const agency = [...types].some(t => t.checked && t.value === AGENCY_TYPE);

        if (agency) {
            select.value = AGENCY_GROUP;
        }

        // The options close rather than the select, so the chosen value is
        // still submitted. A placeholder option is left alone: it is already
        // disabled and never a valid answer.
        for (const option of select.options) {
            if (option.value === '' || option.value === AGENCY_GROUP) continue;
            option.disabled = agency;
        }

        note.style.display = agency ? '' : 'none';
    }

    types.forEach(t => t.addEventListener('change', apply));

    // A validation bounce comes back with the agency type already checked.
    apply();
})();
</script>
