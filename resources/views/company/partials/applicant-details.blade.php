{{-- ── ANG APPLICANT NGA GIHUKMAN SA EMPLOYER ──

     Kaniadto ang laray naghatag ug ngalan, numero, ug porsyento. Ang employer
     mipindot ug Hired o Rejected gikan sa usa ka numero nga walay bisan unsa
     sa likod niini — ug kana nga numero mao ang tibuok angkon sa sistema.

     Dinhi makita niya ang applicant sumala sa panan-aw sa MATCH: ang mga
     field nga gi-score, ug ang kuwenta mismo — kada criterion, pila ang
     bili niini, ug natuman ba.

     DILI ang tibuok NSRP. Ang employer naghukom kung tawgon ba siya sa
     interview, dili nagbasa sa iyang panimalay: ang civil status, dependents,
     4Ps, disability ug OFW history magpabilin sa PESO. Ang RA 10173 nangayo
     sa labing gamay nga motubag sa pangutana, ug ang criteria mao ang
     pangutana.

     Gi-include kausa kada pahina. Ang buton:
     <button type="button" class="js-applicant-details" data-id="{{ $app->job_matching_id }}">View</button> --}}

<div class="modal fade" id="applicantDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                <h6 class="modal-title text-white fw-bold">
                    <i class="ph ph-user-circle me-2"></i><span id="apdName">Applicant</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="apdBody" style="font-size:13px;color:var(--g-700);">
                <div class="text-center py-4" style="color:var(--n-500);">Loading…</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const URL_TEMPLATE = @json(route('company.applicants.details', ['id' => '__ID__']));

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    // Usa ka laray sa listahan, o walay bisan unsa kung walay sulod — ang
    // blangko nga ulohan nagsulti nga naay kulang sa sistema, dili sa tawo.
    const chips = (title, items) => {
        if (!items || !items.length) return '';
        return `<div class="mb-3">
            <div class="fw-bold mb-2" style="font-size:12px;color:var(--n-500);letter-spacing:.4px;">${esc(title)}</div>
            <div class="d-flex flex-wrap gap-1">${items.map(i =>
                `<span style="font-size:12px;background:var(--g-50,#f1fbf2);color:var(--g-700);border:1px solid var(--n-200);border-radius:999px;padding:3px 10px;">${esc(i)}</span>`
            ).join('')}</div>
        </div>`;
    };

    function render(d) {
        document.getElementById('apdName').textContent = d.name || 'Applicant';

        const facts = [
            ['Sex', d.sex], ['Age', d.age], ['Contact', d.contact],
            ['Email', d.email], ['Applied', d.applied], ['Status', d.status],
        ].filter(([, v]) => v !== null && v !== undefined && v !== '');

        // Ang kuwenta sa porsyento. Kini ang hinungdan sa tibuok kahon.
        const criteria = (d.criteria || []).map(c => {
            const met  = c.matched === true;
            const half = c.matched === 'partial';
            const mark = met ? 'ph-fill ph-check-circle' : (half ? 'ph-fill ph-minus-circle' : 'ph-fill ph-x-circle');
            const tint = met ? 'var(--g-600)' : (half ? 'var(--warn)' : 'var(--danger)');
            return `<div class="d-flex align-items-start gap-2 py-2" style="border-bottom:1px solid var(--n-200);">
                <i class="${mark}" style="color:${tint};font-size:16px;margin-top:1px;"></i>
                <div>
                    <div style="font-weight:600;">${esc(c.label)}</div>
                    ${c.note ? `<div style="font-size:12px;color:var(--n-500);">${esc(c.note)}</div>` : ''}
                </div>
            </div>`;
        }).join('');

        const education = (d.education || []).map(e =>
            `<div class="py-2" style="border-bottom:1px solid var(--n-200);">
                <div style="font-weight:600;">${esc(e.level)}</div>
                <div style="font-size:12px;color:var(--n-500);">
                    ${[e.school, e.course, e.year].filter(Boolean).map(esc).join(' · ')}
                </div>
            </div>`
        ).join('');

        const work = (d.work || []).map(w =>
            `<div class="py-2" style="border-bottom:1px solid var(--n-200);">
                <div style="font-weight:600;">${esc(w.position)}</div>
                <div style="font-size:12px;color:var(--n-500);">
                    ${[w.company, [w.from, w.to].filter(Boolean).join(' – ')].filter(Boolean).map(esc).join(' · ')}
                </div>
            </div>`
        ).join('');

        document.getElementById('apdBody').innerHTML = `
            <div class="d-flex align-items-center justify-content-between gap-3 p-3 mb-3 rounded-3"
                 style="background:var(--g-50,#f1fbf2);border:1px solid var(--n-200);">
                <div class="d-flex flex-wrap gap-3">
                    ${facts.map(([k, v]) =>
                        `<div><div style="font-size:11px;color:var(--n-500);">${esc(k)}</div>
                         <div style="font-weight:600;">${esc(v)}</div></div>`).join('')}
                </div>
                <div class="text-center">
                    <div style="font-size:28px;font-weight:800;color:var(--g-700);line-height:1;">${esc(d.percentage)}%</div>
                    <div style="font-size:11px;color:var(--n-500);">match</div>
                </div>
            </div>

            <div class="fw-bold mb-1" style="font-size:12px;color:var(--n-500);letter-spacing:.4px;">HOW THE MATCH WAS SCORED</div>
            ${criteria || '<div class="py-2" style="color:var(--n-500);">This posting asked for nothing that can be scored.</div>'}

            ${education ? `<div class="fw-bold mt-4 mb-1" style="font-size:12px;color:var(--n-500);letter-spacing:.4px;">EDUCATIONAL BACKGROUND</div>${education}` : ''}
            ${work ? `<div class="fw-bold mt-4 mb-1" style="font-size:12px;color:var(--n-500);letter-spacing:.4px;">WORK EXPERIENCE</div>${work}` : ''}

            <div class="mt-4">
                ${chips('PREFERRED OCCUPATION', d.preferred_occupations)}
                ${chips('LANGUAGE', d.languages)}
                ${chips('SKILLS', d.skills)}
                ${chips('ELIGIBILITY / LICENSE', d.eligibilities)}
                ${chips('TRAINING', d.training)}
            </div>

            <div class="mt-3 p-2 rounded-3" style="background:var(--n-50,#f7f7f7);font-size:11.5px;color:var(--n-500);">
                Shown here: only what the match is scored on. The rest of this
                applicant's PESO registration stays with the office.
            </div>`;
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-applicant-details');
        if (!btn) return;

        const box = document.getElementById('applicantDetailsModal');
        document.getElementById('apdName').textContent = 'Applicant';
        document.getElementById('apdBody').innerHTML =
            '<div class="text-center py-4" style="color:var(--n-500);">Loading…</div>';
        bootstrap.Modal.getOrCreateInstance(box).show();

        fetch(URL_TEMPLATE.replace('__ID__', encodeURIComponent(btn.dataset.id)), {
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j.error || 'Could not load.')))
        .then(render)
        .catch(msg => {
            document.getElementById('apdBody').innerHTML =
                `<div class="text-center py-4" style="color:var(--danger);">${esc(msg)}</div>`;
        });
    });
})();
</script>
@endpush
