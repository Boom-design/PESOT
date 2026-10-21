{{--
    The counter's upload step follows the employer type.

    Same job as partials/registration-documents, and not the same script: the
    counter's boxes are all required, so hiding one is not enough. A hidden
    input that is still required cannot be focused, and the browser refuses to
    submit the form while saying nothing a person can act on — the page simply
    stops working. Each box gives up its `required` on the way out and takes it
    back on the way in.

    At the SRA and Job Vacancy counters the type is fixed to one option, so
    this settles once on load and never runs again. The Job Fair desk takes
    both markets and chooses in the form, which is why the script is here.
--}}
<script>
(function () {
    const AGENCY_TYPE = 'Overseas Recruitment Agency';

    const docs  = document.querySelectorAll('.walkin-doc');
    const types = document.querySelectorAll('input[name="employer_type"]');
    if (!docs.length || !types.length) return;

    const permitBox  = document.querySelector('.walkin-doc input[name="business_permit"]');
    const permitDate = document.querySelector('input[name="business_permit_expires_at"]');
    const permitLbl  = permitBox ? permitBox.closest('.walkin-doc').querySelector('.peso-label') : null;

    function labelPermit() {
        if (!permitLbl || !permitDate) return;

        const year = (permitDate.value || '').slice(0, 4);
        for (const node of permitLbl.childNodes) {
            if (node.nodeType === Node.TEXT_NODE && node.textContent.includes('Business Permit')) {
                node.textContent = 'CDO Business Permit' + (year.length === 4 ? ' ' + year : '') + ' *';
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

            // A hidden required input is a form that will not submit and will
            // not say why, so the flag travels with the box.
            for (const input of doc.querySelectorAll('input')) {
                if (show) {
                    if (input.dataset.wasRequired === '1') input.required = true;
                } else {
                    input.dataset.wasRequired = input.required ? '1' : '0';
                    input.required = false;
                }
            }

            if (!show) continue;

            const number = doc.querySelector('.walkin-doc-no');
            if (number) number.textContent = (++n) + '. ';
        }

        labelPermit();
    }

    types.forEach(t => t.addEventListener('change', apply));
    if (permitDate) permitDate.addEventListener('change', labelPermit);

    apply();
})();
</script>
