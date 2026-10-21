{{--
    The document list follows the employer type.

    PESO SRA, 2026-10-21: an overseas recruitment agency is accredited on its
    own papers — agency profile, certificate of renewal of licence, the DMW
    job order balance, the branch organisational chart and the authority to
    operate a branch office — and has no SEC/DTI or Vacancy Posting to give.
    Until now every employer was shown the local five.

    The employer type is chosen on this same page, so both lists are in the
    markup and this shows one of them. Every file field is optional at
    registration, and a hidden field simply arrives empty; nothing is rejected
    for being invisible.

    The numbering is written here rather than by the loop, because the numbers
    have to close up when half the boxes are hidden — a list that reads 1, 2,
    6, 7 is a list with something missing.

    The business permit's label carries the year, read from the date beside
    it. The office writes "BUSINESS PERMIT 202_" on its own slip and fills the
    blank in by hand; the page fills it in from the date so the two always
    agree. The date starts on the 31st of December of the current year and is
    typed over when the permit says otherwise.
--}}
<script>
(function () {
    const AGENCY_TYPE = 'Overseas Recruitment Agency';

    const docs  = document.querySelectorAll('.reg-doc');
    const types = document.querySelectorAll('input[name="employer_type"]');
    const line  = document.getElementById('docCountLine');
    if (!docs.length || !types.length) return;

    const permit     = document.querySelector('.reg-doc input[name="business_permit"]');
    const permitBox  = permit ? permit.closest('.reg-doc') : null;
    const permitDate = document.querySelector('input[name="business_permit_expires_at"]');
    const permitText = permitBox ? permitBox.querySelector('.peso-label') : null;
    const permitName = permitText ? permitText.childNodes : null;

    function labelPermit() {
        if (!permitText || !permitDate) return;

        // The label is "<no>. CDO Business Permit <year> (to be followed)".
        // Only the bare text node in the middle is rewritten, so the number
        // span and the grey note beside it are left alone.
        const year = (permitDate.value || '').slice(0, 4);
        for (const node of permitName) {
            if (node.nodeType === Node.TEXT_NODE && node.textContent.includes('Business Permit')) {
                node.textContent = 'CDO Business Permit' + (year.length === 4 ? ' ' + year : '') + ' ';
                return;
            }
        }
    }

    function apply() {
        const agency = [...types].some(t => t.checked && t.value === AGENCY_TYPE);
        const wanted = agency ? 'overseas' : 'local';

        let n = 0;
        for (const doc of docs) {
            const market = doc.dataset.market;
            const show   = market === 'both' || market === wanted;

            doc.hidden = !show;
            if (!show) continue;

            const number = doc.querySelector('.reg-doc-no');
            if (number) number.textContent = (++n) + '. ';
        }

        // The logo is in the count above but is not a required document, so
        // the sentence counts one fewer than the boxes on screen.
        if (line) {
            const required = Math.max(n - 1, 0);
            line.innerHTML = 'All <strong style="color:#fff;">' + required
                + ' documents</strong> are required';
        }

        labelPermit();
    }

    types.forEach(t => t.addEventListener('change', apply));
    if (permitDate) permitDate.addEventListener('change', labelPermit);

    // A validation bounce comes back with the type already chosen.
    apply();
})();
</script>
